<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/security.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($action) {

    // POST /api/auth.php?action=admin-login
    case 'admin-login':
        if ($method !== 'POST') respondError('Méthode non autorisée', 405);

        $body     = getBody();
        $password = $body['password'] ?? '';

        // Mot de passe admin (à changer en production !)
        $adminPassword = 'riyaas2024';

        if ($password !== $adminPassword) {
            respondError('Mot de passe incorrect', 401);
        }

        // Générer un token simple
        $token = bin2hex(random_bytes(32));

        // Stocker le token en session côté serveur
        session_start();
        $_SESSION['admin_token'] = $token;
        $_SESSION['admin_time']  = time();

        respond([
            'success' => true,
            'token'   => $token,
        ]);
        break;

    // POST /api/auth.php?action=verify
    case 'verify':
        if ($method !== 'POST') respondError('Méthode non autorisée', 405);

        $body  = getBody();
        $token = $body['token'] ?? '';

        session_start();

        $storedToken = $_SESSION['admin_token'] ?? null;
        $storedTime  = $_SESSION['admin_time']  ?? 0;

        // Token valide pendant 8 heures
        if (!$storedToken || $token !== $storedToken || (time() - $storedTime) > 28800) {
            respondError('Token invalide ou expiré', 401);
        }

        respond(['success' => true, 'valid' => true]);
        break;

    // POST /api/auth.php?action=logout
    case 'logout':
        session_start();
        session_destroy();
        respond(['success' => true]);
        break;

    default:
        respondError('Action invalide', 400);
}