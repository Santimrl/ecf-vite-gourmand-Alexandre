<?php
require_once dirname(__DIR__) . '/includes/fonctions.php';
require_role(['employe', 'admin']);
$pageTitle = 'Menus';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $titre = trim($_POST['titre'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $theme = $_POST['theme'] ?? 'classique';
        $regime = $_POST['regime'] ?? 'classique';
        $nb = (int)($_POST['nb_personnes_min'] ?? 2);
        $prix = (float)($_POST['prix_min'] ?? 0);
        $cond = trim($_POST['conditions_menu'] ?? '');
        $stock = (int)($_POST['stock'] ?? 0);
        $actif = isset($_POST['actif']) ? 1 : 0;
        $platsChoisis = isset($_POST['plats']) && is_array($_POST['plats']) ? array_map('intval', $_POST['plats']) : [];

        if ($titre !== '') {
            if ($id > 0) {
                $sql = 'UPDATE menus SET titre=?, description=?, theme=?, regime=?, nb_personnes_min=?, prix_min=?, conditions_menu=?, stock=?, actif=? WHERE id=?';
                db()->prepare($sql)->execute([$titre, $description, $theme, $regime, $nb, $prix, $cond, $stock, $actif, $id]);
            } else {
                $sql = 'INSERT INTO menus (titre, description, theme, regime, nb_personnes_min, prix_min, conditions_menu, stock, actif) VALUES (?,?,?,?,?,?,?,?,?)';
                db()->prepare($sql)->execute([$titre, $description, $theme, $regime, $nb, $prix, $cond, $stock, $actif]);
                $id = (int)db()->lastInsertId();
            }

            db()->prepare('DELETE FROM menu_plat WHERE menu_id = ?')->execute([$id]);
            $link = db()->prepare('INSERT INTO menu_plat (menu_id, plat_id) VALUES (?,?)');
            foreach ($platsChoisis as $pid) {
                if ($pid > 0) $link->execute([$id, $pid]);
            }

            if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $tmp = $_FILES['image']['tmp_name'];
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($tmp);
                $okMime = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
                if (isset($okMime[$mime])) {
                    $name = 'menu_' . $id . '_' . time() . '.' . $okMime[$mime];
                    $destRel = 'uploads/menus/' . $name;
                    $destAbs = APP_ROOT . '/' . $destRel;
                    if (move_uploaded_file($tmp, $destAbs)) {
                        db()->prepare('INSERT INTO menu_images (menu_id, chemin) VALUES (?,?)')->execute([$id, $destRel]);
                    }
                }
            }
        }
        header('Location: /employe/menus.php?edit=' . $id);
        exit;
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        db()->prepare('DELETE FROM menus WHERE id = ?')->execute([$id]);
        header('Location: /employe/menus.php');
        exit;
    }

    if ($action === 'delimg') {
        $imgId = (int)($_POST['img_id'] ?? 0);
        $menuId = (int)($_POST['menu_id'] ?? 0);
        $img = db()->prepare('SELECT * FROM menu_images WHERE id=? AND menu_id=?');
        $img->execute([$imgId, $menuId]);
        $row = $img->fetch();
        if ($row) {
            $path = APP_ROOT . '/' . $row['chemin'];
            if (is_file($path) && strpos($row['chemin'], 'uploads/menus/') === 0) {
                @unlink($path);
            }
            db()->prepare('DELETE FROM menu_images WHERE id=?')->execute([$imgId]);
        }
        header('Location: /employe/menus.php?edit=' . $menuId);
        exit;
    }
}

$menus = db()->query('SELECT * FROM menus ORDER BY id DESC')->fetchAll();
$edit = null;
$platsMenu = [];
$imagesMenu = [];
if (!empty($_GET['edit'])) {
    $st = db()->prepare('SELECT * FROM menus WHERE id = ?');
    $st->execute([(int)$_GET['edit']]);
    $edit = $st->fetch();
    if ($edit) {
        $pm = db()->prepare('SELECT plat_id FROM menu_plat WHERE menu_id = ?');
        $pm->execute([(int)$edit['id']]);
        $platsMenu = array_column($pm->fetchAll(), 'plat_id');
        $im = db()->prepare('SELECT * FROM menu_images WHERE menu_id = ?');
        $im->execute([(int)$edit['id']]);
        $imagesMenu = $im->fetchAll();
    }
}

$tousPlats = db()->query('SELECT * FROM plats ORDER BY FIELD(type_plat,"entree","plat","dessert"), nom')->fetchAll();

require dirname(__DIR__) . '/includes/header.php';
?>

