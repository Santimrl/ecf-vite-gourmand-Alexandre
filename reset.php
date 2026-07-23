<?php
require_once __DIR__ . '/includes/fonctions.php';
$pageTitle = 'Nouveau mot de passe';
$token = $_GET['token'] ?? '';
$msg = '';
$err = '';

$stmt = db()->prepare('SELECT id FROM utilisateurs WHERE reset_token = ? AND reset_expire > NOW() AND actif = 1');
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    $err = 'Lien invalide ou expiré.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user) {
    csrf_check();
    $mdp = $_POST['password'] ?? '';
    if (!mdp_valide($mdp)) {
        $err = 'Mot de passe trop faible.';
    } else {
        $hash = password_hash($mdp, PASSWORD_DEFAULT);
        $upd = db()->prepare('UPDATE utilisateurs SET mot_de_passe = ?, reset_token = NULL, reset_expire = NULL WHERE id = ?');
        $upd->execute([$hash, $user['id']]);
        $msg = 'Mot de passe mis à jour. Tu peux te connecter.';
        $user = null;
    }
}

require __DIR__ . '/includes/header.php';
?>

<h1 class="h3 mb-3">Réinitialiser le mot de passe</h1>
<?php if ($err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endif; ?>
<?php if ($msg): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>

<?php if ($user): ?>
<form method="post" class="card card-menu p-3" style="max-width:480px">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <div class="mb-3">
        <label class="form-label" for="password">Nouveau mot de passe</label>
        <input class="form-control" type="password" id="password" name="password" required>
    </div>
    <button class="btn btn-vg" type="submit">Enregistrer</button>
</form>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
