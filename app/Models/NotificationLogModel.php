<?php

namespace App\Models;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class NotificationLogModel extends BaseModel
{
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

    private $telegramBotToken = '8782193125:AAGeC6ehNJPjBxg2dzlaUE7F-R6Ht5l4k9k'; 

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
        if (!class_exists(PHPMailer::class)) {
            echo "Skipping Email: PHPMailer is not installed on this machine.\n";
            return false;
        }

        $subject = "PulsePoint Update";
        $message = "Hello! This is a notification from PulsePoint.";

        if ($eventType === 'payment_success') {
            $subject = "Payment Successful - Invoice " . ($payload['invoice_no'] ?? '');
            $message = "<h2>Thank you for your payment!</h2><p>Amount: $" . ($payload['amount'] ?? '') . "</p>";
        }

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['SMTP_USERNAME'] ?? getenv('SMTP_USERNAME'); 
            $mail->Password   = $_ENV['SMTP_PASSWORD'] ?? getenv('SMTP_PASSWORD');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom($mail->Username, 'PulsePoint Fitness');
            $mail->addAddress($toEmail);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $message;

            $mail->send();
            return true;
        } catch (Exception $e) {
            echo "Email Error: {$mail->ErrorInfo}\n";
            return false;
        }
    }

    private function processTelegram($chatId, $eventType, $payload)
    {
        $message = "PulsePoint Notification";

        if ($eventType === 'invoice_sent') {
            $message = "<b>New Invoice Generated</b>\nInvoice Number: " . ($payload['invoice_no'] ?? '');
        }

        $url = "https://api.telegram.org/bot" . $this->telegramBotToken . "/sendMessage";
        $data = ['chat_id' => $chatId, 'text' => $message, 'parse_mode' => 'HTML'];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $response = curl_exec($ch); 
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode != 200) {
            echo "Telegram API Error: " . $response . "\n";
        }

        return ($httpCode == 200);
    }
}
