-- ============================================================
--  DripFit.ks — base de données pour l'HÉBERGEMENT (site en ligne)
--  À importer dans le phpMyAdmin de l'hébergeur, en ayant d'abord
--  cliqué sur la base créée dans le panneau (ex : u123456_dripfit).
--
--  Différences avec dripfit.sql (qui sert pour Laragon) :
--   - pas de CREATE DATABASE / USE (l'hébergeur impose le nom de la base)
--   - pas de produits d'exemple : on les ajoute depuis l'espace admin
--  ⚠ Si tu modifies les tables dans dripfit.sql, fais pareil ici.
-- ============================================================
SET NAMES utf8mb4;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS commande_details;
DROP TABLE IF EXISTS commandes;
DROP TABLE IF EXISTS variantes;
DROP TABLE IF EXISTS produits;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS utilisateurs;
DROP TABLE IF EXISTS connexion_echecs;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Catégories
CREATE TABLE categories (
    id   INT AUTO_INCREMENT PRIMARY KEY,
    nom  VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Produits
CREATE TABLE produits (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    categorie_id INT NOT NULL,
    nom          VARCHAR(150) NOT NULL,
    description  TEXT,
    prix         DECIMAL(8,2) NOT NULL,
    image        VARCHAR(255) DEFAULT NULL,
    actif        TINYINT(1) NOT NULL DEFAULT 1,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_produit_categorie FOREIGN KEY (categorie_id) REFERENCES categories(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Variantes (taille + couleur + stock)
CREATE TABLE variantes (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    produit_id INT NOT NULL,
    taille     VARCHAR(10) NOT NULL,
    couleur    VARCHAR(50) NOT NULL,
    stock      INT NOT NULL DEFAULT 0,
    UNIQUE KEY uniq_variante (produit_id, taille, couleur),
    CONSTRAINT fk_variante_produit FOREIGN KEY (produit_id) REFERENCES produits(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Utilisateurs (comptes clients)
CREATE TABLE utilisateurs (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    prenom       VARCHAR(100) NOT NULL,
    nom          VARCHAR(100) NOT NULL,
    email        VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    telephone    VARCHAR(30) DEFAULT NULL,
    est_admin    TINYINT(1) NOT NULL DEFAULT 0,   -- 1 = accès à l'espace admin
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Commandes (paiement à la livraison)
CREATE TABLE commandes (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT DEFAULT NULL,
    prenom         VARCHAR(100) NOT NULL,
    nom            VARCHAR(100) NOT NULL,
    telephone      VARCHAR(30) NOT NULL,
    email          VARCHAR(150) DEFAULT NULL,
    adresse        VARCHAR(255) NOT NULL,
    ville          VARCHAR(100) NOT NULL,
    pays           ENUM('Kosovë','Shqipëri','Maqedonia e Veriut') NOT NULL,
    total          DECIMAL(9,2) NOT NULL,
    statut         ENUM('en_attente','confirmee','expediee','livree','annulee') NOT NULL DEFAULT 'en_attente',
    created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_commande_utilisateur FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Détails des commandes (une ligne par article)
CREATE TABLE commande_details (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    commande_id   INT NOT NULL,
    variante_id   INT DEFAULT NULL,
    nom_produit   VARCHAR(150) NOT NULL,
    taille        VARCHAR(10) NOT NULL,
    couleur       VARCHAR(50) NOT NULL,
    prix_unitaire DECIMAL(8,2) NOT NULL,
    quantite      INT NOT NULL,
    CONSTRAINT fk_detail_commande FOREIGN KEY (commande_id) REFERENCES commandes(id) ON DELETE CASCADE,
    CONSTRAINT fk_detail_variante FOREIGN KEY (variante_id) REFERENCES variantes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Mots de passe ratés (limite des tentatives de connexion)
CREATE TABLE connexion_echecs (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    email      VARCHAR(150) NOT NULL,
    ip         VARCHAR(45) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email, created_at),
    INDEX idx_ip (ip, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Les 4 catégories de la boutique

INSERT INTO categories (id, nom, slug) VALUES
(1, 'Trenerka',   'trenerka'),
(2, 'Pantallona', 'pantallona'),
(3, 'Shorce',     'shorce'),
(4, 'Duksa',      'duksa'),
(5, 'Maica',      'maica');

