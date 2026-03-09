<?php $user = current_user(); ?>
<nav class="navbar navbar-expand-lg pp-navbar sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="/">PulsePoint Fitness</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
        <li class="nav-item"><a class="nav-link" href="/about">About</a></li>
        <li class="nav-item"><a class="nav-link" href="/plans">Plans</a></li>
        <li class="nav-item"><a class="nav-link" href="/trainers">Trainers</a></li>
        <li class="nav-item"><a class="nav-link" href="/schedule">Classes</a></li>
        <li class="nav-item"><a class="nav-link" href="/locations">Locations</a></li>
        <li class="nav-item"><a class="nav-link" href="/contact">Contact</a></li>
        <li class="nav-item"><a class="nav-link" href="/faq">FAQ</a></li>
        <?php if (!$user): ?>
          <li class="nav-item"><a class="btn btn-outline-light btn-sm" href="/login">Member Login</a></li>
          <li class="nav-item"><a class="btn btn-brand btn-sm" href="/register">Join Now</a></li>
        <?php else: ?>
          <?php if (($user['role_name'] ?? '') === 'admin'): ?>
            <li class="nav-item"><a class="btn btn-outline-light btn-sm" href="/admin/dashboard">Admin</a></li>
          <?php else: ?>
            <li class="nav-item"><a class="btn btn-outline-light btn-sm" href="/member/dashboard">Dashboard</a></li>
          <?php endif; ?>
          <li class="nav-item">
            <form action="/logout" method="post" class="d-inline">
              <?= csrf_input() ?>
              <button class="btn btn-sm btn-danger" type="submit">Logout</button>
            </form>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
