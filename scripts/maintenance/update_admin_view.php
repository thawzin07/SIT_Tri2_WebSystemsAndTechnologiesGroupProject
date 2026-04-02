<?php

chdir(dirname(__DIR__, 2));
/**
 * Update admin/trainers.php to use file input for image upload
 */

$adminTrainersFile = 'app/Views/admin/trainers.php';
$viewContent = <<<'HTML'
<section class="container page-shell">
  <h1 class="section-title">Manage Trainers</h1>
  <p class="section-subtitle">Maintain trainer profiles, specialties, and active status.</p>

  <form action="/admin/trainers/create" method="post" enctype="multipart/form-data" class="card p-3 mb-4">
    <?= csrf_input() ?>
    <h2 class="h5">Create Trainer</h2>
    <div class="row g-2">
      <div class="col-md-2"><input class="form-control" name="name" placeholder="Name" required></div>
      <div class="col-md-2"><input class="form-control" name="specialty" placeholder="Specialty" required></div>
      <div class="col-md-3"><input class="form-control" name="bio" placeholder="Bio" required></div>
      <div class="col-md-2"><input type="file" name="image" class="form-control" accept="image/*" placeholder="Photo"></div>
      <div class="col-md-1"><select name="status" class="form-select"><option>active</option><option>inactive</option></select></div>
      <div class="col-md-1"><button class="btn btn-brand w-100" type="submit">Add</button></div>
    </div>
  </form>

  <div class="table-responsive">
    <table class="table table-sm table-striped align-middle">
      <thead><tr><th>ID</th><th>Photo</th><th>Name</th><th>Specialty</th><th>Bio</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($trainers as $t): ?>
          <?php $hasPhoto = !empty($t['image_path']); ?>
          <tr>
            <form action="/admin/trainers/update" method="post" enctype="multipart/form-data">
              <?= csrf_input() ?>
              <td><?= (int)$t['id'] ?><input type="hidden" name="id" value="<?= (int)$t['id'] ?>"></td>
              <td>
                <div class="trainer-photo-preview" style="width: 40px; height: 40px; border-radius: 50%; overflow: hidden; background: #e0f2fe; display: inline-flex; align-items: center; justify-content: center;">
                  <?php if ($hasPhoto): ?>
                    <img src="<?= e($t['image_path']) ?>" alt="Trainer photo" style="width: 100%; height: 100%; object-fit: cover;">
                  <?php else: ?>
                    <span style="font-size: 0.7rem; color: #1e3a8a; font-weight: bold;">-</span>
                  <?php endif; ?>
                </div>
              </td>
              <td><input class="form-control form-control-sm" name="name" value="<?= e($t['name']) ?>" required></td>
              <td><input class="form-control form-control-sm" name="specialty" value="<?= e($t['specialty']) ?>" required></td>
              <td><input class="form-control form-control-sm" name="bio" value="<?= e($t['bio']) ?>" required></td>
              <td><select name="status" class="form-select form-select-sm"><option value="active" <?= $t['status']==='active'?'selected':'' ?>>active</option><option value="inactive" <?= $t['status']==='inactive'?'selected':'' ?>>inactive</option></select></td>
              <td class="d-flex gap-1">
                <input type="file" name="image" class="form-control form-control-sm" accept="image/*" style="max-width: 100px;">
                <button class="btn btn-sm btn-outline-primary" type="submit">Save</button>
            </form>
            <form action="/admin/trainers/delete" method="post">
              <?= csrf_input() ?>
              <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
              <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
            </form>
              </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
HTML;

file_put_contents($adminTrainersFile, $viewContent);
echo "✓ Updated app/Views/admin/trainers.php with file upload input\n";