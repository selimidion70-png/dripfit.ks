<?php

class Utilisateur
{
    public static function trouverParEmail(string $email): ?array
    {
        $pdo = Database::getConnexion();
        $requete = $pdo->prepare('SELECT * FROM utilisateurs WHERE email = ?');
        $requete->execute([$email]);
        return $requete->fetch() ?: null;
    }

    // Crée le compte et renvoie son id. Le mot de passe est haché (jamais stocké en clair).
    public static function creer(string $prenom, string $nom, string $email, string $motDePasse, ?string $telephone): int
    {
        $pdo = Database::getConnexion();
        $requete = $pdo->prepare(
            'INSERT INTO utilisateurs (prenom, nom, email, mot_de_passe, telephone) VALUES (?, ?, ?, ?, ?)'
        );
        $requete->execute([
            $prenom,
            $nom,
            $email,
            password_hash($motDePasse, PASSWORD_DEFAULT),
            $telephone ?: null,
        ]);
        return (int) $pdo->lastInsertId();
    }

    // ---------- Limite des tentatives de connexion ----------
    // On compte avec NOW() de MySQL pour ne pas dépendre du fuseau horaire de PHP.

    // Trop d'échecs récents pour cet email, ou depuis cette adresse IP ?
    // (limite IP plus haute : au Kosovo, beaucoup de clients mobiles partagent la même IP)
    public static function tropDEchecs(string $email, string $ip, int $minutes = 15): bool
    {
        $pdo = Database::getConnexion();
        $requete = $pdo->prepare(
            'SELECT
                SUM(email = ?) AS par_email,
                SUM(ip = ?)    AS par_ip
             FROM connexion_echecs
             WHERE created_at > NOW() - INTERVAL ? MINUTE'
        );
        $requete->execute([$email, $ip, $minutes]);
        $nb = $requete->fetch();
        return (int) $nb['par_email'] >= 5 || (int) $nb['par_ip'] >= 20;
    }

    public static function noterEchec(string $email, string $ip): void
    {
        $pdo = Database::getConnexion();
        $requete = $pdo->prepare('INSERT INTO connexion_echecs (email, ip) VALUES (?, ?)');
        $requete->execute([mb_substr($email, 0, 150), $ip]);

        // Ménage : on ne garde pas les vieux échecs
        $pdo->exec('DELETE FROM connexion_echecs WHERE created_at < NOW() - INTERVAL 1 DAY');
    }

    public static function effacerEchecs(string $email): void
    {
        $pdo = Database::getConnexion();
        $requete = $pdo->prepare('DELETE FROM connexion_echecs WHERE email = ?');
        $requete->execute([$email]);
    }
}
