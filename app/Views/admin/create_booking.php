<?php
$selectedClassId = (int) old('class_id', '0');
$selectedUserIds = array_map('intval', old_values('user_ids'));
$selectedUserLookup = array_fill_keys($selectedUserIds, true);
$canSubmit = !empty($classes) && !empty($members);
?>

<section class="container page-shell">
  <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
    <div>
      <?php partial('back_button', ['label' => 'Back', 'fallback' => '/admin/bookings']); ?>
      <p class="text-uppercase small text-muted mb-2">Admin Booking Flow</p>
      <h1 class="section-title mb-1">Add Booking</h1>
      <p class="section-subtitle mb-0">Choose one upcoming class, then select one or more members to book in a single action.</p>
    </div>
  </div>

  <form action="/admin/bookings/create" method="post" class="booking-create-shell">
    <?= csrf_input() ?>

    <div class="booking-create-grid">
      <section class="card booking-create-panel">
        <div class="booking-create-panel-header">
          <div>
            <p class="booking-create-eyebrow mb-1">Step 1</p>
            <h2 class="h4 mb-1">Select Class</h2>
            <p class="text-muted mb-0">Choose one class for this booking batch, then we’ll apply your selected members to that session.</p>
          </div>
        </div>

        <?php if (empty($classes)): ?>
          <div class="empty-state mt-3">No upcoming active classes are available right now.</div>
        <?php else: ?>
          <div class="booking-class-grid mt-3">
            <?php foreach ($classes as $class): ?>
              <?php
              $classId = (int) $class['id'];
              $seatsLeft = max(0, (int) $class['capacity'] - (int) $class['booked_count']);
              $isSelected = $selectedClassId === $classId;
              ?>
              <label class="booking-class-option">
                <input
                  type="radio"
                  name="class_id"
                  value="<?= $classId ?>"
                  class="booking-class-radio"
                  <?= $isSelected ? 'checked' : '' ?>
                  required
                >
                <span class="booking-class-card">
                  <span class="booking-class-topline">
                    <span class="booking-class-date"><?= e($class['class_date']) ?></span>
                    <span class="badge-soft <?= $seatsLeft > 0 ? 'success' : 'warning' ?>">
                      <?= $seatsLeft > 0 ? $seatsLeft . ' seats left' : 'Waitlist only' ?>
                    </span>
                  </span>
                  <strong class="booking-class-title"><?= e($class['title']) ?></strong>
                  <span class="booking-class-meta"><?= e(substr($class['start_time'], 0, 5)) ?> - <?= e(substr($class['end_time'], 0, 5)) ?></span>
                  <span class="booking-class-meta">Trainer: <?= e($class['trainer_name']) ?></span>
                  <span class="booking-class-meta">Location: <?= e($class['location_name']) ?></span>
                  <?php if ((int) $class['waitlist_count'] > 0): ?>
                    <span class="booking-class-waitlist"><?= (int) $class['waitlist_count'] ?> waiting</span>
                  <?php endif; ?>
                </span>
              </label>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>

      <section class="card booking-create-panel">
        <div class="booking-create-panel-header">
          <div>
            <p class="booking-create-eyebrow mb-1">Step 2</p>
            <h2 class="h4 mb-1">Select Members</h2>
            <p class="text-muted mb-0">Tick each member you want to process for the selected class.</p>
          </div>
          <span class="badge-soft info" data-booking-selected-count><?= count($selectedUserIds) ?> selected</span>
        </div>

        <?php if (empty($members)): ?>
          <div class="empty-state mt-3">No member accounts are available for booking.</div>
        <?php else: ?>
          <div class="booking-member-toolbar mt-3">
            <label class="form-label mb-1" for="admin-member-filter">Find Member</label>
            <input id="admin-member-filter" type="search" class="form-control" placeholder="Search by name, email, or phone" data-booking-member-filter>
            <div class="booking-member-toolbar-row">
              <label class="booking-select-all">
                <input type="checkbox" data-booking-select-all>
                <span>Select all visible</span>
              </label>
              <span class="text-muted small">Multiple members can be booked at once.</span>
            </div>
          </div>

          <div class="booking-member-list mt-3" data-booking-member-list>
            <?php foreach ($members as $member): ?>
              <?php
              $memberId = (int) $member['id'];
              $searchText = strtolower(trim((string) ($member['full_name'] ?? '') . ' ' . (string) ($member['email'] ?? '') . ' ' . (string) ($member['phone'] ?? '')));
              $checked = isset($selectedUserLookup[$memberId]);
              ?>
              <label class="booking-member-row" data-booking-member-item data-booking-search="<?= e($searchText) ?>">
                <span class="booking-member-check">
                  <input type="checkbox" name="user_ids[]" value="<?= $memberId ?>" <?= $checked ? 'checked' : '' ?> data-booking-member-checkbox>
                </span>
                <span class="booking-member-body">
                  <strong class="booking-member-name"><?= e($member['full_name']) ?></strong>
                  <span class="booking-member-email"><?= e($member['email']) ?></span>
                  <span class="booking-member-meta">
                    <span><?= e($member['phone'] !== '' ? $member['phone'] : 'No phone saved') ?></span>
                    <span class="badge-soft info">Member</span>
                  </span>
                </span>
              </label>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>
    </div>

    <div class="booking-create-actions">
      <p class="text-muted mb-0">Bookings and waitlist entries will follow the same seat-capacity rules as member self-booking.</p>
      <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-secondary" href="/admin/bookings">Cancel</a>
        <button class="btn btn-brand" type="submit" <?= $canSubmit ? '' : 'disabled' ?>>Create Booking(s)</button>
      </div>
    </div>
  </form>
</section>
<?php clear_old(); ?>
