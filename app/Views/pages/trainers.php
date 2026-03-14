<section class="container py-5">
  <h1 class="section-title">Meet Our Trainers</h1>
  <div class="row g-3">
    <?php foreach ($trainers as $trainer): ?>
      <div class="col-md-4">
        <article class="card h-100 border-0 shadow-sm">
          <div class="card-body">
            <h2 class="h5"><?= e($trainer['name']) ?></h2>
            <p class="text-muted"><?= e($trainer['specialty']) ?></p>
            <p><?= e($trainer['bio']) ?></p>
          </div>
        </article>
      </div>
    <?php endforeach; ?>
  </div>
</section>
