<?php require __DIR__ . '/_menu.php'; ?>

<h1>Porositë</h1>

<nav class="filtres" aria-label="Filtro sipas statusit">
    <a href="<?= url('admin_commandes') ?>" <?= $statutActif === null ? 'aria-current="page"' : '' ?>>Të gjitha (<?= array_sum($parStatut) ?>)</a>
    <?php foreach (STATUTS_COMMANDE as $code => $libelle): ?>
        <a href="<?= url('admin_commandes', ['statut' => $code]) ?>" <?= $statutActif === $code ? 'aria-current="page"' : '' ?>><?= e($libelle) ?> (<?= $parStatut[$code] ?>)</a>
    <?php endforeach; ?>
</nav>

<?php if (empty($commandes)): ?>
    <p class="vide">Asnjë porosi këtu.</p>
<?php else: ?>
    <div class="tableau-defilant">
        <table class="tableau">
            <thead>
                <tr>
                    <th scope="col">Nr.</th>
                    <th scope="col">Data</th>
                    <th scope="col">Klienti</th>
                    <th scope="col">Dërgesa</th>
                    <th scope="col">Artikuj</th>
                    <th scope="col">Totali</th>
                    <th scope="col">Statusi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($commandes as $c): ?>
                    <tr>
                        <td><a href="<?= url('admin_commande', ['id' => $c['id']]) ?>"><strong><?= (int) $c['id'] ?></strong></a></td>
                        <td><?= date('d.m.Y', strtotime($c['created_at'])) ?><br><small><?= date('H:i', strtotime($c['created_at'])) ?></small></td>
                        <td><?= e($c['prenom'] . ' ' . $c['nom']) ?><br><small><?= e($c['telephone']) ?></small></td>
                        <td><?= e($c['ville']) ?><br><small><?= e($c['pays']) ?></small></td>
                        <td><?= (int) $c['nb_articles'] ?></td>
                        <td><?= prix($c['total']) ?></td>
                        <td><span class="statut statut-<?= e($c['statut']) ?>"><?= e(STATUTS_COMMANDE[$c['statut']]) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
