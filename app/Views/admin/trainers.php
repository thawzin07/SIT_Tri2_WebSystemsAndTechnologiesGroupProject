<section class="container page-shell">
  <?php partial('back_button', ['label' => 'Back', 'fallback' => '/admin/dashboard']); ?>
  <h1 class="section-title">Manage Trainers</h1>
  <p class="section-subtitle">Maintain trainer profiles, specialties, and active status.</p>

  <form action="/admin/trainers/create" method="post" enctype="multipart/form-data" class="card p-3 mb-4">
    <?= csrf_input() ?>
    <h2 class="h5">Create Trainer</h2>
    <div class="row g-2">
      <div class="col-md-2"><input class="form-control" name="name" placeholder="Name" required></div>
      <div class="col-md-2"><input class="form-control" name="specialty" placeholder="Specialty" required></div>
      <div class="col-md-3"><input class="form-control" name="bio" placeholder="Bio" required></div>
      <div class="col-md-2">
        <label for="create-trainer-image" class="btn btn-outline-secondary w-100 trainer-file-btn">Choose File</label>
        <input id="create-trainer-image" type="file" name="image" class="trainer-image-input visually-hidden" accept="image/*" data-preview-target="#create-trainer-photo-preview">
      </div>
      <div class="col-md-1 d-flex align-items-center justify-content-center">
        <div id="create-trainer-photo-preview" class="trainer-photo-preview" data-empty-text="-">
          <span class="trainer-photo-placeholder">-</span>
        </div>
      </div>
      <div class="col-md-1"><select name="status" class="form-select"><option>active</option><option>inactive</option></select></div>
      <div class="col-md-1"><button class="btn btn-brand w-100" type="submit">Add</button></div>
    </div>
  </form>

  <div class="table-responsive">
    <table class="table table-sm table-striped align-middle">
      <thead><tr><th>ID</th><th>Photo</th><th>Name</th><th>Specialty</th><th>Bio</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($trainers as $t): ?>
          <?php
            $trainerImage = media_url((string) ($t['image_path'] ?? ''), 'trainer');
            $hasPhoto = $trainerImage !== '';
          ?>
          <tr>
            <form action="/admin/trainers/update" method="post" enctype="multipart/form-data">
              <?= csrf_input() ?>
              <td><?= (int)$t['id'] ?><input type="hidden" name="id" value="<?= (int)$t['id'] ?>"></td>
              <td>
                <div id="trainer-photo-preview-<?= (int)$t['id'] ?>" class="trainer-photo-preview" data-empty-text="-">
                  <?php if ($hasPhoto): ?>
                    <img src="<?= e($trainerImage) ?>" alt="Trainer photo" class="trainer-photo-img">
                  <?php else: ?>
                    <span class="trainer-photo-placeholder">-</span>
                  <?php endif; ?>
                </div>
              </td>
              <td><input class="form-control form-control-sm" name="name" value="<?= e($t['name']) ?>" required></td>
              <td><input class="form-control form-control-sm" name="specialty" value="<?= e($t['specialty']) ?>" required></td>
              <td><input class="form-control form-control-sm" name="bio" value="<?= e($t['bio']) ?>" required></td>
              <td><select name="status" class="form-select form-select-sm"><option value="active" <?= $t['status']==='active'?'selected':'' ?>>active</option><option value="inactive" <?= $t['status']==='inactive'?'selected':'' ?>>inactive</option></select></td>
              <td class="trainer-actions-cell">
                <div class="trainer-actions">
                  <label for="trainer-image-<?= (int)$t['id'] ?>" class="btn btn-sm btn-outline-secondary trainer-file-btn">Choose File</label>
                  <input id="trainer-image-<?= (int)$t['id'] ?>" type="file" name="image" class="trainer-image-input visually-hidden" accept="image/*" data-preview-target="#trainer-photo-preview-<?= (int)$t['id'] ?>">
                  <button class="btn btn-sm btn-outline-primary" type="submit">Save</button>
                </div>
            </form>
            <form action="/admin/trainers/delete" method="post" class="trainer-delete-form">
              <?= csrf_input() ?>
              <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
              <button class="btn btn-sm btn-outline-danger trainer-delete-btn" type="submit">Delete</button>
            </form>
              </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
