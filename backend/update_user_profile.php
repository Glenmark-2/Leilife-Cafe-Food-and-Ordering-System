<?php 
require_once __DIR__ . '/db_script/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_info'])) {
        
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name  = trim($_POST['last_name'] ?? '');
        $phone      = trim($_POST['phone_number'] ?? '');
        $user_id    = $_SESSION['user_id'] ?? null;

        if (!$user_id) {
            die("User not logged in.");
        }

        if ($first_name === '' || $last_name === '') {
            die("First and Last name are required.");
        }

        if ($phone !== '' && !preg_match('/^\+63\d{9}$/', $phone)) {
            die("Invalid phone number.");
        }

        $sql = "UPDATE users 
                SET first_name = :first_name, 
                    last_name = :last_name, 
                    phone_number = :phone
                WHERE user_id = :user_id";

        $stmt = $pdo->prepare($sql);
        $rs = $stmt->execute([
            ':first_name' => $first_name,
            ':last_name'  => $last_name,
            ':phone'      => $phone,
            ':user_id'         => $user_id
        ]);

        if ($rs) {
            // redirect back to profile or show success
            header("Location: /leilife/public/index.php?page=user-profile");
            exit;
        } else {
            die("Error updating profile.");
        }
    }
}
?>
