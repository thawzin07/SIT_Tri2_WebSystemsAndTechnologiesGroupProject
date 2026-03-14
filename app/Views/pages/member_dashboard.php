<section class="container page-shell">
  <h1 class="section-title">Member Dashboard</h1>
  <p class="section-subtitle">Manage your membership, update your profile, and book upcoming classes quickly.</p>

  <div class="row g-3 mb-4">
    <div class="col-lg-7">
      <div class="card p-4 h-100">
        <h2 class="h5 mb-3">Membership Status</h2>
        <?php if ($membership): ?>
          <p class="mb-1"><strong>Plan:</strong> <?= e($membership['plan_name']) ?></p>
          <p class="mb-1"><strong>Status:</strong> <span class="badge-soft <?= $membership['status'] === 'active' ? 'success' : 'warning' ?>"><?= e($membership['status']) ?></span></p>
          <p class="mb-1"><strong>Start:</strong> <?= e($membership['start_date']) ?></p>
          <p class="mb-3"><strong>End:</strong> <?= e($membership['end_date']) ?></p>
          <div class="d-flex gap-2 flex-wrap">
            <form action="/member/membership/renew" method="post" class="d-inline">
              <?= csrf_input() ?>
              <input type="hidden" name="plan_id" value="<?= (int) $membership['plan_id'] ?>">
              <button class="btn btn-sm btn-brand" type="submit">Renew</button>
            </form>
            <?php if ($membership['status'] === 'active'): ?>
              <form action="/member/membership/cancel" method="post" class="d-inline">
                <?= csrf_input() ?>
                <input type="hidden" name="membership_id" value="<?= (int) $membership['id'] ?>">
                <button class="btn btn-sm btn-outline-danger" type="submit">Cancel</button>
              </form>
            <?php endif; ?>
          </div>
        <?php else: ?>
          <p class="mb-3">No active membership found.</p>
          <a class="btn btn-brand btn-sm" href="/plans">Subscribe Now</a>
        <?php endif; ?>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card-muted p-4 h-100">
        <h2 class="h5 mb-3">Quick Actions</h2>
        <div class="d-grid gap-2">
          <a class="btn btn-outline-primary" href="/member/profile">Update Profile</a>
          <a class="btn btn-outline-primary" href="/member/bookings">Manage Bookings</a>
          <a class="btn btn-outline-primary" href="/schedule">Browse Class Schedule</a>
        </div>
      </div>
    </div>
  </div>

  <div class="card p-3 mb-4">
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

  <div class="card p-3">
    <h2 class="h5">Upcoming Classes</h2>
    <?php if (empty($classes)): ?>
      <div class="empty-state">No upcoming classes available right now.</div>
    <?php else: ?>
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
                  <span class="badge-soft <?= $seatsLeft > 0 ? 'success' : 'warning' ?>">
                    <?= $seatsLeft > 0 ? 'Open' : 'Full' ?>
                  </span>
                  <small class="ms-2"><?= $seatsLeft ?> / <?= (int)$class['capacity'] ?> seats</small>
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
    <?php endif; ?>
  </div>
</section>
