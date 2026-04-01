<section class="container page-shell">
  <h1 class="section-title">Class Schedule</h1>
  <p class="section-subtitle">Browse upcoming sessions by date, trainer, and location. Members can book directly from this page.</p>
  
  <!-- Filter Form -->
  <form method="get" class="card p-3 mb-4">
    <h2 class="h6 mb-3">Filter Classes</h2>
    <div class="row g-3">
      <div class="col-md-4">
        <label for="date_from" class="form-label">Date from</label>
        <input type="date" id="date_from" name="date_from" class="form-control" value="<?= e($filters['date_from']) ?>">
      </div>
      <div class="col-md-4">
        <label for="date_to" class="form-label">Date to</label>
        <input type="date" id="date_to" name="date_to" class="form-control" value="<?= e($filters['date_to']) ?>">
      </div>
      <div class="col-md-4">
        <label for="date" class="form-label">Specific date (optional)</label>
        <input type="date" id="date" name="date" class="form-control" value="<?= e($filters['date']) ?>">
      </div>
      <div class="col-md-4">
        <label for="trainer" class="form-label">Trainer</label>
        <select id="trainer" name="trainer" class="form-select">
          <option value="">All Trainers</option>
          <?php foreach ($trainers as $trainer): ?>
            <option value="<?= (int)$trainer['id'] ?>" <?= $filters['trainer'] == $trainer['id'] ? 'selected' : '' ?>>
              <?= e($trainer['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label for="location" class="form-label">Location</label>
        <select id="location" name="location" class="form-select">
          <option value="">All Locations</option>
          <?php foreach ($locations as $location): ?>
            <option value="<?= (int)$location['id'] ?>" <?= $filters['location'] == $location['id'] ? 'selected' : '' ?>>
              <?= e($location['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
    <div class="mt-3">
      <button type="submit" class="btn btn-primary">Filter</button>
      <a href="/schedule" class="btn btn-outline-secondary">Clear Filters</a>
    </div>
  </form>
  
  <?php $scheduleUser = current_user(); ?>
  <?php $isMemberUser = is_member(); ?>
  <?php if (empty($classes)): ?>
    <div class="empty-state" role="status">No classes scheduled yet. Check back soon for new sessions.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-striped align-middle">
        <thead><tr><th>Class</th><th>Date</th><th>Time</th><th>Trainer</th><th>Location</th><th>Availability</th><th>Action</th></tr></thead>
        <tbody>
          <?php foreach ($classes as $class): ?>
            <?php $seatsLeft = max(0, (int)$class['capacity'] - (int)$class['booked_count']); ?>
            <tr>
              <td><?= e($class['title']) ?></td>
              <td><?= e($class['class_date']) ?></td>
              <td><?= e(substr($class['start_time'],0,5)) ?> - <?= e(substr($class['end_time'],0,5)) ?></td>
              <td><?= e($class['trainer_name']) ?></td>
              <td><?= e($class['location_name']) ?></td>
              <td>
                <span class="badge-soft <?= $seatsLeft > 0 ? 'success' : 'warning' ?>">
                  <?= $seatsLeft > 0 ? 'Open' : 'Full' ?>
                </span>
                <span class="ms-2"><?= $seatsLeft ?> / <?= (int)$class['capacity'] ?></span>
                <?php if ((int)$class['waitlist_count'] > 0): ?>
                  <div><small><?= (int)$class['waitlist_count'] ?> waiting</small></div>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($isMemberUser): ?>
                  <form action="/member/bookings/book" method="post">
                    <?= csrf_input() ?>
                    <input type="hidden" name="class_id" value="<?= (int)$class['id'] ?>">
                    <input type="hidden" name="redirect_to" value="/schedule">
                    <button class="btn btn-sm btn-brand" type="submit"><?= $seatsLeft > 0 ? 'Book' : 'Join Waitlist' ?></button>
                  </form>
                <?php elseif ($scheduleUser === null): ?>
                  <div class="d-flex gap-2 flex-wrap">
                    <a class="btn btn-sm btn-outline-primary" href="/login">Log In to Book</a>
                    <a class="btn btn-sm btn-outline-secondary" href="/register">Register</a>
                  </div>
                <?php else: ?>
                  <span class="text-muted small">Member account required for booking.</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>
