<?php
$label = (string) ($label ?? 'Back');
$fallback = (string) ($fallback ?? '/');
$className = trim((string) ($className ?? ''));
$buttonClass = 'back-link';
if ($className !== '') {
    $buttonClass .= ' ' . $className;
}
?>
<a
  href="<?= e($fallback) ?>"
  class="<?= e($buttonClass) ?>"
  data-smart-back
  data-fallback="<?= e($fallback) ?>"
>
  <span class="back-link-arrow" aria-hidden="true">‹</span>
  <span><?= e($label) ?></span>
</a>
