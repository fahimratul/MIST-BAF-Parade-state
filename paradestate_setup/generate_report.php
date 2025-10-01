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
            font-family: Arial;
            margin: 0;
            padding: 15px;
            font-size: 11px;
            line-height: 1.2;
            color: #000;
        }
        
        .header {
            text-align: center;
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 18px;
            padding-bottom: 10px;
            width: fit-content ;
            max-width: fit-content;
            justify-self: center;

        }
        
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 12px;
        }
        
        .report-table th,
        .report-table td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
        }
        
        .report-table th {
            background-color: yellow;
            color: #000000ff;
            font-weight: bold;
            font-size: 12px;
            padding-bottom: 5px;
        }
        
        .dept-header {
            font-weight: bold;
            text-align: left;
            padding-left: 5px;
        }
        .dept-header.CSE{
            background-color: #c6d9f1;
        }
        .dept-header.ME{
            background-color: yellowgreen;
        }
        .dept-header.AE{
            background-color: #ffffcc;
        }
        .dept-header.EECE{
            background-color: #fabf8f;
        }
        .dept-ser {
            background-color: #ffffffff;
            font-weight: bold;
            text-align: center;
            padding-left: 5px;
        }
        
       
        
        .grand-total {
            font-weight: bold;
            background-color:yellow;
        }
        
        .summary-section {
            margin-top: 15px;
            font-size: 12px;
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
        .highlight {
            background-color: yellowgreen;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="header">
        <strong style="background-color: aqua;text-decoration: underline;">PARADE STATE MIST STUDENT OFFRS (AIR FORCE)</strong> 
         &nbsp;&nbsp;&nbsp;&nbsp;<strong style="background-color: yellow;">Dt: <?php echo $formatted_date; ?></strong>
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
                    $level_data = [
                        'level' => $level,
                        'dhaka' => $parade_data['parade_data'][$dept][$level]['MIST Dhaka Mess'] ?? ['total' => 0, 'present' => 0, 'absent' => 0],
                        'mirpur' => $parade_data['parade_data'][$dept][$level]['MIST Mirpur Mess'] ?? ['total' => 0, 'present' => 0, 'absent' => 0],
                        'akr' => $parade_data['parade_data'][$dept][$level]['BAF Base AKR'] ?? ['total' => 0, 'present' => 0, 'absent' => 0]
                    ];
                    
                    // Check if this level has any data
                    if ($level_data['dhaka']['total'] > 0 || $level_data['mirpur']['total'] > 0 || $level_data['akr']['total'] > 0) {
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
                <td rowspan="<?php echo count($dept_levels); ?>" class="dept-ser"><?php echo $ser_no; ?>.</td>
                <td rowspan="<?php echo count($dept_levels); ?>" class="dept-header <?php echo $dept; ?>"><?php echo $dept; ?></td>
                <?php $first_row = false; endif; ?>
                
                <td class="dept-header <?php echo $dept; ?>"><?php echo $level_data['level']; ?></td>
                
                <!-- MIST Dhaka Mess -->
                <td class="dept-header <?php echo $dept; ?>"><?php echo $level_data['dhaka']['total'] > 0 ? $level_data['dhaka']['total'] : '-'; ?></td>
                <td class="dept-header <?php echo $dept; ?>"><?php echo $level_data['dhaka']['present'] > 0 ? $level_data['dhaka']['present'] : '-'; ?></td>
                <td class="dept-header <?php echo $dept; ?>"><?php echo $level_data['dhaka']['absent'] > 0 ? $level_data['dhaka']['absent'] : '-'; ?></td>

                <!-- MIST Mirpur Mess -->
                <td class="dept-header <?php echo $dept; ?>"><?php echo $level_data['mirpur']['total'] > 0 ? $level_data['mirpur']['total'] : '-'; ?></td>
                <td class="dept-header <?php echo $dept; ?>"><?php echo $level_data['mirpur']['present'] > 0 ? $level_data['mirpur']['present'] : '-'; ?></td>
                <td class="dept-header <?php echo $dept; ?>"><?php echo $level_data['mirpur']['absent'] > 0 ? $level_data['mirpur']['absent'] : '-'; ?></td>

                <!-- BAF Base AKR -->
                <td class="dept-header <?php echo $dept; ?>"><?php echo $level_data['akr']['total'] > 0 ? $level_data['akr']['total'] : '-'; ?></td>
                <td class="dept-header <?php echo $dept; ?>"><?php echo $level_data['akr']['present'] > 0 ? $level_data['akr']['present'] : '-'; ?></td>
                <td class="dept-header <?php echo $dept; ?>"><?php echo $level_data['akr']['absent'] > 0 ? $level_data['akr']['absent'] : '-'; ?></td>

                <!-- Level wise total -->
                <td class="dept-header <?php echo $dept; ?>"><?php echo $level_data['level_total']; ?></td>
                
                <!-- Department total (only on first row) -->
                <?php if($level_data === $dept_levels[0]): ?>
                <td rowspan="<?php echo count($dept_levels); ?>" class="dept-header <?php echo $dept; ?>"><?php echo $dept_total_str; ?></td>
                <?php endif; ?>
            </tr>
            <?php 
                    endforeach;
                    $ser_no++;
                endif;
            endforeach; 
            ?>
            
            <!-- Grand Total Row -->
            <tr class="grand-total">
                <td colspan="3"><strong>Grand Total :</strong></td>
                <td><strong><?php echo $grand_totals['dhaka']['str']; ?></strong></td>
                <td><strong><?php echo $grand_totals['dhaka']['present']; ?></strong></td>
                <td><strong><?php echo $grand_totals['dhaka']['absent']; ?></strong></td>
                <td><strong><?php echo $grand_totals['mirpur']['str']; ?></strong></td>
                <td><strong><?php echo $grand_totals['mirpur']['present']; ?></strong></td>
                <td><strong><?php echo $grand_totals['mirpur']['absent']; ?></strong></td>
                <td><strong><?php echo $grand_totals['akr']['str']; ?></strong></td>
                <td><strong><?php echo $grand_totals['akr']['present']; ?></strong></td>
                <td><strong><?php echo $grand_totals['akr']['absent']; ?></strong></td>
                <td><strong><?php echo $summary['total_strength']; ?></strong></td>
                <td><strong><?php echo $summary['total_strength']; ?></strong></td>
            </tr>
        </tbody>
    </table>

    <!-- Summary Section -->
    <div class="summary-section">
        <div class="summary-item">
            1. <strong class="highlight">Grand Total:</strong> Dhaka Mess + MIST Mess + BAF Base AKR = 
            <?php echo $grand_totals['dhaka']['str']; ?> + 
            <?php echo $grand_totals['mirpur']['str']; ?> + 
            <?php echo $grand_totals['akr']['str']; ?> = 
            <?php echo $summary['total_strength']; ?>
        </div>
        
        <div class="summary-item">
            2. <strong class="highlight">Level wise Total Offrs:</strong> 
            <?php 
            $level_mapping = ['I' => '1', 'II' => '2', 'III' => '3', 'IV' => '4'];
            $level_parts = [];
            
            foreach(['I', 'II', 'III', 'IV'] as $level) {
                $count = $summary['level_totals'][$level] ?? 0;
                $level_num = $level_mapping[$level];
                if($count > 0) {
                    $level_parts[] = 'Level-' . $level_num . '=' . $count;
                } else {
                    $level_parts[] = 'Level-' . $level_num . '= Nil';
                }
            }
            echo implode(', ', $level_parts);
            ?>
        </div>
        
        <div class="summary-item">
            3. <strong class="highlight">Total On Parade:</strong> Dhaka Mess + MIST Mess + BAF Base AKR = 
            <?php echo $grand_totals['dhaka']['present']; ?> + 
            <?php echo $grand_totals['mirpur']['present']; ?> + 
            <?php echo $grand_totals['akr']['present']; ?> = 
            <?php echo $summary['total_on_parade']; ?>
        </div>
        
        <div class="summary-item">
            4. <strong class="highlight">Total Female Offrs:</strong> 
            <?php if(!empty($summary['female_by_location']) && $summary['total_female'] > 0): ?>
                (<?php 
                $female_parts = [];
                foreach($summary['female_by_location'] as $location => $count) {
                    if($count > 0) {
                        if(strpos($location, 'Dhaka') !== false) {
                            $female_parts[] = 'In MIST Dhaka Mess: ' . $count;
                        } elseif(strpos($location, 'Mirpur') !== false) {
                            $female_parts[] = 'In MIST Mirpur Mess: ' . $count;
                        } elseif(strpos($location, 'AKR') !== false || strpos($location, 'BAF') !== false) {
                            $female_parts[] = 'AKR: ' . $count;
                        }
                    }
                }
                echo implode(' and ', $female_parts);
                ?>)=<?php echo $summary['total_female']; ?>
            <?php else: ?>
                0
            <?php endif; ?>
        </div>
        
        <div class="section-title">5. <strong class="highlight">Details of Absent/Off-parade:</strong></div>
        
        <div class="text-left absent-details">
            <strong style="background-color: aqua;">a. MIST Mirpur Mess:</strong><br><br>
            <?php if(!empty($absent_officers)): ?>
            <ol>
                <?php 
                foreach($absent_officers as $officer): 
                    if($officer['mess_location'] == 'MIST Mirpur Mess'): 
                ?>
                <li><?php echo $officer['rank'] . ' ' . $officer['name'] . '- ' . $officer['department'] . ', L -' . str_replace([ 'III', 'II','IV'], ['3', '2', '4'], $officer['level']) . ' , ' . $officer['short_status']; ?></li>
                <?php 
                    endif;
                endforeach; 
                ?>
            </ol>
            <?php endif; ?>
            <br>
            <strong  style="background-color: aqua;">b. MIST Dhaka Mess:</strong><br>
            <?php if(!empty($absent_officers)): ?>
            <ol>
                <?php 
                foreach($absent_officers as $officer): 
                    if($officer['mess_location'] == 'MIST Dhaka Mess'): 
                ?>
                <li><?php echo $officer['rank'] . ' ' . $officer['name'] . '- ' . $officer['department'] . ', L -' . str_replace([ 'III', 'II','IV'], ['3', '2', '4'], $officer['level']) . ' , ' . $officer['short_status']; ?></li>
                <?php 
                    endif;
                endforeach; 
                ?>
            </ol>
            <?php endif; ?>
                <br>
            <strong style="background-color: aqua;">c. BAF Base AKR:</strong><br>
                <?php if(!empty($absent_officers)): ?>
            <ol>
                <?php 
                foreach($absent_officers as $officer): 
                    if($officer['mess_location'] == 'BAF Base AKR'): 
                ?>
                <li><?php echo $officer['rank'] . ' ' . $officer['name'] . '- ' . $officer['department'] . ', L -' . str_replace([ 'III', 'II','IV'], ['3', '2', '4'], $officer['level']) . ' , ' . $officer['short_status']; ?></li>
                <?php
                    endif;
                endforeach; 
                ?>
            </ol>
            <?php endif; ?>
            <br>
        </div>
        
        <div class="section-title">7. <strong class="highlight">Summary of Absents/Off-parade:</strong> <?php echo str_pad($summary['total_absent'], 2, '0', STR_PAD_LEFT); ?></div>
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

        <div class="section-title">8. <strong class="highlight">Maj Changes/ Note:</strong></div>
        <div style="height: 20px;"></div>
    </div>
</body>
</html>

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
            'margin_left' => 14,
            'margin_right' => 10,
            'margin_top' => 12,
            'margin_bottom' => 12,
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
        $filename = "Details_Okay_Report(BAF)_{$formatted_date}.pdf";
        
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