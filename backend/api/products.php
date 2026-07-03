<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/security.php';

$db     = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'list';

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

        // Ajouter l'URL complète de l'image
        foreach ($products as &$p) {
            $p['cover_image_url'] = $p['cover_image']
                ? 'https://riyaas.grosbit.com/assets/images/products/' . $p['cover_image']
                : null;
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
            $img['url'] = 'https://riyaas.grosbit.com/assets/images/products/' . $img['image_path'];
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
    case 'categories':
        $stmt = $db->query("SELECT * FROM categories ORDER BY sort_order ASC");
        respond(['success' => true, 'data' => $stmt->fetchAll()]);
        break;

    // GET /api/products.php?action=featured
    case 'featured':
        $stmt = $db->prepare("
            SELECT p.id, p.name, p.slug, p.price, p.en_promotion, p.prix_promotion,
                   p.stock_quantity, p.seuil_alerte_stock, p.stock_status,
                   c.name AS category_name,
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
            $p['cover_image_url'] = $p['cover_image']
                ? 'https://riyaas.grosbit.com/assets/images/products/' . $p['cover_image']
                : null;
            $p['final_price'] = ($p['en_promotion'] && $p['prix_promotion'])
                ? (float) $p['prix_promotion']
                : (float) $p['price'];
        }

        respond(['success' => true, 'data' => $products]);
        break;

    default:
        respondError('Action invalide', 400);
}