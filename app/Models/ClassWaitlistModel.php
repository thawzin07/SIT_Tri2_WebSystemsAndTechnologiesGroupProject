<?php

namespace App\Models;

class ClassWaitlistModel extends BaseModel
{
    public function userWaitingEntries(int $userId): array
    {
        $sql = 'SELECT w.*, c.title, c.class_date, c.start_time, c.end_time, l.name AS location_name, t.name AS trainer_name
                FROM class_waitlist w
                JOIN classes c ON c.id = w.class_id
                JOIN gym_locations l ON l.id = c.location_id
                JOIN trainers t ON t.id = c.trainer_id
                WHERE w.user_id = :user_id AND w.waitlist_status = :waitlist_status
                ORDER BY c.class_date ASC, c.start_time ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'waitlist_status' => 'waiting',
        ]);

        return $stmt->fetchAll();
    }

    public function cancelByMember(int $waitlistId, int $userId): bool
    {
        $stmt = $this->db->prepare('UPDATE class_waitlist SET waitlist_status = :waitlist_status, updated_at = NOW() WHERE id = :id AND user_id = :user_id AND waitlist_status = :current_status');
        $stmt->execute([
            'waitlist_status' => 'removed',
            'id' => $waitlistId,
            'user_id' => $userId,
            'current_status' => 'waiting',
        ]);

        return $stmt->rowCount() > 0;
    }
}
