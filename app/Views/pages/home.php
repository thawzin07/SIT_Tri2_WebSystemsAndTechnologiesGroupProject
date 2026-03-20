<section class="hero-section text-white">
  <div class="container py-5">
    <div class="row align-items-center g-4 py-5">
      <div class="col-lg-7">
        <p class="text-uppercase small fw-semibold mb-2">Performance Gym Platform</p>
        <h1 class="display-4 fw-bold mb-3">Train smarter with classes, coaches, and membership in one place.</h1>
        <p class="lead mb-4 text-white-50">PulsePoint helps you book classes, track your fitness routine, and stay consistent through guided coaching.</p>
        <div class="d-flex gap-2 flex-wrap mb-4">
          <a href="/register" class="btn btn-brand btn-lg">Start Membership</a>
          <a href="/schedule" class="btn btn-light-strong btn-lg">View Classes</a>
        </div>
        <div class="d-flex flex-wrap">
          <span class="metric-pill">24/7 Access Options</span>
          <span class="metric-pill">Expert Coaches</span>
          <span class="metric-pill">Flexible Plans</span>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="glass-panel p-4">
          <h2 class="h4 mb-3">Why Members Choose Us</h2>
          <ul class="mb-0 ps-3">
            <li>Structured classes from beginner to advanced</li>
            <li>Live seat availability and waitlist flow</li>
            <li>Manage profile, bookings, and membership online</li>
            <li>Multiple gym locations across the city</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="container page-shell pb-0">
  <h2 class="section-title">Membership Highlights</h2>
  <p class="section-subtitle">Choose a plan that fits your training style and commitment level.</p>
  <div class="row g-3">
    <?php foreach ($plans as $plan): ?>
      <div class="col-md-4">
        <article class="card h-100">
          <div class="card-body d-flex flex-column">
            <h3 class="h5 mb-2"><?= e($plan['name']) ?></h3>
            <p class="display-6 fw-bold mb-1">$<?= e(number_format((float) $plan['price'], 2)) ?></p>
            <p class="text-muted mb-3"><?= e($plan['duration_months']) ?> month(s)</p>
            <p class="mb-0 flex-grow-1"><?= e($plan['description']) ?></p>
          </div>
        </article>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="container page-shell pb-0">
  <h2 class="section-title">Featured Classes</h2>
  <p class="section-subtitle">Popular upcoming sessions available for members.</p>
  <div class="row g-3">
    <?php foreach ($classes as $class): ?>
      <div class="col-md-4">
        <article class="card h-100">
          <div class="card-body">
            <h3 class="h5"><?= e($class['title']) ?></h3>
            <p class="mb-1"><strong>Date:</strong> <?= e($class['class_date']) ?></p>
            <p class="mb-1"><strong>Time:</strong> <?= e(substr($class['start_time'],0,5)) ?> - <?= e(substr($class['end_time'],0,5)) ?></p>
            <p class="mb-1"><strong>Trainer:</strong> <?= e($class['trainer_name']) ?></p>
            <p class="mb-0"><strong>Spots left:</strong> <?= max(0, (int)$class['capacity'] - (int)$class['booked_count']) ?></p>
          </div>
        </article>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="container page-shell pb-0">
  <h2 class="section-title">Featured Trainers</h2>
  <p class="section-subtitle">Certified coaches focused on strength, mobility, and performance.</p>
  <div class="row g-3">
    <?php foreach ($trainers as $trainer): ?>
      <div class="col-md-4">
        <article class="card h-100">
          <div class="card-body">
            <h3 class="h5"><?= e($trainer['name']) ?></h3>
            <p class="text-muted mb-2"><?= e($trainer['specialty']) ?></p>
            <p class="mb-0"><?= e($trainer['bio']) ?></p>
          </div>
        </article>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="container page-shell">
  <div class="cta-band p-4 p-md-5 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
    <div>
      <h2 class="h3 mb-2">Ready to start your training journey?</h2>
      <p class="mb-0 text-muted">Create your member account and book your first class in minutes.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <a href="/register" class="btn btn-brand">Join Now</a>
      <a href="/plans" class="btn btn-outline-primary">Compare Plans</a>
    </div>
  </div>
</section>
