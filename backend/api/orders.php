<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/security.php';
require_once __DIR__ . '/../lib/cart.php';

$db     = getDB();
$method = $_SERVER['REQUEST_METHOD'];

// POST /api/orders.php — Créer une commande
// Body : {
//   first_name, last_name, phone, city,
//   email?, address?, delivery_notes?,
//   cart: [ { product_id, variant_id?, quantity, color? } ]
// }
if ($method !== 'POST') {
    respondError('Méthode non autorisée', 405);
}

$body = getBody();

$required = ['first_name', 'last_name', 'phone', 'city', 'cart'];
foreach ($required as $field) {
    if (empty($body[$field])) {
        respondError("Champ manquant : $field", 400);
    }
}

$firstName     = sanitize($body['first_name']);
$lastName      = sanitize($body['last_name']);
$phone         = sanitize($body['phone']);
$city          = sanitize($body['city']);
$email         = sanitize($body['email'] ?? '');
$address       = sanitize($body['address'] ?? '');
$deliveryNotes = sanitize($body['delivery_notes'] ?? '');
$cart          = $body['cart'];

if (!is_array($cart) || empty($cart)) {
    respondError('Panier vide', 400);
}

// Revalidation complète des prix côté serveur (jamais confiance au client)
$validated = validateCartItems($db, $cart);
$items     = $validated['items'];
$total     = $validated['total'];

if (empty($items) || $total <= 0) {
    respondError('Aucun article valide dans le panier', 400);
}

$deposit      = round($total * DEPOSIT_PERCENT / 100);
$balance      = $total - $deposit;
$customerName = $firstName . ' ' . $lastName;
$orderNumber  = 'RC-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
$deliveryDate = date('Y-m-d', strtotime('+' . DELIVERY_DAYS . ' days'));

$db->beginTransaction();

try {
    $db->prepare("
        INSERT INTO orders (
            order_number, customer_name, customer_email, customer_phone,
            total_amount, deposit_amount, balance_amount,
            order_status, payment_status,
            delivery_address, delivery_city, delivery_notes, delivery_deadline
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', 'awaiting_deposit', ?, ?, ?, ?)
    ")->execute([
        $orderNumber, $customerName, $email, $phone,
        $total, $deposit, $balance,
        $address, $city, $deliveryNotes, $deliveryDate
    ]);

    $orderId = (int) $db->lastInsertId();

    $stmtItem = $db->prepare("
        INSERT INTO order_items (order_id, product_id, variant_id, product_name, size, color, unit_price, quantity, subtotal)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    foreach ($items as $item) {
        $stmtItem->execute([
            $orderId,
            $item['product_id'],
            $item['variant_id'],
            $item['product_name'],
            $item['size'] ?: null,
            $item['color'] ?: null,
            $item['unit_price'],
            $item['quantity'],
            $item['subtotal'],
        ]);
    }

    $db->commit();
} catch (Exception $e) {
    $db->rollBack();
    respondError('Erreur lors de la création de la commande', 500);
}

respond([
    'success'       => true,
    'order_number'  => $orderNumber,
    'items'         => $items,
    'total'         => $total,
    'deposit'       => $deposit,
    'balance'       => $balance,
    'delivery_date' => date('d/m/Y', strtotime($deliveryDate)),
    'customer'      => $customerName,
    'phone'         => $phone,
    'city'          => $city,
]);
