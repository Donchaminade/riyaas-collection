<?php
/**
 * RIYAA'S COLLECTION — MailController
 * Envoi d'emails via PHPMailer + Gmail
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once ROOT_PATH . '/vendor/phpmailer/PHPMailer.php';
require_once ROOT_PATH . '/vendor/phpmailer/SMTP.php';
require_once ROOT_PATH . '/vendor/phpmailer/Exception.php';

class MailController
{
    public static function sendNewOrderNotification(array $order, array $items): void
    {
        $itemsList = '';
        foreach ($items as $item) {
            $itemsList .= $item['product_name'] . " x" . $item['quantity'] . " = " . number_format($item['subtotal'], 0, ',', ' ') . " FCFA\n";
        }

        $message = "Bonjour Hairiyatou !\n\n";
        $message .= "Nouvelle commande sur Riyaa's Collection !\n\n";
        $message .= "Numero : " . $order['order_number'] . "\n";
        $message .= "Date : " . date('d/m/Y a H:i') . "\n\n";
        $message .= "CLIENTE\n";
        $message .= "Nom : " . $order['customer_name'] . "\n";
        $message .= "Email : " . $order['customer_email'] . "\n";
        $message .= "Telephone : " . $order['customer_phone'] . "\n\n";
        $message .= "LIVRAISON\n";
        $message .= "Adresse : " . $order['delivery_address'] . "\n";
        $message .= "Ville : " . $order['delivery_city'] . "\n";
        $message .= "Avant le : " . date('d/m/Y', strtotime($order['delivery_deadline'])) . "\n\n";
        $message .= "ARTICLES\n" . $itemsList . "\n";
        $message .= "PAIEMENT\n";
        $message .= "Total : " . number_format($order['total_amount'], 0, ',', ' ') . " FCFA\n";
        $message .= "Acompte : " . number_format($order['deposit_amount'], 0, ',', ' ') . " FCFA\n";
        $message .= "Reste a la livraison : " . number_format($order['balance_amount'], 0, ',', ' ') . " FCFA\n\n";
        $message .= "Riyaa's Collection";

        try {
            $mail = new PHPMailer(true);

            // Configuration SMTP Gmail
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USERNAME;
            $mail->Password   = MAIL_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            // Expéditeur et destinataire
            $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
            $mail->addAddress(ADMIN_EMAIL);

            // Contenu
            $mail->Subject = "Nouvelle commande — " . $order['order_number'];
            $mail->Body    = $message;

            $mail->send();
            error_log("Email envoye pour commande : " . $order['order_number']);

        } catch (Exception $e) {
            error_log("Erreur email : " . $e->getMessage());
        }
    }
}