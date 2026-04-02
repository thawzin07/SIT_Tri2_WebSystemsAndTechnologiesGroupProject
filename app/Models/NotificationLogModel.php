<?php

namespace App\Models;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class NotificationLogModel extends BaseModel
{
    private string $telegramBotToken = '';

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
        $token = $_ENV['TELEGRAM_BOT_TOKEN'] ?? getenv('TELEGRAM_BOT_TOKEN') ?: '';
        $this->telegramBotToken = is_string($token) ? trim($token) : '';
    }

    public function processQueue()
    {
        $stmt = $this->db->query("SELECT * FROM notification_logs WHERE status = 'queued'");
        $pendingLogs = $stmt->fetchAll();

        echo "Found " . count($pendingLogs) . " queued notifications to process...\n";

        foreach ($pendingLogs as $log) {
            $success = false;
            $errorMessage = null;
            $payload = json_decode((string) ($log['payload_json'] ?? ''), true);
            if (!is_array($payload)) {
                $payload = [];
            }

            if ($log['channel'] === 'email') {
                $emailTarget = $this->resolveRegisteredEmailTarget((int) ($log['user_id'] ?? 0), (string) ($log['target'] ?? ''));
                if ($emailTarget === '') {
                    $errorMessage = 'Missing valid recipient email.';
                } else {
                    [$success, $errorMessage] = $this->processEmail($emailTarget, (string) $log['event_type'], $payload);
                }
            } elseif ($log['channel'] === 'telegram') {
                [$success, $errorMessage] = $this->processTelegram((string) $log['target'], (string) $log['event_type'], $payload);
            }

            $newStatus = $success ? 'sent' : 'failed';
            $updateStmt = $this->db->prepare("UPDATE notification_logs SET status = :status, error_message = :error_message, sent_at = NOW() WHERE id = :id");
            $updateStmt->execute([
                'status' => $newStatus,
                'error_message' => $success ? null : substr((string) ($errorMessage ?: 'Delivery failed.'), 0, 255),
                'id' => $log['id'],
            ]);
            
            echo "Message ID {$log['id']} marked as {$newStatus}.\n";
        }
    }

    private function resolveRegisteredEmailTarget(int $userId, string $fallbackTarget): string
    {
        if ($userId > 0) {
            $stmt = $this->db->prepare('SELECT email FROM users WHERE id = :id LIMIT 1');
            $stmt->execute(['id' => $userId]);
            $row = $stmt->fetch();
            $email = trim((string) ($row['email'] ?? ''));
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $email;
            }
        }

        $fallbackTarget = trim($fallbackTarget);
        return filter_var($fallbackTarget, FILTER_VALIDATE_EMAIL) ? $fallbackTarget : '';
    }

    private function processEmail($toEmail, $eventType, array $payload): array
    {
    if (!class_exists(PHPMailer::class)) {
        return [false, 'PHPMailer class not available.'];
    }

    $smtpUsername = $_ENV['SMTP_USERNAME'] ?? getenv('SMTP_USERNAME') ?: '';
    $smtpPassword = $_ENV['SMTP_PASSWORD'] ?? getenv('SMTP_PASSWORD') ?: '';
    if ($smtpUsername === '' || $smtpPassword === '') {
        return [false, 'SMTP credentials missing (SMTP_USERNAME / SMTP_PASSWORD).'];
    }
    if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        return [false, 'Recipient email is invalid.'];
    }

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
        $mail->Username   = $smtpUsername;
        $mail->Password   = $smtpPassword;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom($mail->Username, 'PulsePoint Fitness');
        $mail->addAddress($toEmail);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;

        $sent = $mail->send();
        return [$sent, $sent ? null : 'Unknown SMTP failure.'];
    } catch (\Exception $e) {
        return [false, $e->getMessage()];
    }
}

    private function processTelegram($chatId, $eventType, array $payload): array
    {
    if (empty($chatId)) {
        return [false, 'Telegram chat id is empty.'];
    }
    if ($this->telegramBotToken === '') {
        return [false, 'TELEGRAM_BOT_TOKEN is missing.'];
    }

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
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($response === false) {
        $err = curl_error($ch);
        curl_close($ch);
        return [false, $err !== '' ? $err : 'Telegram request failed.'];
    }
    curl_close($ch);
    if ($httpCode !== 200) {
        return [false, 'Telegram API returned HTTP ' . $httpCode . '.'];
    }
    return [true, null];
    }
}
