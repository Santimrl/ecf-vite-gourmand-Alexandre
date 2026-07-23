<?php
require_once dirname(__DIR__) . '/includes/fonctions.php';
require_role(['admin']);
$pageTitle = 'Employes';
$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'creer') {
        $email = trim($_POST['email'] ?? '');
        $mdp = $_POST['password'] ?? '';
        $prenom = trim($_POST['prenom'] ?? 'Employe');
        $nom = trim($_POST['nom'] ?? 'VG');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !mdp_valide($mdp)) {
            $err = 'Email ou mdp invalide (10+ maj min chiffre special).';
        } else {
            $check = db()->prepare('SELECT id FROM utilisateurs WHERE email=?');
            $check->execute([$email]);
            if ($check->fetch()) {
                $err = 'Email deja pris.';
            } else {
                $hash = password_hash($mdp, PASSWORD_DEFAULT);
                db()->prepare('INSERT INTO utilisateurs (nom,prenom,email,gsm,adresse,mot_de_passe,role) VALUES (?,?,?,?,?,?,?)')
                    ->execute([$nom, $prenom, $email, '0600000000', 'Bordeaux', $hash, 'employe']);
                envoyer_mail_app($email, 'Compte employe cree',
                    "Bonjour,\n\nUn compte employe a ete cree pour toi sur Vite & Gourmand.\nUsername: $email\nLe mot de passe te sera donne par l'administrateur (pas dans ce mail).\n");
                $msg = 'Employe cree. Le mdp n\'est pas dans le mail.';
            }
        }
    }

    if ($action === 'toggle') {
        $id = (int)$_POST['id'];
        $u = db()->prepare('SELECT role, actif FROM utilisateurs WHERE id=?');
        $u->execute([$id]);
        $row = $u->fetch();
        if ($row && $row['role'] === 'employe') {
            $new = $row['actif'] ? 0 : 1;
            db()->prepare('UPDATE utilisateurs SET actif=? WHERE id=?')->execute([$new, $id]);
        }
        header('Location: /admin/employes.php');
        exit;
    }
}

$employes = db()->query('SELECT id, nom, prenom, email, actif, created_at FROM utilisateurs WHERE role="employe" ORDER BY id DESC')->fetchAll();
require dirname(__DIR__) . '/includes/header.php';
?>

<h1 class="h3 mb-3">Comptes employes</h1>
<p><a href="/admin/index.php">Retour admin</a></p>
<?php if ($msg): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endif; ?>

<div class="card card-menu p-3 mb-4" style="max-width:560px">
    <h2 class="h5">Creer un employe</h2>
    <form method="post">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="creer">
        <div class="mb-2"><label class="form-label">Prenom</label><input class="form-control" name="prenom" required></div>
        <div class="mb-2"><label class="form-label">Nom</label><input class="form-control" name="nom" required></div>
        <div class="mb-2"><label class="form-label">Email (login)</label><input class="form-control" type="email" name="email" required></div>
        <div class="mb-2"><label class="form-label">Mot de passe</label><input class="form-control" type="password" name="password" required>
            <div class="form-text">Tu le donnes a part, pas par mail.</div>
        </div>
        <button class="btn btn-vg" type="submit">Creer</button>
    </form>
</div>

<table class="table table-sm bg-white">
    <thead><tr><th>Nom</th><th>Email</th><th>Actif</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($employes as $e): ?>
        <tr>
            <td><?= e($e['prenom'].' '.$e['nom']) ?></td>
            <td><?= e($e['email']) ?></td>
            <td><?= $e['actif'] ? 'oui' : 'non' ?></td>
            <td>
                <form method="post">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="action" value="toggle">
                    <input type="hidden" name="id" value="<?= (int)$e['id'] ?>">
                    <button class="btn btn-sm btn-outline-secondary" type="submit"><?= $e['actif'] ? 'Desactiver' : 'Reactiver' ?></button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
