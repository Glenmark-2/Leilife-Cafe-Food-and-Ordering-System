<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . "/db_script/db.php"; // PDO connection

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fname    = trim($_POST["fname"]);
    $lname    = trim($_POST["lname"]);
    $email    = trim($_POST["email"]);
    $phone    = trim($_POST["phone_number"]);
    $password = trim($_POST["password"]);
    $confirm  = trim($_POST["confirm_password"]);
    $terms    = isset($_POST['terms']) ? true : false;

    // ✅ Validate
    if (!$terms) {
        echo "<script>alert('You must accept the Terms & Conditions.'); window.history.back();</script>";
        exit;
    }

    if ($password !== $confirm) {
        echo "<script>alert('Passwords do not match!'); window.history.back();</script>";
        exit;
    }

    if (empty($fname) || empty($lname) || empty($email) || empty($password)) {
        echo "<script>alert('Please fill in all required fields!'); window.history.back();</script>";
        exit;
    }

    // ✅ Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $username = strtolower($fname . "." . $lname);

    try {
        $stmt = $pdo->prepare("INSERT INTO users 
            (username, first_name, last_name, email, phone_number, password_hash) 
            VALUES (:username, :fname, :lname, :email, :phone, :password_hash)");

        $stmt->execute([
            ':username'      => $username,
            ':fname'         => $fname,
            ':lname'         => $lname,
            ':email'         => $email,
            ':phone'         => $phone,
            ':password_hash' => $password_hash
        ]);

        // ✅ Redirect to homepage
        header("Location: /Leilife/public/index.php?page=home");
        exit;


    } catch (PDOException $e) {
        echo "<script>alert('Database error: " . $e->getMessage() . "'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('Invalid request method.'); window.history.back();</script>";
}
