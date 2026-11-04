<?php

class CommandeController
{
    // Formulaire de livraison (GET) et enregistrement de la commande (POST)
    public function formulaire(): void
    {
        [$lignes, $sousTotal] = PanierController::contenu();

        if (empty($lignes)) {
            flash('erreur', 'Shporta juaj është e zbrazët.');
            redirect('home');
        }

        $utilisateur = utilisateur_connecte();
        $erreurs = [];
        $client = [
            'prenom'    => $utilisateur['prenom'] ?? '',
            'nom'       => $utilisateur['nom'] ?? '',
            'telephone' => $utilisateur['telephone'] ?? '',
            'email'     => $utilisateur['email'] ?? '',
            'adresse'   => '',
            'ville'     => '',
            'pays'      => 'Kosovë',
        ];

        if (est_post()) {
            csrf_verifier();

            foreach ($client as $champ => $valeur) {
                $client[$champ] = trim($_POST[$champ] ?? '');
            }

            // Validation
            foreach (['prenom' => 'Emri është i detyrueshëm.', 'nom' => 'Mbiemri është i detyrueshëm.', 'adresse' => 'Adresa është e detyrueshme.', 'ville' => 'Qyteti është i detyrueshëm.'] as $champ => $message) {
                if ($client[$champ] === '') {
                    $erreurs[$champ] = $message;
                }
            }
            if (!preg_match('/^\+?[0-9 ]{8,20}$/', $client['telephone'])) {
                $erreurs['telephone'] = 'Numri i telefonit nuk është i vlefshëm (vetëm shifra, me ose pa +).';
            }
            if ($client['email'] !== '' && !filter_var($client['email'], FILTER_VALIDATE_EMAIL)) {
                $erreurs['email'] = 'Adresa e emailit nuk është e vlefshme.';
            }
            if (!array_key_exists($client['pays'], PAYS_LIVRAISON)) {
                $erreurs['pays'] = 'Zgjidhni shtetin e dorëzimit.';
            }
            foreach ($lignes as $ligne) {
                if ($ligne['quantite'] > $ligne['stock']) {
                    $erreurs['stock'] = 'Nuk ka stok të mjaftueshëm për ' . $ligne['nom'] . ' (' . $ligne['taille'] . ', ' . $ligne['couleur'] . '). Ndryshoni shportën.';
                }
            }

            if (empty($erreurs)) {
                $total = $sousTotal + PAYS_LIVRAISON[$client['pays']];
                try {
                    $commandeId = Commande::creer($client, $lignes, $total, $utilisateur['id'] ?? null);
                    Notification::nouvelleCommande($commandeId); // email au cousin (un échec ne bloque pas la commande)
                    $_SESSION['panier'] = [];
                    $_SESSION['derniere_commande'] = $commandeId; // autorise l'affichage de la confirmation
                    redirect('confirmation', ['id' => $commandeId]);
                } catch (RuntimeException $e) {
                    $erreurs['stock'] = $e->getMessage();
                }
            }
        }

        render('commande', [
            'titre'     => 'Porosia',
            'lignes'    => $lignes,
            'sousTotal' => $sousTotal,
            'client'    => $client,
            'erreurs'   => $erreurs,
        ]);
    }

    public function confirmation(): void
    {
        $commande = Commande::trouver((int) ($_GET['id'] ?? 0));

        if (!$commande || !$this->peutVoir($commande)) {
            http_response_code(404);
            render('erreur404');
            return;
        }

        render('confirmation', [
            'titre'    => 'Porosia u konfirmua',
            'commande' => $commande,
            'details'  => Commande::details((int) $commande['id']),
        ]);
    }

    // Historique des commandes du client connecté
    public function historique(): void
    {
        $utilisateur = utilisateur_connecte();
        if (!$utilisateur) {
            flash('erreur', 'Kyçuni për të parë porositë tuaja.');
            redirect('connexion');
        }

        $commandes = Commande::parUtilisateur((int) $utilisateur['id']);
        foreach ($commandes as &$commande) {
            $commande['details'] = Commande::details((int) $commande['id']);
        }
        unset($commande);

        render('mes_commandes', [
            'titre'     => 'Porositë e mia',
            'commandes' => $commandes,
        ]);
    }

    // Une commande n'est visible que par son propriétaire, ou juste après l'avoir passée
    private function peutVoir(array $commande): bool
    {
        $utilisateur = utilisateur_connecte();
        if ($utilisateur && (int) $commande['utilisateur_id'] === (int) $utilisateur['id']) {
            return true;
        }
        return (int) ($_SESSION['derniere_commande'] ?? 0) === (int) $commande['id'];
    }
}
