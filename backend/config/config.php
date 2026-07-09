<?php
/**
 * RIYAA'S COLLECTION — Configuration centrale du backend
 *
 * En production (Hostinger), NE PAS modifier ce fichier :
 * créer un fichier `config.local.php` dans ce même dossier qui définit
 * les constantes à écraser (il est chargé en premier et n'est pas versionné).
 *
 * Exemple de config.local.php :
 *   <?php
 *   define('DB_HOST', 'localhost');
 *   define('DB_NAME', 'u123456_riyaas');
 *   define('DB_USER', 'u123456_user');
 *   define('DB_PASS', 'mot-de-passe-hostinger');
 *   define('ADMIN_PASSWORD', 'un-mot-de-passe-fort');
 *   define('AUTH_SECRET', 'une-longue-chaine-aleatoire-unique');
 *   define('ASSETS_BASE_URL', 'https://riyaas.grosbit.com/uploads/products');
 *   define('ALLOWED_ORIGINS', ['https://riyaas-collection.vercel.app']);
 */

if (file_exists(__DIR__ . '/config.local.php')) {
    require_once __DIR__ . '/config.local.php';
}

// ── Base de données ──────────────────────────────────────────
defined('DB_HOST')    || define('DB_HOST', 'localhost');
defined('DB_NAME')    || define('DB_NAME', 'riyaas_collection');
defined('DB_USER')    || define('DB_USER', 'root');
defined('DB_PASS')    || define('DB_PASS', '');
defined('DB_CHARSET') || define('DB_CHARSET', 'utf8mb4');

// ── Authentification admin ───────────────────────────────────
// À CHANGER ABSOLUMENT EN PRODUCTION (via config.local.php)
defined('ADMIN_PASSWORD') || define('ADMIN_PASSWORD', 'riyaas2024');
// Secret utilisé pour signer les tokens (HMAC) — à changer en production
defined('AUTH_SECRET')    || define('AUTH_SECRET', 'riyaas-dev-secret-a-changer-en-production');
// Durée de validité du token admin (secondes) — 8 heures
defined('AUTH_TOKEN_TTL') || define('AUTH_TOKEN_TTL', 28800);

// ── Règles métier ────────────────────────────────────────────
defined('DEPOSIT_PERCENT') || define('DEPOSIT_PERCENT', 50); // acompte en %
defined('DELIVERY_DAYS')   || define('DELIVERY_DAYS', 5);    // délai de livraison par défaut

// ── Images produits ──────────────────────────────────────────
// URL publique de base des images produits.
// Laisser vide ('') pour l'auto-détection : {scheme}://{host}/{chemin-backend}/uploads/products
defined('ASSETS_BASE_URL') || define('ASSETS_BASE_URL', '');

// ── CORS : origines autorisées à appeler l'API ───────────────
defined('ALLOWED_ORIGINS') || define('ALLOWED_ORIGINS', [
    'http://localhost:5173',
    'http://localhost:3000',
    'http://127.0.0.1:5500',
    'http://localhost',
    'https://riyaas-collection.vercel.app',
    'https://riyaascollection.vercel.app',
]);

date_default_timezone_set('Africa/Lome');

// Ne jamais afficher les erreurs PHP dans les réponses JSON (elles sont loguées)
ini_set('display_errors', '0');
ini_set('log_errors', '1');
