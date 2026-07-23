<?php
require_once __DIR__ . '/includes/fonctions.php';
$pageTitle = 'Contact';
$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $titre = trim($_POST['titre'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $email = trim($_POST['email'] ?? '');
    if ($titre === '' || $desc === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = 'Merci de remplir correctement le formulaire.';
    } else {
        $ins = db()->prepare('INSERT INTO contact_messages (titre, description, email) VALUES (?,?,?)');
        $ins->execute([$titre, $desc, $email]);
        envoyer_mail_app(MAIL_ENTREPRISE, '[Contact] ' . $titre, "De: $email\n\n$desc");
        $msg = 'Message envoyé. Nous vous répondrons rapidement.';
    }
}

require __DIR__ . '/includes/header.php';
?>

<h1 class="h3 mb-3">Contact</h1>
<?php if ($err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endif; ?>
<?php if ($msg): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>

<form method="post" class="card card-menu p-3" style="max-width:640px">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <div class="mb-3">
        <label class="form-label" for="titre">Titre</label>
        <input class="form-control" id="titre" name="titre" required>
    </div>
    <div class="mb-3">
        <label class="form-label" for="description">Description</label>
        <textarea class="form-control" id="description" name="description" rows="5" required></textarea>
    </div>
    <div class="mb-3">
        <label class="form-label" for="email">Votre email</label>
        <input class="form-control" type="email" id="email" name="email" required>
    </div>
    <button class="btn btn-vg" type="submit">Envoyer</button>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
