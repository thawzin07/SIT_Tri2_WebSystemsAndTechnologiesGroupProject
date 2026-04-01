<section class="container page-shell">
  <?php partial('back_button', ['label' => 'Back', 'fallback' => '/admin/dashboard']); ?>
  <h1 class="section-title">Manage Membership Plans</h1>
  <p class="section-subtitle">Create and update available subscription plans and pricing.</p>

  <form action="/admin/plans/create" method="post" class="card p-3 mb-4">
    <?= csrf_input() ?>
    <h2 class="h5">Create Plan</h2>
    <div class="row g-2">
      <div class="col-md-2"><input class="form-control" name="name" placeholder="Name" required></div>
      <div class="col-md-2"><input type="number" step="0.01" class="form-control" name="price" placeholder="Price" required></div>
      <div class="col-md-2"><input type="number" class="form-control" name="duration_months" placeholder="Months" required></div>
      <div class="col-md-4"><input class="form-control" name="description" placeholder="Description" required></div>
      <div class="col-md-1"><select name="status" class="form-select"><option>active</option><option>inactive</option></select></div>
      <div class="col-md-1"><button class="btn btn-brand w-100" type="submit">Add</button></div>
    </div>
  </form>

  <div class="table-responsive">
    <table class="table table-sm table-striped align-middle">
      <thead><tr><th>ID</th><th>Name</th><th>Price</th><th>Months</th><th>Description</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($plans as $p): ?>
          <tr>
            <form action="/admin/plans/update" method="post">
              <?= csrf_input() ?>
              <td><?= (int)$p['id'] ?><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"></td>
              <td><input class="form-control form-control-sm" name="name" value="<?= e($p['name']) ?>" required></td>
              <td><input type="number" step="0.01" class="form-control form-control-sm" name="price" value="<?= e((string)$p['price']) ?>" required></td>
              <td><input type="number" class="form-control form-control-sm" name="duration_months" value="<?= (int)$p['duration_months'] ?>" required></td>
              <td><input class="form-control form-control-sm" name="description" value="<?= e($p['description']) ?>" required></td>
              <td><select name="status" class="form-select form-select-sm"><option value="active" <?= $p['status']==='active'?'selected':'' ?>>active</option><option value="inactive" <?= $p['status']==='inactive'?'selected':'' ?>>inactive</option></select></td>
              <td class="d-flex gap-1">
                <button class="btn btn-sm btn-outline-primary" type="submit">Save</button>
            </form>
            <form action="/admin/plans/delete" method="post">
              <?= csrf_input() ?>
              <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
              <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
            </form>
              </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>
