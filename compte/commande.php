<?php
require_once dirname(__DIR__) . '/includes/fonctions.php';
require_login();
$user = utilisateur_connecte();

if ($user['role'] === 'admin') {
    header('Location: /admin/index.php');
    exit;
}
if ($user['role'] === 'employe') {
    header('Location: /employe/index.php');
    exit;
}
if ($user['role'] !== 'utilisateur') {
    die('Espace client uniquement.');
}

$id = (int)($_GET['id'] ?? 0);
$st = db()->prepare('SELECT c.*, m.titre FROM commandes c JOIN menus m ON m.id = c.menu_id WHERE c.id = ? AND c.utilisateur_id = ?');
$st->execute([$id, $user['id']]);
$c = $st->fetch();
if (!$c) {
    header('Location: /compte/index.php');
    exit;
}

$suivi = db()->prepare('SELECT * FROM commande_suivi WHERE commande_id = ? ORDER BY created_at');
$suivi->execute([$id]);
$histo = $suivi->fetchAll();

$avis = db()->prepare('SELECT * FROM avis WHERE commande_id = ?');
$avis->execute([$id]);
$monAvis = $avis->fetch();

$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'annuler' && $c['statut'] === 'en_attente') {
        db()->prepare('UPDATE commandes SET statut="annulee" WHERE id=?')->execute([$id]);
        db()->prepare('INSERT INTO commande_suivi (commande_id, statut) VALUES (?,"annulee")')->execute([$id]);
        db()->prepare('UPDATE menus SET stock = stock + 1 WHERE id=?')->execute([$c['menu_id']]);
        header('Location: /compte/commande.php?id='.$id);
        exit;
    }

    if ($action === 'modifier' && $c['statut'] === 'en_attente') {
        $nb = (int)($_POST['nb_personnes'] ?? 0);
        $adresse = trim($_POST['adresse_prestation'] ?? '');
        $datep = $_POST['date_prestation'] ?? '';
        $heure = $_POST['heure_prestation'] ?? '';
        $lieu = trim($_POST['lieu'] ?? '');
        $ville = trim($_POST['ville'] ?? '');
        $km = (float)($_POST['km_hors_bordeaux'] ?? 0);

        $menu = db()->prepare('SELECT * FROM menus WHERE id=?');
        $menu->execute([$c['menu_id']]);
        $m = $menu->fetch();
        if ($nb < (int)$m['nb_personnes_min']) {
            $err = 'Pas assez de personnes.';
        } else {
            $prixMenu = round(((float)$m['prix_min'] / max(1,(int)$m['nb_personnes_min'])) * $nb, 2);
            $reduc = 0;
            if ($nb >= (int)$m['nb_personnes_min'] + 5) {
                $reduc = 10;
                $prixMenu = round($prixMenu * 0.9, 2);
            }
            $livraison = 0;
            if (mb_strtolower($ville) !== 'bordeaux') {
                $livraison = round(LIVRAISON_BASE + $km * LIVRAISON_PAR_KM, 2);
            }
            $total = round($prixMenu + $livraison, 2);
            db()->prepare('UPDATE commandes SET nb_personnes=?, adresse_prestation=?, date_prestation=?, heure_prestation=?, lieu=?, ville=?, km_hors_bordeaux=?, prix_menu=?, prix_livraison=?, reduction_pct=?, total=? WHERE id=?')
                ->execute([$nb, $adresse, $datep, $heure, $lieu, $ville, $km, $prixMenu, $livraison, $reduc, $total, $id]);
            header('Location: /compte/commande.php?id='.$id.'&ok=1');
            exit;
        }
    }

    if ($action === 'avis' && $c['statut'] === 'terminee' && !$monAvis) {
        $note = (int)($_POST['note'] ?? 0);
        $com = trim($_POST['commentaire'] ?? '');
        if ($note < 1 || $note > 5 || $com === '') {
            $err = 'Note 1 a 5 + commentaire.';
        } else {
            db()->prepare('INSERT INTO avis (commande_id, utilisateur_id, note, commentaire) VALUES (?,?,?,?)')
                ->execute([$id, $user['id'], $note, $com]);
            header('Location: /compte/commande.php?id='.$id);
            exit;
        }
    }
}

