<?php
// officers.php - Officers List and Management
require_once 'config.php';
require_once 'functions.php';

$message = '';

// Handle officer deletion
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM officers WHERE id = ?");
        if ($stmt->execute([$_GET['delete']])) {
            $message = '<div class="alert alert-success"><i class="fas fa-check"></i> Officer deleted successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Failed to delete officer.</div>';
        }
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Error: ' . $e->getMessage() . '</div>';
    }
}

// Search and filter parameters
$search = $_GET['search'] ?? '';
$department_filter = $_GET['department'] ?? '';
$level_filter = $_GET['level'] ?? '';
$mess_filter = $_GET['mess'] ?? '';
$gender_filter = $_GET['gender'] ?? '';

// Build WHERE clause
$where_conditions = [];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(name LIKE ? OR rank LIKE ?)";
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

if (!empty($department_filter)) {
    $where_conditions[] = "department = ?";
    $params[] = $department_filter;
}

if (!empty($level_filter)) {
    $where_conditions[] = "level = ?";
    $params[] = $level_filter;
}

if (!empty($mess_filter)) {
    $where_conditions[] = "mess_location = ?";
    $params[] = $mess_filter;
}

if (!empty($gender_filter)) {
    $where_conditions[] = "gender = ?";
    $params[] = $gender_filter;
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Get officers with filters
$sql = "SELECT * FROM officers $where_clause ORDER BY department, level, name";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$officers = $stmt->fetchAll();

// Get statistics
$stats = getOfficersCount($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Officers Management - BAF System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .stats-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
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
        .dept-badge {
            font-size: 0.8em;
            padding: 4px 8px;
        }
        .officer-row:hover {
            background-color: #f8f9fa;
        }
        .filters-card {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
        }
        .gender-male { color: #007bff; }
        .gender-female { color: #e83e8c; }
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
                <a class="nav-link active" href="officers.php">Officers</a>
                <a class="nav-link" href="parade.php">Parade State</a>
                <a class="nav-link" href="reports.php">Reports</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Header with Statistics -->
        <div class="stats-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2><i class="fas fa-users"></i> Officers Management</h2>
                    <p class="mb-0">Total: <?php echo $stats['total']; ?> officers | Female: <?php echo $stats['female']; ?> | Present Today: <?php echo $stats['on_parade_today']; ?></p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="add_officer.php" class="btn btn-light btn-lg">
                        <i class="fas fa-user-plus"></i> Add New Officer
                    </a>
                </div>
            </div>
        </div>

        <?php echo $message; ?>

        <!-- Filters -->
        <div class="card filters-card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label"><i class="fas fa-search"></i> Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Name or rank..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label"><i class="fas fa-building"></i> Department</label>
                        <select name="department" class="form-select">
                            <option value="">All Departments</option>
                            <option value="CSE" <?php echo $department_filter == 'CSE' ? 'selected' : ''; ?>>CSE</option>
                            <option value="EECE" <?php echo $department_filter == 'EECE' ? 'selected' : ''; ?>>EECE</option>
                            <option value="ME" <?php echo $department_filter == 'ME' ? 'selected' : ''; ?>>ME</option>
                            <option value="AE" <?php echo $department_filter == 'AE' ? 'selected' : ''; ?>>AE</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label"><i class="fas fa-layer-group"></i> Level</label>
                        <select name="level" class="form-select">
                            <option value="">All Levels</option>
                            <option value="I" <?php echo $level_filter == 'I' ? 'selected' : ''; ?>>Level I</option>
                            <option value="II" <?php echo $level_filter == 'II' ? 'selected' : ''; ?>>Level II</option>
                            <option value="III" <?php echo $level_filter == 'III' ? 'selected' : ''; ?>>Level III</option>
                            <option value="IV" <?php echo $level_filter == 'IV' ? 'selected' : ''; ?>>Level IV</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label"><i class="fas fa-map-marker-alt"></i> Mess Location</label>
                        <select name="mess" class="form-select">
                            <option value="">All Locations</option>
                            <option value="MIST Dhaka Mess" <?php echo $mess_filter == 'MIST Dhaka Mess' ? 'selected' : ''; ?>>MIST Dhaka Mess</option>
                            <option value="MIST Mirpur Mess" <?php echo $mess_filter == 'MIST Mirpur Mess' ? 'selected' : ''; ?>>MIST Mirpur Mess</option>
                            <option value="BAF Base AKR" <?php echo $mess_filter == 'BAF Base AKR' ? 'selected' : ''; ?>>BAF Base AKR</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label"><i class="fas fa-venus-mars"></i> Gender</label>
                        <select name="gender" class="form-select">
                            <option value="">All Genders</option>
                            <option value="Male" <?php echo $gender_filter == 'Male' ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo $gender_filter == 'Female' ? 'selected' : ''; ?>>Female</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-custom">
                            <i class="fas fa-filter"></i> Apply Filters
                        </button>
                        <a href="officers.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Clear Filters
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Officers Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-list"></i> Officers List (<?php echo count($officers); ?> officers)</h4>
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-download"></i> Export
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (empty($officers)): ?>
                <div class="text-center p-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5>No officers found</h5>
                    <p class="text-muted">Try adjusting your search criteria or <a href="add_officer.php">add a new officer</a>.</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Rank</th>
                                <th>Department</th>
                                <th>Level</th>
                                <th>Mess Location</th>
                                <th>Gender</th>
                                <th>Added</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($officers as $officer): ?>
                            <tr class="officer-row">
                                <td><strong>#<?php echo $officer['id']; ?></strong></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($officer['name']); ?></strong>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($officer['rank']); ?></span>
                                </td>
                                <td>
                                    <span class="badge dept-badge <?php 
                                        echo match($officer['department']) {
                                            'CSE' => 'bg-primary',
                                            'EECE' => 'bg-success',
                                            'ME' => 'bg-warning text-dark',
                                            'AE' => 'bg-info text-dark',
                                            default => 'bg-secondary'
                                        };
                                    ?>"><?php echo $officer['department']; ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-dark"><?php echo $officer['level']; ?></span>
                                </td>
                                <td>
                                    <small><?php echo $officer['mess_location']; ?></small>
                                </td>
                                <td>
                                    <i class="fas <?php echo $officer['gender'] == 'Female' ? 'fa-female gender-female' : 'fa-male gender-male'; ?>"></i>
                                    <?php echo $officer['gender']; ?>
                                </td>
                                <td>
                                    <small class="text-muted"><?php echo date('d/m/Y', strtotime($officer['created_at'])); ?></small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-primary" title="Edit" onclick="editOfficer(<?php echo $officer['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" title="Delete" onclick="deleteOfficer(<?php echo $officer['id']; ?>, '<?php echo htmlspecialchars($officer['name']); ?>')">
                                            <i class="fas fa-trash"></i>
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

        <!-- Department Summary -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-chart-pie"></i> Department Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php
                            $departments = ['CSE', 'EECE', 'ME', 'AE'];
                            foreach($departments as $dept) {
                                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM officers WHERE department = ?");
                                $stmt->execute([$dept]);
                                $count = $stmt->fetch()['count'];
                            ?>
                            <div class="col-md-3">
                                <div class="text-center p-3 border rounded">
                                    <h4 class="text-primary"><?php echo $count; ?></h4>
                                    <p class="mb-0"><strong><?php echo $dept; ?></strong></p>
                                    <small class="text-muted"><?php echo round(($count / $stats['total']) * 100, 1); ?>%</small>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle text-danger"></i> Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong id="officerName"></strong>?</p>
                    <p class="text-danger"><small><i class="fas fa-warning"></i> This action cannot be undone. All related parade records will also be deleted.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" id="confirmDelete" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete Officer
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteOfficer(id, name) {
            document.getElementById('officerName').textContent = name;
            document.getElementById('confirmDelete').href = 'officers.php?delete=' + id;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }

        function editOfficer(id) {
            // For now, redirect to add officer page with edit functionality
            // You can create a separate edit_officer.php page
            alert('Edit functionality - Officer ID: ' + id + '\nYou can implement edit_officer.php for this feature.');
        }

        // Auto-hide success/error messages after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.remove();
                }, 500);
            });
        }, 5000);
    </script>
</body>
</html>