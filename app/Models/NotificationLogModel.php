<?php

namespace App\Models;

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
}
