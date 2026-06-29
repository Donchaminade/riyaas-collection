<?php
/**
 * RIYAA'S COLLECTION — OrderController
 */

require_once APP_PATH . '/controllers/Controller.php';
require_once APP_PATH . '/controllers/MailController.php';
require_once ROOT_PATH . '/config/mail.php';

class OrderController extends Controller
{
    public function checkout(): void
    {
        $cart = $_SESSION['cart'] ?? [];
        if (empty($cart)) $this->redirect('/panier');

        $total   = $this->calculateTotal($cart);
        $deposit = $total * 0.5;
        $balance = $total * 0.5;

        $this->render('checkout/index', [
            'pageTitle' => "Commander — Riyaa's Collection",
            'cart'      => $cart,
            'total'     => $total,
            'deposit'   => $deposit,
            'balance'   => $balance,
        ]);
    }

    public function store(): void
    {
        $cart = $_SESSION['cart'] ?? [];
        if (empty($cart)) $this->redirect('/panier');

        $firstName = trim($_POST['first_name']     ?? '');
        $lastName  = trim($_POST['last_name']      ?? '');
        $email     = trim($_POST['email']          ?? '');
        $phone     = trim($_POST['phone']          ?? '');
        $address   = trim($_POST['address']        ?? '');
        $city      = trim($_POST['city']           ?? 'Lomé');
        $notes     = trim($_POST['delivery_notes'] ?? '');

        if (!$firstName || !$lastName || !$email || !$phone || !$address) {
            $this->redirect('/commander');
        }

        $db           = getDB();
        $total        = $this->calculateTotal($cart);
        $deposit      = round($total * 0.5);
        $balance      = round($total * 0.5);
        $customerName = $firstName . ' ' . $lastName;
        $orderNumber  = $this->generateOrderNumber();
        $deliveryDate = date('Y-m-d', strtotime('+5 days'));

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
            $address, $city, $notes, $deliveryDate
        ]);

        $orderId = (int) $db->lastInsertId();

        foreach ($cart as $item) {
            $db->prepare("
                INSERT INTO order_items (
                    order_id, product_id, variant_id,
                    product_name, size, unit_price, quantity, subtotal
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ")->execute([
                $orderId,
                $item['product_id'],
                $item['variant_id'] ?: null,
                $item['product_name'],
                $item['size'] ?? '',
                $item['unit_price'],
                $item['quantity'],
                $item['unit_price'] * $item['quantity']
            ]);
        }

        $_SESSION['pending_order_id']     = $orderId;
        $_SESSION['pending_order_number'] = $orderNumber;

        // Envoyer notification email à l'admin
        $stmt2 = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt2->execute([$orderId]);
        $itemsForMail = $stmt2->fetchAll();

        $orderStmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
        $orderStmt->execute([$orderId]);
        $orderInfo = $orderStmt->fetch();

        MailController::sendNewOrderNotification($orderInfo, $itemsForMail);

        // Rediriger vers le paiement TMoney
        $this->redirect('/paiement/acompte?order_id=' . $orderId);
    }

    public function confirmation(): void
    {
        $orderNumber = $_SESSION['order_confirmed'] ?? null;
        if (!$orderNumber) $this->redirect('/');

        $db   = getDB();
        $stmt = $db->prepare("
            SELECT o.*, GROUP_CONCAT(oi.product_name SEPARATOR ', ') as items_list
            FROM orders o
            LEFT JOIN order_items oi ON oi.order_id = o.id
            WHERE o.order_number = ?
            GROUP BY o.id
        ");
        $stmt->execute([$orderNumber]);
        $order = $stmt->fetch();

        unset($_SESSION['order_confirmed']);
        unset($_SESSION['cart']);

        $this->render('checkout/confirmation', [
            'pageTitle' => "Commande confirmée — Riyaa's Collection",
            'order'     => $order,
        ]);
    }

    public function failed(): void
    {
        $this->render('checkout/failed', [
            'pageTitle' => "Paiement échoué — Riyaa's Collection",
        ]);
    }

    public function invoice(): void
    {
        $orderNumber = $_GET['ref'] ?? null;
        if (!$orderNumber) $this->redirect('/');

        $db   = getDB();
        $stmt = $db->prepare("SELECT * FROM orders WHERE order_number = ? LIMIT 1");
        $stmt->execute([$orderNumber]);
        $order = $stmt->fetch();

        if (!$order) $this->redirect('/');

        $stmt = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $stmt->execute([$order['id']]);
        $items = $stmt->fetchAll();

        $this->render('checkout/invoice', [
            'pageTitle' => "Facture {$orderNumber} — Riyaa's Collection",
            'order'     => $order,
            'items'     => $items,
        ]);
    }

    private function calculateTotal(array $cart): float
    {
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['unit_price'] * $item['quantity'];
        }
        return $total;
    }

    private function generateOrderNumber(): string
    {
        $date = date('Ymd');
        $rand = strtoupper(substr(uniqid(), -4));
        return "RC-{$date}-{$rand}";
    }
}