-- ============================================================
--  DripFit.ks — mise à jour pour l'espace admin
--  À importer UNE SEULE FOIS sur une base déjà existante
--  (si tu réimportes dripfit.sql, ce n'est pas nécessaire).
-- ============================================================

USE dripfit;

-- 1 = administrateur (accès à index.php?page=admin), 0 = client
ALTER TABLE utilisateurs
    ADD COLUMN est_admin TINYINT(1) NOT NULL DEFAULT 0 AFTER telephone;

-- Pour donner l'accès admin à un compte (créé avant sur le site) :
-- UPDATE utilisateurs SET est_admin = 1 WHERE email = 'ton.email@exemple.com';
