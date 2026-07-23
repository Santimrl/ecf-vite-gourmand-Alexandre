<?php

define('APP_NAME', 'Vite & Gourmand');
define('APP_URL', 'http://localhost:8081');
define('APP_ROOT', __DIR__);

define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3307');
define('DB_NAME', 'vite_gourmand');
define('DB_USER', 'root');
define('DB_PASS', 'usbw');

define('LIVRAISON_BASE', 5.00);
define('LIVRAISON_PAR_KM', 0.59);

define('MAIL_ENTREPRISE', 'contact@vite-gourmand.local');
define('MAIL_FROM', 'noreply@vite-gourmand.local');

// true uniquement pour (re)creer les comptes/avis demo, puis remettre a false
define('SEED_ENABLED', true);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
