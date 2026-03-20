<section class="container page-shell">
  <h1 class="section-title">Manage Locations</h1>
  <p class="section-subtitle">Maintain branch addresses, contact numbers, and operating hours.</p>

  <form action="/admin/locations/create" method="post" class="card p-3 mb-4">
    <?= csrf_input() ?>
    <h2 class="h5">Create Location</h2>
    <div class="row g-2">
      <div class="col-md-2"><input class="form-control" name="name" placeholder="Name" required></div>
      <div class="col-md-4"><input class="form-control" name="address" placeholder="Address" required></div>
      <div class="col-md-2"><input class="form-control" name="phone" placeholder="Phone" required></div>
      <div class="col-md-2"><input class="form-control" name="opening_hours" placeholder="Opening hours" required></div>
      <div class="col-md-1"><select name="status" class="form-select"><option>active</option><option>inactive</option></select></div>
      <div class="col-md-1"><button class="btn btn-brand w-100" type="submit">Add</button></div>
    </div>
  </form>

  <div class="table-responsive">
    <table class="table table-sm table-striped align-middle">
      <thead><tr><th>ID</th><th>Name</th><th>Address</th><th>Phone</th><th>Hours</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($locations as $l): ?>
          <tr>
            <form action="/admin/locations/update" method="post">
              <?= csrf_input() ?>
              <td><?= (int)$l['id'] ?><input type="hidden" name="id" value="<?= (int)$l['id'] ?>"></td>
              <td><input class="form-control form-control-sm" name="name" value="<?= e($l['name']) ?>" required></td>
              <td><input class="form-control form-control-sm" name="address" value="<?= e($l['address']) ?>" required></td>
              <td><input class="form-control form-control-sm" name="phone" value="<?= e($l['phone']) ?>" required></td>
              <td><input class="form-control form-control-sm" name="opening_hours" value="<?= e($l['opening_hours']) ?>" required></td>
              <td><select name="status" class="form-select form-select-sm"><option value="active" <?= $l['status']==='active'?'selected':'' ?>>active</option><option value="inactive" <?= $l['status']==='inactive'?'selected':'' ?>>inactive</option></select></td>
              <td class="d-flex gap-1">
                <button class="btn btn-sm btn-outline-primary">Save</button>
            </form>
            <form action="/admin/locations/delete" method="post">
              <?= csrf_input() ?>
              <input type="hidden" name="id" value="<?= (int)$l['id'] ?>">
              <button class="btn btn-sm btn-outline-danger">Delete</button>
            </form>
              </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
