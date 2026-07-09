<?php
/**
 * Validation serveur d'un panier : recalcule les prix depuis la base
 * (promo + supplément variante) sans jamais faire confiance au client.
 *
 * Entrée : [{ product_id, variant_id?, quantity, color? }]
 *          color? : couleur choisie sur la fiche produit (images taguées),
 *          utilisée seulement si la variante n'a pas de couleur.
 * Sortie : ['items' => [...], 'total' => float]
 */
function validateCartItems(PDO $db, array $cart): array {
    $items = [];
    $total = 0.0;

    foreach ($cart as $raw) {
        $productId  = (int)($raw['product_id'] ?? 0);
        $variantId  = (int)($raw['variant_id'] ?? 0);
        $quantity   = max(1, (int)($raw['quantity'] ?? 1));
        $itemColor  = mb_substr(sanitize((string)($raw['color'] ?? '')), 0, 80);

        if (!$productId) continue;

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

        if (!$product) continue;

        $unitPrice = ($product['en_promotion'] && $product['prix_promotion'])
            ? (float) $product['prix_promotion']
            : (float) $product['price'];

        $size  = '';
        $color = '';
        if ($variantId) {
            $stmt = $db->prepare("
                SELECT * FROM product_variants
                WHERE id = ? AND product_id = ? AND is_active = 1
                LIMIT 1
            ");
            $stmt->execute([$variantId, $productId]);
            $variant = $stmt->fetch();

            if ($variant) {
                $size       = $variant['size']  ?? '';
                $color      = $variant['color'] ?? '';
                $unitPrice += (float)($variant['extra_price'] ?? 0);
            } else {
                $variantId = 0;
            }
        }

        // La couleur de la variante prime ; sinon on garde celle choisie côté client
        if ($color === '' && $itemColor !== '') {
            $color = $itemColor;
        }

        $subtotal = $unitPrice * $quantity;
        $total   += $subtotal;

        $items[] = [
            'product_id'   => (int) $product['id'],
            'variant_id'   => $variantId ?: null,
            'product_name' => $product['name'],
            'slug'         => $product['slug'],
            'size'         => $size,
            'color'        => $color,
            'unit_price'   => $unitPrice,
            'quantity'     => $quantity,
            'subtotal'     => $subtotal,
            'image'        => productImageUrl($product['cover_image']),
        ];
    }

    return ['items' => $items, 'total' => $total];
}
