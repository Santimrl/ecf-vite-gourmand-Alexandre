<?php
require_once dirname(__DIR__) . '/includes/fonctions.php';
require_role(['employe', 'admin']);
$pageTitle = 'Espace employé';
require dirname(__DIR__) . '/includes/header.php';
?>

<h1 class="h3">Espace employé</h1>
<p>Bonjour <?= e($_SESSION['user']['prenom']) ?>.</p>

<div class="row g-3">
    <div class="col-md-4">
        <div class="card card-menu p-3">
            <h2 class="h5">Menus / plats / horaires</h2>
            <a class="btn btn-vg btn-sm" href="/employe/menus.php">Gérer les menus</a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-menu p-3">
            <h2 class="h5">Commandes</h2>
            <a class="btn btn-vg btn-sm" href="/employe/commandes.php">Voir / changer statut</a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-menu p-3">
            <h2 class="h5">Avis clients</h2>
            <a class="btn btn-vg btn-sm" href="/employe/avis.php">Valider / refuser</a>
        </div>
    </div>
</div>

<p class="mt-3 small text-muted">Liens vers menus, commandes, avis, horaires, plats.</p>

<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
