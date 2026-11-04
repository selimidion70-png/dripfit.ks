-- ============================================================
--  DripFit.ks — base de données
--  À importer dans phpMyAdmin (onglet "Importer").
--  Crée la base "dripfit", les 6 tables et des données de test.
-- ============================================================

SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS dripfit CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dripfit;

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

-- ============================================================
--  Données de test (à remplacer par les vrais produits et prix)
-- ============================================================

INSERT INTO categories (id, nom, slug) VALUES
(1, 'Trenerka',   'trenerka'),
(2, 'Pantallona', 'pantallona'),
(3, 'Shorce',     'shorce'),
(4, 'Duksa',      'duksa'),
(5, 'Maica',      'maica');

INSERT INTO produits (id, categorie_id, nom, description, prix) VALUES
(1, 1, 'Trenerkë Classic', 'Komplet me jakne me zinxhir dhe pantallona, prerje e drejtë.',   59.90),
(2, 1, 'Trenerkë Tech', 'Komplet nga material i lehtë që merr frymë, prerje e ngushtë.',   74.90),
(3, 2, 'Pantallona Jogger', 'Jogger pambuku, të ngushtuara në fund të këmbëve.',   34.90),
(4, 2, 'Pantallona Cargo', 'Pantallona të gjera me xhepa anësorë.',   39.90),
(5, 3, 'Shorce Training', 'Shorce të lehta për stërvitje, me bel elastik.',   19.90),
(6, 3, 'Shorce Pambuku', 'Shorce të buta pambuku, me xhepa me zinxhir.',   24.90),
(7, 4, 'Duks me kapuç', 'Duks i trashë me kapuç dhe xhep përpara.',   44.90),
(8, 4, 'Duks me jakë të rrumbullakët', 'Duks pambuku me jakë të rrumbullakët.',   36.90),
(9, 5, 'Maicë Basic', 'Maicë pambuku 100%, prerje klasike.',   14.90),
(10, 5, 'Maicë Oversize', 'Maicë e gjerë me supe të ulëta, pambuk i trashë.',   19.90);

INSERT INTO variantes (produit_id, taille, couleur, stock) VALUES
(1,'S','E zezë',8),(1,'M','E zezë',12),(1,'L','E zezë',10),(1,'XL','E zezë',5),(1,'M','Gri',6),(1,'L','Gri',4),
(2,'S','E zezë',5),(2,'M','E zezë',9),(2,'L','E zezë',7),(2,'M','Blu e errët',6),(2,'L','Blu e errët',0),
(3,'S','E zezë',10),(3,'M','E zezë',15),(3,'L','E zezë',12),(3,'M','Gri',8),(3,'L','Gri',8),(3,'XL','Gri',3),
(4,'M','Kaki',7),(4,'L','Kaki',7),(4,'M','E zezë',9),(4,'L','E zezë',6),
(5,'S','E zezë',14),(5,'M','E zezë',20),(5,'L','E zezë',16),(5,'M','E bardhë',10),
(6,'M','Gri',9),(6,'L','Gri',9),(6,'XL','Gri',2),
(7,'S','E zezë',6),(7,'M','E zezë',11),(7,'L','E zezë',9),(7,'XL','E zezë',4),(7,'M','Bezhë',5),(7,'L','Bezhë',0),
(8,'M','E bardhë',8),(8,'L','E bardhë',6),(8,'M','E zezë',10),(8,'L','E zezë',7),
(9,'S','E bardhë',12),(9,'M','E bardhë',15),(9,'L','E bardhë',10),(9,'M','E zezë',14),(9,'L','E zezë',9),
(10,'M','E zezë',8),(10,'L','E zezë',6),(10,'XL','E zezë',3),(10,'M','Bezhë',5);
