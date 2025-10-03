<?php
// backend/login.php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . "/db_script/db.php";

session_start();

// Force JSON output
header("Content-Type: application/json");

// Only allow POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "errors" => ["Invalid request method."]]);
    exit;
}

// CSRF validation
if (
    !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
    $_POST['csrf_token'] !== $_SESSION['csrf_token']
) {
    http_response_code(403);
    echo json_encode(["success" => false, "errors" => ["Security validation failed. Please refresh and try again."]]);
    exit;
}

$login    = strtolower(trim($_POST["login"] ?? ''));
$password = trim($_POST["password"] ?? '');

$errors = [];

// Validation
if (empty($login) || empty($password)) {
    $errors[] = "Please fill in all fields.";
}

if ($errors) {
    echo json_encode(["success" => false, "errors" => $errors]);
    exit;
}

try {
    // ---------- ADMIN LOGIN ----------
    $stmt = $pdo->prepare("SELECT * FROM admin_accounts WHERE email = :input OR username = :input LIMIT 1");
    $stmt->execute(['input' => $login]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        if ((int)$admin['is_active'] === 0) {
            echo json_encode(["success" => false, "errors" => ["This account is temporarily deactivated. Please contact the system administrator."]]);
            exit;
        }

        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id']    = $admin['admin_id'];
            $_SESSION['admin_name']  = $admin['full_name'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['show_welcome'] = true;

            echo json_encode([
                "success"  => true,
                "redirect" => "/Leilife/public/admin.php?page=dashboard"
            ]);
            exit;
        }
    }

    // ---------- DRIVER4 LOGIN ----------
    $stmt = $pdo->prepare("SELECT * FROM driver_accounts WHERE email = :input OR username = :input");
    $stmt->execute(['input' => $login]);
    $driver = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($driver) {
        if ((int)$driver['is_active'] === 0) {
            echo json_encode(["success" => false, "errors" => [
                "This account is temporarily deactivated. Please contact the system administrator."
            ]]);
            exit;
        }

        if (password_verify($password, $driver['password'])) {
            $_SESSION['driver_id']    = $driver['driver_id'];
            $_SESSION['driver_name']  = $driver['full_name'];
            $_SESSION['driver_email'] = $driver['email'];
            $_SESSION['show_welcome'] = true;

            echo json_encode([
                "success"  => true,
                "redirect" => "/Leilife/public/driver.php?page=home"
            ]);
            exit;
        }
    }


    // ---------- USER LOGIN ----------
    $stmt = $pdo->prepare("
        SELECT user_id, username, email, password_hash, auth_provider
        FROM users 
        WHERE email = :input OR username = :input
        LIMIT 1
    ");
    $stmt->execute(['input' => $login]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $invalidLogin = ["success" => false, "errors" => ["Invalid email/username or password."]];
    if (!$user || !password_verify($password, $user['password_hash'])) {
        echo json_encode($invalidLogin);
        exit;
    }

    // ---------- CART MERGE (unchanged) ----------
    $oldSessionId = session_id();
    $guestToken   = $_COOKIE['guest_token'] ?? null;
    $userId       = (int)$user['user_id'];

    try {
        $pdo->beginTransaction();

        $guestCart = null;

        if ($guestToken) {
            $guestStmt = $pdo->prepare("SELECT cart_id FROM carts WHERE guest_token = :gt LIMIT 1");
            $guestStmt->execute([':gt' => $guestToken]);
            $guestCart = $guestStmt->fetch(PDO::FETCH_ASSOC);
        }

        if (!$guestCart) {
            $guestStmt = $pdo->prepare("SELECT cart_id FROM carts WHERE session_id = :sid LIMIT 1");
            $guestStmt->execute([':sid' => $oldSessionId]);
            $guestCart = $guestStmt->fetch(PDO::FETCH_ASSOC);
        }

        if ($guestCart) {
            $guestCartId = (int)$guestCart['cart_id'];

            $userStmt = $pdo->prepare("SELECT cart_id FROM carts WHERE user_id = :uid LIMIT 1");
            $userStmt->execute([':uid' => $userId]);
            $userCart = $userStmt->fetch(PDO::FETCH_ASSOC);

            if ($userCart) {
                $userCartId = (int)$userCart['cart_id'];

                $moveSql = "
                    INSERT INTO cart_items (cart_id, product_id, size, flavor_ids, quantity, created_at, updated_at)
                    SELECT :user_cart_id, product_id, size, flavor_ids, quantity, NOW(), NOW()
                    FROM cart_items
                    WHERE cart_id = :guest_cart_id
                    ON DUPLICATE KEY UPDATE
                        quantity = quantity + VALUES(quantity),
                        updated_at = NOW()
                ";
                $moveStmt = $pdo->prepare($moveSql);
                $moveStmt->execute([
                    ':user_cart_id'  => $userCartId,
                    ':guest_cart_id' => $guestCartId
                ]);

                $pdo->prepare("DELETE FROM carts WHERE cart_id = :guest_cart_id")
                    ->execute([':guest_cart_id' => $guestCartId]);

                recalcCartTotals($pdo, $userCartId);
            } else {
                $upd = $pdo->prepare("
                    UPDATE carts 
                    SET user_id = :uid, session_id = NULL, guest_token = NULL, updated_at = NOW() 
                    WHERE cart_id = :cid
                ");
                $upd->execute([':uid' => $userId, ':cid' => $guestCartId]);

                recalcCartTotals($pdo, $guestCartId);
            }
        }

        $pdo->commit();
    } catch (PDOException $cartEx) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Cart merge error: " . $cartEx->getMessage());
    }

    if (isset($_COOKIE['guest_token'])) {
        setcookie("guest_token", "", time() - 3600, "/");
        unset($_COOKIE['guest_token']);
    }

    $_SESSION['user_id']  = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email']    = $user['email'];
    session_regenerate_id(true);

    echo json_encode([
        "success"  => true,
        "redirect" => "/Leilife/public/index.php?page=home"
    ]);
    exit;
} catch (PDOException $e) {
    error_log("Login DB error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "errors"  => ["Something went wrong. Please try again later."]
    ]);
    exit;
}


/**
 * Recalculate and update cart totals for a cart_id.
 */
function recalcCartTotals(PDO $pdo, int $cartId): void
{
    $sumSql = "
        SELECT COALESCE(SUM(
            (CASE
                WHEN ci.size = 'large' THEN COALESCE(p.price_large, p.product_price)
                ELSE p.product_price
            END) * ci.quantity
        ), 0) AS sub_total
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.product_id
        WHERE ci.cart_id = :cid
    ";
    $stmt = $pdo->prepare($sumSql);
    $stmt->execute([':cid' => $cartId]);
    $sub = (float)$stmt->fetchColumn();

    $delivery = 0.00;
    $total = round($sub + $delivery, 2);

    $upd = $pdo->prepare("
        UPDATE carts 
        SET sub_total = :sub, delivery_fee = :delivery, total = :total, updated_at = NOW() 
        WHERE cart_id = :cid
    ");
    $upd->execute([
        ':sub'      => $sub,
        ':delivery' => $delivery,
        ':total'    => $total,
        ':cid'      => $cartId
    ]);
}
