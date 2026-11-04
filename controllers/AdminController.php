<?php

// Espace admin : commandes, produits, variantes et stock.
// Chaque méthode commence par admin_requis() : seuls les comptes avec est_admin = 1 y ont accès.
class AdminController
{
    private const IMAGE_TAILLE_MAX = 3 * 1024 * 1024; // 3 Mo
    private const IMAGE_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    // ---------- Tableau de bord ----------

    public function tableauDeBord(): void
    {
        admin_requis();

        render('admin/tableau_de_bord', [
            'titre'         => 'Paneli',
            'parStatut'     => Commande::compterParStatut(),
            'totalEncaisse' => Commande::totalEncaisse(),
            'enAttente'     => Commande::toutes('en_attente'),
            'stockFaible'   => Produit::stockFaible(),
        ]);
    }

    // ---------- Commandes ----------

    public function commandes(): void
    {
        admin_requis();

        // Filtre facultatif : ?statut=en_attente
        $statut = $_GET['statut'] ?? null;
        if (!is_string($statut) || !array_key_exists($statut, STATUTS_COMMANDE)) {
            $statut = null;
        }

        render('admin/commandes', [
            'titre'      => 'Porositë',
            'commandes'  => Commande::toutes($statut),
            'parStatut'  => Commande::compterParStatut(),
            'statutActif' => $statut,
        ]);
    }

    // Détail d'une commande (GET) et changement de statut (POST)
    public function commande(): void
    {
        admin_requis();

        $commande = Commande::trouver((int) ($_GET['id'] ?? 0));
        if (!$commande) {
            http_response_code(404);
            render('erreur404');
            return;
        }

        if (est_post()) {
            csrf_verifier();
            $statut = $_POST['statut'] ?? '';

            if (!is_string($statut) || !array_key_exists($statut, STATUTS_COMMANDE)) {
                flash('erreur', 'Statusi nuk është i vlefshëm.');
            } elseif ($statut !== $commande['statut']) {
                try {
                    Commande::changerStatut((int) $commande['id'], $statut);
                    flash('succes', 'Statusi u ndryshua në « ' . STATUTS_COMMANDE[$statut] . ' ».'
                        . ($statut === 'annulee' ? ' Artikujt u kthyen në stok.' : ''));
                } catch (RuntimeException $e) {
                    flash('erreur', $e->getMessage());
                }
            }
            redirect('admin_commande', ['id' => $commande['id']]);
        }

        render('admin/commande', [
            'titre'    => 'Porosia nr. ' . $commande['id'],
            'commande' => $commande,
            'details'  => Commande::details((int) $commande['id']),
        ]);
    }

    // ---------- Produits ----------

    public function produits(): void
    {
        admin_requis();

        render('admin/produits', [
            'titre'    => 'Produktet',
            'produits' => Produit::tousAdmin(),
        ]);
    }

