<?php
ob_start();
require(__DIR__ . '/fpdf186/fpdf.php');
require_once __DIR__ . '/../../backend/db_script/db.php';
require_once __DIR__ . '/../../backend/db_script/appData.php';

$appData = new AppData($pdo);

// === FILTERS ===
$fromDate = $_GET['fromDate'] ?? null;
$toDate = $_GET['toDate'] ?? null;
$status = $_GET['status'] ?? 'all';
$payment = $_GET['payment'] ?? 'all';

// === FETCH SALES DATA ===
$salesData = $appData->getSalesSummary($fromDate, $toDate, $status, $payment);

// Peso function (P only)
function peso($amount) {
    if ($amount === null || $amount === '') return 'P0.00';
    return 'P' . number_format((float)$amount, 2);
}

class PDF extends FPDF
{
    var $headerFill = [210, 180, 140];
    var $subHeaderFill = [188, 143, 143];
    var $dataFillAlt = [255, 248, 220];
    var $dataFill = [255, 255, 255];
    var $borderColor = [139, 69, 19];
    var $textDark = [101, 67, 33];
    var $leftMargin = 15;
    var $rightMargin = 15;
    var $topMargin = 8;
    var $bottomMargin = 20;
    var $isFirstPage = true;

    function Header()
    {
        $this->SetLeftMargin($this->leftMargin);
        $this->SetRightMargin($this->rightMargin);
        $this->SetTopMargin($this->topMargin);
        $this->SetTextColor($this->textDark[0], $this->textDark[1], $this->textDark[2]);

        global $fromDate, $toDate, $status, $payment;

        if ($this->isFirstPage) {
            $this->SetFillColor(245, 222, 179);
            $this->Rect(0, 0, 210, 45, 'F');
            $this->SetFont('Arial', 'B', 18);
            $this->Cell(0, 10, 'Leilife Cafe & Resto - Sales Report', 0, 1, 'C');
            $this->Ln(3);
            $this->SetFont('Arial', '', 11);
            $this->Cell(0, 6, 'Generated on: ' . date('Y-m-d H:i:s'), 0, 1, 'C');
            $this->Ln(5);

            $periodText = ($fromDate && $toDate)
                ? "Period: $fromDate to $toDate"
                : "All Time Summary";
            $filterText = "Status: " . ucfirst($status) . " | Payment: " . ucfirst($payment);

            $this->SetFont('Arial', 'B', 12);
            $this->Cell(0, 7, $periodText, 0, 1, 'C');
            $this->Cell(0, 7, $filterText, 0, 1, 'C');
            $this->Ln(10);
        } else {
            $this->SetFont('Arial', 'B', 12);
            $this->Cell(0, 8, 'Leilife Cafe & Resto - Sales Report (continued)', 0, 1, 'C');
            $this->Ln(4);
        }
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 9);
        $this->SetTextColor($this->textDark[0], $this->textDark[1], $this->textDark[2]);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . ' | Online Ordering System', 0, 0, 'C');
    }

    function CheckPageBreak($h = 10)
    {
        if ($this->GetY() + $h > ($this->h - $this->bMargin)) {
            $this->isFirstPage = false;
            $this->AddPage($this->CurOrientation);
        }
    }

    function FancyTable($title, $headers, $data, $fillColor, $altColor = null)
    {
        $this->Ln(10);
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor($this->textDark[0], $this->textDark[1], $this->textDark[2]);
        $this->Cell(0, 10, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $title), 0, 1, 'L');
        $this->Ln(5);

        $this->SetFillColor($fillColor[0], $fillColor[1], $fillColor[2]);
        $this->SetDrawColor($this->borderColor[0], $this->borderColor[1], $this->borderColor[2]);
        $this->SetFont('Arial', 'B', 11);

        $count = count($headers);
        $width = (180 / $count);
        foreach ($headers as $head) {
            $this->Cell($width, 9, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $head), 1, 0, 'C', true);
        }
        $this->Ln();

        $this->SetFont('Arial', '', 10);
        $fill = false;
        foreach ($data as $row) {
            $this->CheckPageBreak(10);
            $fillColorUsed = $fill && $altColor ? $altColor : $this->dataFill;
            $this->SetFillColor($fillColorUsed[0], $fillColorUsed[1], $fillColorUsed[2]);

            foreach ($row as $col) {
                $this->Cell($width, 8, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $col), 1, 0, 'C', true);
            }
            $this->Ln();
            $fill = !$fill;
        }
    }

    function OverallSummary($summary)
    {
        $this->Ln(15);
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, 'Overall Summary', 0, 1, 'L');
        $this->Ln(5);
        $this->SetFont('Arial', '', 11);
        foreach ($summary as $item) {
            $this->Cell(0, 7, iconv('UTF-8', 'ISO-8859-1//TRANSLIT', '• ' . $item), 0, 1, 'L');
        }
    }
}

