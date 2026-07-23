<?php
require_once dirname(__DIR__) . '/includes/fonctions.php';
require_role(['admin']);
$pageTitle = 'Admin';
require dirname(__DIR__) . '/includes/header.php';
?>

<h1 class="h3">Espace administrateur</h1>
<p>Connecté : <?= e($_SESSION['user']['prenom']) ?></p>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card card-menu p-3 h-100">
            <h2 class="h5">Employés</h2>
            <p class="small">Créer / désactiver un compte employé.</p>
            <a class="btn btn-vg btn-sm" href="/admin/employes.php">Gérer</a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-menu p-3 h-100">
            <h2 class="h5">Stats</h2>
            <p class="small">Commandes par menu et CA.</p>
            <a class="btn btn-vg btn-sm" href="/admin/stats.php">Voir</a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-menu p-3 h-100">
            <h2 class="h5">Comme employé</h2>
            <p class="small">Menus, commandes, avis…</p>
            <a class="btn btn-outline-secondary btn-sm" href="/employe/index.php">Espace employé</a>
        </div>
    </div>
</div>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
