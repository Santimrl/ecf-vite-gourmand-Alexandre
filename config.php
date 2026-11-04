<?php

// Lit une variable d'environnement, sinon valeur par defaut (local USB)
function env($key, $default = '') {
    $v = getenv($key);
    if ($v === false || $v === '') {
        return $default;
    }
    return $v;
}

define('APP_NAME', 'Vite & Gourmand');
define('APP_URL', env('APP_URL', 'http://localhost:8081'));
define('APP_ROOT', __DIR__);

// Local USB par defaut ; Docker passe DB_HOST=db etc.
define('DB_HOST', env('DB_HOST', '127.0.0.1'));
define('DB_PORT', env('DB_PORT', '3307'));
define('DB_NAME', env('DB_NAME', 'vite_gourmand'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', 'usbw'));

// Mongo (Docker). Vide = fallback JSON fichiers (USB sans extension mongo)
define('MONGO_URI', env('MONGO_URI', ''));
define('MONGO_DB', env('MONGO_DB', 'vite_gourmand'));

define('LIVRAISON_BASE', 5.00);
define('LIVRAISON_PAR_KM', 0.59);

define('MAIL_ENTREPRISE', 'contact@vite-gourmand.local');
define('MAIL_FROM', 'noreply@vite-gourmand.local');

// true uniquement pour (re)creer les comptes/avis demo, puis remettre a false
define('SEED_ENABLED', env('SEED_ENABLED', 'true') === 'true' || env('SEED_ENABLED', '1') === '1');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
