<?php
// parade.php - Parade State Management
require_once 'config.php';
require_once 'functions.php';

$message = '';
$current_date = $_POST['parade_date'] ?? date('Y-m-d');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_attendance'])) {
    try {
        foreach ($_POST['officers'] as $officer_id => $data) {
            $status = $data['status'];
            $remarks = $data['remarks'] ?? '';
            updateAttendance($pdo, $officer_id, $current_date, $status, $remarks);
        }
        $message = '<div class="alert alert-success"><i class="fas fa-check"></i> Attendance updated successfully!</div>';
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Error updating attendance: ' . $e->getMessage() . '</div>';
    }
}

$officers = getAllOfficersWithStatus($pdo, $current_date);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parade State Management - BAF System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .status-present { background-color: #d4edda !important; }
        .status-absent { background-color: #f8d7da !important; }
        .dept-group {
            background-color: #e9ecef;
            font-weight: bold;
        }
        .officer-row:hover {
            background-color: #f8f9fa;
        }
        .quick-actions {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .btn-custom {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            color: white;
            margin: 2px;
        }
        .btn-custom:hover {
            background: linear-gradient(45deg, #764ba2, #667eea);
            color: white;
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
                <a class="nav-link active" href="parade.php">Parade State</a>
                <a class="nav-link" href="reports.php">Reports</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Header -->
        <div class="quick-actions">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2><i class="fas fa-clipboard-list"></i> Parade State Management</h2>
                    <p class="mb-0">Mark attendance for officers</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="generate_report.php?date=<?php echo $current_date; ?>" class="btn btn-light" target="_blank">
                        <i class="fas fa-file-pdf"></i> Generate Report
                    </a>
                </div>
            </div>
        </div>

        <?php echo $message; ?>

        <!-- Date Selection and Quick Actions -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <label for="parade_date" class="form-label"><strong>Select Parade Date:</strong></label>
                        <input type="date" id="parade_date" class="form-control" value="<?php echo $current_date; ?>" onchange="loadParadeDate()">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label"><strong>Quick Actions:</strong></label><br>
                        <button type="button" class="btn btn-custom btn-sm" onclick="markAllPresent()">
                            <i class="fas fa-check-circle"></i> Mark All Present
                        </button>
                        <button type="button" class="btn btn-custom btn-sm" onclick="markSelectedStatus('Leave')">
                            <i class="fas fa-calendar-alt"></i> Mark Selected as Leave
                        </button>
                        <button type="button" class="btn btn-custom btn-sm" onclick="markSelectedStatus('CMH')">
                            <i class="fas fa-hospital"></i> Mark Selected as CMH
                        </button>
                        <button type="button" class="btn btn-custom btn-sm" onclick="clearSelection()">
                            <i class="fas fa-times"></i> Clear Selection
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Form -->
        <form method="POST" id="attendanceForm">
            <input type="hidden" name="parade_date" value="<?php echo $current_date; ?>">
            
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-users"></i> Officers Attendance - <?php echo date('d M Y', strtotime($current_date)); ?></h4>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th width="3%">
                                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                    </th>
                                    <th>Name</th>
                                    <th>Rank</th>
                                    <th>Department</th>
                                    <th>Level</th>
                                    <th>Mess Location</th>
                                    <th>Gender</th>
                                    <th>Status</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $current_dept = '';
                                foreach($officers as $officer): 
                                    if($current_dept != $officer['department']):
                                        $current_dept = $officer['department'];
                                ?>
                                <tr class="dept-group">
                                    <td colspan="9" class="text-center">
                                        <strong><?php echo $current_dept; ?> Department</strong>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <tr class="officer-row <?php echo $officer['status'] != 'Present' ? 'status-absent' : 'status-present'; ?>">
                                    <td>
                                        <input type="checkbox" class="officer-select" name="selected[]" value="<?php echo $officer['id']; ?>">
                                    </td>
                                    <td><?php echo htmlspecialchars($officer['name']); ?></td>
                                    <td><?php echo htmlspecialchars($officer['rank']); ?></td>
                                    <td><?php echo $officer['department']; ?></td>
                                    <td class="text-center"><?php echo $officer['level']; ?></td>
                                    <td><?php echo $officer['mess_location']; ?></td>
                                    <td><?php echo $officer['gender']; ?></td>
                                    <td>
                                        <select name="officers[<?php echo $officer['id']; ?>][status]" class="form-select form-select-sm status-select">
                                            <option value="Present" <?php echo $officer['status'] == 'Present' ? 'selected' : ''; ?>>Present</option>
                                            <option value="Leave" <?php echo $officer['status'] == 'Leave' ? 'selected' : ''; ?>>Leave</option>
                                            <option value="CMH" <?php echo $officer['status'] == 'CMH' ? 'selected' : ''; ?>>CMH</option>
                                            <option value="Sick Leave" <?php echo $officer['status'] == 'Sick Leave' ? 'selected' : ''; ?>>Sick Leave</option>
                                            <option value="SIQ" <?php echo $officer['status'] == 'SIQ' ? 'selected' : ''; ?>>SIQ</option>
                                            <option value="Isolation" <?php echo $officer['status'] == 'Isolation' ? 'selected' : ''; ?>>Isolation</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="officers[<?php echo $officer['id']; ?>][remarks]" 
                                               class="form-control form-control-sm" 
                                               value="<?php echo htmlspecialchars($officer['remarks'] ?? ''); ?>" 
                                               placeholder="Optional remarks">
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <button type="submit" name="update_attendance" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i> Update Attendance
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function loadParadeDate() {
            const date = document.getElementById('parade_date').value;
            window.location.href = 'parade.php?date=' + date;
        }

        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.officer-select');
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
        }

        function markAllPresent() {
            document.querySelectorAll('.status-select').forEach(select => {
                select.value = 'Present';
                updateRowColor(select);
            });
        }

        function markSelectedStatus(status) {
            const selected = document.querySelectorAll('.officer-select:checked');
            selected.forEach(checkbox => {
                const row = checkbox.closest('tr');
                const statusSelect = row.querySelector('.status-select');
                statusSelect.value = status;
                updateRowColor(statusSelect);
            });
        }

        function clearSelection() {
            document.querySelectorAll('.officer-select').forEach(cb => cb.checked = false);
            document.getElementById('selectAll').checked = false;
        }

        function updateRowColor(selectElement) {
            const row = selectElement.closest('tr');
            row.classList.remove('status-present', 'status-absent');
            if(selectElement.value === 'Present') {
                row.classList.add('status-present');
            } else {
                row.classList.add('status-absent');
            }
        }

        // Update row colors when status changes
        document.querySelectorAll('.status-select').forEach(select => {
            select.addEventListener('change', function() {
                updateRowColor(this);
            });
        });

        // Confirm before leaving page with unsaved changes
        let formChanged = false;
        document.querySelectorAll('select, input[type="text"]').forEach(element => {
            element.addEventListener('change', () => formChanged = true);
        });

        window.addEventListener('beforeunload', (e) => {
            if (formChanged) {
                e.preventDefault();
                e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
            }
        });

        document.getElementById('attendanceForm').addEventListener('submit', () => {
            formChanged = false;
        });
    </script>
</body>
</html>