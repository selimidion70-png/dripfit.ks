<?php
// ============================================================
//  DripFit.ks — routeur central
//  Toutes les pages passent par ici : index.php?page=...
// ============================================================

// Réglages de l'hébergement : ce fichier n'existe que sur le serveur en ligne
// (voir config/production.exemple.php). Il doit être chargé en premier.
if (is_file(__DIR__ . '/config/production.php')) {
    require __DIR__ . '/config/production.php';
}

require __DIR__ . '/config/database.php';
require __DIR__ . '/config/app.php';

// En ligne : on oblige le https (le cadenas), sinon les données des clients circulent en clair
if (EN_PRODUCTION && !connexion_https()) {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true, 301);
    exit;
}

// En-têtes de sécurité envoyés avec chaque page
header('X-Content-Type-Options: nosniff');           // le navigateur ne "devine" pas le type des fichiers
header('X-Frame-Options: SAMEORIGIN');               // interdit d'afficher le site dans une iframe d'un autre site
header('Referrer-Policy: strict-origin-when-cross-origin');

// Cookie de session protégé :
//  - httponly : JavaScript ne peut pas le lire (protège contre le vol de session)
//  - samesite : il n'est pas envoyé depuis un autre site
//  - secure   : seulement en https (donc pas en local sur Laragon)
ini_set('session.use_strict_mode', '1');
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => connexion_https(),
]);
session_start();

// Chargement automatique des modèles et des contrôleurs
spl_autoload_register(function (string $classe): void {
    foreach (['models', 'controllers'] as $dossier) {
        $fichier = __DIR__ . '/' . $dossier . '/' . $classe . '.php';
        if (is_file($fichier)) {
            require $fichier;
            return;
        }
    }
});

$page = $_GET['page'] ?? 'home';

switch ($page) {
    // Catalogue
    case 'home':
        (new HomeController())->index();
        break;
    case 'produit':
        (new ProduitController())->detail();
        break;

    // Panier
    case 'panier':
        (new PanierController())->index();
        break;
    case 'panier_ajouter':
        (new PanierController())->ajouter();
        break;
    case 'panier_modifier':
        (new PanierController())->modifier();
        break;
    case 'panier_supprimer':
        (new PanierController())->supprimer();
        break;

    // Commande
    case 'commande':
        (new CommandeController())->formulaire();
        break;
    case 'confirmation':
        (new CommandeController())->confirmation();
        break;
    case 'mes_commandes':
        (new CommandeController())->historique();
        break;

    // Compte client
    case 'inscription':
        (new AuthController())->inscription();
        break;
    case 'connexion':
        (new AuthController())->connexion();
        break;
    case 'deconnexion':
        (new AuthController())->deconnexion();
        break;

    // Espace admin (réservé aux comptes avec est_admin = 1)
    case 'admin':
        (new AdminController())->tableauDeBord();
        break;
    case 'admin_commandes':
        (new AdminController())->commandes();
        break;
    case 'admin_commande':
        (new AdminController())->commande();
        break;
    case 'admin_produits':
        (new AdminController())->produits();
        break;
    case 'admin_produit':
        (new AdminController())->produit();
        break;
    case 'admin_variante_ajouter':
        (new AdminController())->varianteAjouter();
        break;
    case 'admin_stock':
        (new AdminController())->stock();
        break;
    case 'admin_variante_supprimer':
        (new AdminController())->varianteSupprimer();
        break;

    default:
        http_response_code(404);
        render('erreur404');
}
