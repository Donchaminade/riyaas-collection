<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/security.php';
require_once __DIR__ . '/../lib/cart.php';

$db     = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($action) {

    // POST /api/cart.php?action=add
    // Body : { "product_id": 1, "variant_id": 0, "quantity": 1, "color": "Rouge" }
    // Retourne l'article complet (prix serveur) à stocker côté client
    case 'add':
        if ($method !== 'POST') respondError('Méthode non autorisée', 405);

        $body      = getBody();
        $productId = (int)($body['product_id'] ?? 0);

        if (!$productId) respondError('Produit invalide', 400);

        $result = validateCartItems($db, [$body]);

        if (empty($result['items'])) respondError('Produit introuvable', 404);

        respond([
            'success' => true,
            'item'    => $result['items'][0],
        ]);
        break;

    // POST /api/cart.php?action=validate
    // Body : { "cart": [ { "product_id": 1, "variant_id": 2, "quantity": 1 } ] }
    // Recalcule tous les prix côté serveur avant commande
    case 'validate':
        if ($method !== 'POST') respondError('Méthode non autorisée', 405);

        $body = getBody();
        $cart = $body['cart'] ?? [];

        if (empty($cart) || !is_array($cart)) respondError('Panier vide', 400);

        $result = validateCartItems($db, $cart);

        if (empty($result['items'])) respondError('Aucun article valide dans le panier', 400);

        $total   = $result['total'];
        $deposit = round($total * DEPOSIT_PERCENT / 100);

        respond([
            'success' => true,
            'cart'    => $result['items'],
            'total'   => $total,
            'deposit' => $deposit,
            'balance' => $total - $deposit,
        ]);
        break;

    default:
        respondError('Action invalide', 400);
}
