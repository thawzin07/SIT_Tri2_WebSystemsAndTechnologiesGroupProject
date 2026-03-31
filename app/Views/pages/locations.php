<section class="container page-shell">
  <h1 class="section-title">Gym Locations</h1>
  <p class="section-subtitle">Find a branch near you and check operating hours.</p>
  <div class="row g-3">
    <?php foreach ($locations as $location): ?>
      <div class="col-md-6">
        <article class="card h-100">
          <div class="location-card-image">
            <?php if (!empty($location['image_path'])): ?>
              <img src="<?= e($location['image_path']) ?>" alt="<?= e($location['name']) ?> location" class="location-card-photo">
            <?php else: ?>
              <div class="location-card-placeholder">Gym</div>
            <?php endif; ?>
          </div>
          <div class="card-body p-4">
            <h2 class="h5"><?= e($location['name']) ?></h2>
            <p class="mb-1"><?= e($location['address']) ?></p>
            <p class="mb-1">Phone: <?= e($location['phone']) ?></p>
            <p class="mb-0">Hours: <?= e($location['opening_hours']) ?></p>
          </div>
        </article>
      </div>
    <?php endforeach; ?>
  </div>
</section>
