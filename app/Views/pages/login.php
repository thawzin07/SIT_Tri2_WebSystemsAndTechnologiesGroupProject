<section class="container page-shell">
  <div class="form-shell">
    <?php partial('back_button', ['label' => !empty($adminMode) ? 'Back' : 'Back', 'fallback' => !empty($adminMode) ? '/' : '/']); ?>
    <form action="<?= !empty($adminMode) ? '/admin/login' : '/login' ?>" method="post" class="card p-4 p-md-5" novalidate>
      <?= csrf_input() ?>
      <p class="text-uppercase small text-muted mb-2"><?= !empty($adminMode) ? 'Admin Access' : 'Member Access' ?></p>
      <h1 class="h3 mb-2"><?= e($title ?? 'Login') ?></h1>
      <p class="text-muted mb-4">Sign in to manage bookings, profile, and membership details.</p>

      <label for="email" class="form-label">Email</label>
      <input id="email" name="email" type="email" class="form-control mb-2" value="<?= old('email') ?>" required>
      <div class="invalid-feedback mb-2">Enter a valid email address.</div>

      <label for="password" class="form-label">Password</label>
      <input id="password" name="password" type="password" class="form-control mb-3" required>
      <div class="invalid-feedback mb-4">Password is required.</div>

      <button class="btn btn-brand w-100" type="submit">Login</button>

      <?php if (empty($adminMode)): ?>
        <p class="mt-3 mb-1">No account? <a href="/register">Register here</a></p>
        <p class="mb-0 small"><a href="/admin/login">Admin login</a></p>
      <?php endif; ?>
    </form>
  </div>
</section>
<?php clear_old(); ?>
