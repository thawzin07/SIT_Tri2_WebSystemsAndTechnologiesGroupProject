<?php

namespace App\Models;

class ContactMessageModel extends BaseModel
{
    public function create(array $data): void
    {
        $stmt = $this->db->prepare('INSERT INTO contact_messages (name, email, subject, message, created_at) VALUES (:name, :email, :subject, :message, NOW())');
        $stmt->execute($data);
    }

    public function all(): array
    {
        $stmt = $this->db->query('SELECT * FROM contact_messages ORDER BY id DESC');
        return $stmt->fetchAll();
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM contact_messages WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
