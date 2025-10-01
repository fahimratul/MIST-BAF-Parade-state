<?php
// index.php - Main Dashboard
require_once 'config.php';
require_once 'functions.php';

$current_date = date('Y-m-d');
$officers_count = getOfficersCount($pdo);
$today_parade_data = getParadeData($pdo, $current_date);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BAF Parade State Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .dashboard-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
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
        }
        .btn-custom {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 25px;
            padding: 12px 30px;
            color: white;
            font-weight: bold;
            margin: 5px;
        }
        .btn-custom:hover {
            background: linear-gradient(45deg, #764ba2, #667eea);
            color: white;
            transform: translateY(-2px);
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
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
                <a class="nav-link" href="officers.php">Officers</a>
                <a class="nav-link" href="parade.php">Parade State</a>
                <a class="nav-link" href="reports.php">Reports</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Header -->
        <div class="dashboard-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-home"></i> Dashboard</h1>
                    <p class="mb-0">Bangladesh Air Force - Military Institute of Science & Technology</p>
                    <p class="mb-0">Date: <?php echo date('d-m-Y'); ?></p>
                </div>
                <div class="col-md-4 text-end">
                    <i class="fas fa-flag fa-4x opacity-50"></i>
                </div>
            </div>
        </div>

        <!-- Statistics Row -->
        <div class="row">
            <div class="col-md-3">
                <div class="stat-card">
                    <h3 class="text-primary"><?php echo $officers_count['total']; ?></h3>
                    <p class="mb-0"><i class="fas fa-users"></i> Total Officers</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h3 class="text-success"><?php echo $officers_count['on_parade_today']; ?></h3>
                    <p class="mb-0"><i class="fas fa-check-circle"></i> On Parade Today</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h3 class="text-warning"><?php echo $officers_count['absent_today']; ?></h3>
                    <p class="mb-0"><i class="fas fa-exclamation-circle"></i> Absent Today</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h3 class="text-info"><?php echo $officers_count['female']; ?></h3>
                    <p class="mb-0"><i class="fas fa-female"></i> Female Officers</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-bolt"></i> Quick Actions</h4>
                    </div>
                    <div class="card-body text-center">
                        <a href="add_officer.php" class="btn btn-custom">
                            <i class="fas fa-user-plus"></i> Add New Officer
                        </a>
                        <a href="parade.php" class="btn btn-custom">
                            <i class="fas fa-clipboard-list"></i> Mark Attendance
                        </a>
                        <a href="generate_report.php?date=<?php echo $current_date; ?>" class="btn btn-custom" target="_blank">
                            <i class="fas fa-file-pdf"></i> Generate Today's Report
                        </a>
                        <a href="reports.php" class="btn btn-custom">
                            <i class="fas fa-chart-bar"></i> View All Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Department Breakdown -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-building"></i> Department Breakdown</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php
                            $departments = ['CSE', 'EECE', 'ME', 'AE'];
                            foreach($departments as $dept) {
                                $dept_stats = getDepartmentStats($pdo, $dept, $current_date);
                            ?>
                            <div class="col-md-3">
                                <div class="card border-primary">
                                    <div class="card-body text-center">
                                        <h5 class="card-title text-primary"><?php echo $dept; ?></h5>
                                        <p class="mb-1">Total: <strong><?php echo $dept_stats['total']; ?></strong></p>
                                        <p class="mb-1">Present: <strong class="text-success"><?php echo $dept_stats['present']; ?></strong></p>
                                        <p class="mb-0">Absent: <strong class="text-warning"><?php echo $dept_stats['absent']; ?></strong></p>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-history"></i> Today's Absent Officers</h4>
                    </div>
                    <div class="card-body">
                        <?php
                        $absent_officers = getAbsentOfficers($pdo, $current_date);
                        if(empty($absent_officers)) {
                            echo '<p class="text-success"><i class="fas fa-check"></i> All officers are present today!</p>';
                        } else {
                        ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Department</th>
                                        <th>Level</th>
                                        <th>Mess Location</th>
                                        <th>Status</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($absent_officers as $officer) { ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($officer['name']); ?></td>
                                        <td><?php echo $officer['department']; ?></td>
                                        <td><?php echo $officer['level']; ?></td>
                                        <td><?php echo $officer['mess_location']; ?></td>
                                        <td>
                                            <span class="badge bg-warning"><?php echo $officer['status']; ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($officer['remarks'] ?? ''); ?></td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>