    // Formulaire produit : création (sans id) ou modification (avec id)
    public function produit(): void
    {
        admin_requis();

        $id = (int) ($_GET['id'] ?? 0);
        $produit = $id ? Produit::trouverAdmin($id) : null;
        if ($id && !$produit) {
            http_response_code(404);
            render('erreur404');
            return;
        }

        $erreurs = [];
        $donnees = [
            'categorie_id' => $produit['categorie_id'] ?? '',
            'nom'          => $produit['nom'] ?? '',
            'description'  => $produit['description'] ?? '',
            'prix'         => $produit['prix'] ?? '',
            'actif'        => $produit['actif'] ?? 1,
        ];

        if (est_post()) {
            csrf_verifier();

            $donnees['categorie_id'] = (int) ($_POST['categorie_id'] ?? 0);
            $donnees['nom']          = trim((string) ($_POST['nom'] ?? ''));
            $donnees['description']  = trim((string) ($_POST['description'] ?? ''));
            $donnees['prix']         = str_replace(',', '.', trim((string) ($_POST['prix'] ?? ''))); // accepte "34,90"
            $donnees['actif']        = isset($_POST['actif']) ? 1 : 0;

            if (!Categorie::existe($donnees['categorie_id'])) {
                $erreurs['categorie_id'] = 'Zgjidhni kategorinë.';
            }
            if ($donnees['nom'] === '' || mb_strlen($donnees['nom']) > 150) {
                $erreurs['nom'] = 'Emri është i detyrueshëm (deri në 150 karaktere).';
            }
            if (!is_numeric($donnees['prix']) || $donnees['prix'] <= 0 || $donnees['prix'] > 99999) {
                $erreurs['prix'] = 'Shkruani një çmim të vlefshëm, p.sh. 34.90';
            }

            // La photo est facultative ; on la vérifie avant d'enregistrer quoi que ce soit
            $photo = $_FILES['image'] ?? null;
            $avecPhoto = is_array($photo) && ($photo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
            if ($avecPhoto) {
                $erreurPhoto = $this->verifierImage($photo);
                if ($erreurPhoto) {
                    $erreurs['image'] = $erreurPhoto;
                }
            }

            if (empty($erreurs)) {
                if ($produit) {
                    Produit::modifier($id, $donnees);
                } else {
                    $id = Produit::creer($donnees);
                }

                if ($avecPhoto) {
                    $this->enregistrerImage($id, $photo, $produit['image'] ?? null);
                } elseif ($produit && isset($_POST['supprimer_image'])) {
                    $this->supprimerFichierImage($produit['image']);
                    Produit::changerImage($id, null);
                }

                flash('succes', $produit ? 'Produkti u ruajt.' : 'Produkti u krijua. Tani shtoni madhësitë dhe ngjyrat.');
                redirect('admin_produit', ['id' => $id]);
            }
        }

        render('admin/produit', [
            'titre'      => $produit ? $produit['nom'] : 'Produkt i ri',
            'produit'    => $produit,
            'donnees'    => $donnees,
            'erreurs'    => $erreurs,
            'categories' => Categorie::toutes(),
            'variantes'  => $produit ? Produit::variantes($id) : [],
        ]);
    }

    // ---------- Variantes (taille + couleur + stock) ----------

    public function varianteAjouter(): void
    {
        admin_requis();
        $produit = $this->produitDuFormulaire();
        csrf_verifier();

        $taille  = (string) ($_POST['taille'] ?? '');
        $couleur = trim((string) ($_POST['couleur'] ?? ''));
        $stock   = (int) ($_POST['stock'] ?? 0);

        if (!in_array($taille, TAILLES, true) || $couleur === '' || mb_strlen($couleur) > 50 || $stock < 0) {
            flash('erreur', 'Zgjidhni madhësinë, shkruani ngjyrën dhe një stok 0 ose më shumë.');
        } else {
            try {
                Produit::ajouterVariante((int) $produit['id'], $taille, $couleur, $stock);
                flash('succes', 'U shtua: ' . $couleur . ' — ' . $taille . '.');
            } catch (PDOException $e) {
                // 23000 = la clé unique (produit, taille, couleur) existe déjà
                if ($e->getCode() !== '23000') {
                    throw $e;
                }
                flash('erreur', 'Kjo madhësi me këtë ngjyrë ekziston tashmë. Ndryshoni stokun e saj në tabelë.');
            }
        }
        redirect('admin_produit', ['id' => $produit['id']]);
    }

    // Enregistre tous les stocks du tableau en une fois : stock[id_variante] = quantité
    public function stock(): void
    {
        admin_requis();
        $produit = $this->produitDuFormulaire();
        csrf_verifier();

        $stocks = $_POST['stock'] ?? [];
        if (is_array($stocks)) {
            foreach ($stocks as $varianteId => $stock) {
                Produit::changerStock((int) $produit['id'], (int) $varianteId, max(0, (int) $stock));
            }
        }
        flash('succes', 'Stoku u ruajt.');
        redirect('admin_produit', ['id' => $produit['id']]);
    }

    public function varianteSupprimer(): void
    {
        admin_requis();
        $produit = $this->produitDuFormulaire();
        csrf_verifier();

        Produit::supprimerVariante((int) $produit['id'], (int) ($_POST['variante_id'] ?? 0));
        flash('succes', 'Varianti u fshi.');
        redirect('admin_produit', ['id' => $produit['id']]);
    }

    // ---------- Outils internes ----------

    // Les formulaires de variantes envoient produit_id en POST ; on vérifie qu'il existe
    private function produitDuFormulaire(): array
    {
        if (!est_post()) {
            redirect('admin_produits');
        }
        $produit = Produit::trouverAdmin((int) ($_POST['produit_id'] ?? 0));
        if (!$produit) {
            flash('erreur', 'Produkti nuk ekziston.');
            redirect('admin_produits');
        }
        return $produit;
    }

    // Renvoie un message d'erreur, ou null si l'image est correcte
    private function verifierImage(array $photo): ?string
    {
        if ($photo['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($photo['tmp_name'])) {
            return 'Fotoja nuk u ngarkua. Provoni përsëri.';
        }
        if ($photo['size'] > self::IMAGE_TAILLE_MAX) {
            return 'Fotoja është shumë e madhe (maksimumi 3 MB).';
        }
        // On regarde le vrai contenu du fichier, pas son nom (un .jpg peut cacher autre chose)
        $type = (new finfo(FILEINFO_MIME_TYPE))->file($photo['tmp_name']);
        if (!isset(self::IMAGE_TYPES[$type]) || getimagesize($photo['tmp_name']) === false) {
            return 'Lejohen vetëm foto JPG, PNG ose WEBP.';
        }
        return null;
    }

    // Nom de fichier choisi par nous (jamais celui envoyé), puis suppression de l'ancienne photo
    private function enregistrerImage(int $produitId, array $photo, ?string $ancienne): void
    {
        $type = (new finfo(FILEINFO_MIME_TYPE))->file($photo['tmp_name']);
        $fichier = 'produit-' . $produitId . '-' . bin2hex(random_bytes(4)) . '.' . self::IMAGE_TYPES[$type];

        if (move_uploaded_file($photo['tmp_name'], $this->dossierImages() . $fichier)) {
            Produit::changerImage($produitId, $fichier);
            $this->supprimerFichierImage($ancienne);
        }
    }

    private function supprimerFichierImage(?string $fichier): void
    {
        // basename() : on ne supprime jamais en dehors du dossier des photos
        if ($fichier && is_file($this->dossierImages() . basename($fichier))) {
            unlink($this->dossierImages() . basename($fichier));
        }
    }

    private function dossierImages(): string
    {
        return __DIR__ . '/../public/img/produits/';
    }
}
