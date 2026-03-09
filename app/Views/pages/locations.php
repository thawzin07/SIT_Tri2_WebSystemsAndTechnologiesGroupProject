<section class="container py-5">
  <h1 class="section-title">Gym Locations</h1>
  <div class="row g-3">
    <?php foreach ($locations as $location): ?>
      <div class="col-md-6">
        <article class="card h-100 border-0 shadow-sm">
          <div class="card-body">
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
