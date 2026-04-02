<section class="container page-shell">
  <?php partial('back_button', ['label' => 'Back', 'fallback' => '/']); ?>
  <h1 class="section-title">Admin Dashboard</h1>
  <p class="section-subtitle">Monitor key activity and jump into daily operations quickly.</p>

  <div class="admin-toolbar d-flex flex-wrap gap-2 align-items-center justify-content-between">
    <p class="mb-0 text-muted">Quick links for high-frequency tasks.</p>
    <div class="d-flex flex-wrap gap-2">
      <a class="btn btn-sm btn-brand" href="/admin/classes">Manage Classes</a>
      <a class="btn btn-sm btn-outline-primary" href="/admin/bookings">Review Bookings</a>
      <a class="btn btn-sm btn-outline-primary" href="/admin/messages">Inbox</a>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card stat-card h-100"><p class="label">Total Users</p><h2 class="metric"><?= (int)$totalUsers ?></h2></div></div>
    <div class="col-md-3"><div class="card stat-card h-100"><p class="label">Active Memberships</p><h2 class="metric"><?= (int)$activeMemberships ?></h2></div></div>
    <div class="col-md-3"><div class="card stat-card h-100"><p class="label">Total Bookings</p><h2 class="metric"><?= (int)$totalBookings ?></h2></div></div>
    <div class="col-md-3"><div class="card stat-card h-100"><p class="label">Most Popular Class</p><h2 class="h6 mb-0"><?= e($popularClass) ?></h2></div></div>
  </div>

  <div class="card p-3">
    <h2 class="h5 mb-3">Management Modules</h2>
    <div class="d-flex flex-wrap gap-2">
      <a class="btn btn-outline-primary" href="/admin/users">Users</a>
      <a class="btn btn-outline-primary" href="/admin/plans">Plans</a>
      <a class="btn btn-outline-primary" href="/admin/trainers">Trainers</a>
      <a class="btn btn-outline-primary" href="/admin/classes">Classes</a>
      <a class="btn btn-outline-primary" href="/admin/locations">Locations</a>
      <a class="btn btn-outline-primary" href="/admin/bookings">Bookings</a>
      <a class="btn btn-outline-primary" href="/admin/messages">Messages</a>
    </div>
  </div>
</section>
