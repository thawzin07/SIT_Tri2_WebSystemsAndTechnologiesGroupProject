<?php

namespace App\Models;

class UserModel extends BaseModel
{
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }

    public function findWithRole(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT u.*, r.name AS role_name FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO users (role_id, full_name, email, password_hash, phone, created_at, updated_at) VALUES (:role_id, :full_name, :email, :password_hash, :phone, NOW(), NOW())');
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function allWithRole(): array
    {
        $stmt = $this->db->query('SELECT u.id, u.full_name, u.email, u.phone, r.name AS role_name, u.created_at FROM users u JOIN roles r ON r.id = u.role_id ORDER BY u.id DESC');
        return $stmt->fetchAll();
    }

    public function updateBasic(int $id, string $fullName, string $phone): void
    {
        $stmt = $this->db->prepare('UPDATE users SET full_name = :full_name, phone = :phone, updated_at = NOW() WHERE id = :id');
        $stmt->execute(['full_name' => $fullName, 'phone' => $phone, 'id' => $id]);
    }

    public function updateBasicWithImage(int $id, string $fullName, string $phone, ?string $profileImagePath): void
    {
        $stmt = $this->db->prepare('UPDATE users
            SET full_name = :full_name,
                phone = :phone,
                profile_image_path = :profile_image_path,
                updated_at = NOW()
            WHERE id = :id');
        $stmt->execute([
            'full_name' => $fullName,
            'phone' => $phone,
            'profile_image_path' => $profileImagePath,
            'id' => $id,
        ]);
    }

    public function updateByAdmin(int $id, int $roleId, string $fullName, string $email, string $phone): void
    {
        $stmt = $this->db->prepare('UPDATE users SET role_id = :role_id, full_name = :full_name, email = :email, phone = :phone, updated_at = NOW() WHERE id = :id');
        $stmt->execute(['role_id' => $roleId, 'full_name' => $fullName, 'email' => $email, 'phone' => $phone, 'id' => $id]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
