<?php

class ProduitController
{
    // Fiche produit avec ses variantes (tailles et couleurs)
    public function detail(): void
    {
        $produit = Produit::trouver((int) ($_GET['id'] ?? 0));

        if (!$produit) {
            http_response_code(404);
            render('erreur404');
            return;
        }

        render('produit', [
            'titre'     => $produit['nom'],
            'produit'   => $produit,
            'variantes' => Produit::variantes((int) $produit['id']),
        ]);
    }
}
