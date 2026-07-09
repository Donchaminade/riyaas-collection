<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/security.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($action) {

    // POST /api/auth.php?action=admin-login
    // Body : { "password": "..." }
    case 'admin-login':
        if ($method !== 'POST') respondError('Méthode non autorisée', 405);

        $body     = getBody();
        $password = (string)($body['password'] ?? '');

        if (!hash_equals(ADMIN_PASSWORD, $password)) {
            // Ralentir les tentatives de brute force
            usleep(500000);
            respondError('Mot de passe incorrect', 401);
        }

        respond([
            'success'    => true,
            'token'      => makeAdminToken(),
            'expires_in' => AUTH_TOKEN_TTL,
        ]);
        break;

    // POST /api/auth.php?action=verify
    // Header : Authorization: Bearer {token}  (ou body { "token": "..." })
    case 'verify':
        if ($method !== 'POST') respondError('Méthode non autorisée', 405);

        $token = getBearerToken() ?: (string)(getBody()['token'] ?? '');

        if (!$token || !isValidAdminToken($token)) {
            respondError('Token invalide ou expiré', 401);
        }

        respond(['success' => true, 'valid' => true]);
        break;

    // POST /api/auth.php?action=logout
    // Token stateless : la déconnexion consiste à supprimer le token côté client
    case 'logout':
        respond(['success' => true]);
        break;

    default:
        respondError('Action invalide', 400);
}
