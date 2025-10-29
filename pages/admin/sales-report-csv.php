<?php
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    die("Access denied. Please login first.");
}

if (!isset($_SESSION['download_token'])) {
    die("Download token not found. Refresh the page and try again.");
}

$downloadToken = $_SESSION['download_token'];
if (!isset($_GET['token']) || $_GET['token'] !== $downloadToken) {
    die("Invalid download token. Please refresh the page and try again.");
}

// --- REGENERATE TOKEN after successful check ---
$_SESSION['download_token'] = bin2hex(random_bytes(16));
$downloadToken = $_SESSION['download_token'];

require_once __DIR__ . '/../../backend/db_script/db.php';
require_once __DIR__ . '/../../backend/db_script/appData.php';

$appData = new AppData($pdo);

$fromDate = $_GET['fromDate'] ?? null;
$toDate = $_GET['toDate'] ?? null;
$status = $_GET['status'] ?? 'all';
$payment = $_GET['payment'] ?? 'all';

$salesData = $appData->getSalesSummary($fromDate, $toDate, $status, $payment);

// --- SEND CSV ---
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="sales_report_' . date('Y-m-d_H-i-s') . '.csv"');

$output = fopen('php://output', 'w');

// --- HEADER ---
fputcsv($output, ['Leilife Café & Resto - Sales Report']);
fputcsv($output, ['Generated on:', date('Y-m-d H:i:s')]);
fputcsv($output, ($fromDate && $toDate) ? ["Period: $fromDate to $toDate"] : ["All Time Summary"]);
fputcsv($output, ['Status: ' . ucfirst($status) . ' | Payment: ' . ucfirst($payment)]);
fputcsv($output, []);

// --- SALES SUMMARY ---
fputcsv($output, ['Sales Summary']);
$summary = $salesData['summary'] ?? [];
fputcsv($output, ['Metric', 'Value']);

$metrics = [
    ['Total Orders', number_format($summary['total_orders'] ?? 0, 0)],
    ['Total Revenue (with charges)', '₱' . number_format($summary['total_revenue'] ?? 0, 2)],
    ['Total Revenue (products only)', '₱' . number_format($summary['total_product_revenue'] ?? 0, 2)],
];

foreach ($metrics as $row) {
    fputcsv($output, $row);
}

fputcsv($output, []);

// --- TOP SELLING PRODUCTS ---
fputcsv($output, ['Top Selling Products']);
fputcsv($output, ['Product', 'Orders', 'Revenue']);
$top = $salesData['top_products'] ?? [];
foreach ($top as $row) {
    fputcsv($output, [
        $row['product_name'] ?? '—',
        number_format($row['orders'] ?? 0, 0),
        '₱' . number_format($row['revenue'] ?? 0, 2)
    ]);
}

fputcsv($output, []);

// --- MAIN CATEGORIES ---
fputcsv($output, ['Main Categories Summary']);
fputcsv($output, ['Category', 'Orders', 'Revenue']);
$main = $salesData['main_categories'] ?? [];
foreach ($main as $row) {
    fputcsv($output, [
        $row['category'] ?? '—',
        number_format($row['total_orders'] ?? 0, 0),
        '₱' . number_format($row['total_revenue'] ?? 0, 2)
    ]);
}

// --- SUBCATEGORIES ---
$subs = $salesData['sub_categories'] ?? [];
foreach ($subs as $categoryName => $products) {
    fputcsv($output, []);
    fputcsv($output, ["Category: $categoryName"]);
    fputcsv($output, ['Product', 'Price Sold', 'Qty Sold', 'Orders', 'Revenue']);
    foreach ($products as $p) {
        fputcsv($output, [
            $p['product_name'] ?? '—',
            '₱' . number_format($p['sold_price'] ?? 0, 2),
            number_format($p['total_quantity'] ?? 0, 0),
            number_format($p['total_orders'] ?? 0, 0),
            '₱' . number_format($p['total_revenue'] ?? 0, 2)
        ]);
    }
}

fclose($output);
exit;
