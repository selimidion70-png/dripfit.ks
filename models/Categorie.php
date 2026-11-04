<?php

class Categorie
{
    public static function toutes(): array
    {
        $pdo = Database::getConnexion();
        return $pdo->query('SELECT * FROM categories ORDER BY id')->fetchAll();
    }

    public static function existe(int $id): bool
    {
        $pdo = Database::getConnexion();
        $requete = $pdo->prepare('SELECT COUNT(*) FROM categories WHERE id = ?');
        $requete->execute([$id]);
        return (bool) $requete->fetchColumn();
    }

    public static function trouverParSlug(string $slug): ?array
    {
        $pdo = Database::getConnexion();
        $requete = $pdo->prepare('SELECT * FROM categories WHERE slug = ?');
        $requete->execute([$slug]);
        return $requete->fetch() ?: null;
    }
}
