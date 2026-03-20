<section class="container page-shell">
  <h1 class="section-title">Contact Us</h1>
  <p class="section-subtitle">Have a question about classes, membership plans, or facilities? Send us a message and we will get back to you.</p>
  <form action="/contact" method="post" class="card p-4" novalidate>
    <?= csrf_input() ?>
    <div class="row g-3">
      <div class="col-md-6"><label for="name" class="form-label">Name</label><input id="name" name="name" class="form-control" value="<?= old('name') ?>" required><div class="invalid-feedback">Name is required.</div></div>
      <div class="col-md-6"><label for="email" class="form-label">Email</label><input id="email" name="email" type="email" class="form-control" value="<?= old('email') ?>" required><div class="invalid-feedback">Enter a valid email address.</div></div>
      <div class="col-12"><label for="subject" class="form-label">Subject</label><input id="subject" name="subject" class="form-control" value="<?= old('subject') ?>" required><div class="invalid-feedback">Subject is required.</div></div>
      <div class="col-12"><label for="message" class="form-label">Message</label><textarea id="message" name="message" rows="5" class="form-control" required><?= old('message') ?></textarea><div class="invalid-feedback">Message is required.</div></div>
    </div>
    <button class="btn btn-brand mt-3" type="submit">Send Message</button>
  </form>
</section>
<?php clear_old(); ?>
