<?php
require_once './db_script/db.php';


if (isset($_POST['mark_read'], $_POST['id'])) {
    $stmt = $pdo->prepare("UPDATE inbox SET status = 1 WHERE sender_id = ?");
    $stmt->execute([$_POST['id']]);
    exit;
}


if (isset($_POST['delete'])) {
    $stmt = $pdo->prepare("UPDATE inbox SET is_archived = 1 WHERE sender_id = ?");
    $stmt->execute([$_POST['delete']]); // wrap in array
    header("Location: /Leilife/public/admin.php?page=inbox");
    exit;
}

?>
