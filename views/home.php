<section class="bandeau">
    <?php if ($categorieActive): ?>
        <h1><?= e($categorieActive['nom']) ?></h1>
    <?php else: ?>
        <h1>Dripfit<span class="logo-ks">.ks</span></h1>
    <?php endif; ?>
    <p>Dërgojmë në Kosovë, Shqipëri dhe Maqedoninë e Veriut. Pagesa bëhet në dorëzim.</p>
</section>

<?php if (empty($produits)): ?>
    <p class="vide">Për momentin nuk ka produkte në këtë kategori.</p>
<?php else: ?>
    <ul class="grille-produits">
        <?php foreach ($produits as $p): ?>
            <li>
                <a class="carte-produit" href="<?= url('produit', ['id' => $p['id']]) ?>">
                    <div class="visuel">
                        <?php if ($p['image']): ?>
                            <img src="public/img/produits/<?= e($p['image']) ?>" alt="<?= e($p['nom']) ?>">
                        <?php else: ?>
                            <svg viewBox="0 0 200 200" aria-hidden="true"><path fill="currentColor" fill-rule="evenodd" d="M36 20 H100 A80 80 0 0 1 100 180 H36 Z M96 58 C96 58 64 100 64 122 A32 32 0 0 0 128 122 C128 100 96 58 96 58 Z"/></svg>
                        <?php endif; ?>
                        <?php if ((int) $p['stock_total'] === 0): ?>
                            <span class="etiquette">Pa stok</span>
                        <?php endif; ?>
                    </div>
                    <span class="carte-categorie"><?= e($p['categorie']) ?></span>
                    <span class="carte-nom"><?= e($p['nom']) ?></span>
                    <span class="carte-prix"><?= prix($p['prix']) ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
