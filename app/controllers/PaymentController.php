<?php
require_once APP_PATH . '/controllers/Controller.php';
require_once ROOT_PATH . '/config/fedapay.php';

class PaymentController extends Controller
{
    public function initiateDeposit(): void
    {
        $orderId = (int) ($_GET['order_id'] ?? 0);
        if (!$orderId) $this->redirect('/panier');

        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = ? LIMIT 1");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        if (!$order) $this->redirect('/panier');

        $data = [
            'description'  => "Acompte 50% - Commande " . $order['order_number'],
            'amount'       => (int) $order['deposit_amount'],
            'currency'     => ['iso' => 'XOF'],
            'callback_url' => APP_URL . '/paiement/callback',
            'return_url'   => APP_URL . '/paiement/retour?order_id=' . $orderId,
            'cancel_url'   => APP_URL . '/paiement/callback?status=canceled',
            'customer'     => [
                'firstname'    => explode(' ', $order['customer_name'])[0],
                'lastname'     => explode(' ', $order['customer_name'])[1] ?? '',
                'email'        => $order['customer_email'],
                'phone_number' => ['number' => $order['customer_phone'], 'country' => 'TG']
            ]
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => 'https://sandbox-api.fedapay.com/v1/transactions',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . FEDAPAY_SECRET_KEY,
                'Content-Type: application/json',
            ],
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        $result = json_decode($response, true);

        if (($httpCode === 200 || $httpCode === 201) && isset($result['v1/transaction'])) {
            $transaction   = $result['v1/transaction'];
            $transactionId = $transaction['id'];
            $paymentUrl    = $transaction['payment_url'];

            $db->prepare("
                INSERT INTO payments (order_id, payment_type, amount, gateway, gateway_transaction_id, status)
                VALUES (?, 'deposit', ?, 'fedapay', ?, 'pending')
            ")->execute([$orderId, $order['deposit_amount'], $transactionId]);

            header('Location: ' . $paymentUrl);
            exit;
        }

        $this->redirect('/commande/echec');
    }

    public function handleCallback(): void
    {
        $status = $_GET['status'] ?? null;
        if ($status === 'canceled' || $status === 'pending') {
            $this->redirect('/commande/echec');
        }

        $payload = file_get_contents('php://input');
        $data    = json_decode($payload, true);
        if (!$data) exit;

        $transaction   = $data['v1/transaction'] ?? $data;
        $transactionId = $transaction['id'] ?? null;
        $fedaStatus    = $transaction['status'] ?? null;
        if (!$transactionId) exit;

        $db = getDB();
        if ($fedaStatus === 'approved') {
            $db->prepare("UPDATE payments SET status='approved', paid_at=NOW() WHERE gateway_transaction_id=?")->execute([$transactionId]);
            $stmt = $db->prepare("SELECT order_id FROM payments WHERE gateway_transaction_id=?");
            $stmt->execute([$transactionId]);
            $payment = $stmt->fetch();
            if ($payment) {
                $db->prepare("UPDATE orders SET payment_status='deposit_paid', order_status='deposit_paid' WHERE id=?")->execute([$payment['order_id']]);
            }
        }

        http_response_code(200);
        echo json_encode(['status' => 'ok']);
        exit;
    }

    public function returnPage(): void
    {
        $orderId = (int) ($_GET['order_id'] ?? 0);
        if (!$orderId) $this->redirect('/');

        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = ? LIMIT 1");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        if (!$order) $this->redirect('/');

        $stmt = $db->prepare("SELECT * FROM payments WHERE order_id=? AND payment_type='deposit' ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$orderId]);
        $payment = $stmt->fetch();
        if (!$payment) $this->redirect('/commande/echec');

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => 'https://sandbox-api.fedapay.com/v1/transactions/' . $payment['gateway_transaction_id'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . FEDAPAY_SECRET_KEY],
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $response   = curl_exec($curl);
        curl_close($curl);
        $result     = json_decode($response, true);
        $fedaStatus = $result['v1/transaction']['status'] ?? null;

        if ($fedaStatus === 'approved') {
            $db->prepare("UPDATE payments SET status='approved', paid_at=NOW() WHERE id=?")->execute([$payment['id']]);
            $db->prepare("UPDATE orders SET payment_status='deposit_paid', order_status='deposit_paid' WHERE id=?")->execute([$orderId]);
            $_SESSION['order_confirmed'] = $order['order_number'];
            unset($_SESSION['cart']);
            $this->redirect('/commande/confirmation');
        } else {
            $this->redirect('/commande/echec');
        }
    }
}