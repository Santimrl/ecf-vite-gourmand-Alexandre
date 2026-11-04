<?php
require_once __DIR__ . '/includes/fonctions.php';
$pageTitle = 'Connexion';
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim($_POST['email'] ?? '');
    $mdp = trim($_POST['password'] ?? '');

    // Anti brute-force simple (session)
    if (!isset($_SESSION['login_essais'])) {
        $_SESSION['login_essais'] = 0;
        $_SESSION['login_blocage'] = 0;
    }
    if (!empty($_SESSION['login_blocage']) && time() < (int)$_SESSION['login_blocage']) {
        $reste = (int)$_SESSION['login_blocage'] - time();
        $erreur = 'Trop de tentatives. Reessaie dans ' . $reste . ' secondes.';
    } else {
        $stmt = db()->prepare('SELECT * FROM utilisateurs WHERE email = ? AND actif = 1 LIMIT 1');
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        $userObj = $row ? Utilisateur::depuisLigne($row) : null;

        if ($row && $userObj && password_verify($mdp, $row['mot_de_passe'])) {
            $_SESSION['login_essais'] = 0;
            $_SESSION['login_blocage'] = 0;
            $_SESSION['user'] = $userObj->toSessionArray();

            if ($userObj->estAdmin()) {
                $redir = '/admin/index.php';
            } elseif ($userObj->estEmployeOuAdmin()) {
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

        $_SESSION['login_essais'] = (int)$_SESSION['login_essais'] + 1;
        if ($_SESSION['login_essais'] >= 5) {
            $_SESSION['login_blocage'] = time() + 60;
            $_SESSION['login_essais'] = 0;
            $erreur = 'Trop de tentatives. Compte bloque 60 secondes.';
        } else {
            $erreur = 'Identifiants incorrects ou compte désactivé.';
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

<h1 class="h3 page-title mb-3">Connexion</h1>
<?php if ($erreur): ?><div class="alert alert-danger"><?= e($erreur) ?></div><?php endif; ?>

<form method="post" class="card card-menu p-4 js-validate" style="max-width:420px" data-validate="login" novalidate>
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <div class="mb-3">
        <label class="form-label" for="email">Email</label>
        <input class="form-control" type="email" id="email" name="email" required autocomplete="username">
        <div class="invalid-feedback">Email invalide.</div>
    </div>
    <div class="mb-3">
        <label class="form-label" for="password">Mot de passe</label>
        <input class="form-control" type="password" id="password" name="password" required autocomplete="current-password">
        <div class="invalid-feedback">Mot de passe requis.</div>
    </div>
    <button class="btn btn-vg w-100 mb-2" type="submit">Se connecter</button>
    <a class="d-block small text-center" href="register.php">Créer un compte</a>
    <a class="d-block small text-center text-muted" href="forgot.php">Mot de passe oublié ?</a>
</form>

<?php require __DIR__ . '/includes/footer.php'; ?>
