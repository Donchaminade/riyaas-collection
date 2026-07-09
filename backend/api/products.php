<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/security.php';

$db     = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'list';

if ($method !== 'GET') respondError('Méthode non autorisée', 405);

switch ($action) {

    // GET /api/products.php?action=list
    // GET /api/products.php?action=list&categorie=jupes-en-soie
    case 'list':
        $categorySlug = isset($_GET['categorie']) ? sanitize($_GET['categorie']) : null;

        $sql = "
            SELECT p.id, p.name, p.slug, p.price, p.en_promotion, p.prix_promotion,
                   p.stock_quantity, p.seuil_alerte_stock, p.stock_status, p.material,
                   p.description, p.delivery_days, p.is_featured,
                   c.name AS category_name, c.slug AS category_slug,
                   pi.image_path AS cover_image
            FROM products p
            JOIN categories c ON p.category_id = c.id
            LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_cover = 1
            WHERE p.is_active = 1
        ";
        $params = [];

        if ($categorySlug) {
            $sql     .= " AND c.slug = ?";
            $params[] = $categorySlug;
        }

        $sql .= " ORDER BY p.created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll();

        foreach ($products as &$p) {
            $p['cover_image_url'] = productImageUrl($p['cover_image']);
            $p['final_price'] = ($p['en_promotion'] && $p['prix_promotion'])
                ? (float) $p['prix_promotion']
                : (float) $p['price'];
        }

        respond(['success' => true, 'data' => $products]);
        break;

    // GET /api/products.php?action=show&slug=jupe-droite
    case 'show':
        $slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : null;
        if (!$slug) respondError('Slug manquant', 400);

        $stmt = $db->prepare("
            SELECT p.*, c.name AS category_name, c.slug AS category_slug
            FROM products p
            JOIN categories c ON p.category_id = c.id
            WHERE p.slug = ? AND p.is_active = 1
            LIMIT 1
        ");
        $stmt->execute([$slug]);
        $product = $stmt->fetch();

        if (!$product) respondError('Produit introuvable', 404);

        // Images
        $stmt = $db->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_cover DESC, sort_order ASC");
        $stmt->execute([$product['id']]);
        $images = $stmt->fetchAll();

        foreach ($images as &$img) {
            $img['url'] = productImageUrl($img['image_path']);
        }

        // Variantes
        $stmt = $db->prepare("SELECT * FROM product_variants WHERE product_id = ? AND is_active = 1");
        $stmt->execute([$product['id']]);
        $variants = $stmt->fetchAll();

        $product['final_price'] = ($product['en_promotion'] && $product['prix_promotion'])
            ? (float) $product['prix_promotion']
            : (float) $product['price'];

        $product['images']   = $images;
        $product['variants'] = $variants;

        respond(['success' => true, 'data' => $product]);
        break;

    // GET /api/products.php?action=categories
    //   Par défaut : uniquement les catégories ayant au moins un produit actif,
    //   avec l'image de couverture du produit actif le plus récent comme visuel.
    // GET /api/products.php?action=categories&all=1
    //   Toutes les catégories (utilisé par le formulaire produit de l'admin).
    case 'categories':
        $includeEmpty = !empty($_GET['all']);

        $sql = "
            SELECT c.id, c.name, c.slug, c.description, c.sort_order,
                   COUNT(p.id) AS product_count,
                   (SELECT pi.image_path
                    FROM products p2
                    JOIN product_images pi ON pi.product_id = p2.id AND pi.is_cover = 1
                    WHERE p2.category_id = c.id AND p2.is_active = 1
                    ORDER BY p2.created_at DESC, p2.id DESC
                    LIMIT 1) AS cover_image
            FROM categories c
            LEFT JOIN products p ON p.category_id = c.id AND p.is_active = 1
            GROUP BY c.id, c.name, c.slug, c.description, c.sort_order
        ";
        if (!$includeEmpty) {
            $sql .= " HAVING product_count > 0";
        }
        $sql .= " ORDER BY c.sort_order ASC";

        $categories = $db->query($sql)->fetchAll();

        foreach ($categories as &$c) {
            $c['product_count'] = (int) $c['product_count'];
            $c['image_url']     = productImageUrl($c['cover_image']);
            unset($c['cover_image']);
        }

        respond(['success' => true, 'data' => $categories]);
        break;

    // GET /api/products.php?action=featured
    case 'featured':
        $stmt = $db->prepare("
            SELECT p.id, p.name, p.slug, p.price, p.en_promotion, p.prix_promotion,
                   p.stock_quantity, p.seuil_alerte_stock, p.stock_status,
                   c.name AS category_name, c.slug AS category_slug,
                   pi.image_path AS cover_image
            FROM products p
            JOIN categories c ON p.category_id = c.id
            LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_cover = 1
            WHERE p.is_active = 1 AND p.is_featured = 1
            ORDER BY p.created_at DESC
            LIMIT 6
        ");
        $stmt->execute();
        $products = $stmt->fetchAll();

        foreach ($products as &$p) {
            $p['cover_image_url'] = productImageUrl($p['cover_image']);
            $p['final_price'] = ($p['en_promotion'] && $p['prix_promotion'])
                ? (float) $p['prix_promotion']
                : (float) $p['price'];
        }

        respond(['success' => true, 'data' => $products]);
        break;

    default:
        respondError('Action invalide', 400);
}
