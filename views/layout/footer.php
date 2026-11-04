</main>

<footer class="pied">
    <div class="conteneur pied-ligne">
        <p><strong><?= NOM_SITE ?></strong> — trenerka, pantallona, shorce, duksa dhe maica.</p>
        <p>Dërgesa: <?= e(implode(' · ', array_keys(PAYS_LIVRAISON))) ?>. Pagesa bëhet në dorëzim.</p>
    </div>

    <?php if (CONTACT_TELEPHONE || LIEN_INSTAGRAM || LIEN_TIKTOK): ?>
        <div class="conteneur pied-ligne pied-contact">
            <?php if (CONTACT_TELEPHONE): ?>
                <?php $chiffres = preg_replace('/\D/', '', CONTACT_TELEPHONE); // wa.me veut uniquement des chiffres ?>
                <p>
                    Na kontaktoni:
                    <a href="tel:+<?= e($chiffres) ?>"><?= e(CONTACT_TELEPHONE) ?></a> ·
                    <a href="https://wa.me/<?= e($chiffres) ?>" rel="noopener" target="_blank">WhatsApp</a> ·
                    <a href="viber://chat?number=%2B<?= e($chiffres) ?>">Viber</a>
                </p>
            <?php endif; ?>
            <p>
                <?php if (LIEN_INSTAGRAM): ?><a href="<?= e(LIEN_INSTAGRAM) ?>" rel="noopener" target="_blank">Instagram</a><?php endif; ?>
                <?php if (LIEN_INSTAGRAM && LIEN_TIKTOK): ?> · <?php endif; ?>
                <?php if (LIEN_TIKTOK): ?><a href="<?= e(LIEN_TIKTOK) ?>" rel="noopener" target="_blank">TikTok</a><?php endif; ?>
            </p>
        </div>
    <?php endif; ?>
</footer>

</body>
</html>
