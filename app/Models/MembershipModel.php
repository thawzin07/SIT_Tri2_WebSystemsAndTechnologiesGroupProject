<?php

namespace App\Models;

class MembershipModel extends BaseModel
{
    public function currentForUser(int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT m.*, p.name AS plan_name, p.price, p.duration_months FROM memberships m JOIN membership_plans p ON p.id = m.plan_id WHERE m.user_id = :user_id ORDER BY m.id DESC LIMIT 1');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch() ?: null;
    }

    public function historyForUser(int $userId): array
    {
        $stmt = $this->db->prepare('SELECT m.*, p.name AS plan_name, p.price FROM memberships m JOIN membership_plans p ON p.id = m.plan_id WHERE m.user_id = :user_id ORDER BY m.id DESC');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function create(int $userId, int $planId, string $startDate, string $endDate, string $status = 'active'): void
    {
        $stmt = $this->db->prepare('INSERT INTO memberships (user_id, plan_id, start_date, end_date, status, created_at, updated_at) VALUES (:user_id, :plan_id, :start_date, :end_date, :status, NOW(), NOW())');
        $stmt->execute([
            'user_id' => $userId,
            'plan_id' => $planId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => $status,
        ]);
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
