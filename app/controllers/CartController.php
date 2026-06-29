<?php
/**
 * RIYAA'S COLLECTION — CartController
 * Le panier est stocké en SESSION (simple et rapide).
 */

require_once APP_PATH . '/controllers/Controller.php';
require_once APP_PATH . '/models/Product.php';

class CartController extends Controller
{
    public function index(): void
    {
        $cart  = $_SESSION['cart'] ?? [];
        $total = $this->calculateTotal($cart);

        $this->render('cart/index', [
            'pageTitle' => "Mon panier — Riyaa's Collection",
            'cart'      => $cart,
            'total'     => $total,
            'deposit'   => $total * DEPOSIT_PERCENT / 100,
            'balance'   => $total * (100 - DEPOSIT_PERCENT) / 100,
        ]);
    }

    public function add(): void
    {
        $productId = (int) ($_POST['product_id'] ?? 0);
        $variantId = (int) ($_POST['variant_id'] ?? 0);
        $quantity  = max(1, (int) ($_POST['quantity'] ?? 1));

        if (!$productId) {
            $this->json(['success' => false, 'message' => 'Produit invalide.'], 400);
        }

        // Charger le produit depuis la BDD
        $productModel = new Product();
        $product      = $productModel->getById($productId);

        if (!$product) {
            $this->json(['success' => false, 'message' => 'Produit introuvable.'], 404);
        }

        // Clé unique dans le panier : produit + variante
        $cartKey = $productId . '_' . $variantId;

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        if (isset($_SESSION['cart'][$cartKey])) {
            $_SESSION['cart'][$cartKey]['quantity'] += $quantity;
        } else {
            // Prix de la variante
            $extraPrice = 0;
            $size       = '';
            $color      = '';
            if ($variantId) {
                $variant    = $productModel->getVariantById($variantId);
                $extraPrice = (float) ($variant['extra_price'] ?? 0);
                $size       = $variant['size']  ?? '';
                $color      = $variant['color'] ?? '';
            }

            $_SESSION['cart'][$cartKey] = [
                'product_id'   => $productId,
                'variant_id'   => $variantId,
                'product_name' => $product['name'],
                'size'         => $size,
                'color'        => $color,
                'unit_price'   => (float) $product['price'] + $extraPrice,
                'quantity'     => $quantity,
                'image'        => $product['cover_image'] ?? '',
            ];
        }

        $cartCount = array_sum(array_column($_SESSION['cart'], 'quantity'));

        $this->json([
            'success'    => true,
            'message'    => 'Article ajouté au panier.',
            'cart_count' => $cartCount,
        ]);
    }

    public function update(): void
    {
        $cartKey  = $_POST['cart_key']  ?? '';
        $quantity = (int) ($_POST['quantity'] ?? 0);

        if ($quantity <= 0) {
            unset($_SESSION['cart'][$cartKey]);
        } elseif (isset($_SESSION['cart'][$cartKey])) {
            $_SESSION['cart'][$cartKey]['quantity'] = $quantity;
        }

        $this->redirect('/panier');
    }

    public function remove(): void
    {
        $cartKey = $_POST['cart_key'] ?? '';
        unset($_SESSION['cart'][$cartKey]);
        $this->redirect('/panier');
    }

    // ── Helpers ──────────────────────────────────────────────

    private function calculateTotal(array $cart): float
    {
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['unit_price'] * $item['quantity'];
        }
        return $total;
    }
}
