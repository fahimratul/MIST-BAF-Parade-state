<?php
// generate_report.php - Fixed PDF Report Generator with TCPDF
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
            padding: 10px;
            font-size: 9px;
            line-height: 1.1;
            color: #000;
        }
        
        .header {
            text-align: center;
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 10px;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
        }
        
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 8px;
        }
        
        .report-table th,
        .report-table td {
            border: 0.5px solid #000;
            padding: 2px;
            text-align: center;
            vertical-align: middle;
            font-size: 7px;
        }
        
        .report-table th {
            background-color: #d3d3d3;
            font-weight: bold;
            font-size: 7px;
        }
        
        .dept-cell {
            text-align: left;
            padding-left: 3px;
            font-weight: bold;
        }
        
        .level-cell {
            text-align: center;
            font-size: 7px;
        }
        
        .number-cell {
            text-align: center;
            font-size: 7px;
        }
        
        .total-row {
            font-weight: bold;
            background-color: #f0f0f0;
        }
        
        .summary-section {
            margin-top: 8px;
            font-size: 8px;
            line-height: 1.2;
        }
        
        .summary-item {
            margin-bottom: 2px;
        }
        
        .absent-details {
            margin-top: 5px;
            margin-left: 10px;
        }
        
        .absent-details ol {
            margin: 2px 0;
            padding-left: 12px;
        }
        
        .section-title {
            font-weight: bold;
            margin-top: 5px;
            margin-bottom: 2px;
        }
        
        .text-left {
            text-align: left;
        }
        
        .no-break {
            page-break-inside: avoid;
        }
        
        .dash {
            color: #000;
        }
    </style>
