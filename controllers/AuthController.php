<?php

class AuthController
{
    public function inscription(): void
    {
        if (utilisateur_connecte()) {
            redirect('mes_commandes');
        }

        $erreurs = [];
        $donnees = ['prenom' => '', 'nom' => '', 'email' => '', 'telephone' => ''];

        if (est_post()) {
            csrf_verifier();

            foreach ($donnees as $champ => $valeur) {
                $donnees[$champ] = trim($_POST[$champ] ?? '');
            }
            $donnees['email'] = mb_strtolower($donnees['email']); // même format qu'à la connexion
            $motDePasse = $_POST['mot_de_passe'] ?? '';
            $confirmation = $_POST['confirmation'] ?? '';

            if ($donnees['prenom'] === '') {
                $erreurs['prenom'] = 'Emri është i detyrueshëm.';
            }
            if ($donnees['nom'] === '') {
                $erreurs['nom'] = 'Mbiemri është i detyrueshëm.';
            }
            if (!filter_var($donnees['email'], FILTER_VALIDATE_EMAIL)) {
                $erreurs['email'] = 'Adresa e emailit nuk është e vlefshme.';
            } elseif (Utilisateur::trouverParEmail($donnees['email'])) {
                $erreurs['email'] = 'Ekziston tashmë një llogari me këtë email.';
            }
            if (strlen($motDePasse) < 8) {
                $erreurs['mot_de_passe'] = 'Fjalëkalimi duhet të ketë së paku 8 karaktere.';
            } elseif ($motDePasse !== $confirmation) {
                $erreurs['confirmation'] = 'Fjalëkalimet nuk përputhen.';
            }

            if (empty($erreurs)) {
                $id = Utilisateur::creer($donnees['prenom'], $donnees['nom'], $donnees['email'], $motDePasse, $donnees['telephone']);
                $this->ouvrirSession(['id' => $id] + $donnees);
                flash('succes', 'Mirë se vini, ' . $donnees['prenom'] . '! Llogaria juaj u krijua.');
                redirect('home');
            }
        }

        render('inscription', ['titre' => 'Regjistrimi', 'donnees' => $donnees, 'erreurs' => $erreurs]);
    }

    public function connexion(): void
    {
        if (utilisateur_connecte()) {
            redirect('mes_commandes');
        }

        $erreur = null;
        $email = '';

        if (est_post()) {
            csrf_verifier();

            $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';

            // Anti-force brute : après 5 erreurs en 15 min, on bloque avant même de vérifier le mot de passe
            if (Utilisateur::tropDEchecs($email, $ip)) {
                $erreur = 'Shumë përpjekje të gabuara. Provoni përsëri pas 15 minutash.';
            } else {
                $utilisateur = Utilisateur::trouverParEmail($email);

                if ($utilisateur && password_verify((string) ($_POST['mot_de_passe'] ?? ''), $utilisateur['mot_de_passe'])) {
                    Utilisateur::effacerEchecs($email);
                    $this->ouvrirSession($utilisateur);
                    flash('succes', 'Mirë se u kthyet, ' . $utilisateur['prenom'] . '!');
                    redirect('home');
                }
                Utilisateur::noterEchec($email, $ip);
                $erreur = 'Email ose fjalëkalim i pasaktë.';
            }
        }

        render('connexion', ['titre' => 'Kyçja', 'email' => $email, 'erreur' => $erreur]);
    }

    public function deconnexion(): void
    {
        if (est_post()) {
            csrf_verifier();
            unset($_SESSION['utilisateur'], $_SESSION['derniere_commande']);
            session_regenerate_id(true);
            flash('succes', 'Jeni çkyçur.');
        }
        redirect('home');
    }

    // On garde en session uniquement ce qui est utile (jamais le mot de passe)
    private function ouvrirSession(array $utilisateur): void
    {
        session_regenerate_id(true);
        $_SESSION['utilisateur'] = [
            'id'        => (int) $utilisateur['id'],
            'prenom'    => $utilisateur['prenom'],
            'nom'       => $utilisateur['nom'],
            'email'     => $utilisateur['email'],
            'telephone' => $utilisateur['telephone'] ?? '',
            'est_admin' => (int) ($utilisateur['est_admin'] ?? 0),
        ];
    }
}
