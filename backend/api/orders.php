<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/security.php';

$db     = getDB();
$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    respondError('Méthode non autorisée', 405);
}

$body = getBody();

// Validation des champs obligatoires
$required = ['first_name', 'last_name', 'phone', 'city', 'cart'];
foreach ($required as $field) {
    if (empty($body[$field])) {
        respondError("Champ manquant : $field", 400);
    }
}

$firstName    = sanitize($body['first_name']);
$lastName     = sanitize($body['last_name']);
$phone        = sanitize($body['phone']);
$city         = sanitize($body['city']);
$address      = sanitize($body['address'] ?? '');
$cart         = $body['cart'];

if (!is_array($cart) || empty($cart)) {
    respondError('Panier vide', 400);
}

// Calculer le total
$total = 0;
foreach ($cart as $item) {
    $total += (float)($item['unit_price'] ?? 0) * (int)($item['quantity'] ?? 1);
}

$deposit      = round($total * 0.5);
$balance      = round($total * 0.5);
$customerName = $firstName . ' ' . $lastName;
$orderNumber  = 'RC-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
$deliveryDate = date('Y-m-d', strtotime('+5 days'));

// Créer la commande
$db->prepare("
    INSERT INTO orders (
        order_number, customer_name, customer_email, customer_phone,
        total_amount, deposit_amount, balance_amount,
        order_status, payment_status,
        delivery_address, delivery_city, delivery_deadline
    ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', 'awaiting_deposit', ?, ?, ?)
")->execute([
    $orderNumber, $customerName, '', $phone,
    $total, $deposit, $balance,
    $address, $city, $deliveryDate
]);

$orderId = (int) $db->lastInsertId();

// Enregistrer les articles
foreach ($cart as $item) {
    $productName = sanitize($item['product_name'] ?? '');
    $unitPrice   = (float)($item['unit_price'] ?? 0);
    $quantity    = (int)($item['quantity'] ?? 1);
    $subtotal    = $unitPrice * $quantity;

    $db->prepare("
        INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity, subtotal)
        VALUES (?, ?, ?, ?, ?, ?)
    ")->execute([$orderId, $item['product_id'] ?? 0, $productName, $unitPrice, $quantity, $subtotal]);
}

respond([
    'success'      => true,
    'order_number' => $orderNumber,
    'total'        => $total,
    'deposit'      => $deposit,
    'balance'      => $balance,
    'delivery_date'=> date('d/m/Y', strtotime($deliveryDate)),
    'customer'     => $customerName,
    'phone'        => $phone,
    'city'         => $city,
]);