</head>
<body>
    <div class="header">
        PARADE STATE MIST STUDENT OFFRS (AIR FORCE) Dt: <?php echo $formatted_date; ?>
    </div>

    <table class="report-table">
        <thead>
            <tr>
                <th rowspan="2">Ser</th>
                <th rowspan="2">Dept</th>
                <th rowspan="2">Level</th>
                <th colspan="3">MIST Dhaka Mess</th>
                <th colspan="3">MIST Mirpur Mess</th>
                <th colspan="3">BAF Base AKR</th>
                <th rowspan="2">Level<br>wise<br>Str</th>
                <th rowspan="2">Total<br>Str</th>
            </tr>
            <tr>
                <th>Str</th>
                <th>On<br>Parade</th>
                <th>Absent</th>
                <th>Str</th>
                <th>On<br>Parade</th>
                <th>Absent</th>
                <th>Str</th>
                <th>On<br>Parade</th>
                <th>Absent</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $ser_no = 1;
            $grand_totals = [
                'dhaka' => ['str' => 0, 'present' => 0, 'absent' => 0],
                'mirpur' => ['str' => 0, 'present' => 0, 'absent' => 0],
                'akr' => ['str' => 0, 'present' => 0, 'absent' => 0]
            ];
            
            // Process each department
            foreach($parade_data['departments'] as $dept):
                $dept_levels = [];
                $dept_total_str = 0;
                
                // Collect all levels for this department that have data
                foreach($parade_data['levels'] as $level) {
                    $level_has_data = false;
                    $level_data = [
                        'level' => $level,
                        'dhaka' => $parade_data['parade_data'][$dept][$level]['MIST Dhaka Mess'] ?? ['total' => 0, 'present' => 0, 'absent' => 0],
                        'mirpur' => $parade_data['parade_data'][$dept][$level]['MIST Mirpur Mess'] ?? ['total' => 0, 'present' => 0, 'absent' => 0],
                        'akr' => $parade_data['parade_data'][$dept][$level]['BAF Base AKR'] ?? ['total' => 0, 'present' => 0, 'absent' => 0]
                    ];
                    
                    // Check if this level has any data
                    if ($level_data['dhaka']['total'] > 0 || $level_data['mirpur']['total'] > 0 || $level_data['akr']['total'] > 0) {
                        $level_has_data = true;
                        $level_data['level_total'] = $level_data['dhaka']['total'] + $level_data['mirpur']['total'] + $level_data['akr']['total'];
                        $dept_total_str += $level_data['level_total'];
                        
                        // Add to grand totals
                        $grand_totals['dhaka']['str'] += $level_data['dhaka']['total'];
                        $grand_totals['dhaka']['present'] += $level_data['dhaka']['present'];
                        $grand_totals['dhaka']['absent'] += $level_data['dhaka']['absent'];
                        
                        $grand_totals['mirpur']['str'] += $level_data['mirpur']['total'];
                        $grand_totals['mirpur']['present'] += $level_data['mirpur']['present'];
                        $grand_totals['mirpur']['absent'] += $level_data['mirpur']['absent'];
                        
                        $grand_totals['akr']['str'] += $level_data['akr']['total'];
                        $grand_totals['akr']['present'] += $level_data['akr']['present'];
                        $grand_totals['akr']['absent'] += $level_data['akr']['absent'];
                        
                        $dept_levels[] = $level_data;
                    }
                }
                
                // Only display department if it has data
                if (!empty($dept_levels)):
                    $first_row = true;
                    foreach($dept_levels as $level_data):
            ?>
            <tr>
                <?php if($first_row): ?>
                <td rowspan="<?php echo count($dept_levels); ?>" class="number-cell"><?php echo $ser_no; ?>.</td>
                <td rowspan="<?php echo count($dept_levels); ?>" class="dept-cell"><?php echo $dept; ?></td>
                <?php $first_row = false; endif; ?>
                
                <td class="level-cell"><?php echo $level_data['level']; ?></td>
                
                <!-- MIST Dhaka Mess -->
                <td class="number-cell"><?php echo $level_data['dhaka']['total'] > 0 ? $level_data['dhaka']['total'] : '-'; ?></td>
                <td class="number-cell"><?php echo $level_data['dhaka']['present'] > 0 ? $level_data['dhaka']['present'] : '-'; ?></td>
                <td class="number-cell"><?php echo $level_data['dhaka']['absent'] > 0 ? $level_data['dhaka']['absent'] : '-'; ?></td>
                
                <!-- MIST Mirpur Mess -->
                <td class="number-cell"><?php echo $level_data['mirpur']['total'] > 0 ? $level_data['mirpur']['total'] : '-'; ?></td>
                <td class="number-cell"><?php echo $level_data['mirpur']['present'] > 0 ? $level_data['mirpur']['present'] : '-'; ?></td>
                <td class="number-cell"><?php echo $level_data['mirpur']['absent'] > 0 ? $level_data['mirpur']['absent'] : '-'; ?></td>
                
                <!-- BAF Base AKR -->
                <td class="number-cell"><?php echo $level_data['akr']['total'] > 0 ? $level_data['akr']['total'] : '-'; ?></td>
                <td class="number-cell"><?php echo $level_data['akr']['present'] > 0 ? $level_data['akr']['present'] : '-'; ?></td>
                <td class="number-cell"><?php echo $level_data['akr']['absent'] > 0 ? $level_data['akr']['absent'] : '-'; ?></td>
                
                <!-- Level wise total -->
                <td class="number-cell"><?php echo $level_data['level_total']; ?></td>
                
                <!-- Department total (only on first row) -->
                <?php if($level_data === $dept_levels[0]): ?>
                <td rowspan="<?php echo count($dept_levels); ?>" class="number-cell"><?php echo $dept_total_str; ?></td>
                <?php endif; ?>
            </tr>
            <?php 
                    endforeach;
                    $ser_no++;
                endif;
            endforeach; 
            ?>
            
            <!-- Grand Total Row -->
            <tr class="total-row">
                <td colspan="3"><strong>Grand Total :</strong></td>
                <td class="number-cell"><strong><?php echo $grand_totals['dhaka']['str']; ?></strong></td>
                <td class="number-cell"><strong><?php echo $grand_totals['dhaka']['present']; ?></strong></td>
                <td class="number-cell"><strong><?php echo $grand_totals['dhaka']['absent']; ?></strong></td>
                <td class="number-cell"><strong><?php echo $grand_totals['mirpur']['str']; ?></strong></td>
                <td class="number-cell"><strong><?php echo $grand_totals['mirpur']['present']; ?></strong></td>
                <td class="number-cell"><strong><?php echo $grand_totals['mirpur']['absent']; ?></strong></td>
                <td class="number-cell"><strong><?php echo $grand_totals['akr']['str']; ?></strong></td>
                <td class="number-cell"><strong><?php echo $grand_totals['akr']['present']; ?></strong></td>
                <td class="number-cell"><strong><?php echo $grand_totals['akr']['absent']; ?></strong></td>
                <td class="number-cell"><strong><?php echo $summary['total_strength']; ?></strong></td>
                <td class="number-cell"><strong><?php echo $summary['total_strength']; ?></strong></td>
            </tr>
        </tbody>
    </table>

    <!-- Summary Section -->
    <div class="summary-section no-break">
        <div class="summary-item">
            <strong>1. Grand Total:</strong> Dhaka Mess + MIST Mess + BAF Base AKR = 
            <?php echo $grand_totals['dhaka']['str']; ?>+
            <?php echo $grand_totals['mirpur']['str']; ?>+
            <?php echo $grand_totals['akr']['str']; ?>=
            <?php echo $summary['total_strength']; ?>
        </div>
        
        <div class="summary-item">
            <strong>2. Level wise Total Offrs:</strong> 
            <?php 
            $level_parts = [];
            foreach($summary['level_totals'] as $level => $count) {
                $level_display = str_replace(['I', 'II', 'III', 'IV'], ['1', '2', '3', '4'], $level);
                if($count > 0) {
                    $level_parts[] = 'Level-' . $level_display . '=' . $count;
                } else {
                    $level_parts[] = 'Level-' . $level_display . '= Nil';
                }
            }
            echo implode(', ', $level_parts);
            ?>
        </div>
        
        <div class="summary-item">
            <strong>3. Total On Parade:</strong> Dhaka Mess + MIST Mess + BAF Base AKR = 
            <?php echo $grand_totals['dhaka']['present']; ?>+ 
            <?php echo $grand_totals['mirpur']['present']; ?>+
            <?php echo $grand_totals['akr']['present']; ?>=
            <?php echo $summary['total_on_parade']; ?>
        </div>
        
        <div class="summary-item">
            <strong>4. Total Female Offrs:</strong> 
            <?php if(!empty($summary['female_by_location'])): ?>
                (<?php 
                $female_parts = [];
                foreach($summary['female_by_location'] as $location => $count) {
                    if($count > 0) {
                        $location_name = str_replace(['MIST ', 'BAF Base '], ['In MIST ', ''], $location);
                        $female_parts[] = $location_name . ': ' . $count;
                    }
                }
                echo implode(' and ', $female_parts);
                ?>)=<?php echo $summary['total_female']; ?>
            <?php else: ?>
                0
            <?php endif; ?>
        </div>
        
        <div class="section-title">5. Details of Absent/Off-parade:</div>
        
        <div class="text-left absent-details">
        <?php if(!empty($absent_officers)): ?>
            <strong>a. MIST Mirpur Mess:</strong>

            <ol>
                <?php 
                $mirpur_count = 0;
                foreach($absent_officers as $officer): 
                    if($officer['mess_location'] == 'MIST Mirpur Mess'): 
                ?>
                <li><?php echo $officer['rank'] . ' ' . $officer['name'] . '- ' . $officer['department'] . ', L-' . str_replace(['I', 'II', 'III', 'IV'], ['1', '2', '3', '4'], $officer['level']) . ' , ' . $officer['short_status']; ?></li>
                <?php 
                    $mirpur_count++;
                    endif;
                endforeach; 
                ?>
            </ol>
            
            <strong>b. MIST Dhaka Mess:</strong>
            <ol>
                <?php 
                $dhaka_count = 0;
                foreach($absent_officers as $officer): 
                    if($officer['mess_location'] == 'MIST Dhaka Mess'): 
                ?>
                <li><?php echo $officer['rank'] . ' ' . $officer['name'] . '- ' . $officer['department'] . ', L-' . str_replace(['I', 'II', 'III', 'IV'], ['1', '2', '3', '4'], $officer['level']) . ' , ' . $officer['short_status']; ?></li>
                <?php 
                    $dhaka_count++;
                    endif;
                endforeach; 
                ?>
            </ol>

            
            <strong>c. BAF Base AKR:</strong>
            <ol>
                <?php 
                $akr_count = 0;
                foreach($absent_officers as $officer): 
                    if($officer['mess_location'] == 'BAF Base AKR'): 
                ?>
                <li><?php echo $officer['rank'] . ' ' . $officer['name'] . '- ' . $officer['department'] . ', L-' . str_replace(['I', 'II', 'III', 'IV'], ['1', '2', '3', '4'], $officer['level']) . ' , ' . $officer['short_status']; ?></li>
                <?php 
                    $akr_count++;
                    endif;
                endforeach; 
                ?>
            </ol>
            <?php else: ?>
                <strong>a. MIST Mirpur Mess:</strong>  Nil 
                <br><br>
                <strong>b. MIST Dhaka Mess:</strong>Nil
                <br><br>
                <strong>c. BAF Base AKR:</strong>Nil
                <br><br>
            <?php endif; ?>
        </div>
        
        <div class="section-title">7. Summary of Absents/Off-parade: <?php echo str_pad($summary['total_absent'], 2, '0', STR_PAD_LEFT); ?></div>
        <div class="text-left">
            <?php if(!empty($summary['absent_breakdown'])): ?>
                <?php 
                $breakdown_items = [
                    'Leave' => $summary['absent_breakdown']['Leave'] ?? 0,
                    'CMH' => $summary['absent_breakdown']['CMH'] ?? 0,
                    'Sick Leave' => $summary['absent_breakdown']['Sick Leave'] ?? 0,
                    'SIQ' => $summary['absent_breakdown']['SIQ'] ?? 0,
                    'Isolation' => $summary['absent_breakdown']['Isolation'] ?? 0
                ];
                
                $breakdown_letters = ['a', 'b', 'c', 'd', 'e'];
                $i = 0;
                foreach($breakdown_items as $reason => $count): 
                ?>
                <div><strong><?php echo $breakdown_letters[$i]; ?>. <?php echo $reason; ?>:</strong><?php echo $count > 0 ? str_pad($count, 2, '0', STR_PAD_LEFT) : ''; ?></div>
                <?php 
                $i++;
                endforeach; 
                ?>
            <?php else: ?>
                <div><strong>a. Leave:</strong></div>
                <div><strong>b. CMH:</strong></div>
                <div><strong>c. Sick Leave:</strong></div>
                <div><strong>d. SIQ:</strong></div>
                <div><strong>e. Isolation:</strong></div>
            <?php endif; ?>
        </div>
        
        <div class="section-title">8. Maj Changes/ Note:</div>
        <div style="height: 15px;"></div> <!-- Space for manual notes -->
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
        
        // Remove header and footer
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);
        
        // Set margins (smaller for more content)
        $pdf->SetMargins(8, 8, 8);
        $pdf->SetAutoPageBreak(TRUE, 10);
        
        // Set image scale factor
        $pdf->setImageScale(1.25);
        
        // Set font
        $pdf->SetFont('dejavusans', '', 8);
        
        // Add a page
        $pdf->AddPage();
        
        // Write HTML content
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