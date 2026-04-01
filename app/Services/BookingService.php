<?php

namespace App\Services;

use App\Core\Database;
use PDO;
use Throwable;

class BookingService
{
    public function __construct(private ?PDO $db = null)
    {
        $this->db = $this->db ?? Database::connection();
    }

    public function bookOrWaitlist(int $userId, int $classId): string
    {
        $this->db->beginTransaction();

        try {
            $class = $this->lockActiveClass($classId);
            if ($class === null) {
                $this->db->rollBack();
                return 'class_unavailable';
            }

            if ($this->hasBookedSeat($userId, $classId)) {
                $this->db->rollBack();
                return 'already_booked';
            }

            $waitlistState = $this->currentWaitlistStatus($userId, $classId);
            if ($waitlistState === 'waiting') {
                $this->db->rollBack();
                return 'already_waitlisted';
            }

            if ($this->bookedCount($classId) < (int) $class['capacity']) {
                $this->insertBooking($userId, $classId);
                $this->db->commit();
                return 'booked';
            }

            $this->enqueueWaitlist($userId, $classId);
            $this->db->commit();
            return 'waitlisted';
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function cancelBookingAndPromote(int $userId, int $bookingId): string
    {
        $this->db->beginTransaction();

        try {
            $booking = $this->lockUserBooking($bookingId, $userId);
            if ($booking === null) {
                $this->db->rollBack();
                return 'booking_not_found';
            }

            if (($booking['booking_status'] ?? '') !== 'booked') {
                $this->db->rollBack();
                return 'booking_not_active';
            }

            $classId = (int) $booking['class_id'];
            $this->lockClass($classId);
            $this->cancelBooking($bookingId, $userId, $classId);

            $promoted = $this->promoteNextWaitlistedMember($classId);
            $this->db->commit();

            return $promoted ? 'cancelled_promoted' : 'cancelled';
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    private function lockActiveClass(int $classId): ?array
    {
        $stmt = $this->db->prepare('SELECT id, capacity FROM classes WHERE id = :id AND status = :status AND class_date >= CURDATE() LIMIT 1 FOR UPDATE');
        $stmt->execute([
            'id' => $classId,
            'status' => 'active',
        ]);

        return $stmt->fetch() ?: null;
    }

    private function lockClass(int $classId): void
    {
        $stmt = $this->db->prepare('SELECT id FROM classes WHERE id = :id LIMIT 1 FOR UPDATE');
        $stmt->execute(['id' => $classId]);
    }

    private function hasBookedSeat(int $userId, int $classId): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM bookings WHERE user_id = :user_id AND class_id = :class_id AND booking_status = :booking_status LIMIT 1');
        $stmt->execute([
            'user_id' => $userId,
            'class_id' => $classId,
            'booking_status' => 'booked',
        ]);

        return (bool) $stmt->fetch();
    }

    private function currentWaitlistStatus(int $userId, int $classId): ?string
    {
        $stmt = $this->db->prepare('SELECT waitlist_status FROM class_waitlist WHERE user_id = :user_id AND class_id = :class_id LIMIT 1 FOR UPDATE');
        $stmt->execute([
            'user_id' => $userId,
            'class_id' => $classId,
        ]);

        $row = $stmt->fetch();
        return $row['waitlist_status'] ?? null;
    }

    private function bookedCount(int $classId): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) AS total FROM bookings WHERE class_id = :class_id AND booking_status = :booking_status');
        $stmt->execute([
            'class_id' => $classId,
            'booking_status' => 'booked',
        ]);

        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    private function insertBooking(int $userId, int $classId): void
    {
        $stmt = $this->db->prepare('INSERT INTO bookings (user_id, class_id, booking_status, created_at, updated_at) VALUES (:user_id, :class_id, :booking_status, NOW(), NOW())');
        $stmt->execute([
            'user_id' => $userId,
            'class_id' => $classId,
            'booking_status' => 'booked',
        ]);
    }

    private function enqueueWaitlist(int $userId, int $classId): void
    {
        $stmt = $this->db->prepare('INSERT INTO class_waitlist (user_id, class_id, waitlist_status, promoted_at, created_at, updated_at)
                                    VALUES (:user_id, :class_id, :waitlist_status, NULL, NOW(), NOW())
                                    ON DUPLICATE KEY UPDATE waitlist_status = VALUES(waitlist_status), promoted_at = NULL, updated_at = NOW()');
        $stmt->execute([
            'user_id' => $userId,
            'class_id' => $classId,
            'waitlist_status' => 'waiting',
        ]);
    }

    private function lockUserBooking(int $bookingId, int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT id, class_id, booking_status FROM bookings WHERE id = :id AND user_id = :user_id LIMIT 1 FOR UPDATE');
        $stmt->execute([
            'id' => $bookingId,
            'user_id' => $userId,
        ]);

        return $stmt->fetch() ?: null;
    }

    private function cancelBooking(int $bookingId, int $userId, int $classId): void
    {
        if ($this->hasBookingWithStatus($userId, $classId, 'cancelled')) {
            $deleteStmt = $this->db->prepare('DELETE FROM bookings WHERE id = :id AND user_id = :user_id AND booking_status = :booking_status');
            $deleteStmt->execute([
                'id' => $bookingId,
                'user_id' => $userId,
                'booking_status' => 'booked',
            ]);
            return;
        }

        $updateStmt = $this->db->prepare('UPDATE bookings SET booking_status = :booking_status, updated_at = NOW() WHERE id = :id AND user_id = :user_id AND booking_status = :current_status');
        $updateStmt->execute([
            'booking_status' => 'cancelled',
            'id' => $bookingId,
            'user_id' => $userId,
            'current_status' => 'booked',
        ]);
    }

    private function hasBookingWithStatus(int $userId, int $classId, string $status): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM bookings WHERE user_id = :user_id AND class_id = :class_id AND booking_status = :booking_status LIMIT 1 FOR UPDATE');
        $stmt->execute([
            'user_id' => $userId,
            'class_id' => $classId,
            'booking_status' => $status,
        ]);

        return (bool) $stmt->fetch();
    }

    private function promoteNextWaitlistedMember(int $classId): bool
    {
        while (true) {
            $candidateStmt = $this->db->prepare('SELECT id, user_id FROM class_waitlist WHERE class_id = :class_id AND waitlist_status = :waitlist_status ORDER BY created_at ASC, id ASC LIMIT 1 FOR UPDATE');
            $candidateStmt->execute([
                'class_id' => $classId,
                'waitlist_status' => 'waiting',
            ]);
            $candidate = $candidateStmt->fetch();

            if (!$candidate) {
                return false;
            }

            if ($this->hasBookedSeat((int) $candidate['user_id'], $classId)) {
                $this->markWaitlistAsRemoved((int) $candidate['id']);
                continue;
            }

            $this->insertBooking((int) $candidate['user_id'], $classId);
            $this->markWaitlistAsPromoted((int) $candidate['id']);
            return true;
        }
    }

    private function markWaitlistAsRemoved(int $waitlistId): void
    {
        $stmt = $this->db->prepare('UPDATE class_waitlist SET waitlist_status = :waitlist_status, updated_at = NOW() WHERE id = :id');
        $stmt->execute([
            'waitlist_status' => 'removed',
            'id' => $waitlistId,
        ]);
    }

    private function markWaitlistAsPromoted(int $waitlistId): void
    {
        $stmt = $this->db->prepare('UPDATE class_waitlist SET waitlist_status = :waitlist_status, promoted_at = NOW(), updated_at = NOW() WHERE id = :id');
        $stmt->execute([
            'waitlist_status' => 'promoted',
            'id' => $waitlistId,
        ]);
    }
}
