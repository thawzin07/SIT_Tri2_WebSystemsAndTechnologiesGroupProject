<section class="container page-shell">
  <?php partial('back_button', ['label' => 'Back', 'fallback' => '/admin/dashboard']); ?>
  <h1 class="section-title">Manage Users</h1>
  <p class="section-subtitle">Create users, update profile details, and manage role access.</p>

  <div class="admin-toolbar">
    <label for="users-filter" class="form-label mb-1">Search Users</label>
    <input id="users-filter" type="search" class="form-control" placeholder="Search by name, email, phone, or role" data-table-filter="#users-table">
  </div>

  <form action="/admin/users/create" method="post" class="card p-3 mb-4">
    <?= csrf_input() ?>
    <h2 class="h5">Create User</h2>
    <div class="row g-2">
      <div class="col-md-3"><input class="form-control" name="full_name" placeholder="Full Name" required></div>
      <div class="col-md-3"><input type="email" class="form-control" name="email" placeholder="Email" required></div>
      <div class="col-md-2"><input class="form-control" name="phone" placeholder="Phone"></div>
      <div class="col-md-2"><input type="password" class="form-control" name="password" placeholder="Password" required></div>
      <div class="col-md-1"><select name="role_id" class="form-select"><option value="2">Member</option><option value="1">Admin</option></select></div>
      <div class="col-md-1"><button class="btn btn-brand w-100" type="submit">Add</button></div>
    </div>
  </form>

  <?php if (empty($users)): ?>
    <div class="empty-state">No users found.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table id="users-table" class="table table-striped table-sm align-middle">
        <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($users as $u): ?>
            <tr>
              <form action="/admin/users/update" method="post">
                <?= csrf_input() ?>
                <td><?= (int)$u['id'] ?><input type="hidden" name="id" value="<?= (int)$u['id'] ?>"></td>
                <td><input class="form-control form-control-sm" name="full_name" value="<?= e($u['full_name']) ?>" required></td>
                <td><input class="form-control form-control-sm" name="email" value="<?= e($u['email']) ?>" required></td>
                <td><input class="form-control form-control-sm" name="phone" value="<?= e($u['phone']) ?>"></td>
                <td><select name="role_id" class="form-select form-select-sm"><option value="1" <?= $u['role_name']==='admin'?'selected':'' ?>>Admin</option><option value="2" <?= $u['role_name']==='member'?'selected':'' ?>>Member</option></select></td>
                <td class="d-flex gap-1">
                  <button class="btn btn-sm btn-outline-primary" type="submit">Save</button>
              </form>
              <form action="/admin/users/delete" method="post">
                <?= csrf_input() ?>
                <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
              </form>
                </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>
