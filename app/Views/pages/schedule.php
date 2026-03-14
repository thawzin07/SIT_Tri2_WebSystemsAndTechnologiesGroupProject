<section class="container py-5">
  <h1 class="section-title">Class Schedule</h1>
  <?php if (empty($classes)): ?>
    <div class="empty-state" role="status">No classes scheduled yet. Check back soon for new sessions.</div>
  <?php else: ?>
  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead><tr><th>Class</th><th>Date</th><th>Time</th><th>Trainer</th><th>Location</th><th>Availability</th></tr></thead>
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
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</section>
