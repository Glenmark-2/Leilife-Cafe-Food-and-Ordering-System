<?php
// backend/run_sentiment_daemon.php
$lockFile = __DIR__ . '/sentiment_daemon.lock';

// If the daemon is already running, exit silently
if (file_exists($lockFile) && time() - filemtime($lockFile) < 60) {
    exit;
}

// Touch lock file (heartbeat)
file_put_contents($lockFile, time());

$cmd = "start /B C:\\xampp\\php\\php.exe " . __DIR__ . "\\sentiment_cron.php";
pclose(popen($cmd, "r"));
