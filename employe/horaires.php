<?php
require_once dirname(__DIR__) . '/includes/fonctions.php';
require_role(['employe', 'admin']);
$pageTitle = 'Horaires';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    foreach ($_POST['jour'] as $id => $ignore) {
        $id = (int)$id;
        $ouv = $_POST['ouverture'][$id] !== '' ? $_POST['ouverture'][$id] : null;
        $ferm = $_POST['fermeture'][$id] !== '' ? $_POST['fermeture'][$id] : null;
        $ferme = isset($_POST['ferme'][$id]) ? 1 : 0;
        db()->prepare('UPDATE horaires SET ouverture=?, fermeture=?, ferme=? WHERE id=?')
            ->execute([$ouv, $ferm, $ferme, $id]);
    }
    header('Location: /employe/horaires.php?ok=1');
    exit;
}

$rows = db()->query('SELECT * FROM horaires ORDER BY FIELD(jour,"lundi","mardi","mercredi","jeudi","vendredi","samedi","dimanche")')->fetchAll();
require dirname(__DIR__) . '/includes/header.php';
?>

<h1 class="h3 mb-3">Horaires</h1>
<p><a href="/employe/menus.php">Retour menus</a></p>
<?php if (!empty($_GET['ok'])): ?><div class="alert alert-success">Enregistre.</div><?php endif; ?>

<form method="post" class="card card-menu p-3">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <?php foreach ($rows as $h): ?>
        <div class="row g-2 align-items-center mb-2">
            <div class="col-md-2"><strong><?= e(ucfirst($h['jour'])) ?></strong>
                <input type="hidden" name="jour[<?= (int)$h['id'] ?>]" value="1">
            </div>
            <div class="col-md-3"><input class="form-control" type="time" name="ouverture[<?= (int)$h['id'] ?>]" value="<?= e($h['ouverture'] ? substr($h['ouverture'],0,5) : '') ?>"></div>
            <div class="col-md-3"><input class="form-control" type="time" name="fermeture[<?= (int)$h['id'] ?>]" value="<?= e($h['fermeture'] ? substr($h['fermeture'],0,5) : '') ?>"></div>
            <div class="col-md-2 form-check">
                <input class="form-check-input" type="checkbox" name="ferme[<?= (int)$h['id'] ?>]" id="f<?= (int)$h['id'] ?>" <?= $h['ferme'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="f<?= (int)$h['id'] ?>">Ferme</label>
            </div>
        </div>
    <?php endforeach; ?>
    <button class="btn btn-vg mt-2" type="submit">Sauver</button>
</form>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
