<?php
require_once __DIR__ . '/db_script/db.php';

$pythonExe  = "C:\\Users\\glenm\\AppData\\Local\\Programs\\Python\\Python312\\python.exe";
$scriptPath = __DIR__ . "/ML/sentiment_analyzer.py";
$logFile    = __DIR__ . "/sentiment_log.txt";

function log_msg($msg, $logFile) {
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] $msg\n", FILE_APPEND);
}

try {
    do {
        $stmt = $pdo->query("
            SELECT sender_id, message
            FROM inbox
            WHERE sentiment IS NULL OR sentiment = '' OR sentiment = 'PENDING'
            ORDER BY created_at ASC
            LIMIT 10
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
            log_msg("No pending messages left. Exiting.", $logFile);
            break;
        }

        foreach ($rows as $row) {
            $id = $row['sender_id'];
            $message = trim($row['message']);
            if ($message === '') continue;

            $tempFile = __DIR__ . "/ML/temp_$id.txt";
            file_put_contents($tempFile, $message);

            $command = "\"$pythonExe\" \"$scriptPath\" \"$tempFile\"";
            $output = trim(shell_exec($command . " 2>&1"));
            $result = json_decode($output, true);

            if (!is_array($result) || !isset($result['label'])) {
                log_msg("Invalid output for sender_id=$id: $output", $logFile);
                if (file_exists($tempFile)) unlink($tempFile);
                continue;
            }

            $sentiment = strtoupper($result['label']);
            $score     = floatval($result['score'] ?? 0);

            $update = $pdo->prepare("
                UPDATE inbox SET sentiment = ?, sentiment_score = ? WHERE sender_id = ?
            ");
            $update->execute([$sentiment, $score, $id]);

            if (file_exists($tempFile)) unlink($tempFile);

            log_msg("Processed sender_id=$id → $sentiment ($score)", $logFile);
        }

        // Pause slightly to avoid CPU spikes
        sleep(3);

    } while (true);

} catch (Exception $e) {
    log_msg("Error: " . $e->getMessage(), $logFile);
}
