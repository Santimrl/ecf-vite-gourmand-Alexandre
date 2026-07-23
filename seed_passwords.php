<?php
require_once __DIR__ . '/includes/db.php';

if (!defined('SEED_ENABLED') || !SEED_ENABLED) {
    http_response_code(403);
    exit('Seeds desactives. Mets SEED_ENABLED a true dans config.php, lance le script, puis remets false.');
}

$users = [
    ['Martin', 'Jose', 'jose@vite-gourmand.fr', '0600000001', '12 rue Sainte-Catherine, 33000 Bordeaux', 'Admin123!', 'admin'],
    ['Dupont', 'Julie', 'julie@vite-gourmand.fr', '0600000002', '12 rue Sainte-Catherine, 33000 Bordeaux', 'Employe123!', 'employe'],
    ['Test', 'Client', 'client@test.fr', '0600000003', '5 cours Victor Hugo, 33000 Bordeaux', 'User1234!', 'utilisateur'],
];

$pdo = db();

$nbH = (int)$pdo->query('SELECT COUNT(*) FROM horaires')->fetchColumn();
if ($nbH === 0) {
    echo "Importe d'abord sql/seed.sql (horaires/menus) puis relance.\n";
}

foreach ($users as $u) {
    $hash = password_hash($u[5], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('SELECT id FROM utilisateurs WHERE email = ?');
    $stmt->execute([$u[2]]);
    if ($stmt->fetch()) {
        $upd = $pdo->prepare('UPDATE utilisateurs SET mot_de_passe=?, role=?, actif=1, nom=?, prenom=?, gsm=?, adresse=? WHERE email=?');
        $upd->execute([$hash, $u[6], $u[0], $u[1], $u[3], $u[4], $u[2]]);
        echo "Maj: {$u[2]}\n";
    } else {
        $ins = $pdo->prepare('INSERT INTO utilisateurs (nom,prenom,email,gsm,adresse,mot_de_passe,role,actif) VALUES (?,?,?,?,?,?,?,1)');
        $ins->execute([$u[0], $u[1], $u[2], $u[3], $u[4], $hash, $u[6]]);
        echo "Cree: {$u[2]}\n";
    }
}

echo "\nComptes:\n";
echo "Admin   jose@vite-gourmand.fr / Admin123!\n";
echo "Employe julie@vite-gourmand.fr / Employe123!\n";
echo "User    client@test.fr / User1234!\n";
echo "OK\n";
