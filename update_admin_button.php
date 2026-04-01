<?php
/**
 * Update admin button to be a dropdown menu
 */

$navFile = 'app/Views/partials/navbar.php';
$content = file_get_contents($navFile);

// Find and replace the simple admin button with a dropdown
$oldCode = <<<'CODE'
          <?php if (($user['role_name'] ?? '') === 'admin'): ?>
            <li class="nav-item"><a class="btn btn-outline-secondary btn-sm" href="/admin/dashboard">Admin</a></li>
          <?php else: ?>
            <li class="nav-item"><a class="btn btn-outline-secondary btn-sm" href="/member/dashboard">Dashboard</a></li>
          <?php endif; ?>
CODE;

$newCode = <<<'CODE'
          <?php if (($user['role_name'] ?? '') === 'admin'): ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle btn btn-outline-secondary btn-sm" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Admin</a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="/admin/dashboard">Dashboard</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <form action="/logout" method="post" class="d-inline w-100">
                    <?= csrf_input() ?>
                    <button type="submit" class="dropdown-item">Logout</button>
                  </form>
                </li>
              </ul>
            </li>
          <?php else: ?>
            <li class="nav-item"><a class="btn btn-outline-secondary btn-sm" href="/member/dashboard">Dashboard</a></li>
          <?php endif; ?>
CODE;

if (strpos($content, $oldCode) !== false) {
    $newContent = str_replace($oldCode, $newCode, $content);
    file_put_contents($navFile, $newContent);
    echo "✓ Updated admin button to dropdown menu\n";
} else {
    echo "⚠ Could not find admin button code to replace\n";
}
