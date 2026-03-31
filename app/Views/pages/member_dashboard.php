<section class="container page-shell">
  <h1 class="section-title">Membership & Billing Center</h1>
  <p class="section-subtitle">One place to manage your plan timeline, queued upgrades, renewals, and payment history.</p>

  <?php $membershipStatus = $membership ? (string) ($membership['effective_status'] ?? $membership['status']) : ''; ?>
  <?php if (!empty($expiringSoon) && $membershipStatus === 'active'): ?>
    <div class="alert alert-warning pp-alert mb-4" role="status">
      <strong>Reminder:</strong> Your current plan is nearing expiry. You can renew now and it will queue after your current end date.
    </div>
  <?php endif; ?>
  <?php if (!empty($pendingPayment)): ?>
    <div class="card p-3 mb-4">
      <h2 class="h5 mb-1">Saved Checkout</h2>
      <p class="mb-2">You have a pending payment for <strong><?= e($pendingPayment['plan_name']) ?></strong>.</p>
      <form action="/member/payments/resume" method="post" class="d-inline">
        <?= csrf_input() ?>
        <input type="hidden" name="payment_id" value="<?= (int) $pendingPayment['id'] ?>">
        <button class="btn btn-sm btn-brand" type="submit">Resume Checkout</button>
      </form>
    </div>
  <?php endif; ?>
  <?php if (!empty($failedPayment)): ?>
    <div class="card p-3 mb-4">
      <h2 class="h5 mb-1">Payment Recovery</h2>
      <p class="mb-2">Recent payment for <strong><?= e($failedPayment['plan_name']) ?></strong> failed.</p>
      <form action="/member/payments/resume" method="post" class="d-inline">
        <?= csrf_input() ?>
        <input type="hidden" name="payment_id" value="<?= (int) $failedPayment['id'] ?>">
        <button class="btn btn-sm btn-outline-primary" type="submit">Retry Payment</button>
      </form>
    </div>
  <?php endif; ?>

  <div class="row g-3 mb-4" id="billing">
    <div class="col-lg-7">
      <div class="card p-4 h-100">
        <h2 class="h5 mb-3">Current Plan Snapshot</h2>
        <?php if ($membership): ?>
          <p class="mb-1"><strong>Plan:</strong> <?= e($membership['plan_name']) ?></p>
          <p class="mb-1"><strong>Status:</strong> <span class="badge-soft <?= $membershipStatus === 'active' ? 'success' : 'warning' ?>"><?= e($membershipStatus) ?></span></p>
          <p class="mb-1"><strong>Start:</strong> <?= e($membership['start_date']) ?></p>
          <p class="mb-3"><strong>End:</strong> <?= e($membership['end_date']) ?></p>
          <p class="small text-muted mb-3">
            Rule: Purchases and renewals are queued from your latest plan end date to avoid overlap.
          </p>
          <div class="d-flex gap-2 flex-wrap">
            <a class="btn btn-sm btn-brand" href="/plans">Purchase New Plan</a>
            <form action="/member/payments/checkout" method="post" class="d-inline">
              <?= csrf_input() ?>
              <input type="hidden" name="plan_id" value="<?= (int) $membership['plan_id'] ?>">
              <input type="hidden" name="payment_type" value="renew">
              <button class="btn btn-sm btn-outline-primary" type="submit">Renew Current Plan</button>
            </form>
          </div>
        <?php else: ?>
          <p class="mb-2">No plan found yet.</p>
          <p class="small text-muted mb-3">Rule: You can purchase a new plan. Renewal is enabled after first purchase.</p>
          <a class="btn btn-sm btn-brand" href="/plans">Purchase First Plan</a>
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
    <h2 class="h5">Plan Timeline</h2>
    <div class="table-responsive">
      <table class="table table-sm">
        <thead><tr><th>Plan</th><th>Start</th><th>End</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach ($history as $item): ?>
            <tr>
              <td><?= e($item['plan_name']) ?></td>
              <td><?= e($item['start_date']) ?></td>
              <td><?= e($item['end_date']) ?></td>
              <td><?= e((string) ($item['effective_status'] ?? $item['status'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card p-3 mb-4">
    <h2 class="h5">Billing History</h2>
    <?php if (empty($billingHistory)): ?>
      <div class="empty-state">No billing records yet.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-sm">
          <thead><tr><th>ID</th><th>Plan</th><th>Type</th><th>Amount</th><th>Status</th><th>Paid At</th><th>Created</th><th>Action</th></tr></thead>
          <tbody>
            <?php foreach ($billingHistory as $payment): ?>
              <tr>
                <td>#<?= (int) $payment['id'] ?></td>
                <td><?= e($payment['plan_name']) ?></td>
                <td><?= e(ucfirst((string) $payment['payment_type'])) ?></td>
                <td><?= e(strtoupper((string) $payment['currency'])) ?> <?= e(number_format((float) $payment['amount'], 2)) ?></td>
                <td>
                  <?php $status = (string) $payment['status']; ?>
                  <span class="badge-soft <?= $status === 'paid' ? 'success' : ($status === 'pending' ? 'warning' : 'info') ?>"><?= e($status) ?></span>
                </td>
                <td><?= e((string) ($payment['paid_at'] ?? '-')) ?></td>
                <td><?= e((string) $payment['created_at']) ?></td>
                <td>
                  <?php if (in_array((string) $payment['status'], ['pending', 'failed'], true)): ?>
                    <form action="/member/payments/resume" method="post">
                      <?= csrf_input() ?>
                      <input type="hidden" name="payment_id" value="<?= (int) $payment['id'] ?>">
                      <button class="btn btn-sm btn-outline-primary" type="submit">Resume</button>
                    </form>
                  <?php else: ?>
                    <?php if (!empty($payment['invoice_id'])): ?>
                      <a class="btn btn-sm btn-outline-secondary" href="/member/invoices/download?invoice_id=<?= (int) $payment['invoice_id'] ?>">Download Invoice</a>
                    <?php else: ?>
                      <span class="text-muted small">Invoice processing</span>
                    <?php endif; ?>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
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
