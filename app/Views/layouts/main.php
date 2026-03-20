<?php $app = config('app'); ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? '') ?> | <?= e($app['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="/assets/css/styles.css">
</head>
<body class="d-flex flex-column min-vh-100">
<a class="skip-link" href="#main-content">Skip to main content</a>
<?php partial('navbar'); ?>
<main id="main-content" class="flex-grow-1" tabindex="-1">
    <?php partial('alerts'); ?>
    <?= $content ?>
</main>
<?php partial('footer'); ?>

<!-- Chatbot Widget -->
<div id="chatbot-widget" class="chatbot-widget">
  <div id="chatbot-toggle" class="chatbot-toggle">
    <span>💬</span>
  </div>
  <div id="chatbot-container" class="chatbot-container d-none">
    <div class="chatbot-header">
      <h5>FAQ Chatbot</h5>
      <button id="chatbot-close" class="btn-close"></button>
    </div>
    <div id="chatbot-messages" class="chatbot-messages">
      <div class="message bot">Hi! I'm here to help with your questions. Ask me about memberships, classes, or anything else!</div>
    </div>
    <div class="chatbot-input">
      <input type="text" id="chatbot-input" placeholder="Type your question...">
      <button id="chatbot-send" class="btn btn-primary btn-sm">Send</button>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="/assets/js/main.js"></script>
</body>
</html>
