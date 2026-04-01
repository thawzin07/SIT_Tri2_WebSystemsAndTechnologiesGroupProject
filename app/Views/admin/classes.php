<section class="container page-shell">
  <?php partial('back_button', ['label' => 'Back', 'fallback' => '/admin/dashboard']); ?>
  <h1 class="section-title">Manage Classes</h1>
  <p class="section-subtitle">Create class sessions, assign trainers and locations, and adjust schedules.</p>

  <div class="admin-toolbar">
    <label for="classes-filter" class="form-label mb-1">Search Classes</label>
    <input id="classes-filter" type="search" class="form-control" placeholder="Search by class, trainer, location, or status" data-table-filter="#classes-table">
  </div>

  <form action="/admin/classes/create" method="post" class="card p-3 mb-4">
    <?= csrf_input() ?>
    <h2 class="h5">Create Class</h2>
    <div class="row g-2">
      <div class="col-md-2"><input class="form-control" name="title" placeholder="Title" required></div>
      <div class="col-md-2"><select name="trainer_id" class="form-select" required><?php foreach($trainers as $tr): ?><option value="<?= (int)$tr['id'] ?>"><?= e($tr['name']) ?></option><?php endforeach; ?></select></div>
      <div class="col-md-2"><select name="location_id" class="form-select" required><?php foreach($locations as $lo): ?><option value="<?= (int)$lo['id'] ?>"><?= e($lo['name']) ?></option><?php endforeach; ?></select></div>
      <div class="col-md-2"><input type="date" class="form-control" name="class_date" required></div>
      <div class="col-md-1"><input type="time" class="form-control" name="start_time" required></div>
      <div class="col-md-1"><input type="time" class="form-control" name="end_time" required></div>
      <div class="col-md-1"><input type="number" class="form-control" name="capacity" placeholder="Cap" required></div>
      <div class="col-md-1"><select name="status" class="form-select"><option>active</option><option>inactive</option></select></div>
      <div class="col-12"><input class="form-control" name="description" placeholder="Description" required></div>
      <div class="col-12"><button class="btn btn-brand" type="submit">Add Class</button></div>
    </div>
  </form>

  <?php if (empty($classes)): ?>
    <div class="empty-state">No classes available. Add your first class above.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table id="classes-table" class="table table-sm table-striped align-middle">
        <thead><tr><th>ID</th><th>Title</th><th>Trainer</th><th>Location</th><th>Date</th><th>Time</th><th>Cap</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($classes as $c): ?>
            <tr>
              <form action="/admin/classes/update" method="post">
                <?= csrf_input() ?>
                <td><?= (int)$c['id'] ?><input type="hidden" name="id" value="<?= (int)$c['id'] ?>"></td>
                <td><input class="form-control form-control-sm" name="title" value="<?= e($c['title']) ?>" required><input type="hidden" name="description" value="<?= e($c['description']) ?>"></td>
                <td><select name="trainer_id" class="form-select form-select-sm"><?php foreach($trainers as $tr): ?><option value="<?= (int)$tr['id'] ?>" <?= (int)$tr['id']===(int)$c['trainer_id']?'selected':'' ?>><?= e($tr['name']) ?></option><?php endforeach; ?></select></td>
                <td><select name="location_id" class="form-select form-select-sm"><?php foreach($locations as $lo): ?><option value="<?= (int)$lo['id'] ?>" <?= (int)$lo['id']===(int)$c['location_id']?'selected':'' ?>><?= e($lo['name']) ?></option><?php endforeach; ?></select></td>
                <td><input type="date" class="form-control form-control-sm" name="class_date" value="<?= e($c['class_date']) ?>" required></td>
                <td><input type="time" class="form-control form-control-sm" name="start_time" value="<?= e(substr($c['start_time'],0,5)) ?>" required><input type="time" class="form-control form-control-sm mt-1" name="end_time" value="<?= e(substr($c['end_time'],0,5)) ?>" required></td>
                <td><input type="number" class="form-control form-control-sm" name="capacity" value="<?= (int)$c['capacity'] ?>" required></td>
                <td><select name="status" class="form-select form-select-sm"><option value="active" <?= $c['status']==='active'?'selected':'' ?>>active</option><option value="inactive" <?= $c['status']==='inactive'?'selected':'' ?>>inactive</option></select></td>
                <td class="d-flex gap-1">
                  <button class="btn btn-sm btn-outline-primary" type="submit">Save</button>
              </form>
              <form action="/admin/classes/delete" method="post">
                <?= csrf_input() ?>
                <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
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
