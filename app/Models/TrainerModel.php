<?php

namespace App\Models;

class TrainerModel extends BaseModel
{
    public function active(): array
    {
        $stmt = $this->db->prepare('SELECT * FROM trainers WHERE status = :status ORDER BY id DESC');
        $stmt->execute(['status' => 'active']);
        return $stmt->fetchAll();
    }

    public function all(): array
    {
        $stmt = $this->db->query('SELECT * FROM trainers ORDER BY id DESC');
        return $stmt->fetchAll();
    }

    public function create(array $data): void
    {
        $stmt = $this->db->prepare('INSERT INTO trainers (name, specialty, bio, image_path, status, created_at, updated_at) VALUES (:name, :specialty, :bio, :image_path, :status, NOW(), NOW())');
        $stmt->execute($data);
    }

    public function update(int $id, array $data): void
    {
        $data['id'] = $id;
        $stmt = $this->db->prepare('UPDATE trainers SET name = :name, specialty = :specialty, bio = :bio, image_path = :image_path, status = :status, updated_at = NOW() WHERE id = :id');
        $stmt->execute($data);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM trainers WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
