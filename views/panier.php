<h1>Shporta</h1>

<?php if (empty($lignes)): ?>
    <p class="vide">Shporta juaj është e zbrazët.</p>
    <p><a class="bouton" href="<?= url('home') ?>">Shiko dyqanin</a></p>
<?php else: ?>
    <div class="tableau-defilant">
        <table class="tableau">
            <thead>
                <tr>
                    <th scope="col">Artikulli</th>
                    <th scope="col">Çmimi</th>
                    <th scope="col">Sasia</th>
                    <th scope="col">Nëntotali</th>
                    <th scope="col"><span class="invisible">Veprimet</span></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lignes as $l): ?>
                    <tr>
                        <td>
                            <a href="<?= url('produit', ['id' => $l['produit_id']]) ?>"><?= e($l['nom']) ?></a><br>
                            <small><?= e($l['couleur']) ?> — <?= e($l['taille']) ?></small>
                        </td>
                        <td><?= prix($l['prix']) ?></td>
                        <td>
                            <form method="post" action="<?= url('panier_modifier') ?>" class="form-quantite">
                                <?= csrf_champ() ?>
                                <input type="hidden" name="variante_id" value="<?= $l['variante_id'] ?>">
                                <label class="invisible" for="q<?= $l['variante_id'] ?>">Sasia për <?= e($l['nom']) ?></label>
                                <input type="number" id="q<?= $l['variante_id'] ?>" name="quantite" value="<?= $l['quantite'] ?>" min="0" max="<?= $l['stock'] ?>">
                                <button type="submit" class="bouton bouton-secondaire">OK</button>
                            </form>
                        </td>
                        <td><?= prix($l['sous_total']) ?></td>
                        <td>
                            <form method="post" action="<?= url('panier_supprimer') ?>">
                                <?= csrf_champ() ?>
                                <input type="hidden" name="variante_id" value="<?= $l['variante_id'] ?>">
                                <button type="submit" class="lien" aria-label="Hiq <?= e($l['nom']) ?> nga shporta">Hiq</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="total-panier">
        <p>Nëntotali: <strong><?= prix($sousTotal) ?></strong></p>
        <a class="bouton" href="<?= url('commande') ?>">Vazhdo me porosinë</a>
    </div>
<?php endif; ?>
