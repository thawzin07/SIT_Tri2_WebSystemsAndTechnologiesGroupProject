<?php

namespace App\Models;

class LocationModel extends BaseModel
{
    public function active(): array
    {
        $stmt = $this->db->prepare('SELECT * FROM gym_locations WHERE status = :status ORDER BY id DESC');
        $stmt->execute(['status' => 'active']);
        return $stmt->fetchAll();
    }

    public function all(): array
    {
        $stmt = $this->db->query('SELECT * FROM gym_locations ORDER BY id DESC');
        return $stmt->fetchAll();
    }

    public function create(array $data): void
    {
        $stmt = $this->db->prepare('INSERT INTO gym_locations (name, address, phone, opening_hours, status, latitude, longitude, map_place_id, image_path, created_at, updated_at) VALUES (:name, :address, :phone, :opening_hours, :status, :latitude, :longitude, :map_place_id, :image_path, NOW(), NOW())');
        $stmt->execute($data);
    }

    public function update(int $id, array $data): void
    {
        $data['id'] = $id;
        $stmt = $this->db->prepare('UPDATE gym_locations SET name = :name, address = :address, phone = :phone, opening_hours = :opening_hours, status = :status, latitude = :latitude, longitude = :longitude, map_place_id = :map_place_id, image_path = :image_path, updated_at = NOW() WHERE id = :id');
        $stmt->execute($data);
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM gym_locations WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
