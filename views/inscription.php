<?php
$erreur = fn(string $champ) => isset($erreurs[$champ]) ? '<span class="erreur-champ">' . e($erreurs[$champ]) . '</span>' : '';
?>
<div class="page-etroite">
    <h1>Krijo llogari</h1>

    <form method="post" action="<?= url('inscription') ?>" class="formulaire" novalidate>
        <?= csrf_champ() ?>

        <label for="prenom">Emri</label>
        <input type="text" id="prenom" name="prenom" value="<?= e($donnees['prenom']) ?>" required autocomplete="given-name">
        <?= $erreur('prenom') ?>

        <label for="nom">Mbiemri</label>
        <input type="text" id="nom" name="nom" value="<?= e($donnees['nom']) ?>" required autocomplete="family-name">
        <?= $erreur('nom') ?>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= e($donnees['email']) ?>" required autocomplete="email">
        <?= $erreur('email') ?>

        <label for="telephone">Telefoni (opsional)</label>
        <input type="tel" id="telephone" name="telephone" value="<?= e($donnees['telephone']) ?>" autocomplete="tel">

        <label for="mot_de_passe">Fjalëkalimi (së paku 8 karaktere)</label>
        <input type="password" id="mot_de_passe" name="mot_de_passe" required autocomplete="new-password">
        <?= $erreur('mot_de_passe') ?>

        <label for="confirmation">Përsërit fjalëkalimin</label>
        <input type="password" id="confirmation" name="confirmation" required autocomplete="new-password">
        <?= $erreur('confirmation') ?>

        <button type="submit" class="bouton">Krijo llogarinë</button>
    </form>

    <p class="note">Keni llogari? <a href="<?= url('connexion') ?>">Kyçuni</a></p>
</div>
