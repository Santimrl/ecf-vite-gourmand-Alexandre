<?php
require_once __DIR__ . '/includes/db.php';

if (!defined('SEED_ENABLED') || !SEED_ENABLED) {
    http_response_code(403);
    exit('Seeds desactives. Mets SEED_ENABLED a true dans config.php, lance le script, puis remets false.');
}

$pdo = db();

$clients = [
    ['Leroy', 'Marie', 'marie.leroy@test.fr', '0611111111', '10 rue Sainte-Catherine, 33000 Bordeaux', 'User1234!'],
    ['Bernard', 'Thomas', 'thomas.bernard@test.fr', '0622222222', '3 cours Victor Hugo, 33000 Bordeaux', 'User1234!'],
    ['Roux', 'Sophie', 'sophie.roux@test.fr', '0633333333', '22 rue Fondaudege, 33000 Bordeaux', 'User1234!'],
    ['Moreau', 'Karim', 'karim.moreau@test.fr', '0644444444', '8 place de la Victoire, 33000 Bordeaux', 'User1234!'],
    ['Dupont', 'Claire', 'claire.dupont@test.fr', '0655555555', '15 quai des Chartrons, 33000 Bordeaux', 'User1234!'],
];

$avisTxt = [
    'Menu Noel parfait pour toute la famille, on a tout mange.',
    'Livraison a l\'heure, plats encore bien chauds. Top.',
    'On commande chez eux depuis un moment, toujours nickel.',
    'Bon rapport qualite prix pour un repas d\'entreprise.',
    'Le vegetarien etait vraiment bon, pas juste une salade.',
];

$notes = [5, 4, 5, 4, 5];
$menus = [2, 1, 1, 1, 3];

foreach ($clients as $i => $c) {
    $hash = password_hash($c[5], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('SELECT id FROM utilisateurs WHERE email = ?');
    $stmt->execute([$c[2]]);
    $row = $stmt->fetch();
    if ($row) {
        $uid = (int)$row['id'];
        $pdo->prepare('UPDATE utilisateurs SET mot_de_passe=?, nom=?, prenom=?, gsm=?, adresse=?, role="utilisateur", actif=1 WHERE id=?')
            ->execute([$hash, $c[0], $c[1], $c[3], $c[4], $uid]);
    } else {
        $pdo->prepare('INSERT INTO utilisateurs (nom,prenom,email,gsm,adresse,mot_de_passe,role,actif) VALUES (?,?,?,?,?,?,"utilisateur",1)')
            ->execute([$c[0], $c[1], $c[2], $c[3], $c[4], $hash]);
        $uid = (int)$pdo->lastInsertId();
    }

    $menuId = $menus[$i];
    $prix = (float)$pdo->query('SELECT prix_min FROM menus WHERE id=' . (int)$menuId)->fetchColumn();

    $chk = $pdo->prepare('SELECT c.id FROM commandes c JOIN avis a ON a.commande_id = c.id WHERE c.email = ? LIMIT 1');
    $chk->execute([$c[2]]);
    if ($chk->fetch()) {
        echo "Deja OK: {$c[2]}\n";
        continue;
    }

    $ins = $pdo->prepare('INSERT INTO commandes
        (utilisateur_id, menu_id, nom, prenom, email, gsm, adresse_prestation, date_prestation, heure_prestation, lieu, ville, km_hors_bordeaux, nb_personnes, prix_menu, prix_livraison, reduction_pct, total, statut)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,0,4,?,0,0,?,"terminee")');
    $ins->execute([
        $uid, $menuId, $c[0], $c[1], $c[2], $c[3], $c[4],
        '2026-06-15', '12:30:00', 'Domicile', 'Bordeaux', $prix, $prix
    ]);
    $cid = (int)$pdo->lastInsertId();
    $pdo->prepare('INSERT INTO commande_suivi (commande_id, statut) VALUES (?,"terminee")')->execute([$cid]);
    $pdo->prepare('INSERT INTO avis (commande_id, utilisateur_id, note, commentaire, valide) VALUES (?,?,?,?,1)')
        ->execute([$cid, $uid, $notes[$i], $avisTxt[$i]]);
    echo "Avis cree: {$c[1]} {$c[0]}\n";
}

echo "OK — 5 clients demo (mdp User1234!) + avis valides\n";
