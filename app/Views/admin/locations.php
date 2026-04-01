<section class="container page-shell">
  <h1 class="section-title">Manage Locations</h1>
  <p class="section-subtitle">Maintain branch addresses, contact numbers, and operating hours.</p>

  <form action="/admin/locations/create" method="post" enctype="multipart/form-data" class="card p-3 mb-4">
    <?= csrf_input() ?>
    <h2 class="h5">Create Location</h2>
    <div class="row g-2 location-create-row align-items-end">
      <div class="col-12 col-md-6 col-lg-2"><input class="form-control" name="name" placeholder="Name" required></div>
      <div class="col-12 col-md-6 col-lg-3"><input class="form-control" name="address" placeholder="Address" required></div>
      <div class="col-12 col-md-6 col-lg-2"><input class="form-control" name="phone" placeholder="Phone" required></div>
      <div class="col-12 col-md-6 col-lg-2"><input class="form-control" name="opening_hours" placeholder="Opening hours" required></div>
      <div class="col-12 col-md-6 col-lg-2">
        <label for="create-location-image" class="btn btn-outline-secondary w-100 location-file-btn">Choose File</label>
        <input id="create-location-image" type="file" name="image" class="location-image-input visually-hidden" accept="image/*">
      </div>
      <div class="col-12 col-md-6 col-lg-1"><select name="status" class="form-select"><option>active</option><option>inactive</option></select></div>
      <div class="col-12 col-md-6 col-lg-auto ms-lg-auto"><button class="btn btn-brand w-100" type="submit">Add</button></div>
    </div>
  </form>

  <div class="table-responsive">
    <table class="table table-sm table-striped align-middle">
      <thead><tr><th>ID</th><th>Photo</th><th>Name</th><th>Address</th><th>Phone</th><th>Hours</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($locations as $l): ?>
          <?php $hasPhoto = !empty($l['image_path']); ?>
          <tr>
            <form action="/admin/locations/update" method="post" enctype="multipart/form-data">
              <?= csrf_input() ?>
              <td><?= (int)$l['id'] ?><input type="hidden" name="id" value="<?= (int)$l['id'] ?>"></td>
              <td>
                <div id="location-photo-preview-<?= (int)$l['id'] ?>" class="location-photo-preview" data-empty-text="-">
                  <?php if ($hasPhoto): ?>
                    <img src="<?= e($l['image_path']) ?>" alt="Location photo" class="location-photo-img">
                  <?php else: ?>
                    <span class="location-photo-placeholder">-</span>
                  <?php endif; ?>
                </div>
              </td>
              <td><input class="form-control form-control-sm" name="name" value="<?= e($l['name']) ?>" required></td>
              <td><input class="form-control form-control-sm" name="address" value="<?= e($l['address']) ?>" required></td>
              <td><input class="form-control form-control-sm" name="phone" value="<?= e($l['phone']) ?>" required></td>
              <td><input class="form-control form-control-sm" name="opening_hours" value="<?= e($l['opening_hours']) ?>" required></td>
              <td><select name="status" class="form-select form-select-sm"><option value="active" <?= $l['status']==='active'?'selected':'' ?>>active</option><option value="inactive" <?= $l['status']==='inactive'?'selected':'' ?>>inactive</option></select></td>
              <td class="location-actions-cell">
                <div class="location-actions">
                  <label for="location-image-<?= (int)$l['id'] ?>" class="btn btn-outline-secondary location-file-btn">Choose File</label>
                  <input id="location-image-<?= (int)$l['id'] ?>" type="file" name="image" class="location-image-input visually-hidden" accept="image/*" data-preview-target="#location-photo-preview-<?= (int)$l['id'] ?>">
                  <button class="btn btn-sm btn-outline-primary" type="submit">Save</button>
                </div>
            </form>
            <form action="/admin/locations/delete" method="post" class="location-delete-form">
              <?= csrf_input() ?>
              <input type="hidden" name="id" value="<?= (int)$l['id'] ?>">
              <button class="btn btn-sm btn-outline-danger location-delete-btn" type="submit">Delete</button>
            </form>
              </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
