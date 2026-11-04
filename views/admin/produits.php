<?php require __DIR__ . '/_menu.php'; ?>

<div class="titre-ligne">
    <h1>Produktet</h1>
    <a class="bouton" href="<?= url('admin_produit') ?>">+ Produkt i ri</a>
</div>

<div class="tableau-defilant">
    <table class="tableau">
        <thead>
            <tr>
                <th scope="col"><span class="invisible">Foto</span></th>
                <th scope="col">Produkti</th>
                <th scope="col">Kategoria</th>
                <th scope="col">Çmimi</th>
                <th scope="col">Stoku</th>
                <th scope="col">Në dyqan</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($produits as $p): ?>
                <tr class="<?= $p['actif'] ? '' : 'ligne-inactive' ?>">
                    <td class="cellule-miniature">
                        <?php if ($p['image']): ?>
                            <img class="miniature" src="public/img/produits/<?= e($p['image']) ?>" alt="">
                        <?php else: ?>
                            <span class="miniature"></span>
                        <?php endif; ?>
                    </td>
                    <td><a href="<?= url('admin_produit', ['id' => $p['id']]) ?>"><strong><?= e($p['nom']) ?></strong></a></td>
                    <td><?= e($p['categorie']) ?></td>
                    <td><?= prix($p['prix']) ?></td>
                    <td>
                        <?= (int) $p['stock_total'] ?> copë
                        <br><small><?= (int) $p['nb_variantes'] ?> variante</small>
                    </td>
                    <td><?= $p['actif'] ? 'Po' : '<span class="texte-rouge">Jo (i fshehur)</span>' ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
