<?php
// add_officer.php - Add New Officer
require_once 'config.php';
require_once 'functions.php';

$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $name = trim($_POST['name']);
        $rank = trim($_POST['rank']);
        $department = $_POST['department'];
        $level = $_POST['level'];
        $mess_location = $_POST['mess_location'];
        $gender = $_POST['gender'];
        
        // Validate required fields
        if (empty($name) || empty($rank) || empty($department) || empty($level) || empty($mess_location)) {
            throw new Exception('Please fill in all required fields.');
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO officers (name, rank, department, level, mess_location, gender)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        if ($stmt->execute([$name, $rank, $department, $level, $mess_location, $gender])) {
            $message = '<div class="alert alert-success"><i class="fas fa-check"></i> Officer added successfully!</div>';
            // Clear form after successful submission
            $_POST = [];
        } else {
            throw new Exception('Failed to add officer.');
        }
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> Error: ' . $e->getMessage() . '</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Officer - BAF System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .form-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .btn-custom {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
        }
        .btn-custom:hover {
            background: linear-gradient(45deg, #764ba2, #667eea);
            color: white;
            transform: translateY(-2px);
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
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
                <a class="nav-link" href="reports.php">Reports</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Header -->
        <div class="form-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2><i class="fas fa-user-plus"></i> Add New Officer</h2>
                    <p class="mb-0">Add a new officer to the BAF parade system</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="officers.php" class="btn btn-light">
                        <i class="fas fa-list"></i> View All Officers
                    </a>
                </div>
            </div>
        </div>

        <?php echo $message; ?>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <form method="POST" novalidate>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">
                                            <i class="fas fa-user"></i> Full Name <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="name" 
                                               name="name" 
                                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                                               placeholder="e.g., Officer Ahmed Rahman"
                                               required>
                                        <div class="invalid-feedback">
                                            Please provide a valid name.
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="rank" class="form-label">
                                            <i class="fas fa-medal"></i> Rank <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="rank" 
                                               name="rank" 
                                               value="<?php echo htmlspecialchars($_POST['rank'] ?? ''); ?>"
                                               placeholder="e.g., Flg Offr"
                                               required>
                                        <div class="invalid-feedback">
                                            Please provide a valid rank.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="department" class="form-label">
                                            <i class="fas fa-building"></i> Department <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="department" name="department" required>
                                            <option value="">Select Department</option>
                                            <option value="CSE" <?php echo ($_POST['department'] ?? '') == 'CSE' ? 'selected' : ''; ?>>CSE - Computer Science & Engineering</option>
                                            <option value="EECE" <?php echo ($_POST['department'] ?? '') == 'EECE' ? 'selected' : ''; ?>>EECE - Electrical, Electronic & Communication Engineering</option>
                                            <option value="ME" <?php echo ($_POST['department'] ?? '') == 'ME' ? 'selected' : ''; ?>>ME - Mechanical Engineering</option>
                                            <option value="AE" <?php echo ($_POST['department'] ?? '') == 'AE' ? 'selected' : ''; ?>>AE - Aeronautical Engineering</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select a department.
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="level" class="form-label">
                                            <i class="fas fa-layer-group"></i> Level <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="level" name="level" required>
                                            <option value="">Select Level</option>
                                            <option value="I" <?php echo ($_POST['level'] ?? '') == 'I' ? 'selected' : ''; ?>>Level I</option>
                                            <option value="II" <?php echo ($_POST['level'] ?? '') == 'II' ? 'selected' : ''; ?>>Level II</option>
                                            <option value="III" <?php echo ($_POST['level'] ?? '') == 'III' ? 'selected' : ''; ?>>Level III</option>
                                            <option value="IV" <?php echo ($_POST['level'] ?? '') == 'IV' ? 'selected' : ''; ?>>Level IV</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select a level.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="mess_location" class="form-label">
                                            <i class="fas fa-map-marker-alt"></i> Mess Location <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="mess_location" name="mess_location" required>
                                            <option value="">Select Mess Location</option>
                                            <option value="MIST Dhaka Mess" <?php echo ($_POST['mess_location'] ?? '') == 'MIST Dhaka Mess' ? 'selected' : ''; ?>>MIST Dhaka Mess</option>
                                            <option value="MIST Mirpur Mess" <?php echo ($_POST['mess_location'] ?? '') == 'MIST Mirpur Mess' ? 'selected' : ''; ?>>MIST Mirpur Mess</option>
                                            <option value="BAF Base AKR" <?php echo ($_POST['mess_location'] ?? '') == 'BAF Base AKR' ? 'selected' : ''; ?>>BAF Base AKR</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select a mess location.
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="gender" class="form-label">
                                            <i class="fas fa-venus-mars"></i> Gender <span class="text-danger">*</span>
                                        </label>
                                        <select class="form-select" id="gender" name="gender" required>
                                            <option value="Male" <?php echo ($_POST['gender'] ?? 'Male') == 'Male' ? 'selected' : ''; ?>>Male</option>
                                            <option value="Female" <?php echo ($_POST['gender'] ?? '') == 'Female' ? 'selected' : ''; ?>>Female</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-custom btn-lg me-3">
                                    <i class="fas fa-save"></i> Add Officer
                                </button>
                                <a href="officers.php" class="btn btn-outline-secondary btn-lg">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Instructions Card -->
        <div class="row justify-content-center mt-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-info-circle"></i> Instructions</h6>
                    </div>
                    <div class="card-body">
                        <ul class="mb-0">
                            <li><strong>Name:</strong> Enter the full name of the officer (e.g., Officer Ahmed Rahman)</li>
                            <li><strong>Rank:</strong> Enter the military rank (e.g., Flg Offr, Sqn Ldr, etc.)</li>
                            <li><strong>Department:</strong> Select the academic department the officer belongs to</li>
                            <li><strong>Level:</strong> Select the appropriate level (I, II, III, or IV)</li>
                            <li><strong>Mess Location:</strong> Select where the officer is stationed</li>
                            <li><strong>Gender:</strong> Select Male or Female</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();

        // Auto-format name (capitalize first letters)
        document.getElementById('name').addEventListener('blur', function() {
            this.value = this.value.replace(/\w\S*/g, function(txt) {
                return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
            });
        });

        // Auto-format rank (uppercase)
        document.getElementById('rank').addEventListener('blur', function() {
            this.value = this.value.toUpperCase();
        });
    </script>
</body>
</html>