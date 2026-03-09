<section class="container py-5">
  <h1 class="section-title">Admin Dashboard</h1>
  <div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card border-0 shadow-sm p-3"><p class="mb-1">Total Users</p><h2 class="h3 mb-0"><?= (int)$totalUsers ?></h2></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm p-3"><p class="mb-1">Active Memberships</p><h2 class="h3 mb-0"><?= (int)$activeMemberships ?></h2></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm p-3"><p class="mb-1">Total Bookings</p><h2 class="h3 mb-0"><?= (int)$totalBookings ?></h2></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm p-3"><p class="mb-1">Most Popular Class</p><h2 class="h6 mb-0"><?= e($popularClass) ?></h2></div></div>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <a class="btn btn-brand" href="/admin/users">Users</a>
    <a class="btn btn-brand" href="/admin/plans">Plans</a>
    <a class="btn btn-brand" href="/admin/trainers">Trainers</a>
    <a class="btn btn-brand" href="/admin/classes">Classes</a>
    <a class="btn btn-brand" href="/admin/locations">Locations</a>
    <a class="btn btn-brand" href="/admin/bookings">Bookings</a>
    <a class="btn btn-brand" href="/admin/messages">Messages</a>
  </div>
</section>