// === GENERATE PDF ===
$pdf = new PDF();
$pdf->AddPage();

// === SALES SUMMARY ===
$summary = $salesData['summary'] ?? [];
$summary_headers = ['Metric', 'Value'];
$summary_data = [
    ['Total Orders', number_format($summary['total_orders'] ?? 0)],
    ['Total Revenue (with charges)', peso($summary['total_revenue'] ?? 0)],
    ['Total Revenue (products only)', peso($summary['total_product_revenue'] ?? 0)]
];
$pdf->FancyTable('Sales Summary', $summary_headers, $summary_data, [222, 184, 135], [255, 248, 220]);

// === TOP 5 PRODUCTS ===
$top = $salesData['top_products'] ?? [];
$top_headers = ['Product', 'Orders', 'Revenue'];
$top_data = [];
foreach ($top as $row) {
    $top_data[] = [
        $row['product_name'] ?? '—',
        number_format($row['orders'] ?? 0),
        peso($row['revenue'] ?? 0)
    ];
}
$pdf->FancyTable('Top Selling Products', $top_headers, $top_data, [205, 133, 63], [255, 239, 213]);

// === MAIN CATEGORIES TABLE ===
$main = $salesData['main_categories'] ?? [];
$main_headers = ['Main Category', 'Orders', 'Revenue'];
$main_data = [];
foreach ($main as $row) {
    $main_data[] = [
        $row['category'] ?? '—',
        number_format($row['total_orders'] ?? 0),
        peso($row['total_revenue'] ?? 0)
    ];
}
$pdf->FancyTable('Main Categories Summary', $main_headers, $main_data, [188, 143, 143], [255, 248, 220]);

// === SUBCATEGORIES BREAKDOWN ===
$subs = $salesData['sub_categories'] ?? [];
if (!empty($subs)) {
    foreach ($subs as $categoryName => $products) {
        $headers = ['Product', 'Price Sold', 'Qty Sold', 'Orders', 'Revenue'];
        $rows = [];
        foreach ($products as $p) {
            $rows[] = [
                $p['product_name'] ?? '—',
                peso($p['sold_price'] ?? 0),
                number_format($p['total_quantity'] ?? 0),
                number_format($p['total_orders'] ?? 0),
                peso($p['total_revenue'] ?? 0)
            ];
        }
        $pdf->FancyTable("Category: $categoryName", $headers, $rows, [210, 180, 140], [255, 248, 220]);
    }
}

// === FINAL SUMMARY ===
$summary_points = [
    'Total Orders: ' . number_format($summary['total_orders'] ?? 0),
    'Total Revenue (with charges): ' . peso($summary['total_revenue'] ?? 0),
    'Total Revenue (products only): ' . peso($summary['total_product_revenue'] ?? 0),
    'Status: ' . ucfirst($status),
    'Payment: ' . ucfirst($payment),
    'Report Period: ' . (($fromDate && $toDate) ? "$fromDate to $toDate" : "All Time"),
    'Generated Automatically via Online Ordering System'
];
$pdf->OverallSummary($summary_points);

ob_end_clean();
$pdf->Output('I', 'leilife_sales_report.pdf');
exit;
?>
