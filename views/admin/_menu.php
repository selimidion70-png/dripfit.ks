<?php
// Sous-menu de l'espace admin, inclus en haut de chaque vue admin
$pageAdmin = $_GET['page'] ?? 'admin';
$liensAdmin = [
    'admin'           => 'Paneli',
    'admin_commandes' => 'Porositë',
    'admin_produits'  => 'Produktet',
];
// La fiche d'une commande ou d'un produit garde l'onglet correspondant actif
$ongletActif = ['admin_commande' => 'admin_commandes', 'admin_produit' => 'admin_produits'][$pageAdmin] ?? $pageAdmin;
?>
<nav class="menu-admin" aria-label="Administrimi">
    <?php foreach ($liensAdmin as $page => $libelle): ?>
        <a href="<?= url($page) ?>" <?= $ongletActif === $page ? 'aria-current="page"' : '' ?>><?= e($libelle) ?></a>
    <?php endforeach; ?>
</nav>
