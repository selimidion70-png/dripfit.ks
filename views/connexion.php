<div class="page-etroite">
    <h1>Kyçu</h1>

    <?php if ($erreur): ?>
        <p class="message message-erreur" role="alert"><?= e($erreur) ?></p>
    <?php endif; ?>

    <form method="post" action="<?= url('connexion') ?>" class="formulaire">
        <?= csrf_champ() ?>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= e($email) ?>" required autocomplete="email">

        <label for="mot_de_passe">Fjalëkalimi</label>
        <input type="password" id="mot_de_passe" name="mot_de_passe" required autocomplete="current-password">

        <button type="submit" class="bouton">Kyçu</button>
    </form>

    <p class="note">Nuk keni llogari? <a href="<?= url('inscription') ?>">Regjistrohuni</a></p>
</div>
