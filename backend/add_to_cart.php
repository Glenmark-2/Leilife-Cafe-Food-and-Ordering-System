<?php
session_start();
require_once '../backend/db_script/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false, 'message'=>'Invalid request']);
    exit;
}

$productId = $_POST['product_id'] ?? null;
$quantity = intval($_POST['quantity'] ?? 1);
$size = $_POST['size'] ?? 'medium';
$flavor = $_POST['flavor'] ?? '';

if (!$productId) {
    echo json_encode(['success'=>false, 'message'=>'No product ID']);
    exit;
}

// fetch product info
$stmt = $pdo->prepare("SELECT product_name, product_price, price_large, product_picture 
                       FROM products WHERE product_id=:id");
$stmt->execute(['id'=>$productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo json_encode(['success'=>false, 'message'=>'Product not found']);
    exit;
}

$price = ($size === 'large') ? $product['price_large'] : $product['product_price'];

// cart item
$item = [
    'id' => $productId,
    'name' => $product['product_name'],
    'size' => $size,
    'flavor' => $flavor,
    'quantity' => $quantity,
    'price' => $price,
    'image' => $product['product_picture']
];

// initialize cart
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// check if same item exists
$found = false;
foreach ($_SESSION['cart'] as &$cartItem) {
    if ($cartItem['id']==$item['id'] && 
        $cartItem['size']==$item['size'] && 
        $cartItem['flavor']==$item['flavor']) {
        $cartItem['quantity'] += $quantity;
        $found = true;
        break;
    }
}
if (!$found) $_SESSION['cart'][] = $item;

// return JSON cart
echo json_encode([
    'success' => true,
    'cart' => $_SESSION['cart']
]);

