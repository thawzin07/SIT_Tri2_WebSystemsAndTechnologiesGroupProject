<?php $app = config('app'); ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? '') ?> | <?= e($app['name']) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
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
  <button id="chatbot-toggle" class="chatbot-toggle" type="button" aria-expanded="false" aria-controls="chatbot-container" aria-label="Open chat assistant">
    <span>Chat</span>
  </button>
  <div id="chatbot-container" class="chatbot-container d-none">
    <div class="chatbot-header">
      <div class="chatbot-header-copy">
        <h3 class="h5">PulsePoint Assistant</h3>
        <p>Ask about plans, classes, trainers, and bookings.</p>
      </div>
      <div class="chatbot-header-actions">
        <button id="chatbot-clear" class="chatbot-clear-btn" type="button" aria-label="Clear chat history">New chat</button>
        <button id="chatbot-close" class="btn-close btn-close-white" type="button" aria-label="Close chat"></button>
      </div>
    </div>
    <div id="chatbot-messages" class="chatbot-messages"></div>
    <div class="chatbot-quick-prompts" id="chatbot-quick-prompts" role="group" aria-label="Suggested questions">
      <button type="button" class="chatbot-prompt-btn" data-chatbot-prompt="What membership plans do you offer?">Plans</button>
      <button type="button" class="chatbot-prompt-btn" data-chatbot-prompt="Who are your trainers and what do they teach?">Trainers</button>
      <button type="button" class="chatbot-prompt-btn" data-chatbot-prompt="How can I book a class?">Book class</button>
      <button type="button" class="chatbot-prompt-btn" data-chatbot-prompt="Where are your gym locations?">Locations</button>
    </div>
    <div class="chatbot-input">
      <input type="text" id="chatbot-input" placeholder="Ask your question..." autocomplete="off" aria-label="Chat message input">
      <button id="chatbot-send" class="btn btn-primary btn-sm" type="button">Send</button>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="/assets/js/main.js"></script>
</body>
</html>
