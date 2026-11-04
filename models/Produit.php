<?php

class Produit
{
    // Tous les produits actifs, avec le nom de la catégorie et le stock total
    public static function tous(?int $categorieId = null): array
    {
        $pdo = Database::getConnexion();
        $sql = 'SELECT p.*, c.nom AS categorie,
                       (SELECT COALESCE(SUM(v.stock), 0) FROM variantes v WHERE v.produit_id = p.id) AS stock_total
                FROM produits p
                JOIN categories c ON c.id = p.categorie_id
                WHERE p.actif = 1';
        $params = [];
        if ($categorieId !== null) {
            $sql .= ' AND p.categorie_id = ?';
            $params[] = $categorieId;
        }
        $sql .= ' ORDER BY p.id';

        $requete = $pdo->prepare($sql);
        $requete->execute($params);
        return $requete->fetchAll();
    }

    public static function trouver(int $id): ?array
    {
        $pdo = Database::getConnexion();
        $requete = $pdo->prepare(
            'SELECT p.*, c.nom AS categorie, c.slug AS categorie_slug
             FROM produits p
             JOIN categories c ON c.id = p.categorie_id
             WHERE p.id = ? AND p.actif = 1'
        );
        $requete->execute([$id]);
        return $requete->fetch() ?: null;
    }

    // Les variantes (taille / couleur / stock) d'un produit
    public static function variantes(int $produitId): array
    {
        $pdo = Database::getConnexion();
        $requete = $pdo->prepare(
            "SELECT * FROM variantes WHERE produit_id = ?
             ORDER BY couleur, FIELD(taille, 'XS','S','M','L','XL','XXL')"
        );
        $requete->execute([$produitId]);
        return $requete->fetchAll();
    }

    // Une variante précise, avec le nom et le prix de son produit
    public static function variante(int $varianteId): ?array
    {
        $pdo = Database::getConnexion();
        $requete = $pdo->prepare(
            'SELECT v.id, v.produit_id, v.taille, v.couleur, v.stock, p.nom, p.prix
             FROM variantes v
             JOIN produits p ON p.id = v.produit_id
             WHERE v.id = ? AND p.actif = 1'
        );
        $requete->execute([$varianteId]);
        return $requete->fetch() ?: null;
    }

    // ---------- Admin ----------

    // Tous les produits, y compris les désactivés
    public static function tousAdmin(): array
    {
        $pdo = Database::getConnexion();
        return $pdo->query(
            'SELECT p.*, c.nom AS categorie,
                    (SELECT COALESCE(SUM(v.stock), 0) FROM variantes v WHERE v.produit_id = p.id) AS stock_total,
                    (SELECT COUNT(*) FROM variantes v WHERE v.produit_id = p.id) AS nb_variantes
             FROM produits p
             JOIN categories c ON c.id = p.categorie_id
             ORDER BY p.actif DESC, p.id'
        )->fetchAll();
    }

    // Un produit même désactivé (trouver() ne renvoie que les actifs)
    public static function trouverAdmin(int $id): ?array
    {
        $pdo = Database::getConnexion();
        $requete = $pdo->prepare('SELECT * FROM produits WHERE id = ?');
        $requete->execute([$id]);
        return $requete->fetch() ?: null;
    }

    // $donnees : categorie_id, nom, description, prix, actif
    public static function creer(array $donnees): int
    {
        $pdo = Database::getConnexion();
        $requete = $pdo->prepare(
            'INSERT INTO produits (categorie_id, nom, description, prix, actif) VALUES (?, ?, ?, ?, ?)'
        );
        $requete->execute([$donnees['categorie_id'], $donnees['nom'], $donnees['description'], $donnees['prix'], $donnees['actif']]);
        return (int) $pdo->lastInsertId();
    }

    public static function modifier(int $id, array $donnees): void
    {
        $pdo = Database::getConnexion();
        $requete = $pdo->prepare(
            'UPDATE produits SET categorie_id = ?, nom = ?, description = ?, prix = ?, actif = ? WHERE id = ?'
        );
        $requete->execute([$donnees['categorie_id'], $donnees['nom'], $donnees['description'], $donnees['prix'], $donnees['actif'], $id]);
    }

    public static function changerImage(int $id, ?string $fichier): void
    {
        $pdo = Database::getConnexion();
        $requete = $pdo->prepare('UPDATE produits SET image = ? WHERE id = ?');
        $requete->execute([$fichier, $id]);
    }

    // Ajoute une variante. Lance une PDOException (code 23000) si elle existe déjà.
    public static function ajouterVariante(int $produitId, string $taille, string $couleur, int $stock): void
    {
        $pdo = Database::getConnexion();
        $requete = $pdo->prepare('INSERT INTO variantes (produit_id, taille, couleur, stock) VALUES (?, ?, ?, ?)');
        $requete->execute([$produitId, $taille, $couleur, $stock]);
    }

    // Le "AND produit_id = ?" empêche de modifier la variante d'un autre produit
    public static function changerStock(int $produitId, int $varianteId, int $stock): void
    {
        $pdo = Database::getConnexion();
        $requete = $pdo->prepare('UPDATE variantes SET stock = ? WHERE id = ? AND produit_id = ?');
        $requete->execute([$stock, $varianteId, $produitId]);
    }

    // Les anciennes commandes gardent leurs infos (nom, taille, couleur sont copiés dans commande_details)
    public static function supprimerVariante(int $produitId, int $varianteId): void
    {
        $pdo = Database::getConnexion();
        $requete = $pdo->prepare('DELETE FROM variantes WHERE id = ? AND produit_id = ?');
        $requete->execute([$varianteId, $produitId]);
    }

    // Variantes presque épuisées (pour le tableau de bord)
    public static function stockFaible(int $seuil = 3): array
    {
        $pdo = Database::getConnexion();
        $requete = $pdo->prepare(
            'SELECT v.*, p.nom
             FROM variantes v
             JOIN produits p ON p.id = v.produit_id
             WHERE p.actif = 1 AND v.stock <= ?
             ORDER BY v.stock, p.nom'
        );
        $requete->execute([$seuil]);
        return $requete->fetchAll();
    }
}
