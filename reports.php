<?php
// reports.php - Reports Dashboard
require_once 'config.php';
require_once 'functions.php';

// Get date range for reports
$start_date = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
$end_date = $_GET['end_date'] ?? date('Y-m-d'); // Current date

// Get reports for the date range
$stmt = $pdo->prepare("
    SELECT report_date, total_strength, total_on_parade, total_absent, total_female, generated_at
    FROM reports
    WHERE report_date BETWEEN ? AND ?
    ORDER BY report_date DESC
");
$stmt->execute([$start_date, $end_date]);
$reports = $stmt->fetchAll();

// Calculate summary statistics
$total_reports = count($reports);
$avg_attendance = $total_reports > 0 ? round(array_sum(array_column($reports, 'total_on_parade')) / $total_reports, 1) : 0;
$avg_absent = $total_reports > 0 ? round(array_sum(array_column($reports, 'total_absent')) / $total_reports, 1) : 0;

// Get attendance trends (last 30 days)
$stmt = $pdo->prepare("
    SELECT report_date, total_on_parade, total_absent
    FROM reports
    WHERE report_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    ORDER BY report_date ASC
");
$stmt->execute();
$trends = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports Dashboard - BAF System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .reports-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
            border-left: 4px solid #667eea;
        }
        .btn-custom {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            color: white;
        }
        .btn-custom:hover {
            background: linear-gradient(45deg, #764ba2, #667eea);
            color: white;
        }
        .report-row:hover {
            background-color: #f8f9fa;
        }
        .trend-chart {
            height: 300px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 20px;
            background: #f8f9fa;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-military-tech"></i> BAF Parade State System
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">Dashboard</a>
                <a class="nav-link" href="officers.php">Officers</a>
                <a class="nav-link" href="parade.php">Parade State</a>
                <a class="nav-link active" href="reports.php">Reports</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Header -->
        <div class="reports-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2><i class="fas fa-chart-bar"></i> Reports Dashboard</h2>
                    <p class="mb-0">Generate and view parade state reports</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="generate_report.php?date=<?php echo date('Y-m-d'); ?>" class="btn btn-light" target="_blank">
                        <i class="fas fa-file-pdf"></i> Today's Report
                    </a>
                </div>
            </div>
        </div>

        <!-- Summary Statistics -->
        <div class="row">
            <div class="col-md-3">
                <div class="stat-card">
                    <h3 class="text-primary"><?php echo $total_reports; ?></h3>
                    <p class="mb-0"><i class="fas fa-file-alt"></i> Total Reports</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h3 class="text-success"><?php echo $avg_attendance; ?></h3>
                    <p class="mb-0"><i class="fas fa-user-check"></i> Avg Attendance</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h3 class="text-warning"><?php echo $avg_absent; ?></h3>
                    <p class="mb-0"><i class="fas fa-user-times"></i> Avg Absent</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h3 class="text-info"><?php echo $total_reports > 0 ? round(($avg_attendance / ($avg_attendance + $avg_absent)) * 100, 1) : 0; ?>%</h3>
                    <p class="mb-0"><i class="fas fa-percentage"></i> Attendance Rate</p>
                </div>
            </div>
        </div>

        <!-- Quick Report Generation -->
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-bolt"></i> Quick Report Generation</h5>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Select Date:</label>
                                <input type="date" id="reportDate" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="col-md-9">
                                <button class="btn btn-custom" onclick="generateReport()">
                                    <i class="fas fa-file-pdf"></i> Generate PDF Report
                                </button>
                                <button class="btn btn-outline-primary" onclick="generateSummary()">
                                    <i class="fas fa-list"></i> View Summary
                                </button>
                                <div class="btn-group ms-2">
                                    <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                        <i class="fas fa-calendar"></i> Quick Dates
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="setQuickDate('today')">Today</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="setQuickDate('yesterday')">Yesterday</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="setQuickDate('week')">This Week</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="setQuickDate('month')">This Month</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Date Range Filter -->
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Start Date:</label>
                                <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">End Date:</label>
                                <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
                            </div>
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-custom">
                                    <i class="fas fa-filter"></i> Filter Reports
                                </button>
                                <a href="reports.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Clear Filter
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Trend Chart -->
        <?php if (!empty($trends)): ?>
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-line"></i> Attendance Trend (Last 30 Days)</h5>
                    </div>
                    <div class="card-body">
                        <div class="trend-chart d-flex align-items-center justify-content-center">
                            <div class="text-center">
                                <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Chart visualization would be implemented here using Chart.js or similar library</p>
                                <p><small>Data points: <?php echo count($trends); ?> days</small></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Reports Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-table"></i> Generated Reports (<?php echo count($reports); ?>)</h5>
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-download"></i> Export All
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($reports)): ?>
                        <div class="text-center p-5">
                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                            <h5>No reports found</h5>
                            <p class="text-muted">No reports have been generated for the selected date range.</p>
                            <a href="generate_report.php?date=<?php echo date('Y-m-d'); ?>" class="btn btn-custom" target="_blank">
                                <i class="fas fa-plus"></i> Generate Today's Report
                            </a>
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Date</th>
                                        <th>Total Strength</th>
                                        <th>On Parade</th>
                                        <th>Absent</th>
                                        <th>Attendance %</th>
                                        <th>Female Officers</th>
                                        <th>Generated</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($reports as $report): ?>
                                    <tr class="report-row">
                                        <td>
                                            <strong><?php echo date('d M Y', strtotime($report['report_date'])); ?></strong>
                                            <br><small class="text-muted"><?php echo date('D', strtotime($report['report_date'])); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo $report['total_strength']; ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success"><?php echo $report['total_on_parade']; ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning text-dark"><?php echo $report['total_absent']; ?></span>
                                        </td>
                                        <td>
                                            <?php 
                                            $attendance_percent = round(($report['total_on_parade'] / $report['total_strength']) * 100, 1);
                                            $color_class = $attendance_percent >= 90 ? 'success' : ($attendance_percent >= 80 ? 'warning' : 'danger');
                                            ?>
                                            <span class="badge bg-<?php echo $color_class; ?>"><?php echo $attendance_percent; ?>%</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?php echo $report['total_female']; ?></span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo date('d/m/Y H:i', strtotime($report['generated_at'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="generate_report.php?date=<?php echo $report['report_date']; ?>" 
                                                   class="btn btn-outline-primary" target="_blank" title="View PDF">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-info" 
                                                        title="View Details" onclick="viewDetails('<?php echo $report['report_date']; ?>')">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function generateReport() {
            const date = document.getElementById('reportDate').value;
            if (date) {
                window.open('generate_report.php?date=' + date, '_blank');
            } else {
                alert('Please select a date first.');
            }
        }

        function generateSummary() {
            const date = document.getElementById('reportDate').value;
            if (date) {
                // You can implement a summary view page
                alert('Summary view for ' + date + '\nThis would show a detailed breakdown of the parade state.');
            } else {
                alert('Please select a date first.');
            }
        }

        function setQuickDate(period) {
            const dateInput = document.getElementById('reportDate');
            const today = new Date();
            let targetDate = new Date();

            switch(period) {
                case 'today':
                    targetDate = today;
                    break;
                case 'yesterday':
                    targetDate.setDate(today.getDate() - 1);
                    break;
                case 'week':
                    targetDate.setDate(today.getDate() - today.getDay());
                    break;
                case 'month':
                    targetDate = new Date(today.getFullYear(), today.getMonth(), 1);
                    break;
            }

            dateInput.value = targetDate.toISOString().split('T')[0];
        }

        function viewDetails(date) {
            // This would open a detailed view of the parade state for that date
            alert('Detailed view for ' + date + '\nThis would show officer-by-officer breakdown.');
        }
    </script>
</body>
</html>