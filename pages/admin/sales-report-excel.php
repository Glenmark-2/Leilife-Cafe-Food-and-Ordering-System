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

require __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../backend/db_script/db.php';
require_once __DIR__ . '/../../backend/db_script/appData.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Html;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

$appData = new AppData($pdo);

$fromDate = $_GET['fromDate'] ?? null;
$toDate = $_GET['toDate'] ?? null;
$status = $_GET['status'] ?? 'all';
$payment = $_GET['payment'] ?? 'all';

// If no date, fetch all data
$fromDateParam = $fromDate ?: null;
$toDateParam = $toDate ?: null;

$salesData = $appData->getSalesSummary($fromDateParam, $toDateParam, $status, $payment);

// === CREATE SHEET ===
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Sales Report');

// === HEADER ===
$sheet->mergeCells('B2:U3');
$sheet->setCellValue('B2', 'Leilife Café & Resto - Sales Report');
$sheet->getStyle('B2:U3')->applyFromArray([
    'font' => ['bold' => true, 'size' => 16],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'cfb281']],
]);

$sheet->mergeCells('B4:U4');
$sheet->setCellValue('B4', 'Generated on: ' . date('Y-m-d H:i:s'));
$sheet->getStyle('B4:U4')->getAlignment()->setHorizontal('center');

$sheet->mergeCells('B5:U5');
$sheet->setCellValue('B5', ($fromDate && $toDate) ? "Period: $fromDate to $toDate" : "All Time Summary");
$sheet->getStyle('B5:U5')->getAlignment()->setHorizontal('center');

$sheet->mergeCells('B6:U6');
$sheet->setCellValue('B6', 'Status: ' . ucfirst($status) . ' | Payment: ' . ucfirst($payment));
$sheet->getStyle('B6:U6')->getAlignment()->setHorizontal('center');

$currentRow = 7;

// === SALES SUMMARY ===
$sheet->mergeCells("B{$currentRow}:U{$currentRow}");
$sheet->setCellValue("B{$currentRow}", "Sales Summary");
$sheet->getStyle("B{$currentRow}:U{$currentRow}")->getFont()->setBold(true);
$currentRow++;

$sheet->mergeCells("B{$currentRow}:E{$currentRow}");
$sheet->setCellValue("B{$currentRow}", "Metric");
$sheet->getStyle("B{$currentRow}:I{$currentRow}")->applyFromArray([
    'font' => ['bold' => true],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'e0b37d']],
]);

$sheet->mergeCells("F{$currentRow}:I{$currentRow}");
$sheet->setCellValue("F{$currentRow}", "Value");
$sheet->getStyle("F{$currentRow}:I{$currentRow}")->getFont()->setBold(true);
$currentRow++;

$summary = $salesData['summary'] ?? [];
$metrics = [
    ['Total Orders', ($summary['total_orders'] ?? 0)],
    ['Total Revenue (with charges)', '₱' . number_format($summary['total_revenue'] ?? 0, 2)],
    ['Total Revenue (products only)', '₱' . number_format($summary['total_product_revenue'] ?? 0, 2)]
];

foreach ($metrics as $row) {
    $sheet->mergeCells("B{$currentRow}:E{$currentRow}");
    $sheet->setCellValue("B{$currentRow}", $row[0]);
    $sheet->getStyle("B{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

    $sheet->mergeCells("F{$currentRow}:I{$currentRow}");
    $sheet->setCellValue("F{$currentRow}", $row[1]);
    $sheet->getStyle("F{$currentRow}:I{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    if (strpos($row[0], 'Revenue') !== false) {
        $sheet->getStyle("F{$currentRow}:I{$currentRow}")
              ->getNumberFormat()
              ->setFormatCode('#,##0.00'); 
    }

    $currentRow++;
}


// === TOP SELLING PRODUCTS ===
$currentRow++;
$sheet->mergeCells("B{$currentRow}:U{$currentRow}");
$sheet->setCellValue("B{$currentRow}", "Top Selling Products");
$sheet->getStyle("B{$currentRow}:U{$currentRow}")->getFont()->setBold(true);
$currentRow++;

$sheet->mergeCells("B{$currentRow}:E{$currentRow}");
$sheet->mergeCells("F{$currentRow}:I{$currentRow}");
$sheet->mergeCells("J{$currentRow}:M{$currentRow}");
$sheet->setCellValue("B{$currentRow}", "Product");
$sheet->setCellValue("F{$currentRow}", "Orders");
$sheet->setCellValue("J{$currentRow}", "Revenue");
$sheet->getStyle("B{$currentRow}:M{$currentRow}")->applyFromArray([
    'font' => ['bold' => true],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'e0b37d']],
]);
$currentRow++;

