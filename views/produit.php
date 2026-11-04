<?php
$enStock = array_filter($variantes, fn($v) => $v['stock'] > 0);
?>
<p class="fil"><a href="<?= url('home') ?>"><?= NOM_SITE ?></a> / <a href="<?= url('home', ['categorie' => $produit['categorie_slug']]) ?>"><?= e($produit['categorie']) ?></a></p>

<article class="fiche">
    <div class="visuel visuel-grand">
        <?php if ($produit['image']): ?>
            <img src="public/img/produits/<?= e($produit['image']) ?>" alt="<?= e($produit['nom']) ?>">
        <?php else: ?>
            <svg viewBox="0 0 200 200" aria-hidden="true"><path fill="currentColor" fill-rule="evenodd" d="M36 20 H100 A80 80 0 0 1 100 180 H36 Z M96 58 C96 58 64 100 64 122 A32 32 0 0 0 128 122 C128 100 96 58 96 58 Z"/></svg>
        <?php endif; ?>
    </div>

    <div class="fiche-infos">
        <h1><?= e($produit['nom']) ?></h1>
        <p class="fiche-prix"><?= prix($produit['prix']) ?></p>
        <p><?= nl2br(e($produit['description'])) ?></p>

        <?php if (empty($enStock)): ?>
            <p class="message message-erreur">Ky produkt nuk është në stok.</p>
        <?php else: ?>
            <form method="post" action="<?= url('panier_ajouter') ?>" class="formulaire">
                <?= csrf_champ() ?>

                <label for="variante_id">Ngjyra dhe madhësia</label>
                <select id="variante_id" name="variante_id" required>
                    <option value="">Zgjidhni…</option>
                    <?php foreach ($variantes as $v): ?>
                        <option value="<?= (int) $v['id'] ?>" <?= $v['stock'] <= 0 ? 'disabled' : '' ?>>
                            <?= e($v['couleur']) ?> — <?= e($v['taille']) ?><?= $v['stock'] <= 0 ? ' (pa stok)' : ($v['stock'] <= 3 ? ' (vetëm ' . (int) $v['stock'] . ' copë)' : '') ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="quantite">Sasia</label>
                <input type="number" id="quantite" name="quantite" value="1" min="1" max="20" required>

                <button type="submit" class="bouton">Shto në shportë</button>
            </form>
        <?php endif; ?>

        <p class="note">Pagesa bëhet në dorëzim — Kosovë, Shqipëri, Maqedonia e Veriut.</p>
    </div>
</article>
