<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/nosql.php';
require_once dirname(__DIR__) . '/classes/Repositories.php';

function e($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

function utilisateur_connecte() {
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

function require_login() {
    if (!utilisateur_connecte()) {
        header('Location: /login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

function require_role($roles) {
    require_login();
    if (!is_array($roles)) {
        $roles = [$roles];
    }
    if (!in_array($_SESSION['user']['role'], $roles, true)) {
        http_response_code(403);
        die('Accès refusé.');
    }
}

function envoyer_mail_app($to, $sujet, $message) {
    $headers = 'From: ' . MAIL_FROM . "\r\n" . 'Content-Type: text/plain; charset=utf-8';
    $ok = @mail($to, $sujet, $message, $headers);
    $log = APP_ROOT . '/data/mails.log';
    $ligne = date('c') . " | to=$to | $sujet | " . ($ok ? 'mail()' : 'log-only') . "\n$message\n----\n";
    file_put_contents($log, $ligne, FILE_APPEND);
    return $ok;
}

function mdp_valide($mdp) {
    if (strlen($mdp) < 10) return false;
    if (!preg_match('/[A-Z]/', $mdp)) return false;
    if (!preg_match('/[a-z]/', $mdp)) return false;
    if (!preg_match('/[0-9]/', $mdp)) return false;
    if (!preg_match('/[^A-Za-z0-9]/', $mdp)) return false;
    return true;
}

function csrf_token() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_check() {
    if (empty($_POST['csrf']) || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])) {
        die('Jeton CSRF invalide.');
    }
}

function horaires_footer() {
    $rows = db()->query('SELECT * FROM horaires ORDER BY FIELD(jour,"lundi","mardi","mercredi","jeudi","vendredi","samedi","dimanche")')->fetchAll();
    return $rows;
}

function label_statut($statut) {
    $map = [
        'en_attente' => 'En attente',
        'accepte' => 'Acceptee',
        'en_preparation' => 'En preparation',
        'en_livraison' => 'En livraison',
        'livre' => 'Livree',
        'attente_materiel' => 'Attente retour materiel',
        'terminee' => 'Terminee',
        'annulee' => 'Annulee',
    ];
    return $map[$statut] ?? $statut;
}

function label_theme($t) {
    $map = ['Noel' => 'Noel', 'Paques' => 'Paques', 'classique' => 'Classique', 'evenement' => 'Evenement'];
    return $map[$t] ?? $t;
}

function label_regime($r) {
    $map = ['vegetarien' => 'Vegetarien', 'vegan' => 'Vegan', 'classique' => 'Classique'];
    return $map[$r] ?? $r;
}
