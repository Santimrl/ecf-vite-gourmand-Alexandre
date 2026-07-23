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

$pageTitle = 'Mon espace';

$cmds = db()->prepare('SELECT c.*, m.titre,
    (SELECT id FROM avis a WHERE a.commande_id = c.id LIMIT 1) AS avis_id
    FROM commandes c
    JOIN menus m ON m.id = c.menu_id
    WHERE c.utilisateur_id = ?
    ORDER BY c.created_at DESC');
$cmds->execute([$user['id']]);
$commandes = $cmds->fetchAll();

require dirname(__DIR__) . '/includes/header.php';
?>

<h1 class="h3 mb-3">Mon espace</h1>
<?php if (!empty($_GET['ok'])): ?><div class="alert alert-success">Commande enregistree.</div><?php endif; ?>

<p><a class="btn btn-outline-secondary btn-sm" href="/compte/profil.php">Modifier mes infos</a></p>

<h2 class="h5">Mes commandes</h2>
<?php if (!$commandes): ?>
    <p class="text-muted">Aucune commande. Va sur <a href="/menus.php">Menus</a> pour en passer une.</p>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-sm bg-white">
            <thead><tr><th>#</th><th>Menu</th><th>Date</th><th>Total</th><th>Statut</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($commandes as $c): ?>
                <tr>
                    <td><?= (int)$c['id'] ?></td>
                    <td><?= e($c['titre']) ?></td>
                    <td><?= e($c['date_prestation']) ?></td>
                    <td><?= e($c['total']) ?> €</td>
                    <td><?= e(label_statut($c['statut'])) ?></td>
                        <td>
                            <a href="/compte/commande.php?id=<?= (int)$c['id'] ?>">Detail</a>
                            <?php if ($c['statut'] === 'terminee' && empty($c['avis_id'])): ?>
                                · <a href="/compte/commande.php?id=<?= (int)$c['id'] ?>"><strong>Laisser un avis</strong></a>
                            <?php endif; ?>
                        </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
