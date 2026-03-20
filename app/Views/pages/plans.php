<section class="container page-shell">
  <h1 class="section-title">Membership Plans</h1>
  <p class="section-subtitle">Choose the package that matches your training goals. All plans include access to dashboard booking and progress tools.</p>
  <div class="row g-3">
    <?php foreach ($plans as $index => $plan): ?>
      <div class="col-md-4">
        <article class="card h-100 <?= $index === 1 ? 'border-primary' : '' ?>">
          <div class="card-body d-flex flex-column p-4">
            <?php if ($index === 1): ?>
              <span class="badge-soft info mb-3">Most Popular</span>
            <?php endif; ?>
            <h2 class="h5 mb-2"><?= e($plan['name']) ?></h2>
            <p class="display-6 fw-bold mb-1">$<?= e(number_format((float) $plan['price'], 2)) ?></p>
            <p class="text-muted mb-3"><?= e($plan['duration_months']) ?> month(s)</p>
            <p class="flex-grow-1"><?= e($plan['description']) ?></p>
            <?php if (current_user() && !is_admin()): ?>
              <form action="/member/payments/checkout" method="post">
                <?= csrf_input() ?>
                <input type="hidden" name="plan_id" value="<?= (int)$plan['id'] ?>">
                <input type="hidden" name="payment_type" value="purchase">
                <label class="form-label small mt-1 mb-1" for="promo-<?= (int)$plan['id'] ?>">Promo Code (optional)</label>
                <input id="promo-<?= (int)$plan['id'] ?>" class="form-control form-control-sm mb-2" name="promo_code" placeholder="e.g. WELCOME10">
                <button class="btn btn-brand w-100" type="submit">Checkout</button>
              </form>
            <?php else: ?>
              <a href="/register" class="btn btn-brand w-100">Get Started</a>
            <?php endif; ?>
          </div>
        </article>
      </div>
    <?php endforeach; ?>
  </div>
</section>
