<?php
/**
 * RIYAA'S COLLECTION — Configuration générale
 */

// ── Environnement ────────────────────────────────────────────
define('APP_ENV',  'development'); // 'production' en ligne
define('APP_NAME', "Riyaa's Collection");
define('APP_URL',  'http://localhost/riyaas-collection/public');

// ── Affichage des erreurs (désactiver en production) ─────────
if (APP_ENV === 'development') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

// ── Timezone ─────────────────────────────────────────────────
date_default_timezone_set('Africa/Lome');

// ── Devise ───────────────────────────────────────────────────
define('CURRENCY',        'FCFA');
define('CURRENCY_ISO',    'XOF');

// ── Règles métier ─────────────────────────────────────────────
define('DEPOSIT_PERCENT',  50);    // Acompte = 50% du total
define('DELIVERY_DAYS',    5);     // Délai de livraison garanti

// ── Chemins ──────────────────────────────────────────────────
define('ROOT_PATH',   dirname(__DIR__));
define('APP_PATH',    ROOT_PATH . '/app');
define('VIEW_PATH',   APP_PATH  . '/views');
define('PUBLIC_PATH', ROOT_PATH . '/public');
