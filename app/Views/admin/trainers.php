<section class="container page-shell">
  <h1 class="section-title">Manage Trainers</h1>
  <p class="section-subtitle">Maintain trainer profiles, specialties, and active status.</p>

  <form action="/admin/trainers/create" method="post" class="card p-3 mb-4">
    <?= csrf_input() ?>
    <h2 class="h5">Create Trainer</h2>
    <div class="row g-2">
      <div class="col-md-2"><input class="form-control" name="name" placeholder="Name" required></div>
      <div class="col-md-2"><input class="form-control" name="specialty" placeholder="Specialty" required></div>
      <div class="col-md-4"><input class="form-control" name="bio" placeholder="Bio" required></div>
      <div class="col-md-2"><input class="form-control" name="image_path" placeholder="Image path"></div>
      <div class="col-md-1"><select name="status" class="form-select"><option>active</option><option>inactive</option></select></div>
      <div class="col-md-1"><button class="btn btn-brand w-100" type="submit">Add</button></div>
    </div>
  </form>

  <div class="table-responsive">
    <table class="table table-sm table-striped align-middle">
      <thead><tr><th>ID</th><th>Name</th><th>Specialty</th><th>Bio</th><th>Image</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($trainers as $t): ?>
          <tr>
            <form action="/admin/trainers/update" method="post">
              <?= csrf_input() ?>
              <td><?= (int)$t['id'] ?><input type="hidden" name="id" value="<?= (int)$t['id'] ?>"></td>
              <td><input class="form-control form-control-sm" name="name" value="<?= e($t['name']) ?>" required></td>
              <td><input class="form-control form-control-sm" name="specialty" value="<?= e($t['specialty']) ?>" required></td>
              <td><input class="form-control form-control-sm" name="bio" value="<?= e($t['bio']) ?>" required></td>
              <td><input class="form-control form-control-sm" name="image_path" value="<?= e($t['image_path']) ?>"></td>
              <td><select name="status" class="form-select form-select-sm"><option value="active" <?= $t['status']==='active'?'selected':'' ?>>active</option><option value="inactive" <?= $t['status']==='inactive'?'selected':'' ?>>inactive</option></select></td>
              <td class="d-flex gap-1">
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
