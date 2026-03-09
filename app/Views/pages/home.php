<section class="hero-section text-white">
  <div class="container py-5">
    <div class="row align-items-center g-4 py-5">
      <div class="col-lg-7">
        <p class="text-uppercase small fw-semibold mb-2">Train Better, Live Stronger</p>
        <h1 class="display-4 fw-bold mb-3">Push your limits at PulsePoint Fitness</h1>
        <p class="lead mb-4">Join a high-energy gym community with expert coaching, premium facilities, and flexible plans.</p>
        <div class="d-flex gap-2 flex-wrap">
          <a href="/register" class="btn btn-brand btn-lg">Start Membership</a>
          <a href="/schedule" class="btn btn-outline-light btn-lg">View Classes</a>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="glass-panel p-4">
          <h2 class="h4">Why Members Choose Us</h2>
          <ul class="mb-0">
            <li>24/7 gym access options</li>
            <li>Certified strength and conditioning coaches</li>
            <li>Group classes for all levels</li>
            <li>Multiple locations across the city</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="container py-5">
  <h2 class="section-title">Membership Highlights</h2>
  <div class="row g-3">
    <?php foreach ($plans as $plan): ?>
      <div class="col-md-4">
        <article class="card h-100 border-0 shadow-sm">
          <div class="card-body">
            <h3 class="h5"><?= e($plan['name']) ?></h3>
            <p class="display-6 fw-bold mb-2">$<?= e(number_format((float) $plan['price'], 2)) ?></p>
            <p><?= e($plan['duration_months']) ?> month(s)</p>
            <p><?= e($plan['description']) ?></p>
          </div>
        </article>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="container pb-5">
  <h2 class="section-title">Featured Classes</h2>
  <div class="row g-3">
    <?php foreach ($classes as $class): ?>
      <div class="col-md-4">
        <article class="card h-100 border-0 shadow-sm">
          <div class="card-body">
            <h3 class="h5"><?= e($class['title']) ?></h3>
            <p class="mb-1"><strong>Date:</strong> <?= e($class['class_date']) ?></p>
            <p class="mb-1"><strong>Time:</strong> <?= e(substr($class['start_time'],0,5)) ?> - <?= e(substr($class['end_time'],0,5)) ?></p>
            <p class="mb-1"><strong>Trainer:</strong> <?= e($class['trainer_name']) ?></p>
            <p class="mb-0"><strong>Spots:</strong> <?= max(0, (int)$class['capacity'] - (int)$class['booked_count']) ?></p>
          </div>
        </article>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="bg-light py-5">
  <div class="container">
    <h2 class="section-title">Featured Trainers</h2>
    <div class="row g-3">
      <?php foreach ($trainers as $trainer): ?>
        <div class="col-md-4">
          <article class="card h-100 border-0 shadow-sm">
            <div class="card-body">
              <h3 class="h5"><?= e($trainer['name']) ?></h3>
              <p class="text-muted mb-2"><?= e($trainer['specialty']) ?></p>
              <p class="mb-0"><?= e($trainer['bio']) ?></p>
            </div>
          </article>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="container py-5">
  <h2 class="section-title">Location Snapshot</h2>
  <div class="row g-3">
    <?php foreach ($locations as $location): ?>
      <div class="col-md-6">
        <article class="card h-100 border-0 shadow-sm">
          <div class="card-body">
            <h3 class="h5"><?= e($location['name']) ?></h3>
            <p class="mb-1"><?= e($location['address']) ?></p>
            <p class="mb-1">Phone: <?= e($location['phone']) ?></p>
            <p class="mb-0">Hours: <?= e($location['opening_hours']) ?></p>
          </div>
        </article>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="container pb-5">
  <h2 class="section-title">Member Testimonials</h2>
  <div class="row g-3">
    <div class="col-md-4"><blockquote class="card p-3 h-100 border-0 shadow-sm">"I transformed my strength in 4 months." <cite class="d-block mt-2">- Amira, Member</cite></blockquote></div>
    <div class="col-md-4"><blockquote class="card p-3 h-100 border-0 shadow-sm">"The trainers are focused and supportive." <cite class="d-block mt-2">- Joshua, Member</cite></blockquote></div>
    <div class="col-md-4"><blockquote class="card p-3 h-100 border-0 shadow-sm">"Clean facilities and flexible class schedule." <cite class="d-block mt-2">- Priya, Member</cite></blockquote></div>
  </div>
</section>
