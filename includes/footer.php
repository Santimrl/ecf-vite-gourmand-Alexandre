<?php
$horaires = [];
try {
    $horaires = horaires_footer();
} catch (Exception $e) {
    $horaires = [];
}
?>
</main>
<footer class="footer-vg text-white mt-5 py-4">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5><?= e(APP_NAME) ?></h5>
                <p class="mb-1">Traiteur à Bordeaux depuis 25 ans — Julie &amp; José</p>
                <p class="small mb-0">
                    <a class="link-light" href="/mentions-legales.php">Mentions legales</a> ·
                    <a class="link-light" href="/cgv.php">CGV</a> ·
                    <a class="link-light" href="/confidentialite.php">Confidentialite</a> ·
                    <a class="link-light" href="/contact.php">Contact</a>
                </p>
            </div>
            <div class="col-md-6">
                <h6>Horaires</h6>
                <ul class="list-unstyled small mb-0">
                    <?php if (!$horaires): ?>
                        <li>Horaires bientôt disponibles</li>
                    <?php else: ?>
                        <?php foreach ($horaires as $h): ?>
                            <li>
                                <?= e(ucfirst($h['jour'])) ?> :
                                <?php if ($h['ferme']): ?>
                                    fermé
                                <?php else: ?>
                                    <?= e(substr($h['ouverture'], 0, 5)) ?> – <?= e(substr($h['fermeture'], 0, 5)) ?>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
