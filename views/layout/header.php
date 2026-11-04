<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= !empty($titre) ? e($titre) . ' — ' : '' ?><?= NOM_SITE ?></title>
    <link rel="icon" type="image/svg+xml" href="public/img/icone.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600&family=Barlow+Condensed:ital,wght@0,600;0,700;1,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>

<header class="entete">
    <div class="conteneur entete-ligne">
        <a class="logo" href="<?= url('home') ?>" aria-label="<?= NOM_SITE ?>, ballina">
            <svg width="34" height="34" viewBox="0 0 200 200" aria-hidden="true">
                <path fill="currentColor" fill-rule="evenodd" d="M36 20 H100 A80 80 0 0 1 100 180 H36 Z M96 58 C96 58 64 100 64 122 A32 32 0 0 0 128 122 C128 100 96 58 96 58 Z"/>
            </svg>
            <span class="logo-texte">Dripfit<span class="logo-ks">.ks</span></span>
        </a>

        <nav class="menu-compte" aria-label="Llogaria dhe shporta">
            <?php if ($u = utilisateur_connecte()): ?>
                <?php if (est_admin()): ?>
                    <a class="lien-admin" href="<?= url('admin') ?>">Admin</a>
                <?php endif; ?>
                <a href="<?= url('mes_commandes') ?>">Porositë e mia</a>
                <form method="post" action="<?= url('deconnexion') ?>">
                    <?= csrf_champ() ?>
                    <button type="submit" class="lien">Çkyçu</button>
                </form>
            <?php else: ?>
                <a href="<?= url('connexion') ?>">Kyçu</a>
                <a href="<?= url('inscription') ?>">Regjistrohu</a>
            <?php endif; ?>
            <a class="lien-panier" href="<?= url('panier') ?>">Shporta <span class="pastille"><?= (int) $nbArticlesPanier ?></span></a>
        </nav>
    </div>

    <nav class="menu-categories" aria-label="Kategoritë">
        <div class="conteneur">
            <a href="<?= url('home') ?>" <?= empty($categorieActive) && ($_GET['page'] ?? 'home') === 'home' ? 'aria-current="page"' : '' ?>>Të gjitha</a>
            <?php foreach ($categoriesMenu as $cat): ?>
                <a href="<?= url('home', ['categorie' => $cat['slug']]) ?>" <?= !empty($categorieActive) && $categorieActive['id'] === $cat['id'] ? 'aria-current="page"' : '' ?>><?= e($cat['nom']) ?></a>
            <?php endforeach; ?>
        </div>
    </nav>
</header>

<main class="conteneur contenu">
    <?php if ($flash): ?>
        <p class="message message-<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></p>
    <?php endif; ?>
