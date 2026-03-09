<?php

namespace App\Models;

class BookingModel extends BaseModel
{
    public function userBookings(int $userId): array
    {
        $sql = 'SELECT b.*, c.title, c.class_date, c.start_time, c.end_time, l.name AS location_name
                FROM bookings b
                JOIN classes c ON c.id = b.class_id
                JOIN gym_locations l ON l.id = c.location_id
                WHERE b.user_id = :user_id
                ORDER BY c.class_date DESC, c.start_time DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function existsActiveBooking(int $userId, int $classId): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM bookings WHERE user_id = :user_id AND class_id = :class_id AND booking_status = :booking_status LIMIT 1');
        $stmt->execute(['user_id' => $userId, 'class_id' => $classId, 'booking_status' => 'booked']);
        return (bool) $stmt->fetch();
    }

    public function classBookingCount(int $classId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) AS total FROM bookings WHERE class_id = :class_id AND booking_status = :booking_status');
        $stmt->execute(['class_id' => $classId, 'booking_status' => 'booked']);
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function create(int $userId, int $classId): void
    {
        $stmt = $this->db->prepare('INSERT INTO bookings (user_id, class_id, booking_status, created_at, updated_at) VALUES (:user_id, :class_id, :booking_status, NOW(), NOW())');
        $stmt->execute(['user_id' => $userId, 'class_id' => $classId, 'booking_status' => 'booked']);
    }

    public function cancel(int $bookingId, int $userId): void
    {
        $stmt = $this->db->prepare('UPDATE bookings SET booking_status = :booking_status, updated_at = NOW() WHERE id = :id AND user_id = :user_id');
        $stmt->execute(['booking_status' => 'cancelled', 'id' => $bookingId, 'user_id' => $userId]);
    }

    public function allWithDetails(): array
    {
        $sql = 'SELECT b.*, u.full_name, u.email, c.title, c.class_date, c.start_time
                FROM bookings b
                JOIN users u ON u.id = b.user_id
                JOIN classes c ON c.id = b.class_id
                ORDER BY b.id DESC';
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function updateStatus(int $id, string $status): void
    {
        $stmt = $this->db->prepare('UPDATE bookings SET booking_status = :booking_status, updated_at = NOW() WHERE id = :id');
        $stmt->execute(['booking_status' => $status, 'id' => $id]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM bookings WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function countAll(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) AS total FROM bookings');
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    public function popularClass(): ?array
    {
        $sql = 'SELECT c.title, COUNT(*) AS total_bookings
                FROM bookings b
                JOIN classes c ON c.id = b.class_id
                WHERE b.booking_status = "booked"
                GROUP BY c.id, c.title
                ORDER BY total_bookings DESC
                LIMIT 1';
        $stmt = $this->db->query($sql);
        return $stmt->fetch() ?: null;
    }
}
