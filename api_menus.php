<?php
require_once __DIR__ . '/includes/fonctions.php';
header('Content-Type: application/json; charset=utf-8');

$sql = 'SELECT m.id, m.titre, m.description, m.theme, m.regime,
               m.nb_personnes_min, m.prix_min, m.stock,
               (SELECT chemin FROM menu_images mi WHERE mi.menu_id = m.id LIMIT 1) AS image
        FROM menus m
        WHERE m.actif = 1';
$params = [];

if (isset($_GET['prix_max']) && $_GET['prix_max'] !== '') {
    $sql .= ' AND m.prix_min <= ?';
    $params[] = (float)$_GET['prix_max'];
}
if (isset($_GET['prix_min']) && $_GET['prix_min'] !== '') {
    $sql .= ' AND m.prix_min >= ?';
    $params[] = (float)$_GET['prix_min'];
}
if (!empty($_GET['theme'])) {
    $sql .= ' AND m.theme = ?';
    $params[] = $_GET['theme'];
}
if (!empty($_GET['regime'])) {
    $sql .= ' AND m.regime = ?';
    $params[] = $_GET['regime'];
}
if (isset($_GET['nb_min']) && $_GET['nb_min'] !== '') {
    $sql .= ' AND m.nb_personnes_min <= ?';
    $params[] = (int)$_GET['nb_min'];
}

$sql .= ' ORDER BY m.titre';
$stmt = db()->prepare($sql);
$stmt->execute($params);
echo json_encode($stmt->fetchAll(), JSON_UNESCAPED_UNICODE);
