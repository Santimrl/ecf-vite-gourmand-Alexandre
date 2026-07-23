<?php
require_once __DIR__ . '/includes/fonctions.php';
$pageTitle = 'Confidentialite';
require __DIR__ . '/includes/header.php';
?>

<h1 class="h3">Confidentialite / RGPD</h1>
<p>On collecte surtout ce qu'il faut pour gerer les comptes et les commandes :</p>
<ul>
    <li>identite (nom, prenom)</li>
    <li>coordonnees (mail, tel, adresse)</li>
    <li>historique de commandes et avis</li>
</ul>
<p>Ces infos servent uniquement au fonctionnement du site Vite &amp; Gourmand (pas de revente).</p>
<p>Tu peux demander l'acces, la correction ou la suppression de tes donnees en ecrivant via la page <a href="/contact.php">Contact</a>.</p>
<p>Les mots de passe sont stockes hashes, pas en clair.</p>

<?php require __DIR__ . '/includes/footer.php'; ?>
