<?php

namespace App\Models;

class MembershipModel extends BaseModel
{
    public function syncExpiredForUser(int $userId): void
    {
        $stmt = $this->db->prepare('UPDATE memberships
            SET status = :expired, updated_at = NOW()
            WHERE user_id = :user_id
              AND status = :active
              AND start_date <= CURDATE()
              AND end_date < CURDATE()');
        $stmt->execute([
            'expired' => 'expired',
            'user_id' => $userId,
            'active' => 'active',
        ]);
    }

    public function currentForUser(int $userId): ?array
    {
        $this->syncExpiredForUser($userId);
        $stmt = $this->db->prepare('SELECT m.*, p.name AS plan_name, p.price, p.duration_months,
            CASE
                WHEN m.status = :active_case_queued AND m.start_date > CURDATE() THEN :queued
                WHEN m.status = :active_case_expired AND m.end_date < CURDATE() THEN :expired
                ELSE m.status
            END AS effective_status
            FROM memberships m
            JOIN membership_plans p ON p.id = m.plan_id
            WHERE m.user_id = :user_id
            ORDER BY
                CASE
                    WHEN m.status = :active_order_current AND m.start_date <= CURDATE() AND m.end_date >= CURDATE() THEN 0
                    WHEN m.status = :active_order_future AND m.start_date > CURDATE() THEN 1
                    ELSE 2
                END,
                CASE
                    WHEN m.status = :active_order_date THEN m.start_date
                    ELSE m.end_date
                END ASC,
                m.id DESC
            LIMIT 1');
        $stmt->execute([
            'user_id' => $userId,
            'active_case_queued' => 'active',
            'active_case_expired' => 'active',
            'active_order_current' => 'active',
            'active_order_future' => 'active',
            'active_order_date' => 'active',
            'expired' => 'expired',
            'queued' => 'queued',
        ]);
        return $stmt->fetch() ?: null;
    }

    public function historyForUser(int $userId): array
    {
        $this->syncExpiredForUser($userId);
        $stmt = $this->db->prepare('SELECT m.*, p.name AS plan_name, p.price,
            CASE
                WHEN m.status = :active_case_queued AND m.start_date > CURDATE() THEN :queued
                WHEN m.status = :active_case_expired AND m.end_date < CURDATE() THEN :expired
                ELSE m.status
            END AS effective_status
            FROM memberships m
            JOIN membership_plans p ON p.id = m.plan_id
            WHERE m.user_id = :user_id
            ORDER BY m.start_date DESC, m.id DESC');
        $stmt->execute([
            'user_id' => $userId,
            'active_case_queued' => 'active',
            'active_case_expired' => 'active',
            'expired' => 'expired',
            'queued' => 'queued',
        ]);
        return $stmt->fetchAll();
    }

    public function currentActiveForUser(int $userId): ?array
    {
        $this->syncExpiredForUser($userId);
        $stmt = $this->db->prepare('SELECT m.*, p.name AS plan_name, p.price, p.duration_months
            FROM memberships m
            JOIN membership_plans p ON p.id = m.plan_id
            WHERE m.user_id = :user_id
              AND m.status = :status
              AND m.start_date <= CURDATE()
              AND m.end_date >= CURDATE()
            ORDER BY m.end_date DESC, m.id DESC
            LIMIT 1');
        $stmt->execute([
            'user_id' => $userId,
            'status' => 'active',
        ]);
        return $stmt->fetch() ?: null;
    }

    public function latestScheduledActiveForUser(int $userId): ?array
    {
        $this->syncExpiredForUser($userId);
        $stmt = $this->db->prepare('SELECT m.*, p.name AS plan_name, p.price, p.duration_months
            FROM memberships m
            JOIN membership_plans p ON p.id = m.plan_id
            WHERE m.user_id = :user_id
              AND m.status = :status
              AND m.end_date >= CURDATE()
            ORDER BY m.end_date DESC, m.id DESC
            LIMIT 1');
        $stmt->execute([
            'user_id' => $userId,
            'status' => 'active',
        ]);
        return $stmt->fetch() ?: null;
    }

    public function create(int $userId, int $planId, string $startDate, string $endDate, string $status = 'active'): int
    {
        $stmt = $this->db->prepare('INSERT INTO memberships (user_id, plan_id, start_date, end_date, status, created_at, updated_at) VALUES (:user_id, :plan_id, :start_date, :end_date, :status, NOW(), NOW())');
        $stmt->execute([
            'user_id' => $userId,
            'plan_id' => $planId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $status,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function updateWindowAndStatus(int $membershipId, string $startDate, string $endDate, string $status = 'active'): void
    {
        $stmt = $this->db->prepare('UPDATE memberships
            SET start_date = :start_date,
                end_date = :end_date,
                status = :status,
                updated_at = NOW()
            WHERE id = :id');
        $stmt->execute([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $status,
            'id' => $membershipId,
        ]);
    }

    public function closeOtherActiveMemberships(int $userId, int $exceptMembershipId, string $endDate): void
    {
        $stmt = $this->db->prepare('UPDATE memberships
            SET status = :status,
                end_date = CASE
                    WHEN start_date > :end_date_case THEN start_date
                    ELSE LEAST(end_date, :end_date_min)
                END,
                updated_at = NOW()
            WHERE user_id = :user_id
              AND status = :active
              AND id <> :except_id');
        $stmt->execute([
            'status' => 'cancelled',
            'end_date_case' => $endDate,
            'end_date_min' => $endDate,
            'user_id' => $userId,
            'active' => 'active',
            'except_id' => $exceptMembershipId,
        ]);
    }

    public function shiftFutureActiveMemberships(int $userId, string $afterDate, int $shiftDays): void
    {
        if ($shiftDays <= 0) {
            return;
        }

        $stmt = $this->db->prepare('UPDATE memberships
            SET start_date = DATE_ADD(start_date, INTERVAL :shift_days DAY),
                end_date = DATE_ADD(end_date, INTERVAL :shift_days DAY),
                updated_at = NOW()
            WHERE user_id = :user_id
              AND status = :status
              AND start_date > :after_date');
        $stmt->execute([
            'shift_days' => $shiftDays,
            'user_id' => $userId,
            'status' => 'active',
            'after_date' => $afterDate,
        ]);
    }

    public function findDuplicateWindow(int $userId, int $planId, string $startDate, string $endDate): ?array
    {
        $stmt = $this->db->prepare('SELECT *
            FROM memberships
            WHERE user_id = :user_id
              AND plan_id = :plan_id
              AND start_date = :start_date
              AND end_date = :end_date
              AND status = :status
            ORDER BY id DESC
            LIMIT 1');
        $stmt->execute([
            'user_id' => $userId,
            'plan_id' => $planId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'active',
        ]);
        return $stmt->fetch() ?: null;
    }

    public function cancel(int $membershipId, int $userId): void
    {
        $stmt = $this->db->prepare('UPDATE memberships SET status = :status, updated_at = NOW() WHERE id = :id AND user_id = :user_id');
        $stmt->execute(['status' => 'cancelled', 'id' => $membershipId, 'user_id' => $userId]);
    }

    public function countActive(): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) AS total FROM memberships WHERE status = :status');
        $stmt->execute(['status' => 'active']);
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }
}
