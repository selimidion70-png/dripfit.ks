<?php

// Emails envoyés par le site.
// En local, Laragon les attrape dans Mailpit (http://localhost:8025) : rien ne part vraiment.
class Notification
{
    /**
     * Prévient le cousin qu'une commande vient d'arriver.
     * Renvoie false si l'envoi a échoué : la commande reste enregistrée quand même,
     * elle sera visible dans l'admin.
     */
    public static function nouvelleCommande(int $commandeId): bool
    {
        $commande = Commande::trouver($commandeId);
        if (!$commande || EMAIL_COMMANDES === '') {
            return false;
        }

        $lignes = [];
        foreach (Commande::details($commandeId) as $d) {
            $lignes[] = '- ' . $d['quantite'] . ' × ' . $d['nom_produit'] . ' (' . $d['couleur'] . ', ' . $d['taille'] . ') = ' . prix($d['prix_unitaire'] * $d['quantite']);
        }

        // Le texte de l'email (en albanais, c'est le cousin qui le lit)
        $message = 'Porosi e re nr. ' . $commandeId . "\n\n"
            . 'Klienti: ' . $commande['prenom'] . ' ' . $commande['nom'] . "\n"
            . 'Telefoni: ' . $commande['telephone'] . "\n"
            . ($commande['email'] ? 'Email: ' . $commande['email'] . "\n" : '')
            . 'Adresa: ' . $commande['adresse'] . ', ' . $commande['ville'] . ', ' . $commande['pays'] . "\n\n"
            . "Artikujt:\n" . implode("\n", $lignes) . "\n\n"
            . 'Totali për t\'u arkëtuar: ' . prix($commande['total']) . "\n\n"
            . "Telefononi klientin për ta konfirmuar, pastaj ndryshoni statusin këtu:\n"
            . url_absolue('admin_commande', ['id' => $commandeId]) . "\n";

        // Sujet : seulement des valeurs sûres (numéro + total), jamais un texte tapé par le client,
        // car un retour à la ligne dans un en-tête permettrait d'ajouter d'autres destinataires
        $sujet = mb_encode_mimeheader('Porosi e re nr. ' . $commandeId . ' — ' . prix($commande['total']), 'UTF-8');

        $entetes = [
            'From'         => mb_encode_mimeheader(NOM_SITE, 'UTF-8') . ' <' . EMAIL_EXPEDITEUR . '>',
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Transfer-Encoding' => '8bit',
        ];
        // "Répondre" écrit directement au client (son email a été vérifié avec filter_var)
        if ($commande['email']) {
            $entetes['Reply-To'] = $commande['email'];
        }

        // Le @ cache l'avertissement PHP si le serveur mail ne répond pas ; on le note dans le journal
        $envoye = @mail(EMAIL_COMMANDES, $sujet, $message, $entetes);
        if (!$envoye) {
            error_log('Email de la commande ' . $commandeId . ' non envoyé à ' . EMAIL_COMMANDES);
        }
        return $envoye;
    }
}
