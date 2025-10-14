<?php
require __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// === HARD-CODED SAMPLE DATA ===
$data = [
    ['Order ID' => 'ORD001', 'Customer' => 'Juan Dela Cruz', 'Total' => 1500.50, 'Date' => '2025-10-14'],
    ['Order ID' => 'ORD002', 'Customer' => 'Maria Santos', 'Total' => 2300.00, 'Date' => '2025-10-14'],
    ['Order ID' => 'ORD003', 'Customer' => 'Pedro Reyes', 'Total' => 980.75,  'Date' => '2025-10-13'],
];

// === CREATE SPREADSHEET ===
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Sales Report');

// Headers
$headers = ['Order ID', 'Customer', 'Total', 'Date'];
$sheet->fromArray($headers, NULL, 'A1');

// Fill data
$row = 2;
foreach ($data as $d) {
    $sheet->setCellValue("A$row", $d['Order ID']);
    $sheet->setCellValue("B$row", $d['Customer']);
    $sheet->setCellValue("C$row", $d['Total']);
    $sheet->setCellValue("D$row", $d['Date']);
    $row++;
}

// === OPTION 1: VIEW IN BROWSER ===
if (isset($_GET['view'])) {
    echo "<h2>📊 Sales Report</h2>";
    echo "<table border='1' cellpadding='6' cellspacing='0'>";
    echo "<tr><th>Order ID</th><th>Customer</th><th>Total</th><th>Date</th></tr>";

    foreach ($data as $row) {
        echo "<tr>
            <td>{$row['Order ID']}</td>
            <td>{$row['Customer']}</td>
            <td>₱" . number_format($row['Total'], 2) . "</td>
            <td>{$row['Date']}</td>
        </tr>";
    }

    echo "</table><br>";
    echo "<a href='?download=1'>⬇️ Download Excel</a>";
    exit;
}

// === OPTION 2: DOWNLOAD AS EXCEL ===
if (isset($_GET['download'])) {
    $filename = 'sales_report_' . date('Y-m-d_H-i-s') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=\"$filename\"");
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

// === DEFAULT: redirect to view mode ===
header("Location: ?view=1");
exit;
