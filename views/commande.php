<?php
// Petit helper local : affiche l'erreur d'un champ s'il y en a une
$erreur = fn(string $champ) => isset($erreurs[$champ]) ? '<span class="erreur-champ">' . e($erreurs[$champ]) . '</span>' : '';
?>
<h1>Porosia</h1>

<?php if (isset($erreurs['stock'])): ?>
    <p class="message message-erreur"><?= e($erreurs['stock']) ?> <a href="<?= url('panier') ?>">Shiko shportën</a></p>
<?php endif; ?>

<div class="deux-colonnes">
    <form method="post" action="<?= url('commande') ?>" class="formulaire" novalidate>
        <?= csrf_champ() ?>
        <h2>Adresa e dorëzimit</h2>

        <?php if (!utilisateur_connecte()): ?>
            <p class="note">Keni llogari? <a href="<?= url('connexion') ?>">Kyçuni</a> që kjo porosi të ruhet te porositë tuaja.</p>
        <?php endif; ?>

        <label for="prenom">Emri</label>
        <input type="text" id="prenom" name="prenom" value="<?= e($client['prenom']) ?>" required autocomplete="given-name">
        <?= $erreur('prenom') ?>

        <label for="nom">Mbiemri</label>
        <input type="text" id="nom" name="nom" value="<?= e($client['nom']) ?>" required autocomplete="family-name">
        <?= $erreur('nom') ?>

        <label for="telephone">Telefoni (postieri do t'ju telefonojë)</label>
        <input type="tel" id="telephone" name="telephone" value="<?= e($client['telephone']) ?>" required autocomplete="tel" placeholder="+383 44 123 456">
        <?= $erreur('telephone') ?>

        <label for="email">Email (opsional)</label>
        <input type="email" id="email" name="email" value="<?= e($client['email']) ?>" autocomplete="email">
        <?= $erreur('email') ?>

        <label for="adresse">Adresa</label>
        <input type="text" id="adresse" name="adresse" value="<?= e($client['adresse']) ?>" required autocomplete="street-address">
        <?= $erreur('adresse') ?>

        <label for="ville">Qyteti</label>
        <input type="text" id="ville" name="ville" value="<?= e($client['ville']) ?>" required autocomplete="address-level2">
        <?= $erreur('ville') ?>

        <label for="pays">Shteti</label>
        <select id="pays" name="pays" required>
            <?php foreach (PAYS_LIVRAISON as $pays => $frais): ?>
                <option value="<?= e($pays) ?>" <?= $client['pays'] === $pays ? 'selected' : '' ?>>
                    <?= e($pays) ?><?= $frais > 0 ? ' (dërgesa ' . prix($frais) . ')' : '' ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?= $erreur('pays') ?>

        <button type="submit" class="bouton">Konfirmo porosinë</button>
        <p class="note">Nuk ka pagesë online: paguani me para në dorë kur ta merrni porosinë.</p>
    </form>

    <aside class="recap">
        <h2>Shporta juaj</h2>
        <ul>
            <?php foreach ($lignes as $l): ?>
                <li>
                    <span><?= $l['quantite'] ?> × <?= e($l['nom']) ?><br><small><?= e($l['couleur']) ?> — <?= e($l['taille']) ?></small></span>
                    <span><?= prix($l['sous_total']) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
        <p class="recap-total"><span>Nëntotali</span> <strong><?= prix($sousTotal) ?></strong></p>
    </aside>
</div>
