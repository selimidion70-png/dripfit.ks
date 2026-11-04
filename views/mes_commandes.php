<h1>Porositë e mia</h1>

<?php if (empty($commandes)): ?>
    <p class="vide">Ende nuk keni bërë asnjë porosi.</p>
    <p><a class="bouton" href="<?= url('home') ?>">Shiko dyqanin</a></p>
<?php else: ?>
    <?php foreach ($commandes as $c): ?>
        <section class="recap commande-historique">
            <header>
                <h2>Porosia nr. <?= (int) $c['id'] ?></h2>
                <span class="statut statut-<?= e($c['statut']) ?>"><?= e(STATUTS_COMMANDE[$c['statut']] ?? $c['statut']) ?></span>
            </header>
            <p class="note">Bërë më <?= date('d.m.Y', strtotime($c['created_at'])) ?> në ora <?= date('H:i', strtotime($c['created_at'])) ?> — dërgesa në <?= e($c['ville']) ?>, <?= e($c['pays']) ?></p>
            <ul>
                <?php foreach ($c['details'] as $d): ?>
                    <li>
                        <span><?= (int) $d['quantite'] ?> × <?= e($d['nom_produit']) ?> <small>(<?= e($d['couleur']) ?> — <?= e($d['taille']) ?>)</small></span>
                        <span><?= prix($d['prix_unitaire'] * $d['quantite']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <p class="recap-total"><span>Totali</span> <strong><?= prix($c['total']) ?></strong></p>
        </section>
    <?php endforeach; ?>
<?php endif; ?>
