<?php

namespace App\Models;

class GymClassModel extends BaseModel
{
    public function upcomingActive(): array
    {
        $sql = 'SELECT c.*, t.name AS trainer_name, l.name AS location_name,
                (SELECT COUNT(*) FROM bookings b WHERE b.class_id = c.id AND b.booking_status = "booked") AS booked_count
                ,(SELECT COUNT(*) FROM class_waitlist w WHERE w.class_id = c.id AND w.waitlist_status = "waiting") AS waitlist_count
                FROM classes c
                JOIN trainers t ON t.id = c.trainer_id
                JOIN gym_locations l ON l.id = c.location_id
                WHERE c.status = :status AND c.class_date >= CURDATE()
                ORDER BY c.class_date ASC, c.start_time ASC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['status' => 'active']);
        return $stmt->fetchAll();
    }

    public function all(): array
    {
        $sql = 'SELECT c.*, t.name AS trainer_name, l.name AS location_name FROM classes c JOIN trainers t ON t.id = c.trainer_id JOIN gym_locations l ON l.id = c.location_id ORDER BY c.class_date DESC, c.start_time DESC';
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM classes WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): void
    {
        $stmt = $this->db->prepare('INSERT INTO classes (trainer_id, location_id, title, description, class_date, start_time, end_time, capacity, status, created_at, updated_at) VALUES (:trainer_id, :location_id, :title, :description, :class_date, :start_time, :end_time, :capacity, :status, NOW(), NOW())');
        $stmt->execute($data);
    }

    public function update(int $id, array $data): void
    {
        $data['id'] = $id;
        $stmt = $this->db->prepare('UPDATE classes SET trainer_id = :trainer_id, location_id = :location_id, title = :title, description = :description, class_date = :class_date, start_time = :start_time, end_time = :end_time, capacity = :capacity, status = :status, updated_at = NOW() WHERE id = :id');
        $stmt->execute($data);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM classes WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
