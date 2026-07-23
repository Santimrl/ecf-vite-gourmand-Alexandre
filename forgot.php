<?php
require_once __DIR__ . '/includes/fonctions.php';
$pageTitle = 'Mot de passe oublié';
$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim($_POST['email'] ?? '');
    $stmt = db()->prepare('SELECT id, prenom FROM utilisateurs WHERE email = ? AND actif = 1');
    $stmt->execute([$email]);
    $u = $stmt->fetch();
    $msg = 'Si un compte existe, un lien de réinitialisation a été envoyé par mail.';
    if ($u) {
        $token = bin2hex(random_bytes(16));
        $exp = date('Y-m-d H:i:s', time() + 3600);
        $upd = db()->prepare('UPDATE utilisateurs SET reset_token = ?, reset_expire = ? WHERE id = ?');
        $upd->execute([$token, $exp, $u['id']]);
        $lien = APP_URL . '/reset.php?token=' . $token;
        envoyer_mail_app($email, 'Réinitialisation mot de passe', "Bonjour {$u['prenom']},\n\nClique ici : $lien\n(valable 1h)\n");
    }
}

require __DIR__ . '/includes/header.php';
?>

<h1 class="h3 mb-3">Mot de passe oublié</h1>
<?php if ($msg): ?><div class="alert alert-info"><?= e($msg) ?></div><?php endif; ?>

<form method="post" class="card card-menu p-3" style="max-width:480px">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <div class="mb-3">
        <label class="form-label" for="email">Votre email</label>
        <input class="form-control" type="email" id="email" name="email" required>
    </div>
    <button class="btn btn-vg" type="submit">Recevoir le lien</button>
</form>
<p class="small text-muted mt-2">Si le mail marche pas en local, regarde data/mails.log</p>

<?php require __DIR__ . '/includes/footer.php'; ?>
