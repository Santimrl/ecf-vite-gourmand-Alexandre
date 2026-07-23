<?php
require_once __DIR__ . '/includes/fonctions.php';
$pageTitle = 'Menus';
require __DIR__ . '/includes/header.php';
?>

<h1 class="h3 page-title mb-2">Nos menus</h1>
<span class="chip-tous">Tous</span>
<p class="text-muted">Tu peux filtrer sans recharger la page.</p>

<form id="filtres" class="row g-2 filtres-box p-3 mb-4">
    <div class="col-md-2">
        <label class="form-label" for="prix_max">Prix max (€)</label>
        <input class="form-control" type="number" step="0.01" id="prix_max" name="prix_max">
    </div>
    <div class="col-md-2">
        <label class="form-label" for="prix_min">Prix min (€)</label>
        <input class="form-control" type="number" step="0.01" id="prix_min" name="prix_min">
    </div>
    <div class="col-md-2">
        <label class="form-label" for="theme">Thème</label>
        <select class="form-select" id="theme" name="theme">
            <option value="">Tous</option>
            <option>Noel</option>
            <option>Paques</option>
            <option>classique</option>
            <option>evenement</option>
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label" for="regime">Régime</label>
        <select class="form-select" id="regime" name="regime">
            <option value="">Tous</option>
            <option>vegetarien</option>
            <option>vegan</option>
            <option>classique</option>
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label" for="nb_min">Nb pers. min</label>
        <input class="form-control" type="number" id="nb_min" name="nb_min" min="1">
    </div>
    <div class="col-md-2 d-flex align-items-end">
        <button type="button" id="btnFiltrer" class="btn btn-vg w-100">Filtrer</button>
    </div>
</form>

<div id="liste-menus" class="row g-3">
    <p class="text-muted">Chargement…</p>
</div>

<script src="assets/js/menus.js"></script>
<?php require __DIR__ . '/includes/footer.php'; ?>
