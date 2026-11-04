<section class="bandeau">
    <h1>Faleminderit, <?= e($commande['prenom']) ?>!</h1>
    <p>Porosia juaj nr. <?= (int) $commande['id'] ?> u regjistrua. Do t'ju telefonojmë në <?= e($commande['telephone']) ?> për ta konfirmuar dërgesën.</p>
</section>

<div class="deux-colonnes">
    <section class="recap">
        <h2>Artikujt</h2>
        <ul>
            <?php foreach ($details as $d): ?>
                <li>
                    <span><?= (int) $d['quantite'] ?> × <?= e($d['nom_produit']) ?><br><small><?= e($d['couleur']) ?> — <?= e($d['taille']) ?></small></span>
                    <span><?= prix($d['prix_unitaire'] * $d['quantite']) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
        <p class="recap-total"><span>Totali për t'u paguar në dorëzim</span> <strong><?= prix($commande['total']) ?></strong></p>
    </section>

    <section class="recap">
        <h2>Dërgesa</h2>
        <p>
            <?= e($commande['prenom'] . ' ' . $commande['nom']) ?><br>
            <?= e($commande['adresse']) ?><br>
            <?= e($commande['ville']) ?>, <?= e($commande['pays']) ?>
        </p>
        <p><a class="bouton" href="<?= url('home') ?>">Kthehu te dyqani</a></p>
    </section>
</div>
