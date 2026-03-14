<section class="container page-shell">
  <h1 class="section-title">Meet Our Trainers</h1>
  <p class="section-subtitle">Certified trainers with specialties across strength, conditioning, and mobility.</p>
  <div class="row g-3">
    <?php foreach ($trainers as $trainer): ?>
      <div class="col-md-4">
        <article class="card h-100">
          <div class="card-body p-4">
            <h2 class="h5"><?= e($trainer['name']) ?></h2>
            <p class="text-muted mb-2"><?= e($trainer['specialty']) ?></p>
            <p class="mb-0"><?= e($trainer['bio']) ?></p>
          </div>
        </article>
      </div>
    <?php endforeach; ?>
  </div>
</section>
