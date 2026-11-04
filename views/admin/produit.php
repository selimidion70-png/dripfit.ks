<?php
require __DIR__ . '/_menu.php';
$erreur = fn(string $champ) => isset($erreurs[$champ]) ? '<span class="erreur-champ">' . e($erreurs[$champ]) . '</span>' : '';
?>
<p class="fil"><a href="<?= url('admin_produits') ?>">Produktet</a> / <?= $produit ? e($produit['nom']) : 'Produkt i ri' ?></p>

<div class="titre-ligne">
    <h1><?= $produit ? e($produit['nom']) : 'Produkt i ri' ?></h1>
    <?php if ($produit && $produit['actif']): ?>
        <a href="<?= url('produit', ['id' => $produit['id']]) ?>">Shiko në dyqan →</a>
    <?php endif; ?>
</div>

<div class="deux-colonnes">
    <!-- 1. Infos du produit + photo -->
    <form method="post" action="<?= url('admin_produit', $produit ? ['id' => $produit['id']] : []) ?>" enctype="multipart/form-data" class="formulaire recap" novalidate>
        <?= csrf_champ() ?>
        <h2>Të dhënat</h2>

        <label for="nom">Emri</label>
        <input type="text" id="nom" name="nom" value="<?= e($donnees['nom']) ?>" required maxlength="150">
        <?= $erreur('nom') ?>

        <label for="categorie_id">Kategoria</label>
        <select id="categorie_id" name="categorie_id" required>
            <option value="">Zgjidhni…</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= (int) $cat['id'] ?>" <?= (int) $donnees['categorie_id'] === (int) $cat['id'] ? 'selected' : '' ?>><?= e($cat['nom']) ?></option>
            <?php endforeach; ?>
        </select>
        <?= $erreur('categorie_id') ?>

        <label for="prix">Çmimi (€)</label>
        <input type="text" id="prix" name="prix" value="<?= e((string) $donnees['prix']) ?>" required inputmode="decimal" placeholder="34.90" class="champ-court">
        <?= $erreur('prix') ?>

        <label for="description">Përshkrimi</label>
        <textarea id="description" name="description" rows="4"><?= e($donnees['description']) ?></textarea>

        <label for="image">Fotoja (JPG, PNG ose WEBP, deri në 3 MB)</label>
        <?php if (!empty($produit['image'])): ?>
            <img class="apercu" src="public/img/produits/<?= e($produit['image']) ?>" alt="Fotoja aktuale">
            <label class="case"><input type="checkbox" name="supprimer_image" value="1"> Fshi foton</label>
        <?php endif; ?>
        <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">
        <?= $erreur('image') ?>

        <label class="case"><input type="checkbox" name="actif" value="1" <?= $donnees['actif'] ? 'checked' : '' ?>> I dukshëm në dyqan</label>

        <button type="submit" class="bouton"><?= $produit ? 'Ruaj' : 'Krijo produktin' ?></button>
    </form>

    <!-- 2. Variantes : seulement une fois le produit créé -->
    <section class="recap">
        <h2>Madhësitë, ngjyrat dhe stoku</h2>

        <?php if (!$produit): ?>
            <p class="note">Krijoni fillimisht produktin, pastaj shtoni madhësitë dhe ngjyrat.</p>
        <?php else: ?>
            <?php if (empty($variantes)): ?>
                <p class="vide">Ende asnjë variant: produkti shfaqet “pa stok” në dyqan.</p>
            <?php else: ?>
                <form method="post" action="<?= url('admin_stock') ?>">
                    <?= csrf_champ() ?>
                    <input type="hidden" name="produit_id" value="<?= (int) $produit['id'] ?>">
                    <table class="tableau tableau-compact">
                        <thead>
                            <tr>
                                <th scope="col">Ngjyra</th>
                                <th scope="col">Madhësia</th>
                                <th scope="col">Stoku</th>
                                <th scope="col"><span class="invisible">Veprimet</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($variantes as $v): ?>
                                <tr>
                                    <td><?= e($v['couleur']) ?></td>
                                    <td><?= e($v['taille']) ?></td>
                                    <td>
                                        <label class="invisible" for="stock<?= (int) $v['id'] ?>">Stoku për <?= e($v['couleur'] . ' ' . $v['taille']) ?></label>
                                        <input type="number" id="stock<?= (int) $v['id'] ?>" name="stock[<?= (int) $v['id'] ?>]" value="<?= (int) $v['stock'] ?>" min="0" class="champ-stock">
                                    </td>
                                    <td>
                                        <!-- form="..." : le bouton envoie le formulaire de suppression placé plus bas (on ne peut pas imbriquer deux <form>) -->
                                        <button type="submit" form="suppr<?= (int) $v['id'] ?>" class="lien" aria-label="Fshi <?= e($v['couleur'] . ' ' . $v['taille']) ?>">Fshi</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button type="submit" class="bouton bouton-secondaire espace-haut">Ruaj stokun</button>
                </form>

                <?php foreach ($variantes as $v): ?>
                    <form id="suppr<?= (int) $v['id'] ?>" method="post" action="<?= url('admin_variante_supprimer') ?>" hidden
                          onsubmit="return confirm(<?= e(json_encode('Ta fshij ' . $v['couleur'] . ' — ' . $v['taille'] . '?')) ?>);">
                        <?= csrf_champ() ?>
                        <input type="hidden" name="produit_id" value="<?= (int) $produit['id'] ?>">
                        <input type="hidden" name="variante_id" value="<?= (int) $v['id'] ?>">
                    </form>
                <?php endforeach; ?>
            <?php endif; ?>

            <h3>Shto variant</h3>
            <form method="post" action="<?= url('admin_variante_ajouter') ?>" class="ligne-ajout">
                <?= csrf_champ() ?>
                <input type="hidden" name="produit_id" value="<?= (int) $produit['id'] ?>">
                <div>
                    <label for="couleur">Ngjyra</label>
                    <input type="text" id="couleur" name="couleur" required maxlength="50" placeholder="E zezë" list="couleurs-existantes">
                    <datalist id="couleurs-existantes">
                        <?php foreach (array_unique(array_column($variantes, 'couleur')) as $couleur): ?>
                            <option value="<?= e($couleur) ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div>
                    <label for="taille">Madhësia</label>
                    <select id="taille" name="taille" required>
                        <?php foreach (TAILLES as $t): ?>
                            <option value="<?= $t ?>" <?= $t === 'M' ? 'selected' : '' ?>><?= $t ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="stock_nouveau">Stoku</label>
                    <input type="number" id="stock_nouveau" name="stock" value="0" min="0" class="champ-stock">
                </div>
                <button type="submit" class="bouton bouton-secondaire">Shto</button>
            </form>
        <?php endif; ?>
    </section>
</div>