<h1 class="h3 mb-3">Gerer les menus</h1>
<p><a href="/employe/index.php">Retour</a> · <a href="/employe/horaires.php">Horaires</a> · <a href="/employe/plats.php">Plats</a></p>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card card-menu p-3">
            <h2 class="h5"><?= $edit ? 'Modifier menu #'.(int)$edit['id'] : 'Nouveau menu' ?></h2>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" value="<?= $edit ? (int)$edit['id'] : 0 ?>">
                <div class="mb-2"><label class="form-label" for="titre">Titre</label><input class="form-control" id="titre" name="titre" required value="<?= e($edit['titre'] ?? '') ?>"></div>
                <div class="mb-2"><label class="form-label" for="description">Description</label><textarea class="form-control" id="description" name="description" rows="3" required><?= e($edit['description'] ?? '') ?></textarea></div>
                <div class="mb-2"><label class="form-label" for="theme">Theme</label>
                    <select class="form-select" id="theme" name="theme">
                        <?php foreach (['classique','Noel','Paques','evenement'] as $t): ?>
                            <option <?= (($edit['theme'] ?? '') === $t) ? 'selected' : '' ?>><?= e($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-2"><label class="form-label" for="regime">Regime</label>
                    <select class="form-select" id="regime" name="regime">
                        <?php foreach (['classique','vegetarien','vegan'] as $t): ?>
                            <option <?= (($edit['regime'] ?? '') === $t) ? 'selected' : '' ?>><?= e($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="row">
                    <div class="col-6 mb-2"><label class="form-label" for="nb_personnes_min">Nb min</label><input class="form-control" type="number" id="nb_personnes_min" name="nb_personnes_min" value="<?= e($edit['nb_personnes_min'] ?? 2) ?>"></div>
                    <div class="col-6 mb-2"><label class="form-label" for="prix_min">Prix min</label><input class="form-control" type="number" step="0.01" id="prix_min" name="prix_min" value="<?= e($edit['prix_min'] ?? 50) ?>"></div>
                </div>
                <div class="mb-2"><label class="form-label" for="conditions_menu">Conditions</label><textarea class="form-control" id="conditions_menu" name="conditions_menu" rows="2" required><?= e($edit['conditions_menu'] ?? '') ?></textarea></div>
                <div class="mb-2"><label class="form-label" for="stock">Stock</label><input class="form-control" type="number" id="stock" name="stock" value="<?= e($edit['stock'] ?? 5) ?>"></div>

                <div class="mb-3">
                    <label class="form-label">Plats du menu</label>
                    <div class="border rounded p-2" style="max-height:180px;overflow:auto">
                        <?php if (!$tousPlats): ?>
                            <p class="small text-muted mb-0">Aucun plat. Cree-en dans Plats.</p>
                        <?php endif; ?>
                        <?php foreach ($tousPlats as $p): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="plats[]" value="<?= (int)$p['id'] ?>" id="plat<?= (int)$p['id'] ?>"
                                    <?= in_array((int)$p['id'], array_map('intval', $platsMenu), true) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="plat<?= (int)$p['id'] ?>">
                                    <?= e($p['type_plat']) ?> — <?= e($p['nom']) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="image">Ajouter une image</label>
                    <input class="form-control" type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp,image/gif">
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="actif" id="actif" <?= !isset($edit['actif']) || $edit['actif'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="actif">Actif (visible sur le site)</label>
                </div>
                <button class="btn btn-vg" type="submit">Enregistrer</button>
                <?php if ($edit): ?><a class="btn btn-link" href="/employe/menus.php">Nouveau</a><?php endif; ?>
            </form>

            <?php if ($imagesMenu): ?>
                <hr>
                <h3 class="h6">Galerie</h3>
                <div class="row g-2">
                    <?php foreach ($imagesMenu as $img): ?>
                        <div class="col-6">
                            <img src="/<?= e($img['chemin']) ?>" alt="" class="img-fluid rounded border mb-1">
                            <form method="post" onsubmit="return confirm('Supprimer cette image ?');">
                                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="action" value="delimg">
                                <input type="hidden" name="img_id" value="<?= (int)$img['id'] ?>">
                                <input type="hidden" name="menu_id" value="<?= (int)$edit['id'] ?>">
                                <button class="btn btn-sm btn-outline-danger" type="submit">suppr</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="table-responsive">
            <table class="table table-sm bg-white">
                <thead><tr><th>ID</th><th>Titre</th><th>Prix</th><th>Stock</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($menus as $m): ?>
                    <tr>
                        <td><?= (int)$m['id'] ?></td>
                        <td><?= e($m['titre']) ?><?= $m['actif'] ? '' : ' (off)' ?></td>
                        <td><?= e($m['prix_min']) ?> €</td>
                        <td><?= (int)$m['stock'] ?></td>
                        <td class="text-nowrap">
                            <a href="/employe/menus.php?edit=<?= (int)$m['id'] ?>">modif</a>
                            <form method="post" class="d-inline" onsubmit="return confirm('Supprimer ?');">
                                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                                <button class="btn btn-link btn-sm text-danger p-0" type="submit">suppr</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
