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

require(__DIR__ . '/fpdf186/fpdf.php');
require_once __DIR__ . '/../../backend/db_script/db.php';
require_once __DIR__ . '/../../backend/db_script/appData.php';

$appData = new AppData($pdo);

// --- FILTERS ---
$fromDate = $_GET['fromDate'] ?? null;
$toDate = $_GET['toDate'] ?? null;
$status = $_GET['status'] ?? 'all';
$payment = $_GET['payment'] ?? 'all';

// --- FETCH SALES DATA ---
$salesData = $appData->getSalesSummary($fromDate, $toDate, $status, $payment);


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
            $this->Rect(0, 0, 210, 50, 'F');
            $this->SetFont('Arial', 'B', 18);
            $this->Cell(0, 10, 'LEILIFE CAFE & RESTO - SALES REPORT', 0, 1, 'C');
            $this->Ln(3);
            $this->SetFont('Arial', '', 11);
            $this->Cell(0, 6, 'Generated on: ' . date('Y-m-d H:i:s'), 0, 1, 'C');
            $this->Ln(5);

            if ($fromDate && $toDate) {
                $periodText = "Period: $fromDate to $toDate";
            } elseif ($fromDate && !$toDate) {
                $periodText = "Period: From $fromDate Onwards";
            } elseif (!$fromDate && $toDate) {
                $periodText = "Period: Up to $toDate";
            } else {
                $periodText = "All Time Summary";
            }

            $filterText = "Status: " . ucwords(str_replace('_', ' ', $status)) . " | Payment: " . ucwords(str_replace('_', ' ', $payment));


            $this->SetFont('Arial', 'B', 12);
            $this->Cell(0, 7, $periodText, 0, 1, 'C');
            $this->Cell(0, 7, $filterText, 0, 1, 'C');
            $this->Ln(10);
        } else {
            $this->SetFont('Arial', 'B', 12);
            $this->Cell(0, 8, 'LEILIFE CAFE & RESTO - SALES REPORT (CONTINUED)', 0, 1, 'C');
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

    // FancyTable with dynamic row height
    function FancyTable($title, $headers, $data, $fillColor, $altColor = null)
    {
        $this->Ln(10);
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor($this->textDark[0], $this->textDark[1], $this->textDark[2]);
        $this->Cell(0, 10, strtoupper($title), 0, 1, 'L');
        $this->Ln(5);

        $this->SetFillColor($fillColor[0], $fillColor[1], $fillColor[2]);
        $this->SetDrawColor($this->borderColor[0], $this->borderColor[1], $this->borderColor[2]);
        $this->SetFont('Arial', 'B', 11);

        $count = count($headers);
        $width = (180 / $count);

        // Table headers
        foreach ($headers as $head) {
            $this->Cell($width, 9, strtoupper($head), 1, 0, 'C', true);
        }
        $this->Ln();

        $this->SetFont('Arial', '', 10);
        $fill = false;

        foreach ($data as $row) {
            $this->CheckPageBreak(10);

            $fillColorUsed = $fill && $altColor ? $altColor : $this->dataFill;
            $this->SetFillColor($fillColorUsed[0], $fillColorUsed[1], $fillColorUsed[2]);

            // Calculate max number of lines for the row
            $maxLines = 1;
            foreach ($row as $col) {
                $lines = $this->NbLines($width, strtoupper($col));
                if ($lines > $maxLines) $maxLines = $lines;
            }
            $rowHeight = 6 * $maxLines;

            // Draw each cell with same height
            $xStart = $this->GetX();
            $yStart = $this->GetY();

            foreach ($row as $col) {
                $x = $this->GetX();
                $y = $this->GetY();
                $this->Rect($x, $y, $width, $rowHeight);
                $this->MultiCell($width, 6, strtoupper($col), 0, 'C');
                $this->SetXY($x + $width, $yStart);
            }

            $this->Ln($rowHeight);
            $fill = !$fill;
        }
    }

    // Calculate number of lines a cell needs
    function NbLines($w, $txt)
    {
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0) $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 && $s[$nb - 1] == "\n") $nb--;
        $sep = -1;
        $i = $j = $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ') $sep = $i;
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j) $i++;
                } else $i = $sep + 1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else $i++;
        }
        return $nl;
    }

    function OverallSummary($summary)
    {
        $this->Ln(15);
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, 'OVERALL SUMMARY', 0, 1, 'L');
        $this->Ln(5);
        $this->SetFont('Arial', '', 11);
        foreach ($summary as $item) {
            $this->Cell(0, 7, '• ' . strtoupper($item), 0, 1, 'L');
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
    ['Total Revenue (with charges)', number_format($summary['total_revenue'] ?? 0, 2)],
    ['Total Revenue (products only)', number_format($summary['total_product_revenue'] ?? 0, 2)]
];
$pdf->FancyTable('Sales Summary', $summary_headers, $summary_data, [222, 184, 135], [255, 248, 220]);

// === TOP 5 PRODUCTS ===
$top = $salesData['top_products'] ?? [];
$top_headers = ['Product', 'Orders', 'Revenue'];
$top_data = [];
foreach ($top as $row) {
    $top_data[] = [
        ucwords(strtolower($row['product_name'])) ?? '—',
        number_format($row['orders'] ?? 0),
        number_format($row['revenue'] ?? 0, 2)
    ];
}
$pdf->FancyTable('Top Selling Products', $top_headers, $top_data, [245, 245, 220], [255, 239, 213]);


// === MAIN CATEGORIES TABLE ===
$main = $salesData['main_categories'] ?? [];
$main_headers = ['Main Category', 'Orders', 'Revenue'];
$main_data = [];
foreach ($main as $row) {
    $main_data[] = [
        ucwords(strtolower($row['category'])) ?? '—',
        number_format($row['total_orders'] ?? 0),
        number_format($row['total_revenue'] ?? 0, 2)
    ];
}
$pdf->FancyTable('Main Categories Summary', $main_headers, $main_data, [194, 177, 165], [255, 248, 220]);

// === SUBCATEGORIES BREAKDOWN ===
$subs = $salesData['sub_categories'] ?? [];
if (!empty($subs)) {
    foreach ($subs as $categoryName => $products) {
        $headers = ['Product', 'Price Sold', 'Qty Sold', 'Orders', 'Revenue'];
        $rows = [];
        foreach ($products as $p) {
            $rows[] = [
                ucwords(strtolower($p['product_name'])) ?? '—',
                number_format($p['sold_price'] ?? 0),
                number_format($p['total_quantity'] ?? 0),
                number_format($p['total_orders'] ?? 0),
                number_format($p['total_revenue'] ?? 0, 2)
            ];
        }
        $pdf->FancyTable("Category: " . ucwords(str_replace('_', ' ', strtolower($categoryName))), $headers, $rows, [210, 180, 140], [255, 248, 220]);
    }
}

// === FINAL SUMMARY ===
$summary_points = [
    'Total Orders: ' . number_format($summary['total_orders'] ?? 0),
    'Total Revenue (with charges): ' . number_format($summary['total_revenue'] ?? 0, 2),
    'Total Revenue (products only): ' . number_format($summary['total_product_revenue'] ?? 0, 2),
    'Payment: ' . ucwords($payment),
    'Report Period: ' . (($fromDate && $toDate) ? "$fromDate to $toDate" : "All Time"),
    'Generated Automatically via Online Ordering System'
];
$pdf->OverallSummary($summary_points);

ob_end_clean();
$pdf->Output('I', 'leilife_sales_report.pdf');
exit;
