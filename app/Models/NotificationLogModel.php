<?php

namespace App\Models;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class NotificationLogModel extends BaseModel
{
    private $telegramBotToken;

    public function queue(int $userId, string $channel, string $eventType, string $target, array $payload = []): void
    {
        $stmt = $this->db->prepare('INSERT INTO notification_logs
            (user_id, channel, event_type, target, status, error_message, payload_json, sent_at, created_at)
            VALUES
            (:user_id, :channel, :event_type, :target, :status, NULL, :payload_json, NULL, NOW())');
        $stmt->execute([
            'user_id' => $userId,
            'channel' => $channel,
            'event_type' => $eventType,
            'target' => $target,
            'status' => 'queued',
            'payload_json' => empty($payload) ? null : json_encode($payload, JSON_UNESCAPED_SLASHES),
        ]);
    }

    public function __construct()
    {
        parent::__construct();
        $this->telegramBotToken = $_ENV['TELEGRAM_BOT_TOKEN'];
    }

    public function processQueue()
    {
        $stmt = $this->db->query("SELECT * FROM notification_logs WHERE status = 'queued'");
        $pendingLogs = $stmt->fetchAll();

        echo "Found " . count($pendingLogs) . " queued notifications to process...\n";

        foreach ($pendingLogs as $log) {
            $success = false;
            $payload = json_decode($log['payload_json'], true);

            if ($log['channel'] === 'email') {
                $success = $this->processEmail($log['target'], $log['event_type'], $payload);
            } elseif ($log['channel'] === 'telegram') {
                $success = $this->processTelegram($log['target'], $log['event_type'], $payload);
            }

            $newStatus = $success ? 'sent' : 'failed';
            $updateStmt = $this->db->prepare("UPDATE notification_logs SET status = :status, sent_at = NOW() WHERE id = :id");
            $updateStmt->execute([
                'status' => $newStatus, 
                'id' => $log['id']
            ]);
            
            echo "Message ID {$log['id']} marked as {$newStatus}.\n";
        }
    }

    private function processEmail($toEmail, $eventType, $payload)
    {
    if (!class_exists(PHPMailer::class)) return false;

    $mail = new PHPMailer(true);
    $subject = "PulsePoint Fitness Update";
    $message = "Your payment was processed successfully.";

    if ($eventType === 'payment_success' || $eventType === 'membership_renewed') {
        $invoiceModel = new \App\Models\InvoiceModel();
        $invoiceData = $invoiceModel->findDownloadDataByPaymentIdForUser(
            (int)$payload['payment_id'], 
            (int)$payload['user_id']
        );

        if ($invoiceData) {
            $subject = "Invoice from PulsePoint Fitness - " . $invoiceData['invoice_no'];
            $message = "<h1>Hi " . e($invoiceData['full_name']) . "!</h1>" .
                       "<p>Your payment for <b>" . e($invoiceData['plan_name']) . "</b> was successful.</p>" .
                       "<p>Please find your receipt attached to this email.</p>";

            $pdfService = new \App\Services\InvoicePdfService();
            $pdfContent = $pdfService->generateInvoicePdf($invoiceData);
            $fileName = "PulsePoint-" . $invoiceData['invoice_no'] . ".pdf";
            
            $mail->addStringAttachment($pdfContent, $fileName);
        }
    }

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USERNAME'];
        $mail->Password   = $_ENV['SMTP_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom($mail->Username, 'PulsePoint Fitness');
        $mail->addAddress($toEmail);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;

        return $mail->send();
    } catch (\Exception $e) {
        return false;
    }
}

    private function processTelegram($chatId, $eventType, $payload)
    {
    if (empty($chatId)) return false;

    $isRenew = ($eventType === 'membership_renewed' || ($payload['payment_type'] ?? '') === 'renew');
    $icon = $isRenew ? "🔄" : "🆕";
    $label = $isRenew ? "Membership Renewed" : "New Membership Purchased";

    $message = "{$icon} <b>{$label}</b>\n\n";
    $message .= "<b>Amount:</b> " . ($payload['currency'] ?? 'USD') . " " . number_format($payload['amount'] ?? 0, 2) . "\n";
    $message .= "Check your email for the official invoice PDF!";

    $url = "https://api.telegram.org/bot" . $this->telegramBotToken . "/sendMessage";
    $data = ['chat_id' => $chatId, 'text' => $message, 'parse_mode' => 'HTML'];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $response = curl_exec($ch);
    return (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200);
    }
}
