<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/security.php';

// Toutes les actions de ce fichier exigent un token admin valide
// Header requis : Authorization: Bearer {token}
requireAdmin();

$db     = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($action) {

    // ══════════════════════ PRODUITS ══════════════════════

    // GET /api/admin.php?action=products — tous les produits (actifs et inactifs)
    case 'products':
        if ($method !== 'GET') respondError('Méthode non autorisée', 405);

        $stmt = $db->query("
            SELECT p.*, c.name AS category_name,
                   pi.image_path AS cover_image
            FROM products p
            JOIN categories c ON p.category_id = c.id
            LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_cover = 1
            ORDER BY p.created_at DESC
        ");
        $products = $stmt->fetchAll();

        foreach ($products as &$p) {
            $p['cover_image_url'] = productImageUrl($p['cover_image']);
        }

        respond(['success' => true, 'data' => $products]);
        break;

    // GET /api/admin.php?action=product&id=1 — détail (images + variantes)
    case 'product':
        if ($method !== 'GET') respondError('Méthode non autorisée', 405);

        $id = (int)($_GET['id'] ?? 0);
        if (!$id) respondError('ID manquant', 400);

        $stmt = $db->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $product = $stmt->fetch();

        if (!$product) respondError('Produit introuvable', 404);

        $stmt = $db->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_cover DESC, sort_order ASC");
        $stmt->execute([$id]);
        $images = $stmt->fetchAll();
        foreach ($images as &$img) {
            $img['url'] = productImageUrl($img['image_path']);
        }

        $stmt = $db->prepare("SELECT * FROM product_variants WHERE product_id = ?");
        $stmt->execute([$id]);

        $product['images']   = $images;
        $product['variants'] = $stmt->fetchAll();

        respond(['success' => true, 'data' => $product]);
        break;

    // POST /api/admin.php?action=create-product
    // FormData : name, category_id (ou new_category), price, description?, material?,
    //            is_featured?, en_promotion?, prix_promotion?,
    //            stock_status?, stock_quantity?, seuil_alerte_stock?,
    //            sizes? (JSON array), images[] (fichiers)
    case 'create-product': {
        if ($method !== 'POST') respondError('Méthode non autorisée', 405);

        $name       = trim($_POST['name'] ?? '');
        $categoryId = resolveCategoryId($db);
        $price      = (float)($_POST['price'] ?? 0);

        if (!$name || !$categoryId || $price <= 0) {
            respondError('Nom, catégorie et prix sont obligatoires', 400);
        }

        $stockStatus = $_POST['stock_status'] ?? 'made_to_order';
        if (!in_array($stockStatus, ['available', 'made_to_order', 'out_of_stock'], true)) {
            $stockStatus = 'made_to_order';
        }

        $db->prepare("
            INSERT INTO products (category_id, name, slug, description, material, price,
                                  is_featured, stock_status, en_promotion, prix_promotion,
                                  stock_quantity, seuil_alerte_stock, delivery_days)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ")->execute([
            $categoryId,
            $name,
            generateSlug($name),
            trim($_POST['description'] ?? ''),
            trim($_POST['material'] ?? '') ?: 'Soie naturelle 100%',
            $price,
            !empty($_POST['is_featured']) ? 1 : 0,
            $stockStatus,
            !empty($_POST['en_promotion']) ? 1 : 0,
            !empty($_POST['prix_promotion']) ? (float)$_POST['prix_promotion'] : null,
            isset($_POST['stock_quantity']) && $_POST['stock_quantity'] !== '' ? (int)$_POST['stock_quantity'] : null,
            !empty($_POST['seuil_alerte_stock']) ? (int)$_POST['seuil_alerte_stock'] : 3,
            !empty($_POST['delivery_days']) ? (int)$_POST['delivery_days'] : DELIVERY_DAYS,
        ]);

        $productId = (int)$db->lastInsertId();

        saveVariants($db, $productId, $_POST['sizes'] ?? '');

        if (!empty($_FILES['images']['name'][0])) {
            handleImageUpload($db, $productId, $_FILES['images']);
        }

        respond(['success' => true, 'id' => $productId], 201);
        break;
    }

    // POST /api/admin.php?action=update-product
    // FormData : id + mêmes champs que create-product
    case 'update-product': {
        if ($method !== 'POST') respondError('Méthode non autorisée', 405);

        $id = (int)($_POST['id'] ?? 0);
        if (!$id) respondError('ID manquant', 400);

        $name       = trim($_POST['name'] ?? '');
        $categoryId = resolveCategoryId($db);
        $price      = (float)($_POST['price'] ?? 0);

        if (!$name || !$categoryId || $price <= 0) {
            respondError('Nom, catégorie et prix sont obligatoires', 400);
        }

        $stockStatus = $_POST['stock_status'] ?? 'made_to_order';
        if (!in_array($stockStatus, ['available', 'made_to_order', 'out_of_stock'], true)) {
            $stockStatus = 'made_to_order';
        }

        $db->prepare("
            UPDATE products SET
                name = ?, category_id = ?, description = ?, price = ?, material = ?,
                is_featured = ?, stock_status = ?, en_promotion = ?, prix_promotion = ?,
                stock_quantity = ?, seuil_alerte_stock = ?, delivery_days = ?, is_active = ?
            WHERE id = ?
        ")->execute([
            $name,
            $categoryId,
            trim($_POST['description'] ?? ''),
            $price,
            trim($_POST['material'] ?? '') ?: 'Soie naturelle 100%',
            !empty($_POST['is_featured']) ? 1 : 0,
            $stockStatus,
            !empty($_POST['en_promotion']) ? 1 : 0,
            !empty($_POST['prix_promotion']) ? (float)$_POST['prix_promotion'] : null,
            isset($_POST['stock_quantity']) && $_POST['stock_quantity'] !== '' ? (int)$_POST['stock_quantity'] : null,
            !empty($_POST['seuil_alerte_stock']) ? (int)$_POST['seuil_alerte_stock'] : 3,
            !empty($_POST['delivery_days']) ? (int)$_POST['delivery_days'] : DELIVERY_DAYS,
            isset($_POST['is_active']) ? (int)!empty($_POST['is_active']) : 1,
            $id,
        ]);

        if (isset($_POST['sizes'])) {
            $db->prepare("DELETE FROM product_variants WHERE product_id = ?")->execute([$id]);
            saveVariants($db, $id, $_POST['sizes']);
        }

        if (!empty($_FILES['images']['name'][0])) {
            handleImageUpload($db, $id, $_FILES['images']);
        }

        respond(['success' => true]);
        break;
    }

    // POST /api/admin.php?action=delete-product — Body : { "id": 1 }
    // Suppression DÉFINITIVE : produit + images (lignes et fichiers) + variantes.
    // Les order_items conservent l'historique (product_name/prix copiés,
    // product_id passe à NULL via la FK ON DELETE SET NULL).
    // Pour retirer temporairement un produit de la boutique : toggle-product.
    case 'delete-product': {
        if ($method !== 'POST') respondError('Méthode non autorisée', 405);

        $id = (int)(getBody()['id'] ?? 0);
        if (!$id) respondError('ID manquant', 400);

        $stmt = $db->prepare("SELECT id FROM products WHERE id = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) respondError('Produit introuvable', 404);

        // Chemins des fichiers images à supprimer après le commit
        $stmt = $db->prepare("SELECT image_path FROM product_images WHERE product_id = ?");
        $stmt->execute([$id]);
        $imagePaths = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $db->beginTransaction();
        try {
            // Images et variantes partent en cascade (FK ON DELETE CASCADE)
            $db->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            error_log('delete-product #' . $id . ' : ' . $e->getMessage());
            respondError('Suppression impossible : le produit est lié à d\'autres données', 409);
        }

        // Fichiers physiques supprimés seulement une fois le DELETE validé
        foreach ($imagePaths as $path) {
            $file = uploadDir() . basename($path);
            if (is_file($file)) unlink($file);
        }

        respond(['success' => true]);
        break;
    }

    // POST /api/admin.php?action=toggle-product — Body : { "id": 1, "is_active": 1 }
    // Active/désactive la visibilité boutique sans passer par update-product
    case 'toggle-product': {
        if ($method !== 'POST') respondError('Méthode non autorisée', 405);

        $body = getBody();
        $id   = (int)($body['id'] ?? 0);
        if (!$id) respondError('ID manquant', 400);
        if (!isset($body['is_active'])) respondError('Paramètre is_active manquant', 400);

        $isActive = !empty($body['is_active']) ? 1 : 0;

        $stmt = $db->prepare("UPDATE products SET is_active = ? WHERE id = ?");
        $stmt->execute([$isActive, $id]);
        if ($stmt->rowCount() === 0) {
            $check = $db->prepare("SELECT id FROM products WHERE id = ?");
            $check->execute([$id]);
            if (!$check->fetch()) respondError('Produit introuvable', 404);
        }

        respond(['success' => true, 'is_active' => $isActive]);
        break;
    }

    // ══════════════════════ IMAGES ══════════════════════

    // POST /api/admin.php?action=delete-image — Body : { "image_id": 1 }
    case 'delete-image': {
        if ($method !== 'POST') respondError('Méthode non autorisée', 405);

        $imageId = (int)(getBody()['image_id'] ?? 0);
        if (!$imageId) respondError('ID image manquant', 400);

        $stmt = $db->prepare("SELECT image_path FROM product_images WHERE id = ?");
        $stmt->execute([$imageId]);
        $image = $stmt->fetch();

        if (!$image) respondError('Image introuvable', 404);

        $filePath = uploadDir() . $image['image_path'];
        if (file_exists($filePath)) unlink($filePath);

        $db->prepare("DELETE FROM product_images WHERE id = ?")->execute([$imageId]);
        respond(['success' => true]);
        break;
    }

    // POST /api/admin.php?action=set-cover — Body : { "image_id": 1, "product_id": 1 }
    case 'set-cover': {
        if ($method !== 'POST') respondError('Méthode non autorisée', 405);

        $body      = getBody();
        $imageId   = (int)($body['image_id'] ?? 0);
        $productId = (int)($body['product_id'] ?? 0);

        if (!$imageId || !$productId) respondError('Paramètres manquants', 400);

        $db->prepare("UPDATE product_images SET is_cover = 0 WHERE product_id = ?")->execute([$productId]);
        $db->prepare("UPDATE product_images SET is_cover = 1 WHERE id = ?")->execute([$imageId]);
        respond(['success' => true]);
        break;
    }

    // POST /api/admin.php?action=set-image-color — Body : { "image_id": 1, "color": "Rouge bordeaux" }
    // color vide = retirer le tag couleur de l'image
    case 'set-image-color': {
        if ($method !== 'POST') respondError('Méthode non autorisée', 405);

        $body    = getBody();
        $imageId = (int)($body['image_id'] ?? 0);
        $color   = sanitize((string)($body['color'] ?? ''));

        if (!$imageId) respondError('ID image manquant', 400);
        if (mb_strlen($color) > 80) respondError('Couleur trop longue (80 caractères max)', 400);

        $stmt = $db->prepare("SELECT id FROM product_images WHERE id = ?");
        $stmt->execute([$imageId]);
        if (!$stmt->fetch()) respondError('Image introuvable', 404);

        $db->prepare("UPDATE product_images SET color = ? WHERE id = ?")
           ->execute([$color !== '' ? $color : null, $imageId]);
        respond(['success' => true]);
        break;
    }

    // ══════════════════════ COMMANDES ══════════════════════

    // GET /api/admin.php?action=orders — toutes les commandes + articles
    case 'orders':
        if ($method !== 'GET') respondError('Méthode non autorisée', 405);

        $orders = $db->query("SELECT * FROM orders ORDER BY created_at DESC")->fetchAll();

        if ($orders) {
            $ids          = array_column($orders, 'id');
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $db->prepare("SELECT * FROM order_items WHERE order_id IN ($placeholders)");
            $stmt->execute($ids);

            $itemsByOrder = [];
            foreach ($stmt->fetchAll() as $item) {
                $itemsByOrder[$item['order_id']][] = $item;
            }
            foreach ($orders as &$o) {
                $o['items'] = $itemsByOrder[$o['id']] ?? [];
            }
        }

        respond(['success' => true, 'data' => $orders]);
        break;

    // POST /api/admin.php?action=update-order-status
    // Body : { "order_id": 1, "status": "in_production" }
    case 'update-order-status': {
        if ($method !== 'POST') respondError('Méthode non autorisée', 405);

        $body    = getBody();
        $orderId = (int)($body['order_id'] ?? 0);
        $status  = $body['status'] ?? '';

        $allowed = ['pending', 'deposit_paid', 'in_production', 'ready', 'delivered', 'completed', 'cancelled'];
        if (!$orderId || !in_array($status, $allowed, true)) {
            respondError('Paramètres invalides', 400);
        }

        $db->prepare("UPDATE orders SET order_status = ? WHERE id = ?")->execute([$status, $orderId]);
        respond(['success' => true]);
        break;
    }

    // POST /api/admin.php?action=update-payment-status
    // Body : { "order_id": 1, "status": "fully_paid" }
    case 'update-payment-status': {
        if ($method !== 'POST') respondError('Méthode non autorisée', 405);

        $body    = getBody();
        $orderId = (int)($body['order_id'] ?? 0);
        $status  = $body['status'] ?? '';

        $allowed = ['awaiting_deposit', 'deposit_paid', 'balance_paid', 'fully_paid', 'refunded'];
        if (!$orderId || !in_array($status, $allowed, true)) {
            respondError('Paramètres invalides', 400);
        }

        $stmt = $db->prepare("UPDATE orders SET payment_status = ? WHERE id = ?");
        $stmt->execute([$status, $orderId]);
        if ($stmt->rowCount() === 0) {
            $check = $db->prepare("SELECT id FROM orders WHERE id = ?");
            $check->execute([$orderId]);
            if (!$check->fetch()) respondError('Commande introuvable', 404);
        }

        respond(['success' => true]);
        break;
    }

    // POST /api/admin.php?action=delete-order — Body : { "id": 1 }
    // Suppression définitive ; les order_items partent en cascade (FK ON DELETE CASCADE)
    case 'delete-order': {
        if ($method !== 'POST') respondError('Méthode non autorisée', 405);

        $id = (int)(getBody()['id'] ?? 0);
        if (!$id) respondError('ID manquant', 400);

        $stmt = $db->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        if ($stmt->rowCount() === 0) respondError('Commande introuvable', 404);

        respond(['success' => true]);
        break;
    }

    // ══════════════════════ TABLEAU DE BORD ══════════════════════

    // GET /api/admin.php?action=stats
    case 'stats':
        if ($method !== 'GET') respondError('Méthode non autorisée', 405);

        respond([
            'success' => true,
            'data'    => [
                'products_active' => (int)$db->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn(),
                'orders_total'    => (int)$db->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
                'orders_pending'  => (int)$db->query("SELECT COUNT(*) FROM orders WHERE order_status IN ('pending','deposit_paid','in_production','ready')")->fetchColumn(),
                'revenue_total'   => (float)$db->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE order_status != 'cancelled'")->fetchColumn(),
            ],
        ]);
        break;

    // GET /api/admin.php?action=dashboard-stats
    // KPI complets, séries temporelles, tops et dernières activités
    // pour la page Statistiques. Les commandes annulées sont exclues du CA.
    case 'dashboard-stats': {
        if ($method !== 'GET') respondError('Méthode non autorisée', 405);

        // ── KPI globaux ──────────────────────────────────────
        $revenueTotal = (float)$db->query("
            SELECT COALESCE(SUM(total_amount), 0) FROM orders
            WHERE order_status != 'cancelled'
        ")->fetchColumn();

        // CA + nombre de commandes du mois en cours et du mois précédent
        $currentMonth = $db->query("
            SELECT COALESCE(SUM(total_amount), 0) AS revenue, COUNT(*) AS orders
            FROM orders
            WHERE order_status != 'cancelled'
              AND created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
        ")->fetch();

        $previousMonth = $db->query("
            SELECT COALESCE(SUM(total_amount), 0) AS revenue, COUNT(*) AS orders
            FROM orders
            WHERE order_status != 'cancelled'
              AND created_at >= DATE_FORMAT(CURDATE() - INTERVAL 1 MONTH, '%Y-%m-01')
              AND created_at <  DATE_FORMAT(CURDATE(), '%Y-%m-01')
        ")->fetch();

        // Variation en % vs mois précédent (null si pas de référence)
        $percentChange = function (float $current, float $previous): ?float {
            if ($previous == 0.0) return null;
            return round(($current - $previous) / $previous * 100, 1);
        };

        $ordersValidCount = (int)$db->query("
            SELECT COUNT(*) FROM orders WHERE order_status != 'cancelled'
        ")->fetchColumn();
        $averageOrder = $ordersValidCount > 0 ? round($revenueTotal / $ordersValidCount) : 0.0;

        // Acomptes encaissés (acompte payé au minimum) vs soldes en attente
        // (acompte payé mais solde pas encore réglé), hors annulées
        $depositsCollected = (float)$db->query("
            SELECT COALESCE(SUM(deposit_amount), 0) FROM orders
            WHERE order_status != 'cancelled'
              AND payment_status IN ('deposit_paid', 'balance_paid', 'fully_paid')
        ")->fetchColumn();

        $balancePending = (float)$db->query("
            SELECT COALESCE(SUM(balance_amount), 0) FROM orders
            WHERE order_status != 'cancelled'
              AND payment_status = 'deposit_paid'
        ")->fetchColumn();

        // ── Répartition par statut (les 7 statuts, même à zéro) ──
        $ordersByStatus = array_fill_keys(
            ['pending', 'deposit_paid', 'in_production', 'ready', 'delivered', 'completed', 'cancelled'], 0
        );
        foreach ($db->query("SELECT order_status, COUNT(*) AS n FROM orders GROUP BY order_status") as $row) {
            $ordersByStatus[$row['order_status']] = (int)$row['n'];
        }

        $requestsByStatus = array_fill_keys(
            ['new', 'in_review', 'quoted', 'accepted', 'rejected', 'completed'], 0
        );
        foreach ($db->query("SELECT status, COUNT(*) AS n FROM custom_requests GROUP BY status") as $row) {
            $requestsByStatus[$row['status']] = (int)$row['n'];
        }

        // ── Produits ─────────────────────────────────────────
        $productsActive = (int)$db->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn();

        // Rupture déclarée ou quantité sous le seuil d'alerte
        $productsLowStock = (int)$db->query("
            SELECT COUNT(*) FROM products
            WHERE is_active = 1
              AND (stock_status = 'out_of_stock'
                   OR (stock_quantity IS NOT NULL AND stock_quantity <= seuil_alerte_stock))
        ")->fetchColumn();

        // ── Séries temporelles ───────────────────────────────
        // CA + commandes par jour sur 30 jours (trous remplis à 0)
        $dailyRaw = [];
        foreach ($db->query("
            SELECT DATE(created_at) AS day,
                   COALESCE(SUM(total_amount), 0) AS revenue,
                   COUNT(*) AS orders
            FROM orders
            WHERE order_status != 'cancelled'
              AND created_at >= CURDATE() - INTERVAL 29 DAY
            GROUP BY DATE(created_at)
        ") as $row) {
            $dailyRaw[$row['day']] = $row;
        }

        $dailyRevenue = [];
        for ($i = 29; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime("-$i days"));
            $dailyRevenue[] = [
                'date'    => $day,
                'revenue' => isset($dailyRaw[$day]) ? (float)$dailyRaw[$day]['revenue'] : 0.0,
                'orders'  => isset($dailyRaw[$day]) ? (int)$dailyRaw[$day]['orders'] : 0,
            ];
        }

        // CA par mois sur 12 mois (trous remplis à 0)
        $monthlyRaw = [];
        foreach ($db->query("
            SELECT DATE_FORMAT(created_at, '%Y-%m') AS month,
                   COALESCE(SUM(total_amount), 0) AS revenue,
                   COUNT(*) AS orders
            FROM orders
            WHERE order_status != 'cancelled'
              AND created_at >= DATE_FORMAT(CURDATE() - INTERVAL 11 MONTH, '%Y-%m-01')
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ") as $row) {
            $monthlyRaw[$row['month']] = $row;
        }

        $monthlyRevenue = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = date('Y-m', strtotime(date('Y-m-01') . " -$i months"));
            $monthlyRevenue[] = [
                'month'   => $month,
                'revenue' => isset($monthlyRaw[$month]) ? (float)$monthlyRaw[$month]['revenue'] : 0.0,
                'orders'  => isset($monthlyRaw[$month]) ? (int)$monthlyRaw[$month]['orders'] : 0,
            ];
        }

        // ── Tops ─────────────────────────────────────────────
        $topProducts = $db->query("
            SELECT oi.product_name,
                   SUM(oi.quantity) AS quantity,
                   SUM(oi.subtotal) AS revenue
            FROM order_items oi
            JOIN orders o ON o.id = oi.order_id AND o.order_status != 'cancelled'
            GROUP BY oi.product_name
            ORDER BY quantity DESC, revenue DESC
            LIMIT 5
        ")->fetchAll();
        foreach ($topProducts as &$tp) {
            $tp['quantity'] = (int)$tp['quantity'];
            $tp['revenue']  = (float)$tp['revenue'];
        }
        unset($tp);

        $salesByCategory = $db->query("
            SELECT c.name AS category,
                   SUM(oi.quantity) AS quantity,
                   SUM(oi.subtotal) AS revenue
            FROM order_items oi
            JOIN orders o     ON o.id = oi.order_id AND o.order_status != 'cancelled'
            JOIN products p   ON p.id = oi.product_id
            JOIN categories c ON c.id = p.category_id
            GROUP BY c.id, c.name
            ORDER BY revenue DESC
        ")->fetchAll();
        foreach ($salesByCategory as &$sc) {
            $sc['quantity'] = (int)$sc['quantity'];
            $sc['revenue']  = (float)$sc['revenue'];
        }
        unset($sc);

        $topCities = $db->query("
            SELECT delivery_city AS city,
                   COUNT(*) AS orders,
                   COALESCE(SUM(total_amount), 0) AS revenue
            FROM orders
            WHERE order_status != 'cancelled'
              AND delivery_city IS NOT NULL AND delivery_city != ''
            GROUP BY delivery_city
            ORDER BY orders DESC, revenue DESC
            LIMIT 5
        ")->fetchAll();
        foreach ($topCities as &$tc) {
            $tc['orders']  = (int)$tc['orders'];
            $tc['revenue'] = (float)$tc['revenue'];
        }
        unset($tc);

        // ── Dernières activités ──────────────────────────────
        $recentOrders = $db->query("
            SELECT order_number, customer_name, total_amount, order_status, created_at
            FROM orders
            ORDER BY created_at DESC
            LIMIT 8
        ")->fetchAll();

        $recentRequests = $db->query("
            SELECT request_number, customer_name, budget, status, created_at
            FROM custom_requests
            ORDER BY created_at DESC
            LIMIT 5
        ")->fetchAll();

        respond([
            'success' => true,
            'data'    => [
                'kpi' => [
                    'revenue_total'        => $revenueTotal,
                    'revenue_month'        => (float)$currentMonth['revenue'],
                    'revenue_month_prev'   => (float)$previousMonth['revenue'],
                    'revenue_month_change' => $percentChange((float)$currentMonth['revenue'], (float)$previousMonth['revenue']),
                    'orders_month'         => (int)$currentMonth['orders'],
                    'orders_month_prev'    => (int)$previousMonth['orders'],
                    'orders_month_change'  => $percentChange((float)$currentMonth['orders'], (float)$previousMonth['orders']),
                    'average_order'        => $averageOrder,
                    'deposits_collected'   => $depositsCollected,
                    'balance_pending'      => $balancePending,
                    'products_active'      => $productsActive,
                    'products_low_stock'   => $productsLowStock,
                ],
                'orders_by_status'   => $ordersByStatus,
                'requests_by_status' => $requestsByStatus,
                'daily_revenue'      => $dailyRevenue,
                'monthly_revenue'    => $monthlyRevenue,
                'top_products'       => $topProducts,
                'sales_by_category'  => $salesByCategory,
                'top_cities'         => $topCities,
                'recent_orders'      => $recentOrders,
                'recent_requests'    => $recentRequests,
            ],
        ]);
        break;
    }

    default:
        respondError('Action invalide', 400);
}

// ══════════════════════ HELPERS ══════════════════════

function uploadDir(): string {
    $dir = __DIR__ . '/../uploads/products/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    return $dir;
}

function handleImageUpload(PDO $db, int $productId, array $files): void {
    $uploadDir    = uploadDir();
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    $finfo        = new finfo(FILEINFO_MIME_TYPE);

    $stmt = $db->prepare("SELECT COUNT(*) FROM product_images WHERE product_id = ? AND is_cover = 1");
    $stmt->execute([$productId]);
    $hasCover = (int)$stmt->fetchColumn() > 0;

    foreach ($files['tmp_name'] as $i => $tmpName) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
        if (!in_array($finfo->file($tmpName), $allowedTypes, true)) continue;

        $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) continue;

        $filename = 'product_' . $productId . '_' . time() . '_' . $i . '.' . $ext;

        if (move_uploaded_file($tmpName, $uploadDir . $filename)) {
            $isCover = !$hasCover ? 1 : 0;
            $db->prepare("INSERT INTO product_images (product_id, image_path, is_cover, sort_order) VALUES (?, ?, ?, ?)")
               ->execute([$productId, $filename, $isCover, $i]);
            $hasCover = true;
        }
    }
}

// $sizes : JSON '["S","M","L"]' ou chaîne 'S,M,L'
function saveVariants(PDO $db, int $productId, string $sizes): void {
    $decoded = json_decode($sizes, true);
    $list    = is_array($decoded) ? $decoded : explode(',', $sizes);

    $stmt = $db->prepare("INSERT INTO product_variants (product_id, size, extra_price) VALUES (?, ?, 0)");
    foreach ($list as $size) {
        $size = trim((string)$size);
        if ($size !== '') $stmt->execute([$productId, $size]);
    }
}

function generateSlug(string $text): string {
    $text = mb_strtolower($text, 'UTF-8');
    $text = strtr($text, [
        'à' => 'a', 'â' => 'a', 'ä' => 'a', 'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'î' => 'i', 'ï' => 'i', 'ô' => 'o', 'ö' => 'o', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
        'ç' => 'c', 'œ' => 'oe', 'æ' => 'ae', "'" => '-',
    ]);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', trim($text));
    return trim($text, '-') . '-' . substr(uniqid(), -4);
}

// Détermine la catégorie d'un produit depuis $_POST :
// - new_category non vide → réutilise la catégorie du même nom (insensible à la casse)
//   ou la crée avec un slug propre (suffixe numérique uniquement en cas de collision)
// - sinon → category_id tel quel. Retourne 0 si rien d'exploitable.
function resolveCategoryId(PDO $db): int {
    $newCategory = trim($_POST['new_category'] ?? '');

    if ($newCategory === '') {
        return (int)($_POST['category_id'] ?? 0);
    }

    if (mb_strlen($newCategory) > 100) {
        respondError('Nom de catégorie trop long (100 caractères max)', 400);
    }

    $stmt = $db->prepare("SELECT id FROM categories WHERE LOWER(name) = LOWER(?) LIMIT 1");
    $stmt->execute([$newCategory]);
    $existing = $stmt->fetch();
    if ($existing) {
        return (int)$existing['id'];
    }

    // Slug propre, sans suffixe aléatoire (contrairement à generateSlug)
    $base = preg_replace('/-[a-z0-9]{4}$/', '', generateSlug($newCategory));
    if ($base === '') $base = 'categorie';

    $slug = $base;
    $stmt = $db->prepare("SELECT COUNT(*) FROM categories WHERE slug = ?");
    for ($i = 2; ; $i++) {
        $stmt->execute([$slug]);
        if ((int)$stmt->fetchColumn() === 0) break;
        $slug = $base . '-' . $i;
    }

    $maxSort = (int)$db->query("SELECT COALESCE(MAX(sort_order), 0) FROM categories")->fetchColumn();
    $db->prepare("INSERT INTO categories (name, slug, sort_order) VALUES (?, ?, ?)")
       ->execute([$newCategory, $slug, $maxSort + 1]);

    return (int)$db->lastInsertId();
}
