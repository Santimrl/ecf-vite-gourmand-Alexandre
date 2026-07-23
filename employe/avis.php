<?php
require_once dirname(__DIR__) . '/includes/fonctions.php';
require_role(['employe', 'admin']);
$pageTitle = 'Avis';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($action === 'ok' || $action === 'ko') {
        $valide = $action === 'ok' ? 1 : 0;
        db()->prepare('UPDATE avis SET valide = ? WHERE id = ?')->execute([$valide, $id]);
    }
    if ($action === 'suppr') {
        db()->prepare('DELETE FROM avis WHERE id = ?')->execute([$id]);
    }
    header('Location: /employe/avis.php');
    exit;
}

$avis = db()->query('SELECT a.*, u.prenom, u.nom, c.id AS cmd_id
    FROM avis a
    JOIN utilisateurs u ON u.id = a.utilisateur_id
    JOIN commandes c ON c.id = a.commande_id
    ORDER BY a.created_at DESC')->fetchAll();

require dirname(__DIR__) . '/includes/header.php';
?>

<h1 class="h3 mb-3">Avis clients</h1>
<p><a href="/employe/index.php">Retour</a></p>
<p class="small text-muted">Tu peux valider, refuser, ou supprimer un avis (insultes etc.).</p>

<?php foreach ($avis as $a): ?>
    <div class="card card-menu p-3 mb-2">
        <strong><?= e($a['prenom'].' '.$a['nom']) ?></strong> — note <?= (int)$a['note'] ?>/5
        (cmd #<?= (int)$a['cmd_id'] ?>)
        <?php if ($a['valide'] === null): ?><span class="badge text-bg-warning">en attente</span>
        <?php elseif ($a['valide']): ?><span class="badge text-bg-success">valide</span>
        <?php else: ?><span class="badge text-bg-danger">refuse</span><?php endif; ?>
        <p class="mb-2"><?= e($a['commentaire']) ?></p>
        <div class="d-flex gap-2 flex-wrap">
            <?php if ($a['valide'] === null): ?>
            <form method="post" class="d-inline">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                <button class="btn btn-sm btn-vg" name="action" value="ok" type="submit">Valider</button>
                <button class="btn btn-sm btn-outline-danger" name="action" value="ko" type="submit">Refuser</button>
            </form>
            <?php endif; ?>
            <form method="post" class="d-inline" onsubmit="return confirm('Supprimer cet avis pour de bon ?');">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                <button class="btn btn-sm btn-outline-dark" name="action" value="suppr" type="submit">Supprimer</button>
            </form>
        </div>
    </div>
<?php endforeach; ?>

<?php if (!$avis): ?><p class="text-muted">Pas d'avis pour l'instant.</p><?php endif; ?>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
