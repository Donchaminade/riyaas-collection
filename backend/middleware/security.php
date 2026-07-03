<?php
// ── CORS — Autoriser Vercel à parler au backend ──────────────
$allowedOrigins = [
    'http://localhost:5173',
    'http://localhost:3000',
    'http://127.0.0.1:5500',
    'https://riyaas-collection.vercel.app', // ton domaine Vercel (à mettre à jour)
    'https://riyaascollection.vercel.app',
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
}

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

// Répondre aux requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ── Fonction réponse JSON ────────────────────────────────────
function respond(array $data, int $status = 200): void {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function respondError(string $message, int $status = 400): void {
    respond(['success' => false, 'error' => $message], $status);
}

// ── Récupérer le body JSON de la requête ────────────────────
function getBody(): array {
    $body = file_get_contents('php://input');
    return json_decode($body, true) ?? [];
}

// ── Sanitizer une chaîne ─────────────────────────────────────
function sanitize(string $value): string {
    return htmlspecialchars(strip_tags(trim($value)));
}