<?php require __DIR__ . '/_menu.php'; ?>

<p class="fil"><a href="<?= url('admin_commandes') ?>">Porositë</a> / Nr. <?= (int) $commande['id'] ?></p>

<div class="titre-ligne">
    <h1>Porosia nr. <?= (int) $commande['id'] ?></h1>
    <span class="statut statut-<?= e($commande['statut']) ?>"><?= e(STATUTS_COMMANDE[$commande['statut']]) ?></span>
</div>
<p class="note">Bërë më <?= date('d.m.Y', strtotime($commande['created_at'])) ?> në ora <?= date('H:i', strtotime($commande['created_at'])) ?><?= $commande['utilisateur_id'] ? ' · klient me llogari' : ' · pa llogari' ?></p>

<div class="deux-colonnes">
    <section class="recap">
        <h2>Artikujt</h2>
        <ul>
            <?php foreach ($details as $d): ?>
                <li>
                    <span><?= (int) $d['quantite'] ?> × <?= e($d['nom_produit']) ?><br><small><?= e($d['couleur']) ?> — <?= e($d['taille']) ?> · <?= prix($d['prix_unitaire']) ?> copa</small></span>
                    <span><?= prix($d['prix_unitaire'] * $d['quantite']) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
        <p class="recap-total"><span>Për t'u arkëtuar</span> <strong><?= prix($commande['total']) ?></strong></p>
    </section>

    <div>
        <section class="recap">
            <h2>Klienti</h2>
            <p>
                <strong><?= e($commande['prenom'] . ' ' . $commande['nom']) ?></strong><br>
                <a href="tel:<?= e(str_replace(' ', '', $commande['telephone'])) ?>"><?= e($commande['telephone']) ?></a><br>
                <?php if ($commande['email']): ?>
                    <a href="mailto:<?= e($commande['email']) ?>"><?= e($commande['email']) ?></a><br>
                <?php endif; ?>
            </p>
            <p>
                <?= e($commande['adresse']) ?><br>
                <?= e($commande['ville']) ?>, <?= e($commande['pays']) ?>
            </p>
        </section>

        <section class="recap bloc-statut">
            <h2>Statusi</h2>
            <?php if ($commande['statut'] === 'annulee'): ?>
                <p class="note">Kjo porosi është anuluar dhe artikujt janë kthyer në stok. Nuk mund të ndryshohet më.</p>
            <?php else: ?>
                <form method="post" action="<?= url('admin_commande', ['id' => $commande['id']]) ?>" class="formulaire"
                      onsubmit="return this.statut.value !== 'annulee' || confirm('Ta anuloj porosinë? Artikujt kthehen në stok dhe kjo nuk mund të zhbëhet.');">
                    <?= csrf_champ() ?>
                    <label for="statut">Ndrysho statusin</label>
                    <select id="statut" name="statut">
                        <?php foreach (STATUTS_COMMANDE as $code => $libelle): ?>
                            <option value="<?= e($code) ?>" <?= $commande['statut'] === $code ? 'selected' : '' ?>><?= e($libelle) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="bouton">Ruaj</button>
                </form>
            <?php endif; ?>
        </section>
    </div>
</div>
