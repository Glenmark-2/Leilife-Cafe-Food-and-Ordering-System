<?php
require_once __DIR__ . '/db_script/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_address'])) {

        $user_id = $_SESSION['user_id'] ?? null;
        $first_name = $_SESSION['first_name'] ?? null;
        $last_name = $_SESSION['last_name'] ?? null;
        $phone_number = $_SESSION['phone_number'] ?? null;
        $street_address = $_POST['street_address'];
        $barangay = $_POST['barangay'];
        $city = $_POST['city'];
        $province = $_POST['province'];
        $region = $_POST['region'];



        if (!isset($_SESSION['user_id'])) {
            die("Unauthorized access.");
        }


        if (empty($street_address) || empty($barangay) || empty($city) || empty($province) || empty($region)) {
            die("All address fields are required.");
        }

        if (strlen($street_address) > 255) {
            die("Inputs too long.");
        }

        // $sql = "SELECT user_id from addresses where user_id= :user_id";
        // $rs = $pdo->query($sql);

        $stmt = $pdo->prepare("SELECT user_id FROM addresses WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $user_id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $sql = "UPDATE addresses 
            SET first_name = :first_name, 
                last_name = :last_name, 
                phone_number = :phone,
                street_address = :street_address,
                barangay = :barangay,
                city = :city,
                province = :province,
                region = :region
            WHERE user_id = :user_id";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':user_id' => $user_id,
                ':first_name' => $first_name,
                ':last_name' => $last_name,
                ':phone' => $phone_number,
                ':street_address' => $street_address,
                ':barangay' => $barangay,
                ':city' => $city,
                ':province' => $province,
                ':region' => $region
            ]);
        } else {
            $sql = "INSERT INTO addresses 
            (user_id, first_name, last_name, phone_number, street_address, barangay, city, region, province)
            VALUES 
            (:user_id, :first_name, :last_name, :phone, :street_address, :barangay, :city, :region, :province)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':user_id' => $user_id,
                ':first_name' => $first_name,
                ':last_name' => $last_name,
                ':phone' => $phone_number,
                ':street_address' => $street_address,
                ':barangay' => $barangay,
                ':city' => $city,
                ':province' => $province,
                ':region' => $region
            ]);
        }

        header("Location: /leilife/public/index.php?page=user-profile");
        exit;
    }
}
