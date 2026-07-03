<?php
/**
 * RIYAA'S COLLECTION — AdminController
 */

require_once APP_PATH . '/controllers/Controller.php';
require_once APP_PATH . '/models/Product.php';

class AdminController extends Controller
{
    private string $adminPassword = 'riyaas2024';

    private function requireAdmin(): void
    {
        if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
            $this->redirect('/admin/login');
        }
    }

    public function loginForm(): void
    {
        $this->render('admin/login', ['pageTitle' => 'Admin — Connexion']);
    }

    public function login(): void
    {
        $password = $_POST['password'] ?? '';
        if ($password === $this->adminPassword) {
            $_SESSION['is_admin'] = true;
            $this->redirect('/admin');
        } else {
            $this->render('admin/login', [
                'pageTitle' => 'Admin — Connexion',
                'error'     => 'Mot de passe incorrect.',
            ]);
        }
    }

    public function logout(): void
    {
        unset($_SESSION['is_admin']);
        $this->redirect('/admin/login');
    }

    public function dashboard(): void
    {
        $this->requireAdmin();
        $product    = new Product();
        $products   = $product->getAll();
        $categories = $product->getCategories();

        $this->render('admin/dashboard', [
            'pageTitle'  => 'Admin — Tableau de bord',
            'products'   => $products,
            'categories' => $categories,
        ], 'admin');
    }

    public function addForm(): void
    {
        $this->requireAdmin();
        $product    = new Product();
        $categories = $product->getCategories();

        $this->render('admin/product-form', [
            'pageTitle'  => 'Admin — Ajouter un produit',
            'categories' => $categories,
            'product'    => null,
        ], 'admin');
    }

    public function store(): void
    {
        $this->requireAdmin();

       $name             = trim($_POST['name'] ?? '');
        $categoryId       = (int) ($_POST['category_id'] ?? 0);
        $description      = trim($_POST['description'] ?? '');
        $price            = (float) ($_POST['price'] ?? 0);
        $material         = trim($_POST['material'] ?? 'Soie naturelle 100%');
        $isFeatured       = isset($_POST['is_featured']) ? 1 : 0;
        $enPromotion      = isset($_POST['en_promotion']) ? 1 : 0;
        $prixPromotion    = !empty($_POST['prix_promotion']) ? (float) $_POST['prix_promotion'] : null;
        $stockQuantity    = !empty($_POST['stock_quantity']) ? (int) $_POST['stock_quantity'] : null;
        $seuilAlerte      = !empty($_POST['seuil_alerte_stock']) ? (int) $_POST['seuil_alerte_stock'] : 3;

        if (!$name || !$categoryId || $price <= 0) {
            $this->redirect('/admin/produit/ajouter');
        }

        $slug = $this->generateSlug($name);
        $db   = getDB();

        $stmt = $db->prepare("
            INSERT INTO products (category_id, name, slug, description, material, price, is_featured, stock_status, en_promotion, prix_promotion, stock_quantity, seuil_alerte_stock)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'made_to_order', ?, ?, ?, ?)
        ");
        $stmt->execute([$categoryId, $name, $slug, $description, $material, $price, $isFeatured, $enPromotion, $prixPromotion, $stockQuantity, $seuilAlerte]);
        $productId = (int) $db->lastInsertId();

        if (!empty($_FILES['images']['name'][0])) {
            $this->handleImageUpload($productId, $_FILES['images']);
        }

        if (!empty($_POST['sizes'])) {
            foreach ($_POST['sizes'] as $size) {
                $size = trim($size);
                if ($size) {
                    $db->prepare("INSERT INTO product_variants (product_id, size, extra_price) VALUES (?, ?, 0)")
                       ->execute([$productId, $size]);
                }
            }
        }

        $this->redirect('/admin');
    }

    public function editForm(): void
    {
        $this->requireAdmin();
        $id           = (int) ($_GET['id'] ?? 0);
        $productModel = new Product();
        $product      = $productModel->getById($id);
        $categories   = $productModel->getCategories();
        $images       = $productModel->getImages($id);
        $variants     = $productModel->getVariants($id);

        if (!$product) $this->redirect('/admin');

        $this->render('admin/product-form', [
            'pageTitle'  => 'Admin — Modifier le produit',
            'categories' => $categories,
            'product'    => $product,
            'images'     => $images,
            'variants'   => $variants,
        ], 'admin');
    }

    public function update(): void
    {
        $this->requireAdmin();
        $id               = (int) ($_POST['id'] ?? 0);
        $name             = trim($_POST['name'] ?? '');
        $categoryId       = (int) ($_POST['category_id'] ?? 0);
        $description      = trim($_POST['description'] ?? '');
        $price            = (float) ($_POST['price'] ?? 0);
        $material         = trim($_POST['material'] ?? 'Soie naturelle 100%');
        $isFeatured       = isset($_POST['is_featured']) ? 1 : 0;
        $enPromotion      = isset($_POST['en_promotion']) ? 1 : 0;
        $prixPromotion    = !empty($_POST['prix_promotion']) ? (float) $_POST['prix_promotion'] : null;
        $stockQuantity    = !empty($_POST['stock_quantity']) ? (int) $_POST['stock_quantity'] : null;
        $seuilAlerte      = !empty($_POST['seuil_alerte_stock']) ? (int) $_POST['seuil_alerte_stock'] : 3;

        $db = getDB();
        $db->prepare("
            UPDATE products SET name=?, category_id=?, description=?, price=?, material=?, is_featured=?,
                en_promotion=?, prix_promotion=?, stock_quantity=?, seuil_alerte_stock=?
            WHERE id=?
        ")->execute([$name, $categoryId, $description, $price, $material, $isFeatured, $enPromotion, $prixPromotion, $stockQuantity, $seuilAlerte, $id]);

        if (!empty($_FILES['images']['name'][0])) {
            $this->handleImageUpload($id, $_FILES['images']);
        }

        $this->redirect('/admin');
    }

    public function delete(): void
    {
        $this->requireAdmin();
        $id = (int) ($_POST['id'] ?? 0);
        getDB()->prepare("UPDATE products SET is_active = 0 WHERE id = ?")->execute([$id]);
        $this->redirect('/admin');
    }

    public function deleteImage(): void
    {
        $this->requireAdmin();
        $imageId = (int) ($_POST['image_id'] ?? 0);
        $db      = getDB();
        $stmt    = $db->prepare("SELECT image_path FROM product_images WHERE id = ?");
        $stmt->execute([$imageId]);
        $image = $stmt->fetch();

        if ($image) {
            $filePath = PUBLIC_PATH . '/assets/images/products/' . $image['image_path'];
            if (file_exists($filePath)) unlink($filePath);
            $db->prepare("DELETE FROM product_images WHERE id = ?")->execute([$imageId]);
        }

        $this->json(['success' => true]);
    }

    public function setCover(): void
    {
        $this->requireAdmin();
        $imageId   = (int) ($_POST['image_id'] ?? 0);
        $productId = (int) ($_POST['product_id'] ?? 0);
        $db        = getDB();
        $db->prepare("UPDATE product_images SET is_cover = 0 WHERE product_id = ?")->execute([$productId]);
        $db->prepare("UPDATE product_images SET is_cover = 1 WHERE id = ?")->execute([$imageId]);
        $this->json(['success' => true]);
    }

    public function orders(): void
    {
        $this->requireAdmin();
        $db     = getDB();
        $stmt   = $db->query("SELECT * FROM orders ORDER BY created_at DESC");
        $orders = $stmt->fetchAll();

        $this->render('admin/orders', [
            'pageTitle' => 'Admin — Commandes',
            'orders'    => $orders,
        ], 'admin');
    }

    public function updateOrderStatus(): void
    {
        $this->requireAdmin();
        $orderId = (int) ($_POST['order_id'] ?? 0);
        $status  = $_POST['status'] ?? '';

        $allowed = ['pending','deposit_paid','in_production','ready','delivered','completed','cancelled'];
        if (!in_array($status, $allowed)) $this->redirect('/admin/commandes');

        getDB()->prepare("UPDATE orders SET order_status = ? WHERE id = ?")->execute([$status, $orderId]);
        $this->redirect('/admin/commandes');
    }

    private function handleImageUpload(int $productId, array $files): void
    {
        $uploadDir = PUBLIC_PATH . '/assets/images/products/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $db           = getDB();
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $isFirstImage = true;

        $stmt = $db->prepare("SELECT COUNT(*) FROM product_images WHERE product_id = ? AND is_cover = 1");
        $stmt->execute([$productId]);
        $hasCover = (int) $stmt->fetchColumn() > 0;

        foreach ($files['tmp_name'] as $i => $tmpName) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
            if (!in_array($files['type'][$i], $allowedTypes)) continue;

            $ext      = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
            $filename = 'product_' . $productId . '_' . time() . '_' . $i . '.' . strtolower($ext);
            $dest     = $uploadDir . $filename;

            if (move_uploaded_file($tmpName, $dest)) {
                $isCover = (!$hasCover && $isFirstImage) ? 1 : 0;
                $db->prepare("INSERT INTO product_images (product_id, image_path, is_cover, sort_order) VALUES (?, ?, ?, ?)")
                   ->execute([$productId, $filename, $isCover, $i]);
                $isFirstImage = false;
            }
        }
    }

    private function generateSlug(string $text): string
    {
        $text = strtolower($text);
        $text = str_replace(
            ['a','a','a','e','e','e','e','i','i','o','o','u','u','u','c'],
            ['a','a','a','e','e','e','e','i','i','o','o','u','u','u','c'],
            $text
        );
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', trim($text));
        return $text . '-' . substr(uniqid(), -4);
    }
}
