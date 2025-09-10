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
$stmt = $pdo->prepare("SELECT product_name, product_price, price_large, product_picture FROM products WHERE product_id=:id");
$stmt->execute(['id'=>$productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo json_encode(['success'=>false, 'message'=>'Product not found']);
    exit;
}

$price = ($size === 'large') ? $product['price_large'] : $product['product_price'];

// cart item array
$item = [
    'id' => $productId,
    'name' => $product['product_name'],
    'size' => $size,
    'flavor' => $flavor,
    'quantity' => $quantity,
    'price' => $price,
    'image' => $product['product_picture']
];

// initialize session cart
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// check if same item exists in cart
$found = false;
foreach ($_SESSION['cart'] as &$cartItem) {
    if ($cartItem['id']==$item['id'] && $cartItem['size']==$item['size'] && $cartItem['flavor']==$item['flavor']) {
        $cartItem['quantity'] += $quantity;
        $found = true;
        break;
    }
}
if (!$found) $_SESSION['cart'][] = $item;

// generate cart HTML dynamically
ob_start();
?>
<div class="inside-div" id="first-div">
    <div id="top-div">
        <img src="../public/assests/motorbike.png" alt="motor" id="motor">
        <p id="mode">Delivery</p>
        <?php include "../components/buttonTemplate.php";
            echo createButton(25,60,"Change", "change",10);
        ?> 
    </div>

    <h3>My Cart</h3>

    <?php foreach($_SESSION['cart'] as $cItem): ?>
    <div id="mid-div">
        <img src="../public/assests/trash-bin.png" alt="trash" style="width:18px; height:20px;">
        
        <div class="qty-controls">
            <input type="number" min=1 value="<?= $cItem['quantity'] ?>" max=10 disabled>
            <button onclick="changeQty(1)">+</button>
        </div>

        <p class="product-name"><?= htmlspecialchars($cItem['name']) ?> <?= htmlspecialchars($cItem['flavor']) ? "(" . htmlspecialchars($cItem['flavor']) . ")" : "" ?></p>
        <p class="product-price">P<?= number_format($cItem['price'] * $cItem['quantity'],2) ?></p>
    </div>
    <?php endforeach; ?>
</div>

<div class="inside-div" id="second-div">
    <div class="second-div-content">
        <p>Subtotal</p>
        <p>P<?= number_format(array_sum(array_map(fn($i)=>$i['price']*$i['quantity'], $_SESSION['cart'])),2) ?></p>
    </div>
    <div class="second-div-content">
        <p style="margin-top:0;">Delivery fee</p>
        <p style="margin-top:0;">P50.00</p>
    </div>
    <div class="second-div-content">
        <p><b>Total</b></p>
        <p><b>P<?= number_format(array_sum(array_map(fn($i)=>$i['price']*$i['quantity'], $_SESSION['cart'])) + 50,2) ?></b></p>
    </div>

    <?php echo createButton(40,280,"Check out", "check-out",18); ?>
</div>
<?php
$cartHtml = ob_get_clean();

echo json_encode(['success'=>true, 'cart_html'=>$cartHtml]);
