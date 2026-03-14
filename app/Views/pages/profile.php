<section class="container py-5">
  <h1 class="section-title">My Profile</h1>
  <div class="row">
    <div class="col-lg-6">
      <form action="/member/profile" method="post" class="card border-0 shadow-sm p-4" novalidate>
        <?= csrf_input() ?>
        <label for="full_name" class="form-label">Full Name</label>
        <input id="full_name" name="full_name" class="form-control mb-3" value="<?= e($user['full_name']) ?>" required>
        <div class="invalid-feedback mb-3">Full name is required.</div>
        <label for="email" class="form-label">Email</label>
        <input id="email" class="form-control mb-3" value="<?= e($user['email']) ?>" disabled>
        <label for="phone" class="form-label">Phone</label>
        <input id="phone" name="phone" class="form-control mb-3" value="<?= e($user['phone']) ?>">
        <button class="btn btn-brand" type="submit">Save Changes</button>
      </form>
    </div>
  </div>
</section>
