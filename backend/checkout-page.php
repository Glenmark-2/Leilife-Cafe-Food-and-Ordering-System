<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/db_script/db.php'; // must define $pdo

header('Content-Type: application/json');

function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

// require login
if (!isset($_SESSION['user_id'])) {
    jsonResponse(['success' => false, 'message' => 'Not logged in.'], 401);
}

$user_id = (int) $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // ✅ JOIN users + addresses
        $stmt = $pdo->prepare("
            SELECT u.first_name, u.last_name, u.phone_number,
            a.street_address, a.barangay, a.city, a.region,
             a.province, a.note_to_rider
            FROM users u
            LEFT JOIN addresses a ON a.user_id = u.user_id
            WHERE u.user_id = :id
            LIMIT 1

        ");
        $stmt->execute([':id' => $user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            $row = [];
        }

        // save to session
        foreach ($row as $k => $v) {
            $_SESSION[$k] = $v ?? '';
        }

        jsonResponse(['success' => true, 'data' => $row]);
    }

    if ($method === 'POST') {
        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true);
        if (!is_array($input)) {
            jsonResponse(['success' => false, 'message' => 'Invalid JSON body'], 400);
        }

        $action = $input['action'] ?? '';

        if ($action === 'update') {
            $street   = trim($input['street_address'] ?? '');
            $barangay = trim($input['barangay'] ?? '');
            $city     = trim($input['city'] ?? '');
            $region   = trim($input['region'] ?? '');
            $province = trim($input['province'] ?? '');
            $note     = trim($input['note_to_rider'] ?? '');

            $update = $pdo->prepare("
                UPDATE addresses
                SET street_address = :street,
                    barangay = :barangay,
                    city = :city,
                    region = :region,
                    province = :province,
                    note_to_rider = :note
                WHERE user_id = :id
            ");
            $update->execute([
                ':street'   => $street,
                ':barangay' => $barangay,
                ':city'     => $city,
                ':region'   => $region,
                ':province' => $province,
                ':note'     => $note,
                ':id'       => $user_id
            ]);

            if ($update->rowCount() === 0) {
                $ins = $pdo->prepare("
                    INSERT INTO addresses (user_id, street_address, barangay, city, region, province, note_to_rider)
                    VALUES (:id, :street, :barangay, :city, :region, :postal, :province, :note)
                ");
                $ins->execute([
                    ':id'       => $user_id,
                    ':street'   => $street,
                    ':barangay' => $barangay,
                    ':city'     => $city,
                    ':region'   => $region,
                    ':province' => $province,
                    ':note'     => $note
                ]);
            }

            $_SESSION['street_address'] = $street;
            $_SESSION['barangay']       = $barangay;
            $_SESSION['city']           = $city;
            $_SESSION['region']         = $region;
            $_SESSION['province']       = $province;
            $_SESSION['note_to_rider']  = $note;

            jsonResponse(['success' => true, 'message' => 'Address updated.']);
        }

        if ($action === 'update_phone') {
            $phone = trim($input['phone_number'] ?? '');
            // ✅ update in users table instead of addresses
            $update = $pdo->prepare("UPDATE users SET phone_number = :phone WHERE user_id = :id");
            $update->execute([':phone' => $phone, ':id' => $user_id]);

            $_SESSION['phone_number'] = $phone;
            jsonResponse(['success' => true, 'message' => 'Phone updated.']);
        }

        jsonResponse(['success' => false, 'message' => 'Unknown action.'], 400);
    }

    jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);

} catch (PDOException $e) {
    jsonResponse(['success' => false, 'message' => 'DB error: ' . $e->getMessage()], 500);
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
}
 