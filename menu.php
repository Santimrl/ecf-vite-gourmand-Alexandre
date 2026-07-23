<?php
require_once __DIR__ . '/includes/fonctions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = db()->prepare('SELECT * FROM menus WHERE id = ? AND actif = 1');
$stmt->execute([$id]);
$menu = $stmt->fetch();
if (!$menu) {
    http_response_code(404);
    die('Menu introuvable');
}

$imgs = db()->prepare('SELECT chemin FROM menu_images WHERE menu_id = ?');
$imgs->execute([$id]);
$images = $imgs->fetchAll();

$plats = db()->prepare('SELECT p.*, GROUP_CONCAT(a.libelle SEPARATOR ", ") AS allergenes
    FROM plats p
    JOIN menu_plat mp ON mp.plat_id = p.id
    LEFT JOIN allergenes_plat a ON a.plat_id = p.id
    WHERE mp.menu_id = ?
    GROUP BY p.id
    ORDER BY FIELD(p.type_plat,"entree","plat","dessert"), p.nom');
$plats->execute([$id]);
$listePlats = $plats->fetchAll();

$pageTitle = $menu['titre'];
require __DIR__ . '/includes/header.php';
?>

<article>
    <h1 class="h3"><?= e($menu['titre']) ?></h1>
    <p><?= nl2br(e($menu['description'])) ?></p>

    <div class="row g-3 mb-3">
        <?php foreach ($images as $img): ?>
            <div class="col-md-4">
                <img class="img-fluid rounded border" src="/<?= e(ltrim($img['chemin'], '/')) ?>" alt="Photo du menu <?= e($menu['titre']) ?>">
            </div>
        <?php endforeach; ?>
    </div>

    <ul>
        <li>Theme : <strong><?= e(label_theme($menu['theme'])) ?></strong></li>
        <li>Regime : <strong><?= e(label_regime($menu['regime'])) ?></strong></li>
        <li>Minimum : <strong><?= (int)$menu['nb_personnes_min'] ?> personnes</strong></li>
        <li>Prix (pour ce minimum) : <strong><?= e($menu['prix_min']) ?> €</strong></li>
        <li>Stock restant : <strong><?= (int)$menu['stock'] ?></strong> commandes possibles</li>
    </ul>

    <h2 class="h5">Plats</h2>
    <ul>
        <?php foreach ($listePlats as $p): ?>
            <li>
                <strong><?= e(ucfirst($p['type_plat'])) ?></strong> — <?= e($p['nom']) ?>
                <?php if (!empty($p['allergenes'])): ?>
                    <span class="text-muted small">(allergènes : <?= e($p['allergenes']) ?>)</span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="conditions-box my-4">
        <strong>Attention — conditions du menu</strong>
        <p class="mb-0 mt-2"><?= nl2br(e($menu['conditions_menu'])) ?></p>
    </div>

    <?php if ((int)$menu['stock'] <= 0): ?>
        <div class="alert alert-warning">Stock épuisé pour ce menu.</div>
    <?php else: ?>
        <a class="btn btn-vg btn-lg" href="commande.php?menu_id=<?= (int)$menu['id'] ?>">Commander</a>
    <?php endif; ?>
    <a class="btn btn-outline-secondary" href="menus.php">Retour aux menus</a>
</article>

<?php require __DIR__ . '/includes/footer.php'; ?>
