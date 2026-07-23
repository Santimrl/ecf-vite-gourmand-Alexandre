<?php
require_once dirname(__DIR__) . '/includes/fonctions.php';
require_login();
$user = utilisateur_connecte();
if ($user['role'] !== 'utilisateur') {
    header('Location: /index.php');
    exit;
}
$pageTitle = 'Mon profil';
$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $gsm = trim($_POST['gsm'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    if ($nom === '' || $prenom === '' || $gsm === '' || $adresse === '') {
        $err = 'Remplis tout.';
    } else {
        db()->prepare('UPDATE utilisateurs SET nom=?, prenom=?, gsm=?, adresse=? WHERE id=?')
            ->execute([$nom, $prenom, $gsm, $adresse, $user['id']]);
        $_SESSION['user']['nom'] = $nom;
        $_SESSION['user']['prenom'] = $prenom;
        $_SESSION['user']['gsm'] = $gsm;
        $_SESSION['user']['adresse'] = $adresse;
        $msg = 'Profil maj.';
        $user = utilisateur_connecte();
    }
}

require dirname(__DIR__) . '/includes/header.php';
?>

<h1 class="h3 mb-3">Mes infos</h1>
<p><a href="/compte/index.php">Retour</a></p>
<?php if ($msg): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endif; ?>

<form method="post" class="card card-menu p-3" style="max-width:560px">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <div class="mb-2"><label class="form-label">Nom</label><input class="form-control" name="nom" value="<?= e($user['nom']) ?>" required></div>
    <div class="mb-2"><label class="form-label">Prenom</label><input class="form-control" name="prenom" value="<?= e($user['prenom']) ?>" required></div>
    <div class="mb-2"><label class="form-label">Email</label><input class="form-control" value="<?= e($user['email']) ?>" disabled><div class="form-text">L'email sert de login, on le change pas ici.</div></div>
    <div class="mb-2"><label class="form-label">GSM</label><input class="form-control" name="gsm" value="<?= e($user['gsm']) ?>" required></div>
    <div class="mb-2"><label class="form-label">Adresse</label><textarea class="form-control" name="adresse" required><?= e($user['adresse']) ?></textarea></div>
    <button class="btn btn-vg" type="submit">Sauver</button>
</form>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
