<?php $user = current_user(); ?>
<nav class="navbar navbar-expand-lg navbar-light pp-navbar sticky-top" aria-label="Primary navigation">
  <div class="container">
    <a class="navbar-brand" href="/">PulsePoint</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2 py-2 py-lg-0">
        <li class="nav-item"><a class="nav-link" data-nav-link href="/plans">Plans</a></li>
        <li class="nav-item"><a class="nav-link" data-nav-link href="/schedule">Classes</a></li>
        <li class="nav-item"><a class="nav-link" data-nav-link href="/trainers">Trainers</a></li>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">More</a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" data-nav-link href="/about">About</a></li>
            <li><a class="dropdown-item" data-nav-link href="/locations">Locations</a></li>
            <li><a class="dropdown-item" data-nav-link href="/contact">Contact</a></li>
            <li><a class="dropdown-item" data-nav-link href="/faq">FAQ</a></li>
          </ul>
        </li>

        <?php if (!$user): ?>
          <li class="nav-item"><a class="btn btn-outline-secondary btn-sm" href="/login">Member Login</a></li>
          <li class="nav-item"><a class="btn btn-brand btn-sm" href="/register">Join Now</a></li>
        <?php else: ?>
          <?php if (($user['role_name'] ?? '') === 'admin'): ?>
            <li class="nav-item"><a class="btn btn-outline-secondary btn-sm" href="/admin/dashboard">Admin</a></li>
          <?php else: ?>
            <li class="nav-item"><a class="btn btn-outline-secondary btn-sm" href="/member/dashboard">Dashboard</a></li>
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
