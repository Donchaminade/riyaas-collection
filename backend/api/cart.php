<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/security.php';

$db     = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($action) {

    // POST /api/cart.php?action=add
    case 'add':
        if ($method !== 'POST') respondError('Méthode non autorisée', 405);

        $body      = getBody();
        $productId = (int)($body['product_id'] ?? 0);
        $variantId = (int)($body['variant_id'] ?? 0);
        $quantity  = max(1, (int)($body['quantity'] ?? 1));

        if (!$productId) respondError('Produit invalide', 400);

        // Récupérer le produit
        $stmt = $db->prepare("
            SELECT p.id, p.name, p.slug, p.price, p.en_promotion, p.prix_promotion,
                   pi.image_path AS cover_image
            FROM products p
            LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_cover = 1
            WHERE p.id = ? AND p.is_active = 1
            LIMIT 1
        ");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();

        if (!$product) respondError('Produit introuvable', 404);

        // Prix final (promo ou normal)
        $unitPrice = ($product['en_promotion'] && $product['prix_promotion'])
            ? (float) $product['prix_promotion']
            : (float) $product['price'];

        // Récupérer la variante si besoin
        $size  = '';
        $color = '';
        if ($variantId) {
            $stmt = $db->prepare("SELECT * FROM product_variants WHERE id = ? LIMIT 1");
            $stmt->execute([$variantId]);
            $variant = $stmt->fetch();
            if ($variant) {
                $size       = $variant['size'] ?? '';
                $color      = $variant['color'] ?? '';
                $unitPrice += (float)($variant['extra_price'] ?? 0);
            }
        }

        respond([
            'success'  => true,
            'item'     => [
                'product_id'   => $product['id'],
                'variant_id'   => $variantId ?: null,
                'product_name' => $product['name'],
                'slug'         => $product['slug'],
                'size'         => $size,
                'color'        => $color,
                'unit_price'   => $unitPrice,
                'quantity'     => $quantity,
                'image'        => $product['cover_image']
                    ? 'https://riyaas.grosbit.com/assets/images/products/' . $product['cover_image']
                    : null,
            ]
        ]);
        break;

    // POST /api/cart.php?action=validate
    // Valider les prix du panier côté serveur avant commande
    case 'validate':
        if ($method !== 'POST') respondError('Méthode non autorisée', 405);

        $body = getBody();
        $cart = $body['cart'] ?? [];

        if (empty($cart)) respondError('Panier vide', 400);

        $total         = 0;
        $validatedCart = [];

        foreach ($cart as $item) {
            $productId = (int)($item['product_id'] ?? 0);
            if (!$productId) continue;

            $stmt = $db->prepare("
                SELECT id, name, price, en_promotion, prix_promotion
                FROM products WHERE id = ? AND is_active = 1 LIMIT 1
            ");
            $stmt->execute([$productId]);
            $product = $stmt->fetch();

            if (!$product) continue;

            $unitPrice = ($product['en_promotion'] && $product['prix_promotion'])
                ? (float) $product['prix_promotion']
                : (float) $product['price'];

            $quantity  = max(1, (int)($item['quantity'] ?? 1));
            $subtotal  = $unitPrice * $quantity;
            $total    += $subtotal;

            $validatedCart[] = [
                'product_id'   => $product['id'],
                'product_name' => $product['name'],
                'unit_price'   => $unitPrice,
                'quantity'     => $quantity,
                'subtotal'     => $subtotal,
            ];
        }

        respond([
            'success' => true,
            'cart'    => $validatedCart,
            'total'   => $total,
            'deposit' => round($total * 0.5),
            'balance' => round($total * 0.5),
        ]);
        break;

    default:
        respondError('Action invalide', 400);
}