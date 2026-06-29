<?php
/**
 * RIYAA'S COLLECTION — Point d'entrée unique
 * Tout le trafic passe par ce fichier (Front Controller)
 */

declare(strict_types=1);

// ── Démarrer la session ──────────────────────────────────────
session_start();

// ── Autoload des classes (sans Composer pour l'instant) ─────
spl_autoload_register(function (string $class): void {
    $base = dirname(__DIR__) . '/app/';
    $dirs = ['controllers/', 'models/'];
    foreach ($dirs as $dir) {
        $file = $base . $dir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// ── Chargement de la config ──────────────────────────────────
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/config/fedapay.php';

// ── Routeur simple ───────────────────────────────────────────
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri    = rtrim($uri, '/');
$method = $_SERVER['REQUEST_METHOD'];

// Retirer le préfixe du sous-dossier WampServer si besoin
// ex: /riyaas-collection/public devient /
$base_path = '/riyaas-collection/public';
if (str_starts_with($uri, $base_path)) {
    $uri = substr($uri, strlen($base_path));
}
if ($uri === '') $uri = '/';

// ── Table de routage ─────────────────────────────────────────
$routes = [
    // Page d'accueil
    'GET /'                        => ['HomeController',    'index'],

    // Catalogue
    'GET /catalogue'               => ['ProductController', 'catalog'],
    'GET /produit'                 => ['ProductController', 'show'],

    // Panier
    'GET /panier'                  => ['CartController',    'index'],
    'POST /panier/ajouter'         => ['CartController',    'add'],
    'POST /panier/modifier'        => ['CartController',    'update'],
    'POST /panier/supprimer'       => ['CartController',    'remove'],

    // Commande / Checkout
    'GET /commander'               => ['OrderController',   'checkout'],
    'POST /commander'              => ['OrderController',   'store'],
    'GET /commande/confirmation'   => ['OrderController',   'confirmation'],
    'GET /commande/echec'          => ['OrderController',   'failed'],

    // Paiement
    'GET /paiement/acompte'       => ['PaymentController', 'initiateDeposit'],
    'GET /paiement/callback'       => ['PaymentController', 'handleCallback'],
    'POST /paiement/callback'      => ['PaymentController', 'handleCallback'],
    'GET /paiement/retour'         => ['PaymentController', 'returnPage'],

    // Facture
    'GET /facture'                 => ['OrderController',   'invoice'],

    // Admin
    'GET /admin'                        => ['AdminController', 'dashboard'],
    'GET /admin/commandes'          => ['AdminController', 'orders'],
    'POST /admin/commande/statut'   => ['AdminController', 'updateOrderStatus'],
    'GET /admin/login'                  => ['AdminController', 'loginForm'],
    'POST /admin/login'                 => ['AdminController', 'login'],
    'GET /admin/deconnexion'            => ['AdminController', 'logout'],
    'GET /admin/produit/ajouter'        => ['AdminController', 'addForm'],
    'POST /admin/produit/ajouter'       => ['AdminController', 'store'],
    'GET /admin/produit/modifier'       => ['AdminController', 'editForm'],
    'POST /admin/produit/modifier'      => ['AdminController', 'update'],
    'POST /admin/produit/supprimer'     => ['AdminController', 'delete'],
    'POST /admin/image/supprimer'       => ['AdminController', 'deleteImage'],
    'POST /admin/image/couverture'      => ['AdminController', 'setCover'],

    // Compte client
    'GET /connexion'               => ['AuthController',    'loginForm'],
    'POST /connexion'              => ['AuthController',    'login'],
    'GET /inscription'             => ['AuthController',    'registerForm'],
    'POST /inscription'            => ['AuthController',    'register'],
    'GET /deconnexion'             => ['AuthController',    'logout'],
    'GET /mon-compte'              => ['AuthController',    'dashboard'],
    'GET /mes-commandes'           => ['AuthController',    'orders'],
];

// ── Dispatch ─────────────────────────────────────────────────
$key = $method . ' ' . $uri;

if (isset($routes[$key])) {
    [$controllerName, $action] = $routes[$key];

    $controllerFile = dirname(__DIR__) . '/app/controllers/' . $controllerName . '.php';
    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        $controller = new $controllerName();
        $controller->$action();
    } else {
        http_response_code(500);
        die("Erreur : controller $controllerName introuvable.");
    }
} else {
    // 404
    http_response_code(404);
    require_once dirname(__DIR__) . '/app/views/404.php';
}
