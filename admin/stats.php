<?php
require_once dirname(__DIR__) . '/includes/fonctions.php';
require_role(['admin']);
$pageTitle = 'Stats';

$docs = nosql_all('commandes_stats');

if (!$docs) {
    $rows = db()->query('SELECT c.id, c.menu_id, c.total, c.nb_personnes, c.created_at, m.titre
        FROM commandes c JOIN menus m ON m.id = c.menu_id
        WHERE c.statut != "annulee"')->fetchAll();
    foreach ($rows as $r) {
        nosql_insert('commandes_stats', [
            'menu_id' => (int)$r['menu_id'],
            'menu_titre' => $r['titre'],
            'total' => (float)$r['total'],
            'nb_personnes' => (int)$r['nb_personnes'],
            'date' => substr($r['created_at'], 0, 10),
            'commande_id' => (int)$r['id'],
        ]);
    }
    $docs = nosql_all('commandes_stats');
}

$menuFiltre = $_GET['menu'] ?? '';
$debut = $_GET['debut'] ?? '';
$fin = $_GET['fin'] ?? '';

$filtrés = array_filter($docs, function ($d) use ($menuFiltre, $debut, $fin) {
    if ($menuFiltre !== '' && (string)$d['menu_id'] !== (string)$menuFiltre) return false;
    if ($debut !== '' && ($d['date'] ?? '') < $debut) return false;
    if ($fin !== '' && ($d['date'] ?? '') > $fin) return false;
    return true;
});

$parMenu = [];
$caParMenu = [];
foreach ($filtrés as $d) {
    $t = $d['menu_titre'] ?? ('menu '.$d['menu_id']);
    if (!isset($parMenu[$t])) $parMenu[$t] = 0;
    if (!isset($caParMenu[$t])) $caParMenu[$t] = 0;
    $parMenu[$t]++;
    $caParMenu[$t] += (float)$d['total'];
}

$menus = db()->query('SELECT id, titre FROM menus ORDER BY titre')->fetchAll();
$labels = array_keys($parMenu);
$counts = array_values($parMenu);
$cas = array_values($caParMenu);

require dirname(__DIR__) . '/includes/header.php';
?>

<h1 class="h3 mb-3">Stats commandes</h1>
<p><a href="/admin/index.php">Retour</a> · donnees NoSQL : <strong><?= e(nosql_backend_label()) ?></strong></p>

<form class="row g-2 mb-4" method="get">
    <div class="col-md-3">
        <select class="form-select" name="menu">
            <option value="">Tous les menus</option>
            <?php foreach ($menus as $m): ?>
                <option value="<?= (int)$m['id'] ?>" <?= (string)$menuFiltre === (string)$m['id'] ? 'selected' : '' ?>><?= e($m['titre']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3"><input class="form-control" type="date" name="debut" value="<?= e($debut) ?>"></div>
    <div class="col-md-3"><input class="form-control" type="date" name="fin" value="<?= e($fin) ?>"></div>
    <div class="col-md-2"><button class="btn btn-vg" type="submit">Filtrer</button></div>
</form>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card card-menu p-3">
            <h2 class="h6">Nb commandes par menu</h2>
            <canvas id="chartNb" height="200"></canvas>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-menu p-3">
            <h2 class="h6">Chiffre d'affaires par menu (€)</h2>
            <canvas id="chartCa" height="200"></canvas>
            <ul class="mt-3 mb-0 small">
                <?php foreach ($caParMenu as $titre => $ca): ?>
                    <li><?= e($titre) ?> : <strong><?= number_format($ca, 2, ',', ' ') ?> €</strong></li>
                <?php endforeach; ?>
                <?php if (!$caParMenu): ?><li>Aucune donnee (passe une commande pour remplir).</li><?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const labels = <?= json_encode($labels, JSON_UNESCAPED_UNICODE) ?>;
const counts = <?= json_encode($counts) ?>;
const cas = <?= json_encode($cas) ?>;
if (labels.length) {
  new Chart(document.getElementById('chartNb'), {
    type: 'bar',
    data: { labels, datasets: [{ label: 'Commandes', data: counts, backgroundColor: '#2f5d50' }] },
    options: { scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
  });
  new Chart(document.getElementById('chartCa'), {
    type: 'bar',
    data: { labels, datasets: [{ label: 'CA', data: cas, backgroundColor: '#c45c26' }] },
    options: { scales: { y: { beginAtZero: true } } }
  });
}
</script>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
