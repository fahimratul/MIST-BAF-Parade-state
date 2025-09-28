<?php
// generate_report.php - Updated PDF Report Generator with TCPDF
require_once 'config.php';
require_once 'functions.php';

// Check if TCPDF is installed
$tcpdf_paths = [
    'vendor/tecnickcom/tcpdf/tcpdf.php',  // Composer installation
    'tcpdf/tcpdf.php',                    // Manual installation
    'TCPDF/tcpdf.php'                     // Alternative path
];

$tcpdf_found = false;
foreach ($tcpdf_paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $tcpdf_found = true;
        break;
    }
}

if (!$tcpdf_found) {
    // If TCPDF not found, show installation instructions
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>TCPDF Not Found</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="alert alert-warning">
                        <h4><i class="fas fa-exclamation-triangle"></i> TCPDF Library Not Found</h4>
                        <p>The TCPDF library is required to generate PDF reports. Please install it using one of these methods:</p>
                        
                        <h5>Method 1: Quick Installation</h5>
                        <p><a href="install_tcpdf.php" class="btn btn-primary">Run Auto-Installer</a></p>
                        
                        <h5>Method 2: Composer</h5>
                        <pre><code>composer require tecnickcom/tcpdf</code></pre>
                        
                        <h5>Method 3: Manual Download</h5>
                        <p>1. Download TCPDF from <a href="https://tcpdf.org/" target="_blank">https://tcpdf.org/</a></p>
                        <p>2. Extract to your project folder as 'tcpdf/'</p>
                        
                        <hr>
                        <p><strong>For now, here's the HTML version:</strong></p>
                        <a href="?date=<?php echo $_GET['date'] ?? date('Y-m-d'); ?>&format=html" class="btn btn-secondary">View HTML Report</a>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Get date from URL parameter or use current date
$report_date = $_GET['date'] ?? date('Y-m-d');
$formatted_date = date('d-m-y', strtotime($report_date));
$format = $_GET['format'] ?? 'pdf'; // pdf or html

// Get data for the report
$parade_data = getParadeData($pdo, $report_date);
$summary = getReportSummary($pdo, $report_date);
$absent_officers = getDetailedAbsentOfficers($pdo, $report_date);

// Save report to database
saveReport($pdo, $report_date, $summary);

// Generate HTML content
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>BAF Parade State Report - <?php echo $formatted_date; ?></title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            margin: 0;
            padding: 15px;
            font-size: 11px;
            line-height: 1.2;
            color: #000;
        }
        
        .header {
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 15px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 10px;
        }
        
        .report-table th,
        .report-table td {
            border: 1px solid #000;
            padding: 3px;
            text-align: center;
            vertical-align: middle;
        }
        
        .report-table th {
            background-color: #be2424 !important;
            color: #ffffff !important;
            font-weight: bold;
            font-size: 9px;
            border: 1px solid #000000 !important;
        }
        
        .dept-header {
            background-color: #e0e0e0 !important;
            font-weight: bold;
            border: 1px solid #000000 !important;
        }
        
        .grand-total {
            font-weight: bold;
            background-color: #bebb24ff !important;
            border: 1px solid #000000 !important;
        }
        
        .summary-section {
            margin-top: 15px;
            font-size: 10px;
        }
        
        .summary-item {
            margin-bottom: 3px;
            line-height: 1.3;
        }
        
        .absent-details {
            margin-top: 10px;
        }
        
        .absent-details ol {
            margin: 3px 0;
            padding-left: 15px;
        }
        
        .section-title {
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 3px;
            text-decoration: underline;
        }
        
        .text-left {
            text-align: left;
        }
        
        .no-break {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>

    <div class="summary-section no-break">
        <div class="summary-item">
            Assalamualaikum sir,<br><br>
            BAF Students Parade state as on <?php echo sprintf('%02d', date('d', strtotime($report_date))) . '2100 ' . strtoupper(date('M y', strtotime($report_date))); ?> <br>
            <br>

            Total Str: 60<br>
            Offrs: 48<br>
            Cdts: 12<br>
            On Parade: <?php echo $summary['total_on_parade']; ?><br>
            Off Parade: <?php echo $summary['total_absent']; ?><br><br>
            
            Summary of Absent/Off parade:<br> 


        </div>
        
        <div class="summary-item">
            <strong>2. Level wise Total Offrs:</strong> 
            <?php 
            $level_parts = [];
            foreach($summary['level_totals'] as $level => $count) {
                $level_parts[] = formatLevelForSummary($level) . '=' . $count;
            }
            echo implode(', ', $level_parts);
            ?>
        </div>
        
        <div class="summary-item">
            <strong>3. Total On Parade:</strong> MIST Dhaka Mess + MIST Mirpur Mess + BAF Base AKR = 
            <?php echo $grand_totals['MIST Dhaka Mess']['present']; ?> + 
            <?php echo $grand_totals['MIST Mirpur Mess']['present']; ?> + 
            <?php echo $grand_totals['BAF Base AKR']['present']; ?> = 
            <?php echo $summary['total_on_parade']; ?>
        </div>
        
        <div class="summary-item">
            <strong>4. Total Female Offrs:</strong> 
            <?php if(!empty($summary['female_by_location'])): ?>
                (<?php 
                $female_parts = [];
                foreach($summary['female_by_location'] as $location => $count) {
                    if($count > 0) {
                        $female_parts[] = str_replace('MIST ', '', $location) . ': ' . $count;
                    }
                }
                echo implode(' and ', $female_parts);
                ?>)=<?php echo $summary['total_female']; ?>
            <?php else: ?>
                0
            <?php endif; ?>
        </div>
        
        <div class="section-title">5. Details of Absent/Off-parade:</div>
        
        <?php if(!empty($absent_officers)): ?>
            <div class="text-left absent-details">
                <strong>a. MIST Mirpur Mess:</strong>
                <ol>
                    <?php 
                    $mirpur_count = 0;
                    foreach($absent_officers as $officer): 
                        if($mirpur_count < 4): // Show first 4 absent officers as example
                    ?>
                    <li><?php echo $officer['rank'] . ' ' . $officer['name'] . '- ' . $officer['department'] . ', L' . str_replace(['I', 'II', 'III', 'IV'], ['1', '2', '3', '4'], $officer['level']) . ', ' . $officer['short_status']; ?></li>
                    <?php 
                        $mirpur_count++;
                        endif;
                    endforeach; 
                    ?>
                </ol>
                
                <strong>b. MIST Dhaka Mess:</strong><br>
                <strong>c. BAF Base AKR:</strong>
            </div>
        <?php endif; ?>
        
        <div class="section-title">7. Summary of Absents/Off-parade: <?php echo str_pad($summary['total_absent'], 2, '0', STR_PAD_LEFT); ?></div>
        <div class="text-left">
            <?php if(!empty($summary['absent_breakdown'])): ?>
                <?php 
                $breakdown_letters = ['a', 'b', 'c', 'd', 'e'];
                $i = 0;
                foreach($summary['absent_breakdown'] as $reason => $count): 
                ?>
                <div><strong><?php echo $breakdown_letters[$i]; ?>. <?php echo $reason; ?>:</strong> <?php echo str_pad($count, 2, '0', STR_PAD_LEFT); ?></div>
                <?php 
                $i++;
                endforeach; 
                ?>
            <?php else: ?>
                <div><strong>a. Leave:</strong> 00</div>
                <div><strong>b. CMH:</strong> 00</div>
                <div><strong>c. Sick Leave:</strong> 00</div>
                <div><strong>d. SIQ:</strong> 00</div>
                <div><strong>e. Isolation:</strong> 00</div>
            <?php endif; ?>
        </div>
        
        <div class="section-title">8. Maj Changes/ Note:</div>
        <div style="height: 20px;"></div> <!-- Space for manual notes -->
    </div>
</body>
</html>

<?php
$html = ob_get_clean();

// Decide output format
if ($format === 'html' || !$tcpdf_found) {
    // Output as HTML (for preview or if TCPDF not available)
    header('Content-Type: text/html; charset=utf-8');
    echo $html;
} else {
    // Generate PDF using TCPDF
    try {
        // Create new PDF document in landscape orientation
        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator('BAF Parade System');
        $pdf->SetAuthor('Bangladesh Air Force');
        $pdf->SetTitle("Parade State Report - {$formatted_date}");
        $pdf->SetSubject('BAF MIST Student Officers Parade State');
        $pdf->SetKeywords('BAF, Parade, Report, MIST, Officers');
        
        // Enable color mode and disable print color mode restrictions
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);
        
        // Set default header/footer data
        $pdf->SetHeaderData('', 0, '', '', [0,0,0], [255,255,255]);
        $pdf->setFooterData([0,0,0], [255,255,255]);
        
        // Set header/footer fonts
        $pdf->setHeaderFont(['dejavusans', '', 8]);
        $pdf->setFooterFont(['dejavusans', '', 6]);
        
        // Set default monospaced font
        $pdf->SetDefaultMonospacedFont('dejavusansmono');
        
        // Set margins
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(10);
        
        // Set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, 15);
        
        // Set image scale factor
        $pdf->setImageScale(1.25);
        
        // Enable HTML colors and CSS
        $pdf->setCellHeightRatio(1.25);
        
        // Set color mode to RGB (ensure colors are not converted to grayscale)
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetTextColor(0, 0, 0);
        
        // Add a page
        $pdf->AddPage();
        
        // Write HTML content with color support enabled
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Set output filename
        $filename = "BAF_Parade_Report_{$formatted_date}.pdf";
        
        // Output PDF
        $pdf->Output($filename, 'I'); // 'I' for inline display, 'D' for download
        
    } catch (Exception $e) {
        // If PDF generation fails, output HTML with error message
        header('Content-Type: text/html; charset=utf-8');
        echo '<div style="background: #ffebee; padding: 20px; margin: 20px; border: 1px solid #f44336; border-radius: 5px;">';
        echo '<h3 style="color: #c62828;">PDF Generation Error</h3>';
        echo '<p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p><strong>Fallback:</strong> Showing HTML version below.</p>';
        echo '<hr>';
        echo '</div>';
        echo $html;
    }
}
?>