<?php
require_once __DIR__ . '/includes/fonctions.php';

$menuId = isset($_GET['menu_id']) ? (int)$_GET['menu_id'] : (int)($_POST['menu_id'] ?? 0);
if (!utilisateur_connecte()) {
    header('Location: /login.php?redirect=' . urlencode('/commande.php?menu_id=' . $menuId));
    exit;
}
$user = utilisateur_connecte();
if ($user['role'] === 'employe') {
    header('Location: /employe/index.php');
    exit;
}
if ($user['role'] !== 'utilisateur' && $user['role'] !== 'admin') {
    header('Location: /index.php');
    exit;
}

$stmt = db()->prepare('SELECT * FROM menus WHERE id = ? AND actif = 1');
$stmt->execute([$menuId]);
$menu = $stmt->fetch();
if (!$menu) {
    die('Menu invalide');
}

$erreur = '';
$ok = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $gsm = trim($_POST['gsm'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $datep = $_POST['date_prestation'] ?? '';
    $heure = $_POST['heure_prestation'] ?? '';
    $lieu = trim($_POST['lieu'] ?? '');
    $ville = trim($_POST['ville'] ?? '');
    $km = (float)($_POST['km_hors_bordeaux'] ?? 0);
    $nb = (int)($_POST['nb_personnes'] ?? 0);

    if ($nb < (int)$menu['nb_personnes_min']) {
        $erreur = 'Nombre de personnes inférieur au minimum du menu.';
    } elseif ((int)$menu['stock'] <= 0) {
        $erreur = 'Plus de stock.';
    } else {
        $prixBase = (float)$menu['prix_min'];
        $prixMenu = round(($prixBase / max(1, (int)$menu['nb_personnes_min'])) * $nb, 2);
        $reduc = 0;
        if ($nb >= (int)$menu['nb_personnes_min'] + 5) {
            $reduc = 10;
            $prixMenu = round($prixMenu * 0.9, 2);
        }
        $livraison = 0;
        if (mb_strtolower($ville) !== 'bordeaux') {
            $livraison = LIVRAISON_BASE + ($km * LIVRAISON_PAR_KM);
            $livraison = round($livraison, 2);
        }
        $total = round($prixMenu + $livraison, 2);

        if (!empty($_POST['confirmer'])) {
            $pdo = db();
            $pdo->beginTransaction();
            try {
                $ins = $pdo->prepare('INSERT INTO commandes
                    (utilisateur_id, menu_id, nom, prenom, email, gsm, adresse_prestation, date_prestation, heure_prestation, lieu, ville, km_hors_bordeaux, nb_personnes, prix_menu, prix_livraison, reduction_pct, total, statut)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,"en_attente")');
                $ins->execute([
                    $user['id'], $menu['id'], $nom, $prenom, $email, $gsm, $adresse, $datep, $heure, $lieu, $ville, $km, $nb,
                    $prixMenu, $livraison, $reduc, $total
                ]);
                $cid = (int)$pdo->lastInsertId();
                $pdo->prepare('INSERT INTO commande_suivi (commande_id, statut) VALUES (?,"en_attente")')->execute([$cid]);
                $pdo->prepare('UPDATE menus SET stock = stock - 1 WHERE id = ? AND stock > 0')->execute([$menu['id']]);
                $pdo->commit();
                nosql_log_commande($menu['id'], $menu['titre'], $total, $nb);
                envoyer_mail_app($email, 'Confirmation commande #' . $cid, "Bonjour $prenom,\n\nCommande #$cid confirmée.\nTotal: $total €\nMerci !\nVite & Gourmand");
                header('Location: /compte/index.php?ok=1');
                exit;
            } catch (Exception $e) {
                $pdo->rollBack();
                $erreur = 'Erreur enregistrement: ' . $e->getMessage();
            }
        } else {
            $recap = compact('nom','prenom','email','gsm','adresse','datep','heure','lieu','ville','km','nb','prixMenu','livraison','reduc','total');
        }
    }
}

$pageTitle = 'Commander';
require __DIR__ . '/includes/header.php';
?>

<h1 class="h3 mb-3">Commander : <?= e($menu['titre']) ?></h1>
<div class="conditions-box mb-3"><strong>Conditions :</strong> <?= nl2br(e($menu['conditions_menu'])) ?></div>
<?php if ($erreur): ?><div class="alert alert-danger"><?= e($erreur) ?></div><?php endif; ?>

<?php if (!empty($recap)): ?>
    <div class="card card-menu p-3 mb-3">
        <h2 class="h5">Récapitulatif du prix</h2>
        <ul>
            <li>Prix menu (<?= (int)$recap['nb'] ?> pers.) : <strong><?= e($recap['prixMenu']) ?> €</strong>
                <?php if ($recap['reduc']): ?> (réduction <?= (int)$recap['reduc'] ?> %)<?php endif; ?></li>
            <li>Livraison : <strong><?= e($recap['livraison']) ?> €</strong></li>
            <li>Total : <strong><?= e($recap['total']) ?> €</strong></li>
        </ul>
        <form method="post">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="menu_id" value="<?= (int)$menu['id'] ?>">
            <input type="hidden" name="confirmer" value="1">
            <?php foreach (['nom','prenom','email','gsm','adresse','lieu','ville'] as $f): ?>
                <input type="hidden" name="<?= $f ?>" value="<?= e($recap[$f]) ?>">
            <?php endforeach; ?>
            <input type="hidden" name="date_prestation" value="<?= e($recap['datep']) ?>">
            <input type="hidden" name="heure_prestation" value="<?= e($recap['heure']) ?>">
            <input type="hidden" name="km_hors_bordeaux" value="<?= e($recap['km']) ?>">
            <input type="hidden" name="nb_personnes" value="<?= e($recap['nb']) ?>">
            <button class="btn btn-vg" type="submit">Valider la commande</button>
            <a class="btn btn-link" href="/commande.php?menu_id=<?= (int)$menu['id'] ?>">Modifier</a>
        </form>
    </div>
<?php else: ?>
<form method="post" class="card card-menu p-3">
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="menu_id" value="<?= (int)$menu['id'] ?>">
    <div class="row">
        <div class="col-md-6 mb-3"><label class="form-label" for="nom">Nom</label><input class="form-control" id="nom" name="nom" value="<?= e($user['nom']) ?>" required></div>
        <div class="col-md-6 mb-3"><label class="form-label" for="prenom">Prénom</label><input class="form-control" id="prenom" name="prenom" value="<?= e($user['prenom']) ?>" required></div>
    </div>
    <div class="mb-3"><label class="form-label" for="email">Email</label><input class="form-control" type="email" id="email" name="email" value="<?= e($user['email']) ?>" required></div>
    <div class="mb-3"><label class="form-label" for="gsm">GSM</label><input class="form-control" id="gsm" name="gsm" value="<?= e($user['gsm']) ?>" required></div>
    <div class="mb-3"><label class="form-label" for="adresse">Adresse de prestation / facturation</label><textarea class="form-control" id="adresse" name="adresse" rows="2" required><?= e($user['adresse']) ?></textarea></div>
    <div class="row">
        <div class="col-md-4 mb-3"><label class="form-label" for="date_prestation">Date</label><input class="form-control" type="date" id="date_prestation" name="date_prestation" required></div>
        <div class="col-md-4 mb-3"><label class="form-label" for="heure_prestation">Heure</label><input class="form-control" type="time" id="heure_prestation" name="heure_prestation" required></div>
        <div class="col-md-4 mb-3"><label class="form-label" for="lieu">Lieu</label><input class="form-control" id="lieu" name="lieu" required></div>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3"><label class="form-label" for="ville">Ville</label><input class="form-control" id="ville" name="ville" value="Bordeaux" required>
            <div class="form-text">Hors Bordeaux : +5 € + 0,59 €/km</div>
        </div>
        <div class="col-md-3 mb-3"><label class="form-label" for="km_hors_bordeaux">Km (si hors Bordeaux)</label><input class="form-control" type="number" step="0.1" min="0" id="km_hors_bordeaux" name="km_hors_bordeaux" value="0"></div>
        <div class="col-md-3 mb-3"><label class="form-label" for="nb_personnes">Nb personnes</label><input class="form-control" type="number" id="nb_personnes" name="nb_personnes" min="<?= (int)$menu['nb_personnes_min'] ?>" value="<?= (int)$menu['nb_personnes_min'] ?>" required>
            <div class="form-text">Min <?= (int)$menu['nb_personnes_min'] ?> — -10% si +5 pers.</div>
        </div>
    </div>
    <p class="mb-2">Menu choisi : <strong><?= e($menu['titre']) ?></strong> (pré-rempli)</p>
    <button class="btn btn-vg" type="submit">Voir le prix détaillé</button>
</form>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
