<div class="container mt-3">
  <?php if ($msg = flash('success')): ?>
    <div class="alert alert-success" role="alert"><?= e($msg) ?></div>
  <?php endif; ?>
  <?php if ($msg = flash('error')): ?>
    <div class="alert alert-danger" role="alert"><?= e($msg) ?></div>
  <?php endif; ?>
</div>
