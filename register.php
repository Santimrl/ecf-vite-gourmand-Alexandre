<?php
require_once __DIR__ . '/includes/fonctions.php';
$pageTitle = 'Créer un compte';
$erreur = '';
$ok = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $gsm = trim($_POST['gsm'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $mdp = $_POST['password'] ?? '';
    $mdp2 = $_POST['password2'] ?? '';

    if ($nom === '' || $prenom === '' || $gsm === '' || $email === '' || $adresse === '') {
        $erreur = 'Tous les champs sont obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = 'Email invalide.';
    } elseif (!mdp_valide($mdp)) {
        $erreur = 'Mot de passe : 10 caractères min, majuscule, minuscule, chiffre, caractère spécial.';
    } elseif ($mdp !== $mdp2) {
        $erreur = 'Les mots de passe ne correspondent pas.';
    } else {
        $check = db()->prepare('SELECT id FROM utilisateurs WHERE email = ?');
        $check->execute([$email]);
        if ($check->fetch()) {
            $erreur = 'Cet email est déjà utilisé.';
        } else {
            $hash = password_hash($mdp, PASSWORD_DEFAULT);
            $ins = db()->prepare('INSERT INTO utilisateurs (nom,prenom,email,gsm,adresse,mot_de_passe,role) VALUES (?,?,?,?,?,?,"utilisateur")');
            $ins->execute([$nom, $prenom, $email, $gsm, $adresse, $hash]);
            envoyer_mail_app($email, 'Bienvenue chez Vite & Gourmand', "Bonjour $prenom,\n\nBienvenue ! Votre compte utilisateur est créé.\n\nL'équipe Vite & Gourmand");
            $ok = 'Compte créé, tu peux te connecter. (j\'ai aussi mis un mail dans data/mails.log)';
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

<h1 class="h3 mb-3">Créer un compte</h1>
<?php if ($erreur): ?><div class="alert alert-danger"><?= e($erreur) ?></div><?php endif; ?>
<?php if ($ok): ?><div class="alert alert-success"><?= e($ok) ?></div><?php endif; ?>

<form method="post" class="card card-menu p-3">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label" for="nom">Nom</label>
            <input class="form-control" id="nom" name="nom" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label" for="prenom">Prénom</label>
            <input class="form-control" id="prenom" name="prenom" required>
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label" for="gsm">GSM</label>
        <input class="form-control" id="gsm" name="gsm" required>
    </div>
    <div class="mb-3">
        <label class="form-label" for="email">Email</label>
        <input class="form-control" type="email" id="email" name="email" required>
    </div>
    <div class="mb-3">
        <label class="form-label" for="adresse">Adresse postale</label>
        <textarea class="form-control" id="adresse" name="adresse" required rows="2"></textarea>
    </div>
    <div class="mb-3">
        <label class="form-label" for="password">Mot de passe</label>
        <input class="form-control" type="password" id="password" name="password" required>
        <div class="form-text">10+ caractères, maj, min, chiffre, spécial</div>
    </div>
    <div class="mb-3">
        <label class="form-label" for="password2">Confirmer</label>
        <input class="form-control" type="password" id="password2" name="password2" required>
    </div>
    <button class="btn btn-vg" type="submit">S'inscrire</button>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
