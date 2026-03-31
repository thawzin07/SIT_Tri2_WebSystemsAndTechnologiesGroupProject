<?php
/**
 * Setup script to add trainer photo upload functionality
 */

// Update TrainerModel to add find method
$trainerModelCode = <<<'PHP'
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

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM trainers WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
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
PHP;

file_put_contents('app/Models/TrainerModel.php', $trainerModelCode);
echo "✓ Updated TrainerModel.php\n";

// Show administrator nextSteps
echo "\nNext steps to complete trainer photo setup:\n";
echo "1. Update AdminController.php - Add image upload handling methods\n";
echo "2. Update admin/trainers.php view - Change image_path text input to file input\n";
echo "3. Update pages/trainers.php view - Add trainer photos to card display\n";
echo "4. Add CSS styling for trainer cards\n";
echo "\n✓ Directory already created: public/assets/images/trainers/\n";
