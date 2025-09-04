<?php
// backend/middleware/auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

/**
 * Redirects to login if page is protected and user is not logged in.
 */
function requireLogin(string $page): void {
    // Pages that require login
    $protected = ['orders', 'cart', 'checkout-page', 'user-profile'];

    if (in_array($page, $protected, true) && !isLoggedIn()) {
        header("Location: /Leilife/public/index.php?page=login");
        exit;
    }
}
