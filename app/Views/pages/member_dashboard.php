<section class="container py-5">
  <h1 class="section-title">Member Dashboard</h1>
  <div class="row g-3 mb-4">
    <div class="col-md-6">
      <div class="card border-0 shadow-sm p-3 h-100">
        <h2 class="h5">Membership Status</h2>
        <?php if ($membership): ?>
          <p class="mb-1"><strong>Plan:</strong> <?= e($membership['plan_name']) ?></p>
          <p class="mb-1"><strong>Status:</strong> <?= e($membership['status']) ?></p>
          <p class="mb-1"><strong>Start:</strong> <?= e($membership['start_date']) ?></p>
          <p class="mb-2"><strong>End:</strong> <?= e($membership['end_date']) ?></p>
          <form action="/member/membership/renew" method="post" class="d-inline">
            <?= csrf_input() ?>
            <input type="hidden" name="plan_id" value="<?= (int) $membership['plan_id'] ?>">
            <button class="btn btn-sm btn-brand" type="submit">Renew</button>
          </form>
          <?php if ($membership['status'] === 'active'): ?>
            <form action="/member/membership/cancel" method="post" class="d-inline ms-2">
              <?= csrf_input() ?>
              <input type="hidden" name="membership_id" value="<?= (int) $membership['id'] ?>">
              <button class="btn btn-sm btn-outline-danger" type="submit">Cancel</button>
            </form>
          <?php endif; ?>
        <?php else: ?>
          <p>No active membership.</p>
          <a class="btn btn-brand btn-sm" href="/plans">Subscribe Now</a>
        <?php endif; ?>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card border-0 shadow-sm p-3 h-100">
        <h2 class="h5">Quick Actions</h2>
        <div class="d-grid gap-2">
          <a class="btn btn-outline-primary" href="/member/profile">Update Profile</a>
          <a class="btn btn-outline-primary" href="/member/bookings">Manage Bookings</a>
          <a class="btn btn-outline-primary" href="/schedule">Browse Class Schedule</a>
        </div>
      </div>
    </div>
  </div>

  <div class="card border-0 shadow-sm p-3 mb-4">
    <h2 class="h5">Membership History</h2>
    <div class="table-responsive">
      <table class="table table-sm">
        <thead><tr><th>Plan</th><th>Start</th><th>End</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach ($history as $item): ?>
            <tr><td><?= e($item['plan_name']) ?></td><td><?= e($item['start_date']) ?></td><td><?= e($item['end_date']) ?></td><td><?= e($item['status']) ?></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card border-0 shadow-sm p-3">
    <h2 class="h5">Upcoming Classes</h2>
    <div class="table-responsive">
      <table class="table table-sm">
        <thead><tr><th>Class</th><th>Date</th><th>Trainer</th><th>Location</th><th>Availability</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($classes as $class): ?>
            <?php $seatsLeft = max(0, (int)$class['capacity'] - (int)$class['booked_count']); ?>
            <tr>
              <td><?= e($class['title']) ?></td>
              <td><?= e($class['class_date']) ?> <?= e(substr($class['start_time'],0,5)) ?></td>
              <td><?= e($class['trainer_name']) ?></td>
              <td><?= e($class['location_name']) ?></td>
              <td>
                <small><?= $seatsLeft ?> / <?= (int)$class['capacity'] ?> seats</small>
                <?php if ((int)$class['waitlist_count'] > 0): ?>
                  <div><small><?= (int)$class['waitlist_count'] ?> waiting</small></div>
                <?php endif; ?>
              </td>
              <td>
                <form action="/member/bookings/book" method="post">
                  <?= csrf_input() ?>
                  <input type="hidden" name="class_id" value="<?= (int)$class['id'] ?>">
                  <button class="btn btn-sm btn-brand" type="submit"><?= $seatsLeft > 0 ? 'Book' : 'Join Waitlist' ?></button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
