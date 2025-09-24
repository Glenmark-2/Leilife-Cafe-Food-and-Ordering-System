<?php
require_once __DIR__ . '/../db_script/db.php';

if (isset($_POST['mark_read'], $_POST['id'])) {
    $stmt = $pdo->prepare("UPDATE inbox SET status = 1 WHERE sender_id = ?");
    $stmt->execute([$_POST['id']]);
    exit;
}

if (isset($_POST['toggle_archive'])) {
    $id = $_POST['toggle_archive'];

    // Get current archive state
    $stmt = $pdo->prepare("SELECT is_archived FROM inbox WHERE sender_id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $newState = $row['is_archived'] == 1 ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE inbox SET is_archived = ? WHERE sender_id = ?");
        $stmt->execute([$newState, $id]);
    }

    header("Location: /Leilife/public/admin.php?page=inbox&archived=" . ($newState ?? 0));
    exit;
}
?>