$st->execute([$id, $user['id']]);
$c = $st->fetch();

$pageTitle = 'Commande #'.$id;
require dirname(__DIR__) . '/includes/header.php';
?>

<p><a href="/compte/index.php">Retour</a></p>
<?php if (!empty($_GET['ok'])): ?><div class="alert alert-success">Modifie.</div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endif; ?>

<h1 class="h3">Commande #<?= (int)$c['id'] ?></h1>
<ul>
    <li>Menu : <?= e($c['titre']) ?> (non modifiable)</li>
    <li>Statut : <strong><?= e(label_statut($c['statut'])) ?></strong></li>
    <li><?= e($c['date_prestation']) ?> <?= e(substr($c['heure_prestation'],0,5)) ?> — <?= e($c['lieu']) ?>, <?= e($c['ville']) ?></li>
    <li><?= (int)$c['nb_personnes'] ?> pers. — menu <?= e($c['prix_menu']) ?> € + livraison <?= e($c['prix_livraison']) ?> € = <strong><?= e($c['total']) ?> €</strong></li>
</ul>

<?php if (in_array($c['statut'], ['accepte','en_preparation','en_livraison','livre','attente_materiel','terminee'], true)): ?>
    <h2 class="h5">Suivi</h2>
    <ul>
        <?php foreach ($histo as $h): ?>
            <li><?= e($h['created_at']) ?> — <?= e(label_statut($h['statut'])) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php if ($c['statut'] === 'en_attente'): ?>
    <div class="card card-menu p-3 mb-3">
        <h2 class="h5">Modifier (sauf le menu)</h2>
        <form method="post">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="modifier">
            <div class="mb-2"><label class="form-label">Adresse</label><textarea class="form-control" name="adresse_prestation" required><?= e($c['adresse_prestation']) ?></textarea></div>
            <div class="row">
                <div class="col-md-4 mb-2"><label class="form-label">Date</label><input class="form-control" type="date" name="date_prestation" value="<?= e($c['date_prestation']) ?>" required></div>
                <div class="col-md-4 mb-2"><label class="form-label">Heure</label><input class="form-control" type="time" name="heure_prestation" value="<?= e(substr($c['heure_prestation'],0,5)) ?>" required></div>
                <div class="col-md-4 mb-2"><label class="form-label">Lieu</label><input class="form-control" name="lieu" value="<?= e($c['lieu']) ?>" required></div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-2"><label class="form-label">Ville</label><input class="form-control" name="ville" value="<?= e($c['ville']) ?>" required></div>
                <div class="col-md-4 mb-2"><label class="form-label">Km hors Bx</label><input class="form-control" type="number" step="0.1" name="km_hors_bordeaux" value="<?= e($c['km_hors_bordeaux']) ?>"></div>
                <div class="col-md-4 mb-2"><label class="form-label">Nb personnes</label><input class="form-control" type="number" name="nb_personnes" value="<?= (int)$c['nb_personnes'] ?>" required></div>
            </div>
            <button class="btn btn-vg" type="submit">Enregistrer</button>
        </form>
        <form method="post" class="mt-3" onsubmit="return confirm('Annuler la commande ?');">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="annuler">
            <button class="btn btn-outline-danger btn-sm" type="submit">Annuler la commande</button>
        </form>
    </div>
<?php endif; ?>

<?php if ($c['statut'] === 'terminee' && !$monAvis): ?>
    <div class="card card-menu p-3">
        <h2 class="h5">Donner mon avis</h2>
        <form method="post">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="avis">
            <div class="mb-2"><label class="form-label">Note (1-5)</label><input class="form-control" type="number" min="1" max="5" name="note" required></div>
            <div class="mb-2"><label class="form-label">Commentaire</label><textarea class="form-control" name="commentaire" required></textarea></div>
            <button class="btn btn-vg" type="submit">Envoyer</button>
        </form>
    </div>
<?php elseif ($monAvis): ?>
    <p class="text-muted">Avis deja envoye (note <?= (int)$monAvis['note'] ?>).</p>
<?php endif; ?>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
