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

// Collect + sanitize
$email    = strtolower(trim($_POST["email"] ?? ''));
$password = trim($_POST["password"] ?? '');

$errors = [];

// Validation
if (empty($email) || empty($password)) {
    $errors[] = "Please fill in all fields.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Please enter a valid email address.";
}

if ($errors) {
    echo json_encode(["success" => false, "errors" => $errors]);
    exit;
}

try {
    // Fetch only needed fields
    $stmt = $pdo->prepare("
        SELECT user_id, username, email, password_hash, auth_provider
        FROM users WHERE email = :email LIMIT 1
    ");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $invalidLogin = ["success" => false, "errors" => ["Invalid email or password."]];

    if (!$user) {
        echo json_encode($invalidLogin);
        exit;
    }

    // if ($user['auth_provider'] !== 'local') {
    //     echo json_encode([
    //         "success" => false,
    //         "errors"  => ["Please use the correct login method for this account."]
    //     ]);
    //     exit;
    // }

    if (!password_verify($password, $user['password_hash'])) {
        echo json_encode($invalidLogin);
        exit;
    }

    // ---------- CART MERGE ----------
    $oldSessionId = session_id();
    $guestToken   = $_COOKIE['guest_token'] ?? null;
    $userId       = (int)$user['user_id'];

    try {
        $pdo->beginTransaction();

        // 1. Find guest cart (prefer guest_token, fallback session_id)
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

            // 2. Check if user already has a cart
            $userStmt = $pdo->prepare("SELECT cart_id FROM carts WHERE user_id = :uid LIMIT 1");
            $userStmt->execute([':uid' => $userId]);
            $userCart = $userStmt->fetch(PDO::FETCH_ASSOC);

            if ($userCart) {
                // Merge guest cart items into user cart
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

                // Delete old guest cart (cart_items already merged)
                $pdo->prepare("DELETE FROM carts WHERE cart_id = :guest_cart_id")
                    ->execute([':guest_cart_id' => $guestCartId]);

                recalcCartTotals($pdo, $userCartId);
            } else {
                // No existing user cart → claim guest cart for this user
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
    // ---------- END CART MERGE ----------

    // Important: regenerate after merge so session_id change doesn’t break mapping
    session_regenerate_id(true);

    $_SESSION['user_id']   = $user['user_id'];
    $_SESSION['username']  = $user['username'];
    $_SESSION['email']     = $user['email'];

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
