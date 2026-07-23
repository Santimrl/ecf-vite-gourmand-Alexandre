<?php
require_once dirname(__DIR__) . '/includes/fonctions.php';
require_role(['employe', 'admin']);
$pageTitle = 'Commandes';

$statuts = ['en_attente','accepte','en_preparation','en_livraison','livre','attente_materiel','terminee','annulee'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($action === 'statut') {
        $statut = $_POST['statut'] ?? '';
        if (in_array($statut, $statuts, true)) {
            db()->prepare('UPDATE commandes SET statut = ? WHERE id = ?')->execute([$statut, $id]);
            db()->prepare('INSERT INTO commande_suivi (commande_id, statut) VALUES (?,?)')->execute([$id, $statut]);

            $c = db()->prepare('SELECT * FROM commandes WHERE id = ?');
            $c->execute([$id]);
            $cmd = $c->fetch();
            if ($cmd && $statut === 'attente_materiel') {
                envoyer_mail_app($cmd['email'], 'Retour de materiel commande #'.$id,
                    "Bonjour,\n\nMerci de restituer le materiel sous 10 jours ouvres, sinon 600 euros de frais (voir CGV).\nContactez-nous pour organiser le retour.\n");
            }
            if ($cmd && $statut === 'terminee') {
                envoyer_mail_app($cmd['email'], 'Commande terminee #'.$id,
                    "Bonjour,\n\nVotre commande est terminee. Connectez-vous pour laisser un avis.\n");
            }
        }
        header('Location: /employe/commandes.php');
        exit;
    }

    if ($action === 'annuler') {
        $motif = trim($_POST['motif'] ?? '');
        $mode = trim($_POST['mode_contact'] ?? '');
        if ($motif === '' || $mode === '') {
            die('Il faut un motif et le mode de contact (tel ou mail).');
        }
        db()->prepare('UPDATE commandes SET statut="annulee", motif_annulation=?, mode_contact_annulation=? WHERE id=?')
            ->execute([$motif, $mode, $id]);
        db()->prepare('INSERT INTO commande_suivi (commande_id, statut) VALUES (?,"annulee")')->execute([$id]);
        header('Location: /employe/commandes.php');
        exit;
    }
}

$filtreStatut = $_GET['statut'] ?? '';
$filtreClient = trim($_GET['client'] ?? '');
$sql = 'SELECT c.*, m.titre, u.email AS user_email FROM commandes c
        JOIN menus m ON m.id = c.menu_id
        JOIN utilisateurs u ON u.id = c.utilisateur_id WHERE 1=1';
$params = [];
if ($filtreStatut !== '') {
    $sql .= ' AND c.statut = ?';
    $params[] = $filtreStatut;
}
if ($filtreClient !== '') {
    $sql .= ' AND (c.email LIKE ? OR c.nom LIKE ? OR c.prenom LIKE ?)';
    $params[] = '%'.$filtreClient.'%';
    $params[] = '%'.$filtreClient.'%';
    $params[] = '%'.$filtreClient.'%';
}
$sql .= ' ORDER BY c.created_at DESC';
$st = db()->prepare($sql);
$st->execute($params);
$commandes = $st->fetchAll();

require dirname(__DIR__) . '/includes/header.php';
?>

<h1 class="h3 mb-3">Commandes</h1>
<p><a href="/employe/index.php">Retour</a></p>

<form class="row g-2 mb-3" method="get">
    <div class="col-md-3">
        <select class="form-select" name="statut">
            <option value="">Tous les statuts</option>
            <?php foreach ($statuts as $s): ?>
                <option value="<?= e($s) ?>" <?= $filtreStatut === $s ? 'selected' : '' ?>><?= e($s) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-4">
        <input class="form-control" name="client" placeholder="Client (nom / mail)" value="<?= e($filtreClient) ?>">
    </div>
    <div class="col-md-2"><button class="btn btn-vg w-100" type="submit">Filtrer</button></div>
</form>

<?php foreach ($commandes as $c): ?>
    <div class="card card-menu p-3 mb-3">
        <div class="d-flex justify-content-between flex-wrap gap-2">
            <div>
                <strong>#<?= (int)$c['id'] ?></strong> — <?= e($c['titre']) ?><br>
                <?= e($c['prenom'].' '.$c['nom']) ?> (<?= e($c['email']) ?>) · <?= e($c['gsm']) ?><br>
                <?= e($c['date_prestation']) ?> <?= e(substr($c['heure_prestation'],0,5)) ?> · <?= e($c['ville']) ?><br>
                <?= (int)$c['nb_personnes'] ?> pers. · total <?= e($c['total']) ?> € · <span class="badge text-bg-secondary"><?= e(label_statut($c['statut'])) ?></span>
            </div>
        </div>
        <form method="post" class="row g-2 mt-2 align-items-end">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
            <input type="hidden" name="action" value="statut">
            <div class="col-md-4">
                <label class="form-label">Changer statut</label>
                <select class="form-select" name="statut">
                    <?php foreach ($statuts as $s): if ($s === 'annulee') continue; ?>
                        <option value="<?= e($s) ?>" <?= $c['statut'] === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3"><button class="btn btn-vg" type="submit">OK</button></div>
        </form>
        <?php if ($c['statut'] !== 'annulee' && $c['statut'] !== 'terminee'): ?>
        <details class="mt-2">
            <summary>Annuler (apres contact client)</summary>
            <form method="post" class="mt-2">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                <input type="hidden" name="action" value="annuler">
                <div class="mb-2"><label class="form-label">Mode contact</label>
                    <select class="form-select" name="mode_contact" required>
                        <option value="tel">Telephone</option>
                        <option value="mail">Mail</option>
                    </select>
                </div>
                <div class="mb-2"><label class="form-label">Motif</label><textarea class="form-control" name="motif" required></textarea></div>
                <button class="btn btn-outline-danger btn-sm" type="submit">Annuler la commande</button>
            </form>
        </details>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

<?php if (!$commandes): ?><p class="text-muted">Aucune commande.</p><?php endif; ?>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
