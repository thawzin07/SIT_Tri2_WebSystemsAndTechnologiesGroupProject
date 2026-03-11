<div class="container mt-3" aria-live="polite" aria-atomic="true">
  <?php if ($msg = flash('success')): ?>
    <div class="alert alert-success pp-alert" role="status">
      <strong>Success:</strong> <?= e($msg) ?>
    </div>
  <?php endif; ?>
  <?php if ($msg = flash('error')): ?>
    <div class="alert alert-danger pp-alert" role="alert">
      <strong>Error:</strong> <?= e($msg) ?>
    </div>
  <?php endif; ?>
</div>
