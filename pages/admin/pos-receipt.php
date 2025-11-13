<?php
require(__DIR__ . '/fpdf186/fpdf.php');
require_once __DIR__ . '/../../backend/db_script/db.php';
require_once __DIR__ . '/../../backend/db_script/appData.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$appData = new AppData($pdo);

$order_number = $_GET['order_number'] ?? null;
$user_id = $_GET['user_id'] ?? null;

if (!$order_number || !$user_id) {
    die("Order number or user ID missing.");
}

if (!isset($_SESSION['admin_id'])) {
    die("Access denied. Please login first.");
}

$order_info = $appData->getOrderWithItems($user_id, $order_number);


// Create PDF: (orientation: P = portrait, unit: mm, size: 80mm x 200mm)
$totalHeight = 180;


if (!empty($order_info['items'])) {
    $items = [];
    foreach ($order_info['items'] as $item) {
        $productName = ucwords(strtolower($item['product_name']));

        $flavors = !empty($item['flavors']) 
            ? ' (' . implode(', ', array_map(function($f){ return ucwords(strtolower($f)); }, $item['flavors'])) . ')' 
            : '';

        $size = !empty($item['size']) ? ' - ' . ucwords(strtolower($item['size'])) : '';

        $items[] = [
            'item'  => $productName . $flavors . $size,
            'qty'   => $item['quantity'],
            'price' => number_format($item['price'], 2)
        ];
    }
} else {
    $items = [];
}

// var_dump($items);
// exit;

foreach ($items as $p) {
    $totalHeight += 6;
}
$pdf = new FPDF('P', 'mm', array(80, $totalHeight + 50));
$pdf->AddPage();

// ---- Header ----
$imagePath = $_SERVER['DOCUMENT_ROOT'] . '/Leilife/public/assests/leilife.png';
$pdf->Image($imagePath, 30, 5, 20);
// x=25mm, y=5mm, width=30mm
$pdf->SetY(23); // Move cursor to 40mm below top (image ends around here)
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(60, 8, 'LEILIFE CAFE & RESTO', 0, 3, 'C');

$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(77, 81, 86); // equivalent to #4d5156
$pdf->Cell(60, 5, '123 Coffee Street, Manila', 0, 1, 'C');
$pdf->Cell(60, 5, 'Tel: 0912-345-6789', 0, 1, 'C');
$pdf->Cell(60, 5, 'TIN: 123-456-789', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', '', 8);
$pdf->SetTextColor(0, 0, 0);


if (!empty($order_info['order'])) {
    $order = $order_info['order'];
    $on = $order['order_number'];

    $order_type = ($order['delivery_method'] ?? '') === 'home' ? 'Delivery' : 'Pick up';

    $formatted_date = !empty($order['order_date'])
        ? date('F d, Y', strtotime($order['order_date']))
        : '';

    $orderDetails = [
        ['date' => $formatted_date],
        ['order_number' => $on ?? ''],
        ['order_type' => $order_type],
        ['payment_method' => !empty($order['payment_method']) ? ucfirst($order['payment_method']) : ''],
        ['customer' => !empty($order['customer_name']) ? ucwords(strtolower($order['customer_name'])) : 'Unknown Customer'],
    ];

    if ($order_type === 'Delivery') {
        $orderDetails[] = ['delivery_address' => !empty($order['delivery_address']) ? ucwords(($order['delivery_address'])) : ''];
    }
} else {
    $orderDetails = []; 
}



foreach ($orderDetails as $detail) {
    foreach ($detail as $label => $value) {
        $labelFormatted = ucwords(str_replace('_', ' ', $label));
        $value = $value ?? ''; // ensure it's a string

      
        $pdf->Cell(25, 6, $labelFormatted . ':', 0, 0);

        // Value (wraps if too long)
        $pdf->MultiCell(35, 6, $value, 0, 'L');

        $pdf->Ln(1);
    }
}


$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(60, 8, 'ORDER SUMMARY', 0, 3, 'C');



$pdf->SetFont('Arial', 'B', 10);
$pdf->Ln(3);

// Table header
$pdf->Cell(10, 6, 'QTY', 0, 0, 'C');
$pdf->Cell(30, 6, 'ITEM', 0, 0, 'L');
$pdf->Cell(20, 6, 'PRICE', 0, 1, 'R');

// // Sample data
// $items = [
//     ['qty' => 1, 'item' => 'Caramel Macchiato with Extra Syrup and Whipped Cream', 'price' => '180.00'],
//     ['qty' => 2, 'item' => 'Iced Latte', 'price' => '300.00'],
//     ['qty' => 1, 'item' => 'Chocolate Cake Slice', 'price' => '120.00'],
// ];

$pdf->SetFont('Arial', '', 8);

foreach ($items as $p) {
    $x = $pdf->GetX();
    $y = $pdf->GetY();


    $pdf->Cell(10, 6, $p['qty'], 0, 0, 'L');


    $pdf->MultiCell(30, 6, $p['item'], 0, 'L');

    // Calculate height of the item cell (in case it wrapped)
    $itemHeight = $pdf->GetY() - $y;

    // Reset position for PRICE cell
    $pdf->SetXY($x + 40, $y);


    $pdf->Cell(20, $itemHeight, $p['price'], 0, 1, 'R');
}

$subtotal = number_format($order_info['subtotal'] ?? 0, 2, '.', '');
$deliveryFee = number_format($order_info['delivery_fee'] ?? 0, 2, '.', '');
$total = number_format(($order_info['subtotal'] + $order_info['delivery_fee']) ?? 0, 2, '.', '');


$pdf->Ln(5);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(20, 6, "Subtotal", 0, 0, 'L');
$pdf->Cell(40, 6, $subtotal, 0, 1, 'R');

if ($order_type === "Delivery") {
    $pdf->Cell(20, 6, "Delivery fee", 0, 0, 'L');
    $pdf->Cell(40, 6, $deliveryFee, 0, 1, 'R');
}

$pdf->Cell(20, 6, "Total", 0, 0, 'L');
$pdf->Cell(40, 6, $total, 0, 1, 'R');

$pdf->Ln(5);
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(77, 81, 86);
$pdf->Cell(60, 6, "Thank you for your purchase!", 0, 1, 'C');

$pdf->SetFont('Arial', 'I', 8);
$pdf->Cell(60, 6, "This is a system-generated receipt.", 0, 1, 'C');


$pdf->Output('D', 'receipt_' . $on . '.pdf');

exit;
