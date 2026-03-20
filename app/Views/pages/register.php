<section class="container page-shell">
  <div class="form-shell">
    <form action="/register" method="post" class="card p-4 p-md-5" novalidate>
      <?= csrf_input() ?>
      <p class="text-uppercase small text-muted mb-2">New Member</p>
      <h1 class="h3 mb-2">Create Your Account</h1>
      <p class="text-muted mb-4">Get instant access to class bookings, membership plans, and your dashboard.</p>

      <label for="full_name" class="form-label">Full Name</label>
      <input id="full_name" name="full_name" class="form-control mb-2" value="<?= old('full_name') ?>" required>
      <div class="invalid-feedback mb-2">Full name is required.</div>

      <label for="email" class="form-label">Email</label>
      <input id="email" name="email" type="email" class="form-control mb-2" value="<?= old('email') ?>" required>
      <div class="invalid-feedback mb-2">Enter a valid email address.</div>

      <label for="phone" class="form-label">Phone</label>
      <input id="phone" name="phone" class="form-control mb-2" value="<?= old('phone') ?>">

      <label for="password" class="form-label">Password</label>
      <div id="password_hint" class="form-text mb-1">Use at least 8 characters.</div>
      <input id="password" name="password" type="password" class="form-control mb-2" aria-describedby="password_hint" required>
      <div class="invalid-feedback mb-2">Password is required.</div>

      <label for="password_confirmation" class="form-label">Confirm Password</label>
      <input id="password_confirmation" name="password_confirmation" type="password" class="form-control mb-3" required>
      <div class="invalid-feedback mb-4">Confirm your password.</div>

      <button class="btn btn-brand w-100" type="submit">Create Account</button>
      <p class="mt-3 mb-0">Already a member? <a href="/login">Login</a></p>
    </form>
  </div>
</section>
<?php clear_old(); ?>
