<?php
require_once __DIR__ . '/includes/fonctions.php';
$_SESSION = [];
session_destroy();
header('Location: index.php');
exit;
