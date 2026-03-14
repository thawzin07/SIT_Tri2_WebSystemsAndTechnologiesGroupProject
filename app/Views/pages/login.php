<section class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-5">
      <form action="<?= !empty($adminMode) ? '/admin/login' : '/login' ?>" method="post" class="card border-0 shadow-sm p-4" novalidate>
        <?= csrf_input() ?>
        <h1 class="h3 mb-3"><?= e($title ?? 'Login') ?></h1>
        <label for="email" class="form-label">Email</label>
        <input id="email" name="email" type="email" class="form-control mb-2" value="<?= old('email') ?>" required>
        <div class="invalid-feedback mb-2">Enter a valid email address.</div>
        <label for="password" class="form-label">Password</label>
        <input id="password" name="password" type="password" class="form-control mb-3" required>
        <div class="invalid-feedback mb-3">Password is required.</div>
        <button class="btn btn-brand w-100" type="submit">Login</button>
        <?php if (empty($adminMode)): ?>
          <p class="mt-3 mb-1">No account? <a href="/register">Register here</a></p>
          <p class="mb-0 small"><a href="/admin/login">Admin login</a></p>
        <?php endif; ?>
      </form>
    </div>
  </div>
</section>
<?php clear_old(); ?>
