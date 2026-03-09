<?php

namespace App\Models;

class MembershipPlanModel extends BaseModel
{
    public function activePlans(): array
    {
        $stmt = $this->db->prepare('SELECT * FROM membership_plans WHERE status = :status ORDER BY price ASC');
        $stmt->execute(['status' => 'active']);
        return $stmt->fetchAll();
    }

    public function all(): array
    {
        $stmt = $this->db->query('SELECT * FROM membership_plans ORDER BY id DESC');
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM membership_plans WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): void
    {
        $stmt = $this->db->prepare('INSERT INTO membership_plans (name, price, duration_months, description, status, created_at, updated_at) VALUES (:name, :price, :duration_months, :description, :status, NOW(), NOW())');
        $stmt->execute($data);
    }

    public function update(int $id, array $data): void
    {
        $data['id'] = $id;
        $stmt = $this->db->prepare('UPDATE membership_plans SET name = :name, price = :price, duration_months = :duration_months, description = :description, status = :status, updated_at = NOW() WHERE id = :id');
        $stmt->execute($data);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM membership_plans WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
