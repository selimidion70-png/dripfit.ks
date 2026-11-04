<?php require __DIR__ . '/_menu.php'; ?>

<h1>Paneli</h1>

<ul class="chiffres">
    <li>
        <a href="<?= url('admin_commandes', ['statut' => 'en_attente']) ?>">
            <span class="chiffre"><?= $parStatut['en_attente'] ?></span>
            <span>Porosi në pritje</span>
        </a>
    </li>
    <li>
        <a href="<?= url('admin_commandes', ['statut' => 'confirmee']) ?>">
            <span class="chiffre"><?= $parStatut['confirmee'] ?></span>
            <span>Për t'u dërguar</span>
        </a>
    </li>
    <li>
        <a href="<?= url('admin_commandes', ['statut' => 'expediee']) ?>">
            <span class="chiffre"><?= $parStatut['expediee'] ?></span>
            <span>Në rrugë</span>
        </a>
    </li>
    <li>
        <a href="<?= url('admin_commandes', ['statut' => 'livree']) ?>">
            <span class="chiffre"><?= prix($totalEncaisse) ?></span>
            <span>Të arkëtuara (<?= $parStatut['livree'] ?> të dorëzuara)</span>
        </a>
    </li>
</ul>

<div class="deux-colonnes">
    <section class="recap">
        <h2>Porositë e reja</h2>
        <?php if (empty($enAttente)): ?>
            <p class="vide">Asnjë porosi në pritje.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($enAttente as $c): ?>
                    <li>
                        <span>
                            <a href="<?= url('admin_commande', ['id' => $c['id']]) ?>">Nr. <?= (int) $c['id'] ?> — <?= e($c['prenom'] . ' ' . $c['nom']) ?></a><br>
                            <small><?= e($c['ville']) ?>, <?= e($c['pays']) ?> · <?= date('d.m.Y H:i', strtotime($c['created_at'])) ?></small>
                        </span>
                        <span><?= prix($c['total']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <section class="recap">
        <h2>Stoku po mbaron</h2>
        <?php if (empty($stockFaible)): ?>
            <p class="vide">Të gjitha variantet kanë më shumë se 3 copë.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($stockFaible as $v): ?>
                    <li>
                        <span>
                            <a href="<?= url('admin_produit', ['id' => $v['produit_id']]) ?>"><?= e($v['nom']) ?></a><br>
                            <small><?= e($v['couleur']) ?> — <?= e($v['taille']) ?></small>
                        </span>
                        <span class="<?= (int) $v['stock'] === 0 ? 'texte-rouge' : '' ?>"><?= (int) $v['stock'] ?> copë</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</div>
