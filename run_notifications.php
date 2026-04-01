<?php

$composerPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($composerPath)) {
    require_once $composerPath;
}

require_once __DIR__ . '/bootstrap.php'; 

$notifier = new \App\Models\NotificationLogModel();
$notifier->processQueue();

echo "Finished processing queue.\n";