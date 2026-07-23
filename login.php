<?php
require_once __DIR__ . '/includes/fonctions.php';
$pageTitle = 'Connexion';
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim($_POST['email'] ?? '');
    $mdp = trim($_POST['password'] ?? '');

    $stmt = db()->prepare('SELECT * FROM utilisateurs WHERE email = ? AND actif = 1 LIMIT 1');
    $stmt->execute([$email]);
    $u = $stmt->fetch();

    if ($u && password_verify($mdp, $u['mot_de_passe'])) {
        $_SESSION['user'] = [
            'id' => $u['id'],
            'nom' => $u['nom'],
            'prenom' => $u['prenom'],
            'email' => $u['email'],
            'gsm' => $u['gsm'],
            'adresse' => $u['adresse'],
            'role' => $u['role'],
        ];

        if ($u['role'] === 'admin') {
            $redir = '/admin/index.php';
        } elseif ($u['role'] === 'employe') {
            $redir = '/employe/index.php';
        } else {
            $redir = $_GET['redirect'] ?? '/compte/index.php';
            if ($redir === '' || $redir[0] !== '/') {
                $redir = '/compte/index.php';
            }
        }

        header('Location: ' . $redir);
        exit;
    }
    $erreur = 'Identifiants incorrects ou compte désactivé.';
}

require __DIR__ . '/includes/header.php';
?>

<h1 class="h3 page-title mb-3">Connexion</h1>
<?php if ($erreur): ?><div class="alert alert-danger"><?= e($erreur) ?></div><?php endif; ?>

<form method="post" class="card card-menu p-4" style="max-width:420px">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <div class="mb-3">
        <label class="form-label" for="email">Email</label>
        <input class="form-control" type="email" id="email" name="email" required autocomplete="username">
    </div>
    <div class="mb-3">
        <label class="form-label" for="password">Mot de passe</label>
        <input class="form-control" type="password" id="password" name="password" required autocomplete="current-password">
    </div>
    <button class="btn btn-vg w-100 mb-2" type="submit">Se connecter</button>
    <a class="d-block small text-center" href="register.php">Créer un compte</a>
    <a class="d-block small text-center text-muted" href="forgot.php">Mot de passe oublié ?</a>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
