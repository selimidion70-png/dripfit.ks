<?php
// ============================================================
//  Réglages de l'hébergement (le vrai site en ligne)
//
//  1. Sur l'hébergement, copie ce fichier sous le nom  config/production.php
//  2. Remplis les valeurs ci-dessous (données par l'hébergeur)
//
//  Sur ton PC, NE crée PAS config/production.php : sans ce fichier,
//  le site utilise automatiquement les réglages de Laragon.
//  Ne partage jamais config/production.php (il contient le mot de passe de la base).
// ============================================================

// Cache les erreurs PHP aux clients, force le https, sécurise les cookies
define('EN_PRODUCTION', true);

// Base de données : panneau de l'hébergeur > "Bases de données MySQL"
define('DB_HOST', 'localhost');
define('DB_NAME', 'u123456_dripfit');      // nom complet de la base, souvent avec un préfixe
define('DB_USER', 'u123456_dripfit');
define('DB_PASS', 'mot-de-passe-de-la-base');

// Email qui reçoit une alerte à chaque nouvelle commande (celui du cousin)
define('EMAIL_COMMANDES', 'email.du.cousin@gmail.com');

// Adresse d'envoi : doit être une adresse de TON domaine (sinon l'email part en spam)
// Crée-la dans le panneau de l'hébergeur > "Emails", par exemple :
define('EMAIL_EXPEDITEUR', 'porosi@dripfitks.com');
