<section class="container py-5">
  <h1 class="section-title">Manage Bookings</h1>
  <div class="admin-toolbar">
    <label for="bookings-filter" class="form-label mb-1">Search Bookings</label>
    <input id="bookings-filter" type="search" class="form-control" placeholder="Search by member, class, date, or status" data-table-filter="#bookings-table">
  </div>
  <?php if (empty($bookings)): ?>
    <div class="empty-state">No bookings available.</div>
  <?php else: ?>
  <div class="table-responsive"><table id="bookings-table" class="table table-sm table-striped align-middle"><thead><tr><th>ID</th><th>Member</th><th>Email</th><th>Class</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead><tbody>
  <?php foreach ($bookings as $b): ?>
    <tr><td><?= (int)$b['id'] ?></td><td><?= e($b['full_name']) ?></td><td><?= e($b['email']) ?></td><td><?= e($b['title']) ?></td><td><?= e($b['class_date']) ?> <?= e(substr($b['start_time'],0,5)) ?></td><td><?= e($b['booking_status']) ?></td><td class="d-flex gap-1"><form action="/admin/bookings/update" method="post" class="d-flex gap-1"><?= csrf_input() ?><input type="hidden" name="id" value="<?= (int)$b['id'] ?>"><select name="booking_status" class="form-select form-select-sm"><option value="booked" <?= $b['booking_status']==='booked'?'selected':'' ?>>booked</option><option value="cancelled" <?= $b['booking_status']==='cancelled'?'selected':'' ?>>cancelled</option><option value="completed" <?= $b['booking_status']==='completed'?'selected':'' ?>>completed</option></select><button class="btn btn-sm btn-outline-primary">Save</button></form><form action="/admin/bookings/delete" method="post"><?= csrf_input() ?><input type="hidden" name="id" value="<?= (int)$b['id'] ?>"><button class="btn btn-sm btn-outline-danger">Delete</button></form></td></tr>
  <?php endforeach; ?>
  </tbody></table></div>
  <?php endif; ?>
</section>
