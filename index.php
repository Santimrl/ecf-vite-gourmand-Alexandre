<?php
require_once __DIR__ . '/includes/fonctions.php';
$pageTitle = 'Accueil';

$avis = [];
try {
    $sql = "SELECT a.note, a.commentaire, u.prenom, u.nom
            FROM avis a
            JOIN utilisateurs u ON u.id = a.utilisateur_id
            WHERE a.valide = 1
            ORDER BY a.created_at DESC
            LIMIT 12";
    $avis = db()->query($sql)->fetchAll();
} catch (Exception $e) {
    $avis = [];
}

require __DIR__ . '/includes/header.php';
?>

<section class="hero-vg mb-4">
    <div class="row align-items-center g-3">
        <div class="col-lg-6">
            <h1 class="display-6">Vite &amp; Gourmand</h1>
            <p class="lead mb-2">Traiteur a Bordeaux depuis 25 ans. Menus pour Noel, Paques, evenements… et le reste de l'annee.</p>
            <p class="mb-3 opacity-75">Julie &amp; Jose — 12 rue Sainte-Catherine, 33000 Bordeaux</p>
            <a class="btn btn-light btn-lg me-2" href="/menus.php">Voir nos menus</a>
            <a class="btn btn-outline-light btn-lg" href="/contact.php">Nous contacter</a>
        </div>
        <div class="col-lg-6">
            <img src="/assets/img/hero-traiteur.jpg" alt="Table dressée, prestation traiteur" class="img-fluid rounded hero-photo">
        </div>
    </div>
</section>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card card-menu h-100">
            <img src="/assets/img/equipe.jpg" class="card-img-top" alt="Cuisine / préparation des plats" style="height:200px;object-fit:cover;">
            <div class="card-body">
                <h2 class="h5 page-title">Présentation</h2>
                <p>Vite &amp; Gourmand, c'est Julie et José. Traiteur a Bordeaux depuis 25 ans : repas de famille, fêtes, entreprises…</p>
                <p class="mb-0">On propose des menus qui changent selon la saison (Noel, Paques, classique…) et on livre sur Bordeaux et alentours.</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-menu h-100">
            <div class="card-body">
                <h2 class="h5 page-title">Une équipe pro</h2>
                <ul class="mb-0">
                    <li>Allergenes indiqués sur chaque plat</li>
                    <li>Livraison Bordeaux et autour</li>
                    <li>Menus classiques, vegetariens, vegan</li>
                    <li>Commande possible en ligne</li>
                    <li>Suivi de ta commande une fois acceptée</li>
                    <li>Réponse rapide via la page Contact</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<h2 class="h4 page-title mb-3">Avis clients</h2>
<?php if (!$avis): ?>
    <p class="text-muted">Pas encore d'avis pour le moment.</p>
<?php else: ?>
    <div class="avis-marquee mb-4" aria-label="Avis clients">
        <div class="avis-marquee-track">
            <?php
            $boucle = array_merge($avis, $avis);
            foreach ($boucle as $a):
            ?>
                <div class="avis-card avis-slide">
                    <strong><?= e($a['prenom'] . ' ' . substr($a['nom'], 0, 1)) ?>.</strong>
                    <div class="text-warning"><?= str_repeat('★', (int)$a['note']) ?><?= str_repeat('☆', 5 - (int)$a['note']) ?></div>
                    <p class="mb-0 small"><?= e($a['commentaire']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
