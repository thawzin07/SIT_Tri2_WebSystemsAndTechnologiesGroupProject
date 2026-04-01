<section class="container page-shell">
  <h1 class="section-title">Meet Our Trainers</h1>
  <p class="section-subtitle">Certified trainers with specialties across strength, conditioning, and mobility.</p>
  <div class="row g-3">
    <?php foreach ($trainers as $trainer): ?>
      <?php
        $imagePath = media_url((string) ($trainer['image_path'] ?? ''), 'trainer');
        $hasPhoto = $imagePath !== '';
        $nameWords = preg_split('/\s+/', trim((string) ($trainer['name'] ?? ''))) ?: [];
        $initials = '';
        foreach (array_slice($nameWords, 0, 2) as $word) {
            $initials .= strtoupper(substr((string) $word, 0, 1));
        }
        $initials = $initials !== '' ? $initials : 'T';
      ?>
      <div class="col-md-4">
        <article class="card h-100 trainer-card">
          <?php if ($hasPhoto): ?>
            <div class="trainer-card-image">
              <img src="<?= e($imagePath) ?>" alt="<?= e($trainer['name']) ?>" class="trainer-photo">
            </div>
          <?php else: ?>
            <div class="trainer-card-image trainer-card-placeholder">
              <div class="trainer-initials"><?= e($initials) ?></div>
            </div>
          <?php endif; ?>
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