$top = $salesData['top_products'] ?? [];
foreach ($top as $row) {
    $sheet->mergeCells("B{$currentRow}:E{$currentRow}");
    $sheet->setCellValue("B{$currentRow}", $row['product_name'] ?? '—');
    $sheet->getStyle("B{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

    $sheet->mergeCells("F{$currentRow}:I{$currentRow}");
    $sheet->setCellValue("F{$currentRow}", number_format($row['orders'] ?? 0));
    $sheet->getStyle("F{$currentRow}:I{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->mergeCells("J{$currentRow}:M{$currentRow}");
    $sheet->setCellValue("J{$currentRow}", number_format($row['revenue'] ?? 0, 2));
    $sheet->getStyle("J{$currentRow}:M{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("J{$currentRow}:M{$currentRow}")
          ->getNumberFormat()
          ->setFormatCode('"₱"#,##0.00');
    $currentRow++;
}

// === MAIN CATEGORIES ===
$currentRow++;
$sheet->mergeCells("B{$currentRow}:U{$currentRow}");
$sheet->setCellValue("B{$currentRow}", "Main Categories Summary");
$sheet->getStyle("B{$currentRow}:U{$currentRow}")->getFont()->setBold(true);
$currentRow++;

$sheet->mergeCells("B{$currentRow}:E{$currentRow}");
$sheet->mergeCells("F{$currentRow}:I{$currentRow}");
$sheet->mergeCells("J{$currentRow}:M{$currentRow}");
$sheet->setCellValue("B{$currentRow}", "Main Category");
$sheet->setCellValue("F{$currentRow}", "Orders");
$sheet->setCellValue("J{$currentRow}", "Revenue");
$sheet->getStyle("B{$currentRow}:M{$currentRow}")->applyFromArray([
    'font' => ['bold' => true],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'e0b37d']],
]);
$currentRow++;

$main = $salesData['main_categories'] ?? [];
foreach ($main as $row) {
    $sheet->mergeCells("B{$currentRow}:E{$currentRow}");
    $sheet->setCellValue("B{$currentRow}", $row['category'] ?? '—');
    $sheet->getStyle("B{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

    $sheet->mergeCells("F{$currentRow}:I{$currentRow}");
    $sheet->setCellValue("F{$currentRow}", number_format($row['total_orders'] ?? 0));
    $sheet->getStyle("F{$currentRow}:I{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->mergeCells("J{$currentRow}:M{$currentRow}");
    $sheet->setCellValue("J{$currentRow}", number_format($row['total_revenue'] ?? 0, 2));
    $sheet->getStyle("J{$currentRow}:M{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("J{$currentRow}:M{$currentRow}")
          ->getNumberFormat()
          ->setFormatCode('"₱"#,##0.00');
    $currentRow++;
}

// === SUBCATEGORIES ===
$subs = $salesData['sub_categories'] ?? [];
if(!empty($subs)){
    foreach ($subs as $categoryName => $products) {
        $currentRow++;
        $sheet->mergeCells("B{$currentRow}:U{$currentRow}");
        $sheet->setCellValue("B{$currentRow}", "Category: $categoryName");
        $sheet->getStyle("B{$currentRow}:U{$currentRow}")->getFont()->setBold(true);
        $currentRow++;

        $headers = ['Product', 'Price Sold', 'Qty Sold', 'Orders', 'Revenue'];
        $cols = ['B','F','J','N','R'];
        foreach ($headers as $i => $h) {
            $sheet->mergeCells("{$cols[$i]}{$currentRow}:" . chr(ord($cols[$i])+3) . "{$currentRow}");
            $sheet->setCellValue("{$cols[$i]}{$currentRow}", $h);
            if($i === 0){
                $sheet->getStyle("{$cols[$i]}{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            } else {
                $sheet->getStyle("{$cols[$i]}{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
        }
        $sheet->getStyle("B{$currentRow}:U{$currentRow}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'e0b37d']],
        ]);
        $currentRow++;

        foreach ($products as $p) {
            $sheet->mergeCells("B{$currentRow}:E{$currentRow}");
            $sheet->setCellValue("B{$currentRow}", $p['product_name'] ?? '—');
            $sheet->getStyle("B{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            $sheet->mergeCells("F{$currentRow}:I{$currentRow}");
            $sheet->setCellValue("F{$currentRow}", number_format($p['sold_price'] ?? 0, 2));
            $sheet->getStyle("F{$currentRow}:I{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F{$currentRow}:I{$currentRow}")
                  ->getNumberFormat()
                  ->setFormatCode('"₱"#,##0.00');

            $sheet->mergeCells("J{$currentRow}:M{$currentRow}");
            $sheet->setCellValue("J{$currentRow}", number_format($p['total_quantity'] ?? 0));
            $sheet->getStyle("J{$currentRow}:M{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->mergeCells("N{$currentRow}:Q{$currentRow}");
            $sheet->setCellValue("N{$currentRow}", number_format($p['total_orders'] ?? 0));
            $sheet->getStyle("N{$currentRow}:Q{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->mergeCells("R{$currentRow}:U{$currentRow}");
            $sheet->setCellValue("R{$currentRow}", number_format($p['total_revenue'] ?? 0, 2));
            $sheet->getStyle("R{$currentRow}:U{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("R{$currentRow}:U{$currentRow}")
                  ->getNumberFormat()
                  ->setFormatCode('"₱"#,##0.00');
            $currentRow++;
        }
    }
}

// === FORMATTING ===
foreach (range('B', 'U') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

$sheet->getStyle("B6:U" . ($currentRow - 1))
      ->getBorders()
      ->getAllBorders()
      ->setBorderStyle(Border::BORDER_THIN);

// === OUTPUT ===
if (isset($_GET['download'])) {
    $filename = 'sales_report_' . date('Y-m-d_H-i-s') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=\"$filename\"");
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

// View in browser instantly
$writer = new Html($spreadsheet);
$writer->save('php://output');
?>
<!-- <br>
<a href="/leilife/public/admin.php?page=sales-report-excel&download=1">Download Excel</a> -->
