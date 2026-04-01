<?php
echo "Local Background Worker Started\n";
echo "Listening for queued notifications every 5 seconds...\n";
echo "(Press Ctrl+C to stop this worker)\n\n";

while (true) {
    exec('php run_notifications.php', $output);
    $result = implode("\n", $output);
    if (strpos($result, 'marked as') !== false) {
        echo "[" . date('H:i:s') . "] Processed a notification!\n";
    }
    sleep(5); 
}