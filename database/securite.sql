-- ============================================================
--  DripFit.ks — limite des tentatives de connexion
--  À importer UNE SEULE FOIS sur une base déjà existante
--  (si tu réimportes dripfit.sql, ce n'est pas nécessaire).
-- ============================================================

USE dripfit;

-- Une ligne par mot de passe raté (effacées après une connexion réussie)
CREATE TABLE IF NOT EXISTS connexion_echecs (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    email      VARCHAR(150) NOT NULL,
    ip         VARCHAR(45) NOT NULL,          -- 45 caractères : assez pour une adresse IPv6
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email, created_at),
    INDEX idx_ip (ip, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
