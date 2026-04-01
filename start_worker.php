<?php
echo "Local Background Worker Started\n";
echo "Listening for queued notifications every 5 seconds...\n";
echo "(Press Ctrl+C to stop this worker)\n\n";

$phpBinary = PHP_BINARY ?: 'php';
$runScript = __DIR__ . DIRECTORY_SEPARATOR . 'run_notifications.php';

while (true) {
    $output = [];
    $command = '"' . str_replace('"', '\"', $phpBinary) . '" "' . str_replace('"', '\"', $runScript) . '"';
    exec($command, $output);
    $result = implode("\n", $output);
    if (strpos($result, 'marked as') !== false) {
        echo "[" . date('H:i:s') . "] Processed a notification!\n";
    }
    sleep(5); 
}
