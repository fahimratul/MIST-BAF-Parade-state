<?php
// generate_report.php - Updated PDF Report Generator with mPDF
require_once 'config.php';
require_once 'functions.php';

// Check if mPDF is installed
if (!file_exists('vendor/autoload.php')) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>mPDF Not Found</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="alert alert-warning">
                        <h4><i class="fas fa-exclamation-triangle"></i> mPDF Library Not Found</h4>
                        <p>The mPDF library is required to generate PDF reports.</p>
                        <a href="install_composer.php" class="btn btn-primary">Run Auto-Installer</a>
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

require_once 'vendor/autoload.php';

// Get date from URL parameter or use current date
$report_date = $_GET['date'] ?? date('Y-m-d');
$formatted_date = date('d-m-y', strtotime($report_date));
$format = $_GET['format'] ?? 'pdf'; // pdf or html

// Get data for the report
$parade_data = getParadeData($pdo, $report_date);
$summary = getReportSummary($pdo, $report_date);
$absent_officers = getDetailedAbsentOfficers($pdo, $report_date);
$mirpur_mess_data = getMirpurMessData($pdo, $report_date);

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
            padding: 4px;
            text-align: center;
            vertical-align: middle;
        }
        
        .report-table th {
            background-color: #be2424;
            color: #ffffff;
            font-weight: bold;
            font-size: 9px;
        }
        
        .dept-header {
            background-color: #c6d9f0;
            font-weight: bold;
            text-align: left;
            padding-left: 5px;
        }
        
        .dept-total {
            background-color: #c6d9f0;
            font-weight: bold;
        }
        
        .level-total {
            background-color: #ffffcc;
            font-weight: bold;
        }
        
        .grand-total {
            font-weight: bold;
            background-color: #bebb24;
        }
        
        .summary-section {
            margin-top: 15px;
            font-size: 10px;
        }
        
        .summary-item {
            margin-bottom: 5px;
            line-height: 1.4;
        }
        
        .absent-details {
            margin-top: 10px;
            margin-left: 15px;
        }
        
        .absent-details ol {
            margin: 5px 0;
            padding-left: 20px;
        }
        
        .section-title {
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 5px;
        }
        
        .text-left {
            text-align: left;
        }
    </style>
</head>
<body>
    <p>
        Assalamualaikum sir,<br>
        <br>
        BAF Students Parade State as on <?php
            // Format date as "302100 SEP 25" where 2100 is constant
            $dateObj = DateTime::createFromFormat('Y-m-d', $report_date);
            $day = $dateObj ? $dateObj->format('d') : date('d');
            $month = $dateObj ? strtoupper($dateObj->format('M')) : strtoupper(date('M'));
            $year = $dateObj ? $dateObj->format('y') : date('y');
            echo "{$day}2100 {$month} {$year}";
        ?>
        <br>
        <br>
        Total Str: <?php echo $summary['total_strength']; ?><br>
        Offr: 48<br>
        Cdts: <?php echo $summary['total_strength'] - 48; ?><br>
        <br>
        On Parade: <?php echo $summary['total_on_parade']; ?><br>
        Off Parade: <?php echo $summary['total_absent']; ?><br>
        <br>
        Summary of Absent/Off parade:
        <br>
        <?php
        $breakdown_items = [
                    'Leave' => $summary['absent_breakdown']['Leave'] ?? 0,
                    'CMH' => $summary['absent_breakdown']['CMH'] ?? 0,
                    'Sick Leave' => $summary['absent_breakdown']['Sick Leave'] ?? 0,
                    'SIQ' => $summary['absent_breakdown']['SIQ'] ?? 0,
                    'Isolation' => $summary['absent_breakdown']['Isolation'] ?? 0
                ];
            foreach($breakdown_items as $reason => $count): 
                if($count > 0) {
                    echo str_pad($count, 2, '0', STR_PAD_LEFT) . " x {$reason} ("
                    ;
                    // List officer names for this reason
                    $officers_for_reason = array_filter($absent_officers, function($officer) use ($reason) {
                        return $officer['status'] === $reason;
                    });
                    if (!empty($officers_for_reason)) {
                        $names = array_map(function($officer) {
                            return $officer['rank'].' '.$officer['name'] . ' - ' . $officer['department'];
                        }, $officers_for_reason);
                        echo ' ' . implode(', ', $names) . '';
                    }
                    echo ")<br>";
                }
            endforeach;
        ?>
        <br>
        <br>
        Regards,<br>
        Flg Offr Romel<br />
    </p>
    <br>
    <p>
            Assalamualaikum sir,<br>
            <br>
            BAF Students Parade state(Mirpur Mess)  as on <?php
            // Format date as "302100 SEP 25" where 2100 is constant
            $dateObj = DateTime::createFromFormat('Y-m-d', $report_date);
            $day = $dateObj ? $dateObj->format('d') : date('d');
            $month = $dateObj ? strtoupper($dateObj->format('M')) : strtoupper(date('M'));
            $year = $dateObj ? $dateObj->format('y') : date('y');
            echo "{$day}2100 {$month} {$year}";
            ?> 
            <br><br>
            Total Str: <?php echo $mirpur_mess_data['total']; ?>
        
            <br>
            On Parade: <?php echo $mirpur_mess_data['on_parade']; ?></p>
            Absent: <?php echo ($mirpur_mess_data['absent'] == 0) ? 'Nil' : $mirpur_mess_data['absent']; ?></p>

            Disposal:
            <br>
            <?php if($mirpur_mess_data['absent'] !== 0): ?>
            <ol>
                <?php 
                foreach($mirpur_mess_data['officers'] as $officer): 
                    if($officer['status'] !== 'Present'): 
                ?>
                <li><?php echo $officer['rank'] . ' ' . $officer['name'] . '- ' . $officer['status']; ?></li>
                <?php 
                    endif;
                endforeach; 
                ?>
                <br>    
            </ol>
            <?php else: ?>
            Nil
            <br>

            <?php endif; ?>
            Regards
            <br>
        </p>    

<?php
$html = ob_get_clean();

// Decide output format
if ($format === 'html') {
    // Output as HTML (for preview)
    header('Content-Type: text/html; charset=utf-8');
    echo $html;
} else {
    // Generate PDF using mPDF
    try {
        // Create new PDF document in landscape orientation
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',  // A4 Landscape
            'orientation' => 'L',
            'margin_left' => 8,
            'margin_right' => 8,
            'margin_top' => 8,
            'margin_bottom' => 8,
            'margin_header' => 0,
            'margin_footer' => 0,
            'default_font_size' => 10,
            'default_font' => 'Arial'
        ]);
        
        // Set document information
        $mpdf->SetTitle("BAF Parade State Report - {$formatted_date}");
        $mpdf->SetAuthor('Bangladesh Air Force');
        $mpdf->SetCreator('BAF Parade System');
        
        // Write HTML content
        $mpdf->WriteHTML($html);
        
        // Set output filename
        $filename = "BAF_Parade_Report_{$formatted_date}.pdf";
        
        // Output PDF (I = inline display in browser, D = download)
        $mpdf->Output($filename, 'I');
        
    } catch (\Mpdf\MpdfException $e) {
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