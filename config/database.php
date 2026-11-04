<?php
// Connexion à la base de données avec PDO.
// Réglages de Laragon par défaut ; en ligne, config/production.php les définit avant.

defined('DB_HOST') || define('DB_HOST', 'localhost');
defined('DB_NAME') || define('DB_NAME', 'dripfit');   // attention : "dripfit", pas "dripfit.ks"
defined('DB_USER') || define('DB_USER', 'root');
defined('DB_PASS') || define('DB_PASS', '');

class Database
{
    private static ?PDO $connexion = null;

    public static function getConnexion(): PDO
    {
        if (self::$connexion === null) {
            try {
                self::$connexion = new PDO(
                    'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                    DB_USER,
                    DB_PASS,
                    [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                    ]
                );
            } catch (PDOException $e) {
                http_response_code(500);
                if (EN_PRODUCTION) {
                    error_log('Connexion BDD impossible : ' . $e->getMessage());
                    die('Faqja nuk është e disponueshme për momentin. Provoni përsëri pas pak minutash.');
                }
                die('Connexion à la base de données impossible. Vérifie que MySQL est démarré dans Laragon et que la base "' . DB_NAME . '" existe (importe database/dripfit.sql).');
            }
        }
        return self::$connexion;
    }
}
