<?php

chdir(dirname(__DIR__, 2));
/**
 * Add CSS styling for trainer photo cards
 */

$cssFile = 'public/assets/css/styles.css';
$existing = file_get_contents($cssFile);

$newCSS = <<<'CSS'

/* Trainer Card Styles */
.trainer-card {
  overflow: hidden;
  transition: all 200ms ease-in-out;
}

.trainer-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px rgba(15, 23, 42, 0.12);
}

.trainer-card-image {
  width: 100%;
  height: 240px;
  background: linear-gradient(135deg, #e0f2fe 0%, #dbeafe 100%);
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}

.trainer-photo {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
}

.trainer-card-placeholder {
  background: linear-gradient(135deg, #e0f2fe 0%, #dbeafe 100%);
}

.trainer-initials {
  font-size: 3rem;
  font-weight: 800;
  color: #1e3a8a;
  user-select: none;
}

@media (max-width: 767.98px) {
  .trainer-card-image {
    height: 200px;
  }

  .trainer-initials {
    font-size: 2.4rem;
  }
}
CSS;

// Append to CSS file (after the last rule, before or at the end)
if (strpos($existing, '/* Trainer Card Styles */') === false) {
    file_put_contents($cssFile, $existing . $newCSS);
    echo "✓ Added trainer card CSS styling\n";
} else {
    echo "⚠ Trainer card CSS already exists\n";
}