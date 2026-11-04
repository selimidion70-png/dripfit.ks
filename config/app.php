<?php
// Réglages du site + petites fonctions utiles partout

// Valeurs par défaut pour Laragon. En ligne, config/production.php les définit avant
// (voir config/production.exemple.php).
defined('EN_PRODUCTION')    || define('EN_PRODUCTION', false);
defined('EMAIL_COMMANDES')  || define('EMAIL_COMMANDES', 'cousin@exemple.com'); // en local, arrive dans Mailpit
defined('EMAIL_EXPEDITEUR') || define('EMAIL_EXPEDITEUR', 'porosi@dripfit.ks.test');

// En ligne : les erreurs PHP ne s'affichent plus aux clients,
// elles sont écrites dans logs/erreurs.log à la place
if (EN_PRODUCTION) {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', __DIR__ . '/../logs/erreurs.log');
} else {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

// Le visiteur est-il en https ? (certains hébergeurs passent par un proxy qui
// l'indique dans X-Forwarded-Proto au lieu de $_SERVER['HTTPS'])
function connexion_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
}

define('NOM_SITE', 'DripFit.ks');

// Contact affiché en bas de chaque page (laisser vide = pas affiché)
// TODO : demander au cousin son numéro WhatsApp/Viber et ses comptes.
const CONTACT_TELEPHONE = '';   // format international, ex : '+383 44 123 456'
const LIEN_INSTAGRAM    = '';   // ex : 'https://www.instagram.com/dripfit.ks'
const LIEN_TIKTOK       = '';   // ex : 'https://www.tiktok.com/@dripfit.ks'
define('DEVISE', '€');

// Pays de livraison et frais de livraison (en euros).
// TODO : mettre les vrais frais décidés avec ton cousin (0 = gratuit).
const PAYS_LIVRAISON = [
    'Kosovë'             => 0.00,
    'Shqipëri'           => 0.00,
    'Maqedonia e Veriut' => 0.00,
];

const STATUTS_COMMANDE = [
    'en_attente' => 'Në pritje',
    'confirmee'  => 'E konfirmuar',
    'expediee'   => 'E dërguar',
    'livree'     => 'E dorëzuar',
    'annulee'    => 'E anuluar',
];

// Protège l'affichage contre les failles XSS
function e(?string $texte): string
{
    return htmlspecialchars($texte ?? '', ENT_QUOTES, 'UTF-8');
}

// Construit une URL du site : url('produit', ['id' => 3]) -> index.php?page=produit&id=3
function url(string $page = 'home', array $params = []): string
{
    return 'index.php?' . http_build_query(['page' => $page] + $params);
}

// URL complète avec le domaine (pour les liens dans les emails)
function url_absolue(string $page = 'home', array $params = []): string
{
    $dossier = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    return (connexion_https() ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $dossier . '/' . url($page, $params);
}

function redirect(string $page = 'home', array $params = []): void
{
    header('Location: ' . url($page, $params));
    exit;
}

function prix(float|string $montant): string
{
    return number_format((float) $montant, 2, ',', ' ') . ' ' . DEVISE;
}

// Message affiché une seule fois après une redirection
function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

// Jeton CSRF : protège les formulaires POST
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_champ(): string
{
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function csrf_verifier(): void
{
    // Jeton vide en session = aucun formulaire affiché : on refuse aussi
    $jeton = $_SESSION['csrf'] ?? '';
    $envoye = $_POST['csrf'] ?? '';
    if ($jeton === '' || !is_string($envoye) || !hash_equals($jeton, $envoye)) {
        http_response_code(403);
        die('Formulari ka skaduar. Kthehuni mbrapa, rifreskoni faqen dhe provoni përsëri.');
    }
}

function est_post(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function utilisateur_connecte(): ?array
{
    return $_SESSION['utilisateur'] ?? null;
}

function est_admin(): bool
{
    return !empty($_SESSION['utilisateur']['est_admin']);
}

// À appeler au début de chaque page admin : bloque les visiteurs et les clients
function admin_requis(): void
{
    if (!utilisateur_connecte()) {
        flash('erreur', 'Kyçuni për të hyrë në panelin e administrimit.');
        redirect('connexion');
    }
    if (!est_admin()) {
        http_response_code(403);
        render('erreur404');
        exit;
    }
}

// Liste des tailles proposées dans l'admin (même ordre que le tri des variantes)
const TAILLES = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];

// Affiche une vue entourée du header et du footer
function render(string $vue, array $donnees = []): void
{
    extract($donnees);
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    $categoriesMenu = Categorie::toutes();
    $nbArticlesPanier = array_sum($_SESSION['panier'] ?? []);

    require __DIR__ . '/../views/layout/header.php';
    require __DIR__ . '/../views/' . $vue . '.php';
    require __DIR__ . '/../views/layout/footer.php';
}
