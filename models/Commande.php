<?php

class Commande
{
    /**
     * Enregistre la commande, ses lignes, et diminue le stock.
     * Tout se fait dans une transaction : si un article n'a plus assez de stock,
     * rien n'est enregistré et une exception est lancée.
     */
    public static function creer(array $client, array $lignes, float $total, ?int $utilisateurId): int
    {
        $pdo = Database::getConnexion();
        $pdo->beginTransaction();

        try {
            $requete = $pdo->prepare(
                'INSERT INTO commandes (utilisateur_id, prenom, nom, telephone, email, adresse, ville, pays, total)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $requete->execute([
                $utilisateurId,
                $client['prenom'],
                $client['nom'],
                $client['telephone'],
                $client['email'] ?: null,
                $client['adresse'],
                $client['ville'],
                $client['pays'],
                $total,
            ]);
            $commandeId = (int) $pdo->lastInsertId();

            $stock = $pdo->prepare('UPDATE variantes SET stock = stock - ? WHERE id = ? AND stock >= ?');
            $detail = $pdo->prepare(
                'INSERT INTO commande_details (commande_id, variante_id, nom_produit, taille, couleur, prix_unitaire, quantite)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );

            foreach ($lignes as $ligne) {
                $stock->execute([$ligne['quantite'], $ligne['variante_id'], $ligne['quantite']]);
                if ($stock->rowCount() === 0) {
                    throw new RuntimeException('Nuk ka stok të mjaftueshëm për ' . $ligne['nom'] . ' (' . $ligne['taille'] . ', ' . $ligne['couleur'] . ').');
                }
                $detail->execute([
                    $commandeId,
                    $ligne['variante_id'],
                    $ligne['nom'],
                    $ligne['taille'],
                    $ligne['couleur'],
                    $ligne['prix'],
                    $ligne['quantite'],
                ]);
            }

            $pdo->commit();
            return $commandeId;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function trouver(int $id): ?array
    {
        $pdo = Database::getConnexion();
        $requete = $pdo->prepare('SELECT * FROM commandes WHERE id = ?');
        $requete->execute([$id]);
        return $requete->fetch() ?: null;
    }

    public static function details(int $commandeId): array
    {
        $pdo = Database::getConnexion();
        $requete = $pdo->prepare('SELECT * FROM commande_details WHERE commande_id = ? ORDER BY id');
        $requete->execute([$commandeId]);
        return $requete->fetchAll();
    }

    public static function parUtilisateur(int $utilisateurId): array
    {
        $pdo = Database::getConnexion();
        $requete = $pdo->prepare('SELECT * FROM commandes WHERE utilisateur_id = ? ORDER BY created_at DESC, id DESC');
        $requete->execute([$utilisateurId]);
        return $requete->fetchAll();
    }

    // ---------- Admin ----------

    // Toutes les commandes, éventuellement filtrées par statut, avec le nombre d'articles
    public static function toutes(?string $statut = null): array
    {
        $pdo = Database::getConnexion();
        $sql = 'SELECT c.*, (SELECT COALESCE(SUM(d.quantite), 0) FROM commande_details d WHERE d.commande_id = c.id) AS nb_articles
                FROM commandes c';
        $params = [];
        if ($statut !== null) {
            $sql .= ' WHERE c.statut = ?';
            $params[] = $statut;
        }
        $sql .= ' ORDER BY c.created_at DESC, c.id DESC';

        $requete = $pdo->prepare($sql);
        $requete->execute($params);
        return $requete->fetchAll();
    }

    // Nombre de commandes par statut : ['en_attente' => 3, 'livree' => 10, ...]
    public static function compterParStatut(): array
    {
        $pdo = Database::getConnexion();
        $resultat = array_fill_keys(array_keys(STATUTS_COMMANDE), 0);
        foreach ($pdo->query('SELECT statut, COUNT(*) AS nb FROM commandes GROUP BY statut') as $ligne) {
            $resultat[$ligne['statut']] = (int) $ligne['nb'];
        }
        return $resultat;
    }

    // Chiffre d'affaires des commandes livrées (argent réellement encaissé)
    public static function totalEncaisse(): float
    {
        $pdo = Database::getConnexion();
        return (float) $pdo->query("SELECT COALESCE(SUM(total), 0) FROM commandes WHERE statut = 'livree'")->fetchColumn();
    }

    /**
     * Change le statut d'une commande.
     * Une commande annulée est définitive : son stock est remis en vente,
     * et on ne peut plus changer son statut ensuite.
     */
    public static function changerStatut(int $id, string $nouveauStatut): void
    {
        $pdo = Database::getConnexion();
        $pdo->beginTransaction();

        try {
            // FOR UPDATE : bloque la ligne pour éviter deux annulations en même temps
            $requete = $pdo->prepare('SELECT statut FROM commandes WHERE id = ? FOR UPDATE');
            $requete->execute([$id]);
            $ancienStatut = $requete->fetchColumn();

            if ($ancienStatut === false) {
                throw new RuntimeException('Porosia nuk ekziston.');
            }
            if ($ancienStatut === 'annulee') {
                throw new RuntimeException('Një porosi e anuluar nuk mund të ndryshohet më.');
            }

            $requete = $pdo->prepare('UPDATE commandes SET statut = ? WHERE id = ?');
            $requete->execute([$nouveauStatut, $id]);

            // Annulation : on remet les articles en stock
            if ($nouveauStatut === 'annulee') {
                $requete = $pdo->prepare(
                    'UPDATE variantes v
                     JOIN commande_details d ON d.variante_id = v.id
                     SET v.stock = v.stock + d.quantite
                     WHERE d.commande_id = ?'
                );
                $requete->execute([$id]);
            }

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
