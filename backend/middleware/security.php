<?php
require_once __DIR__ . '/../config/config.php';

// ── CORS — Autoriser le frontend (Vercel / local) à parler au backend ──
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, ALLOWED_ORIGINS, true)) {
    header("Access-Control-Allow-Origin: $origin");
    header('Vary: Origin');
}

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Max-Age: 86400');
header('Content-Type: application/json; charset=utf-8');

// Répondre aux requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ── Réponses JSON ────────────────────────────────────────────
function respond(array $data, int $status = 200): void {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function respondError(string $message, int $status = 400): void {
    respond(['success' => false, 'error' => $message], $status);
}

// ── Body JSON de la requête ──────────────────────────────────
function getBody(): array {
    $body = file_get_contents('php://input');
    return json_decode($body, true) ?? [];
}

// ── Sanitizer une chaîne ─────────────────────────────────────
function sanitize(string $value): string {
    return htmlspecialchars(strip_tags(trim($value)));
}

// ── URL publique d'une image produit ─────────────────────────
function productImageUrl(?string $filename): ?string {
    if (!$filename) return null;

    $base = ASSETS_BASE_URL;
    if ($base === '') {
        // Auto-détection : {scheme}://{host}/{racine-backend}/uploads/products
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
        // /chemin/vers/backend/api/xxx.php → /chemin/vers/backend
        $root   = dirname(dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        $root   = ($root === '/' || $root === '\\') ? '' : $root;
        $base   = "$scheme://$host$root/uploads/products";
    }

    return rtrim($base, '/') . '/' . ltrim($filename, '/');
}

// ── Auth admin par token signé (HMAC, stateless) ─────────────
// Format : base64url(expiration).hmac_sha256(expiration, AUTH_SECRET)
function makeAdminToken(): string {
    $expires   = (string)(time() + AUTH_TOKEN_TTL);
    $payload   = rtrim(strtr(base64_encode($expires), '+/', '-_'), '=');
    $signature = hash_hmac('sha256', $payload, AUTH_SECRET);
    return "$payload.$signature";
}

function isValidAdminToken(string $token): bool {
    $parts = explode('.', $token);
    if (count($parts) !== 2) return false;

    [$payload, $signature] = $parts;
    $expected = hash_hmac('sha256', $payload, AUTH_SECRET);
    if (!hash_equals($expected, $signature)) return false;

    $expires = (int) base64_decode(strtr($payload, '-_', '+/'));
    return $expires > time();
}

function getBearerToken(): string {
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    if (preg_match('/Bearer\s+(\S+)/i', $header, $m)) {
        return $m[1];
    }
    return '';
}

// Bloque la requête si le token admin est absent ou invalide
function requireAdmin(): void {
    $token = getBearerToken();
    if (!$token || !isValidAdminToken($token)) {
        respondError('Accès non autorisé', 401);
    }
}
