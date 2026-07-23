<?php
if (!defined('APP_NAME')) {
    require_once __DIR__ . '/fonctions.php';
}
$user = utilisateur_connecte();
$pageTitle = isset($pageTitle) ? $pageTitle . ' — ' . APP_NAME : APP_NAME;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-vg">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="/index.php">
            <img src="/assets/img/logo.svg" alt="" width="40" height="40" class="logo-vg">
            <span><?= e(APP_NAME) ?></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain" aria-controls="navMain" aria-expanded="false" aria-label="Menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="/index.php">Accueil</a></li>
                <li class="nav-item"><a class="nav-link" href="/menus.php">Menus</a></li>
                <li class="nav-item"><a class="nav-link" href="/contact.php">Contact</a></li>
                <?php if ($user && $user['role'] === 'utilisateur'): ?>
                    <li class="nav-item"><a class="nav-link" href="/compte/index.php">Mon espace</a></li>
                <?php endif; ?>
                <?php if ($user && in_array($user['role'], ['employe','admin'], true)): ?>
                    <li class="nav-item"><a class="nav-link" href="/employe/index.php">Espace employé</a></li>
                <?php endif; ?>
                <?php if ($user && $user['role'] === 'admin'): ?>
                    <li class="nav-item"><a class="nav-link" href="/admin/index.php">Admin</a></li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <?php if ($user): ?>
                    <li class="nav-item"><span class="nav-link text-white pe-none">Bonjour <?= e($user['prenom']) ?></span></li>
                    <li class="nav-item"><a class="nav-link" href="/logout.php">Déconnexion</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="/register.php">Créer un compte</a></li>
                    <li class="nav-item"><a class="nav-link btn-nav-login" href="/login.php">Connexion</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<main class="container py-4">
