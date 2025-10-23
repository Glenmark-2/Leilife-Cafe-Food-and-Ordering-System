<?php
require __DIR__ . '/admin/fpdf186/fpdf.php';

require_once '../backend/db_script/db.php';
require_once '../backend/db_script/appData.php';

if (session_status() === PHP_SESSION_NONE) session_start();



$appData = new AppData($pdo);

$order_number = $_GET['order_number'] ?? null;
$user_id = $_GET['user_id'] ?? null;

if (!$order_number || !$user_id) {
    die("Order number or user ID missing.");
}

$order_info = $appData->getOrderWithItems($user_id,$order_number);
// var_dump($order_info);
// exit;
// Create a standard A4 PDF
$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();

// ---- HEADER ----
$imagePath = $_SERVER['DOCUMENT_ROOT'] . '/leilife/public/assests/leilife.png';
$pdf->Image($imagePath, 30, 5, 20);

$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 8, 'LEILIFE CAFE & RESTO', 0, 1, 'C');

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(77, 81, 86);
$pdf->Cell(0, 5, '123 Coffee Street, Manila', 0, 1, 'C');
$pdf->Cell(0, 5, 'Tel: 0912-345-6789', 0, 1, 'C');
$pdf->Cell(0, 5, 'TIN: 123-456-789', 0, 1, 'C');
$pdf->Ln(10);

// ---- ORDER INFO ----
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', '', 10);

if (!empty($order_info['order'])) {
    $order = $order_info['order'];
    $on = $order['order_number'];

    $order_type = ($order['delivery_method'] ?? '') === 'home' ? 'Delivery' : 'Pick up';

    $formatted_date = !empty($order['order_date']) 
                      ? date('F d, Y', strtotime($order['order_date'])) 
                      : '';

    $orderDetails = [
        ['date' => $formatted_date],
        ['order_number' => $on?? ''],
        ['order_type' => $order_type],
        ['payment_method' => !empty($order['payment_method']) ? ucfirst($order['payment_method']) : ''],
        ['customer' => !empty($order['customer_name']) ? ucfirst($order['customer_name']) : 'Unknown Customer'],
    ];

    // Add delivery address only if order type is Delivery
    if ($order_type === 'Delivery') {
        $orderDetails[] = ['delivery_address' => $order['delivery_address'] ?? ''];
    }

} else {
    $orderDetails = []; // no order found
}

foreach ($orderDetails as $detail) {
    foreach ($detail as $label => $value) {
        $labelFormatted = ucwords(str_replace('_', ' ', $label));
        $value = $value ?? '';
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(40, 8, $labelFormatted . ':', 0, 0);
        $pdf->SetFont('Arial', '', 10);
        $pdf->MultiCell(0, 8, $value, 0, 'L');
    }
}
$pdf->Ln(8);

// ---- ORDER SUMMARY ----
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, 'ORDER SUMMARY', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(20, 8, 'QTY', 0, 0, 'C');
$pdf->Cell(110, 8, 'ITEM', 0, 0, 'L');
$pdf->Cell(40, 8, 'PRICE', 0, 1, 'R');

$pdf->SetFont('Arial', '', 10);
if (!empty($order_info['items'])) {
    $items = [];

    foreach ($order_info['items'] as $item) {
        $items[] = [
            'item'  => $item['product_name'] 
                       . (!empty($item['flavors']) ? ' (' . implode(', ', $item['flavors']) . ')' : '')
                       . (!empty($item['size']) ? ' - ' . $item['size'] : ''),
            'qty'   => $item['quantity'],
            'price' => number_format($item['price'], 2)
        ];
    }
} else {
    $items = [];
}

foreach ($items as $p) {
    $pdf->Cell(20, 8, $p['qty'], 0, 0, 'C');
    $pdf->Cell(110, 8, $p['item'], 0, 0, 'L');
    $pdf->Cell(40, 8, $p['price'], 0, 1, 'R');
}

$pdf->Ln(5);

// ---- TOTALS ----
$subtotal = number_format($order_info['subtotal'] ?? 0, 2, '.', '');
$deliveryFee = number_format($order_info['delivery_fee'] ?? 0, 2, '.', '');
$total = number_format(($order_info['subtotal'] + $order_info['delivery_fee']) ?? 0, 2, '.', '');

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(130, 8, "Subtotal", 0, 0, 'R');
$pdf->Cell(40, 8, $subtotal, 0, 1, 'R');

if($order_type === "Delivery"){
    $pdf->Cell(130, 8, "Delivery Fee", 0, 0, 'R');
$pdf->Cell(40, 8, $deliveryFee, 0, 1, 'R');
}

$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(130, 8, "Total", 0, 0, 'R');
$pdf->Cell(40, 8, $total, 0, 1, 'R');

$pdf->Ln(10);

// ---- FOOTER ----
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(77, 81, 86);
$pdf->Cell(0, 6, "Thank you for your purchase!", 0, 1, 'C');

$pdf->SetFont('Arial', 'I', 8);
$pdf->Cell(0, 6, "This is a system-generated receipt.", 0, 1, 'C');


$pdf->Output('D', 'receipt_' . $on . '.pdf');

exit;
