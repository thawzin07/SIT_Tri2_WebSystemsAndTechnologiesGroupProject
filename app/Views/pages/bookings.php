<section class="container py-5">
  <h1 class="section-title">My Bookings</h1>
  <div class="card border-0 shadow-sm p-3 mb-4">
    <h2 class="h5">Book a Class</h2>
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

  <div class="card border-0 shadow-sm p-3 mb-4">
    <h2 class="h5">My Waitlist</h2>
    <div class="table-responsive">
      <table class="table table-sm">
        <thead><tr><th>Class</th><th>Date</th><th>Trainer</th><th>Location</th><th></th></tr></thead>
        <tbody>
        <?php if (empty($waitlistEntries)): ?>
          <tr><td colspan="5">No waitlist entries.</td></tr>
        <?php else: ?>
          <?php foreach ($waitlistEntries as $entry): ?>
            <tr>
              <td><?= e($entry['title']) ?></td>
              <td><?= e($entry['class_date']) ?> <?= e(substr($entry['start_time'],0,5)) ?></td>
              <td><?= e($entry['trainer_name']) ?></td>
              <td><?= e($entry['location_name']) ?></td>
              <td>
                <form action="/member/bookings/waitlist/cancel" method="post">
                  <?= csrf_input() ?>
                  <input type="hidden" name="waitlist_id" value="<?= (int)$entry['id'] ?>">
                  <button class="btn btn-sm btn-outline-danger" type="submit">Leave Waitlist</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card border-0 shadow-sm p-3">
    <h2 class="h5">My Booking History</h2>
    <div class="table-responsive">
      <table class="table table-sm">
        <thead><tr><th>Class</th><th>Date</th><th>Location</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($bookings as $booking): ?>
          <tr>
            <td><?= e($booking['title']) ?></td>
            <td><?= e($booking['class_date']) ?> <?= e(substr($booking['start_time'],0,5)) ?></td>
            <td><?= e($booking['location_name']) ?></td>
            <td><?= e($booking['booking_status']) ?></td>
            <td>
              <?php if ($booking['booking_status'] === 'booked'): ?>
                <form action="/member/bookings/cancel" method="post">
                  <?= csrf_input() ?>
                  <input type="hidden" name="booking_id" value="<?= (int)$booking['id'] ?>">
                  <button class="btn btn-sm btn-outline-danger" type="submit">Cancel</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
