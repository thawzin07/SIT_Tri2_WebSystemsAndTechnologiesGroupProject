<section class="container page-shell">
  <h1 class="section-title">My Profile</h1>
  <p class="section-subtitle">Keep your details up to date so trainers and staff can reach you when needed.</p>
  <?php
    $membershipStatus = $membership ? (string) ($membership['effective_status'] ?? $membership['status']) : 'inactive';
    $statusClass = $membershipStatus === 'active' ? 'success' : ($membershipStatus === 'queued' ? 'info' : 'warning');
    $imagePath = media_url((string) ($user['profile_image_path'] ?? ''), 'profile');
    $hasProfileImage = $imagePath !== '';
    $nameWords = preg_split('/\s+/', trim((string) ($user['full_name'] ?? ''))) ?: [];
    $initials = '';
    foreach (array_slice($nameWords, 0, 2) as $word) {
        $initials .= strtoupper(substr((string) $word, 0, 1));
    }
    $initials = $initials !== '' ? $initials : 'U';
  ?>

  <div class="row g-4 profile-layout">
    <div class="col-lg-7">
      <form action="/member/profile" method="post" enctype="multipart/form-data" class="card p-4 profile-form-card" id="profileForm" novalidate>
        <?= csrf_input() ?>

        <div class="profile-image-uploader mb-4">
          <label for="profile_image" class="profile-image-label">
            <?php if ($hasProfileImage): ?>
              <img src="<?= e($imagePath) ?>" alt="Profile photo" class="profile-image-preview">
            <?php else: ?>
              <div class="profile-image-placeholder" aria-hidden="true"><?= e($initials) ?></div>
            <?php endif; ?>
            <span class="profile-image-overlay"><?= $hasProfileImage ? 'Update image' : 'Add image' ?></span>
          </label>
          <input id="profile_image" name="profile_image" type="file" accept="image/jpeg,image/png,image/webp,image/gif" class="visually-hidden">
          <p class="form-text mt-2 mb-0">PNG, JPG, WEBP, or GIF. Maximum file size: 3MB.</p>
        </div>

        <label for="full_name" class="form-label">Full Name</label>
        <input id="full_name" name="full_name" class="form-control mb-3" value="<?= e($user['full_name']) ?>" required>
        <div class="invalid-feedback mb-3">Full name is required.</div>

        <label for="email" class="form-label">Email</label>
        <input id="email" class="form-control mb-3" value="<?= e($user['email']) ?>" disabled>

        <label for="phone" class="form-label">Phone</label>
        <input id="phone" name="phone" class="form-control mb-4" value="<?= e($user['phone']) ?>">

        <button class="btn btn-brand" type="submit">Save Changes</button>
      </form>
    </div>

    <div class="col-lg-5">
      <aside class="card-muted p-4 profile-status-card">
        <h2 class="h5 mb-3">Membership Status</h2>

        <?php if ($membership): ?>
          <p class="mb-2"><strong>Plan:</strong> <?= e($membership['plan_name']) ?></p>
          <p class="mb-2">
            <strong>Status:</strong>
            <span class="badge-soft <?= e($statusClass) ?>"><?= e($membershipStatus) ?></span>
          </p>
          <p class="mb-2"><strong>Start Date:</strong> <?= e($membership['start_date']) ?></p>
          <p class="mb-0"><strong>End Date:</strong> <?= e($membership['end_date']) ?></p>
        <?php else: ?>
          <div class="empty-state mb-3">No active membership found.</div>
          <a href="/plans" class="btn btn-sm btn-brand">Browse Plans</a>
        <?php endif; ?>
      </aside>
    </div>
  </div>

  <div class="row mt-4">
    <div class="col-lg-7">
      <div class="card p-4 danger-zone">
        <h2 class="h5 text-danger">Delete Profile</h2>
        <p class="mb-3">This will permanently remove your account and related membership and booking data.</p>
        <form action="/member/profile/delete" method="post" onsubmit="return confirm('Delete your profile permanently? This action cannot be undone.');">
          <?= csrf_input() ?>
          <button type="submit" class="btn btn-outline-danger">Delete Profile</button>
        </form>
      </div>
    </div>
  </div>
</section>
