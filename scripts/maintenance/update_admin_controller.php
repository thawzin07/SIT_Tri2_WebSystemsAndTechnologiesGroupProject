<?php

chdir(dirname(__DIR__, 2));
/**
 * Update AdminController to add trainer image upload functionality
 */

$controllerFile = 'app/Controllers/AdminController.php';
$controllerContent = file_get_contents($controllerFile);

// Replace the createTrainer method to handle file uploads
$oldCreateTrainer = <<<'PHP'
    public function createTrainer(): void
    {
        $this->requireAdmin(); verify_csrf();
        (new TrainerModel())->create($this->trainerPayload());
        flash('success', 'Trainer created.'); redirect('/admin/trainers');
    }
PHP;

$newCreateTrainer = <<<'PHP'
    public function createTrainer(): void
    {
        $this->requireAdmin(); verify_csrf();
        $payload = $this->trainerPayload();
        if (isset($_FILES['image']) && is_array($_FILES['image'])) {
            $errorCode = (int) ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE);
            if ($errorCode !== UPLOAD_ERR_NO_FILE) {
                try {
                    $payload['image_path'] = $this->storeTrainerImage($_FILES['image']);
                } catch (\RuntimeException $ex) {
                    flash('error', $ex->getMessage());
                    redirect('/admin/trainers');
                }
            }
        }
        (new TrainerModel())->create($payload);
        flash('success', 'Trainer created.'); redirect('/admin/trainers');
    }
PHP;

$controllerContent = str_replace($oldCreateTrainer, $newCreateTrainer, $controllerContent);

// Replace the updateTrainer method
$oldUpdateTrainer = <<<'PHP'
    public function updateTrainer(): void
    {
        $this->requireAdmin(); verify_csrf();
        (new TrainerModel())->update((int) $_POST['id'], $this->trainerPayload());
        flash('success', 'Trainer updated.'); redirect('/admin/trainers');
    }
PHP;

$newUpdateTrainer = <<<'PHP'
    public function updateTrainer(): void
    {
        $this->requireAdmin(); verify_csrf();
        $id = (int) $_POST['id'];
        $trainerModel = new TrainerModel();
        $existing = $trainerModel->find($id);
        if (!$existing) {
            flash('error', 'Trainer not found.');
            redirect('/admin/trainers');
        }
        
        $payload = $this->trainerPayload();
        $payload['image_path'] = (string) ($existing['image_path'] ?? '');
        
        if (isset($_FILES['image']) && is_array($_FILES['image'])) {
            $errorCode = (int) ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE);
            if ($errorCode !== UPLOAD_ERR_NO_FILE) {
                try {
                    $payload['image_path'] = $this->storeTrainerImage($_FILES['image'], (string) ($existing['image_path'] ?? ''));
                } catch (\RuntimeException $ex) {
                    flash('error', $ex->getMessage());
                    redirect('/admin/trainers');
                }
            }
        }
        
        $trainerModel->update($id, $payload);
        flash('success', 'Trainer updated.'); redirect('/admin/trainers');
    }
PHP;

$controllerContent = str_replace($oldUpdateTrainer, $newUpdateTrainer, $controllerContent);

// Replace deleteTrainer
$oldDeleteTrainer = <<<'PHP'
    public function deleteTrainer(): void
    {
        $this->requireAdmin(); verify_csrf();
        (new TrainerModel())->delete((int) $_POST['id']);
        flash('success', 'Trainer deleted.'); redirect('/admin/trainers');
    }
PHP;

$newDeleteTrainer = <<<'PHP'
    public function deleteTrainer(): void
    {
        $this->requireAdmin(); verify_csrf();
        $id = (int) $_POST['id'];
        $trainer = (new TrainerModel())->find($id);
        if ($trainer) {
            $this->removeTrainerImage((string) ($trainer['image_path'] ?? ''));
        }
        (new TrainerModel())->delete($id);
        flash('success', 'Trainer deleted.'); redirect('/admin/trainers');
    }
PHP;

$controllerContent = str_replace($oldDeleteTrainer, $newDeleteTrainer, $controllerContent);

// Replace trainerPayload method
$oldPayload = <<<'PHP'
    private function trainerPayload(): array
    {
        return [
            'name' => trim((string) $_POST['name']),
            'specialty' => trim((string) $_POST['specialty']),
            'bio' => trim((string) $_POST['bio']),
            'image_path' => trim((string) ($_POST['image_path'] ?? '')),
            'status' => ($_POST['status'] ?? 'inactive') === 'active' ? 'active' : 'inactive',
        ];
    }
PHP;

$newPayload = <<<'PHP'
    private function trainerPayload(): array
    {
        return [
            'name' => trim((string) $_POST['name']),
            'specialty' => trim((string) $_POST['specialty']),
            'bio' => trim((string) $_POST['bio']),
            'image_path' => '',
            'status' => ($_POST['status'] ?? 'inactive') === 'active' ? 'active' : 'inactive',
        ];
    }
PHP;

$controllerContent = str_replace($oldPayload, $newPayload, $controllerContent);

// Add the image upload helper methods before the closing brace
$closingBrace = "\n}";
$newMethods = <<<'PHP'

    private function storeTrainerImage(array $file, string $existingImagePath = ''): string
    {
        $errorCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($errorCode !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Unable to upload image. Please try a different file.');
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size < 1 || $size > (5 * 1024 * 1024)) {
            throw new \RuntimeException('Trainer image must be under 5MB.');
        }

        $tmpName = (string) ($file['tmp_name'] ?? '');
        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            throw new \RuntimeException('Invalid upload source.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = (string) $finfo->file($tmpName);
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];

        if (!isset($allowed[$mimeType])) {
            throw new \RuntimeException('Only JPG, PNG, WEBP, or GIF images are allowed.');
        }

        $relativeDirectory = '/assets/images/trainers';
        $absoluteDirectory = dirname(__DIR__, 2) . '/public' . $relativeDirectory;
        if (!is_dir($absoluteDirectory) && !mkdir($absoluteDirectory, 0775, true) && !is_dir($absoluteDirectory)) {
            throw new \RuntimeException('Unable to prepare trainer image directory.');
        }

        $fileName = 'trainer-' . date('YmdHis') . '-' . bin2hex(random_bytes(3)) . '.' . $allowed[$mimeType];
        $absolutePath = $absoluteDirectory . '/' . $fileName;
        if (!move_uploaded_file($tmpName, $absolutePath)) {
            throw new \RuntimeException('Failed to save uploaded image.');
        }

        $this->removeTrainerImage($existingImagePath);

        return $relativeDirectory . '/' . $fileName;
    }

    private function removeTrainerImage(string $relativePath): void
    {
        if ($relativePath === '' || !str_starts_with($relativePath, '/assets/images/trainers/')) {
            return;
        }

        $absolutePath = dirname(__DIR__, 2) . '/public' . $relativePath;
        if (is_file($absolutePath)) {
            @unlink($absolutePath);
        }
    }
PHP;

$controllerContent = str_replace($closingBrace, $newMethods . $closingBrace, $controllerContent);

file_put_contents($controllerFile, $controllerContent);
echo "✓ Updated AdminController.php with image upload methods\n";