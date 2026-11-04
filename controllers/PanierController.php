<?php

// Le panier est stocké en session : $_SESSION['panier'][id_variante] = quantité
class PanierController
{
    /**
     * Transforme le panier de la session en lignes complètes (nom, prix, stock...).
     * Renvoie [lignes, sous-total]. Utilisé aussi par CommandeController.
     */
    public static function contenu(): array
    {
        $lignes = [];
        $sousTotal = 0.0;

        foreach ($_SESSION['panier'] ?? [] as $varianteId => $quantite) {
            $variante = Produit::variante((int) $varianteId);
            if (!$variante) {
                unset($_SESSION['panier'][$varianteId]); // produit supprimé entre-temps
                continue;
            }
            $ligne = [
                'variante_id' => (int) $variante['id'],
                'produit_id'  => (int) $variante['produit_id'],
                'nom'         => $variante['nom'],
                'taille'      => $variante['taille'],
                'couleur'     => $variante['couleur'],
                'prix'        => (float) $variante['prix'],
                'stock'       => (int) $variante['stock'],
                'quantite'    => (int) $quantite,
            ];
            $ligne['sous_total'] = $ligne['prix'] * $ligne['quantite'];
            $sousTotal += $ligne['sous_total'];
            $lignes[] = $ligne;
        }

        return [$lignes, $sousTotal];
    }

    public function index(): void
    {
        [$lignes, $sousTotal] = self::contenu();
        render('panier', [
            'titre'     => 'Shporta',
            'lignes'    => $lignes,
            'sousTotal' => $sousTotal,
        ]);
    }

    public function ajouter(): void
    {
        if (!est_post()) {
            redirect('home');
        }
        csrf_verifier();

        $variante = Produit::variante((int) ($_POST['variante_id'] ?? 0));
        $quantite = max(1, (int) ($_POST['quantite'] ?? 1));

        if (!$variante) {
            flash('erreur', 'Zgjidhni madhësinë dhe ngjyrën.');
            redirect('home');
        }

        $dejaDansPanier = $_SESSION['panier'][$variante['id']] ?? 0;
        $nouvelleQuantite = $dejaDansPanier + $quantite;

        if ($nouvelleQuantite > $variante['stock']) {
            flash('erreur', 'Nuk ka stok të mjaftueshëm: kanë mbetur ' . $variante['stock'] . ' copë në këtë madhësi dhe ngjyrë.');
            redirect('produit', ['id' => $variante['produit_id']]);
        }

        $_SESSION['panier'][$variante['id']] = $nouvelleQuantite;
        flash('succes', $variante['nom'] . ' u shtua në shportë.');
        redirect('panier');
    }

    public function modifier(): void
    {
        if (!est_post()) {
            redirect('panier');
        }
        csrf_verifier();

        $varianteId = (int) ($_POST['variante_id'] ?? 0);
        $quantite = (int) ($_POST['quantite'] ?? 1);
        $variante = Produit::variante($varianteId);

        if (!$variante || !isset($_SESSION['panier'][$varianteId])) {
            redirect('panier');
        }

        if ($quantite <= 0) {
            unset($_SESSION['panier'][$varianteId]);
        } elseif ($variante['stock'] <= 0) {
            // Plus de stock du tout : on retire la ligne au lieu de la garder à 0
            unset($_SESSION['panier'][$varianteId]);
            flash('erreur', $variante['nom'] . ' nuk është më në stok dhe u hoq nga shporta.');
        } elseif ($quantite > $variante['stock']) {
            $_SESSION['panier'][$varianteId] = (int) $variante['stock'];
            flash('erreur', 'Sasia u përshtat me stokun e disponueshëm (' . $variante['stock'] . ').');
        } else {
            $_SESSION['panier'][$varianteId] = $quantite;
        }
        redirect('panier');
    }

    public function supprimer(): void
    {
        if (est_post()) {
            csrf_verifier();
            unset($_SESSION['panier'][(int) ($_POST['variante_id'] ?? 0)]);
        }
        redirect('panier');
    }
}
