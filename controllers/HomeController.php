<?php

class HomeController
{
    // Page d'accueil : le catalogue, filtrable par catégorie
    public function index(): void
    {
        $categorie = null;
        // is_string : évite une erreur si quelqu'un envoie ?categorie[]=...
        if (!empty($_GET['categorie']) && is_string($_GET['categorie'])) {
            $categorie = Categorie::trouverParSlug($_GET['categorie']);
        }

        render('home', [
            'titre'           => $categorie ? $categorie['nom'] : null, // null = juste "DripFit.ks" dans l'onglet
            'categorieActive' => $categorie,
            'produits'        => Produit::tous($categorie ? (int) $categorie['id'] : null),
        ]);
    }
}
