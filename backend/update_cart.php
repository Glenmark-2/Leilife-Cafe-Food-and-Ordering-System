<?php
session_start();

$raw = file_get_contents("php://input");
$cart = json_decode($raw, true);

if (is_array($cart)) {
    $_SESSION['cart'] = $cart;
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false, 'message'=>'Invalid cart data']);
}
