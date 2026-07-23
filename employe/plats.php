<?php
require_once dirname(__DIR__) . '/includes/fonctions.php';
require_role(['employe', 'admin']);
$pageTitle = 'Plats';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $nom = trim($_POST['nom'] ?? '');
        $type = $_POST['type_plat'] ?? 'plat';
        $desc = trim($_POST['description'] ?? '');
        $allerg = trim($_POST['allergenes'] ?? '');
        if ($nom !== '') {
            db()->prepare('INSERT INTO plats (nom, type_plat, description) VALUES (?,?,?)')->execute([$nom, $type, $desc]);
            $pid = (int)db()->lastInsertId();
            if ($allerg !== '') {
                foreach (explode(',', $allerg) as $a) {
                    $a = trim($a);
                    if ($a !== '') {
                        db()->prepare('INSERT INTO allergenes_plat (plat_id, libelle) VALUES (?,?)')->execute([$pid, $a]);
                    }
                }
            }
        }
    }
    if ($action === 'link') {
        $menuId = (int)$_POST['menu_id'];
        $platId = (int)$_POST['plat_id'];
        db()->prepare('INSERT IGNORE INTO menu_plat (menu_id, plat_id) VALUES (?,?)')->execute([$menuId, $platId]);
    }
    if ($action === 'unlink') {
        db()->prepare('DELETE FROM menu_plat WHERE menu_id=? AND plat_id=?')->execute([(int)$_POST['menu_id'], (int)$_POST['plat_id']]);
    }
    if ($action === 'delete') {
        db()->prepare('DELETE FROM plats WHERE id=?')->execute([(int)$_POST['id']]);
    }
    header('Location: /employe/plats.php');
    exit;
}

$plats = db()->query('SELECT p.*, GROUP_CONCAT(a.libelle SEPARATOR ", ") AS allergenes
    FROM plats p LEFT JOIN allergenes_plat a ON a.plat_id = p.id
    GROUP BY p.id ORDER BY p.type_plat, p.nom')->fetchAll();
$menus = db()->query('SELECT id, titre FROM menus ORDER BY titre')->fetchAll();

require dirname(__DIR__) . '/includes/header.php';
?>

<h1 class="h3 mb-3">Plats</h1>
<p><a href="/employe/menus.php">Retour menus</a></p>

<div class="card card-menu p-3 mb-4">
    <h2 class="h5">Ajouter un plat</h2>
    <form method="post" class="row g-2">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="add">
        <div class="col-md-3"><input class="form-control" name="nom" placeholder="Nom" required></div>
        <div class="col-md-2">
            <select class="form-select" name="type_plat">
                <option value="entree">entree</option>
                <option value="plat">plat</option>
                <option value="dessert">dessert</option>
            </select>
        </div>
        <div class="col-md-3"><input class="form-control" name="description" placeholder="Description"></div>
        <div class="col-md-3"><input class="form-control" name="allergenes" placeholder="Allergenes (separes par ,)"></div>
        <div class="col-md-1"><button class="btn btn-vg w-100" type="submit">+</button></div>
    </form>
</div>

<?php foreach ($plats as $p): ?>
    <div class="border rounded p-2 mb-2 bg-white">
        <strong><?= e($p['type_plat']) ?></strong> — <?= e($p['nom']) ?>
        <?php if ($p['allergenes']): ?><span class="small text-muted">(<?= e($p['allergenes']) ?>)</span><?php endif; ?>
        <form method="post" class="d-inline float-end">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
            <button class="btn btn-link btn-sm text-danger" type="submit">suppr</button>
        </form>
        <form method="post" class="row g-1 mt-1">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="link">
            <input type="hidden" name="plat_id" value="<?= (int)$p['id'] ?>">
            <div class="col-auto">
                <select class="form-select form-select-sm" name="menu_id">
                    <?php foreach ($menus as $m): ?>
                        <option value="<?= (int)$m['id'] ?>"><?= e($m['titre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto"><button class="btn btn-sm btn-outline-secondary" type="submit">lier au menu</button></div>
        </form>
    </div>
<?php endforeach; ?>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
