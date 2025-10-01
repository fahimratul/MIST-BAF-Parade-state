<?php
// Start output buffering to prevent header issues
ob_start();

session_start();
require_once 'config.php';

/**
 * Get default download directory with fallback options
 */
function getDefaultDownloadDir() {
    // Try multiple methods to get user's Downloads folder
    if (isset($_SERVER['USERPROFILE'])) {
        return $_SERVER['USERPROFILE'] . '\\Downloads';
    } elseif (isset($_SERVER['HOMEDRIVE']) && isset($_SERVER['HOMEPATH'])) {
        return $_SERVER['HOMEDRIVE'] . $_SERVER['HOMEPATH'] . '\\Downloads';
    } elseif (function_exists('getenv')) {
        $userProfile = getenv('USERPROFILE');
        if ($userProfile) {
            return $userProfile . '\\Downloads';
        }
    }
    // Fallback to project directory if all else fails
    return __DIR__ . '\\download';
}

// Use config from config.php
$dbHost = $host;
$dbUser = $username;
$dbPass = $password;
$dbName = $dbname;

// Get download directory from user input or use default
$defaultDownloadDir = getDefaultDownloadDir();
$downloadDir = $_POST['download_dir'] ?? $_SESSION['download_dir'] ?? $defaultDownloadDir;

// Store in session for persistence
if (isset($_POST['download_dir'])) {
    $_SESSION['download_dir'] = $_POST['download_dir'];
}

$dumpFile = 'baf_parade_system_backup_' . date('Ymd_His') . '.sql';
$dumpPath = $downloadDir . '\\' . $dumpFile;

// Ensure download directory exists
if (!is_dir($downloadDir)) {
    mkdir($downloadDir, 0755, true);
}

// HANDLE DOWNLOAD REQUEST FIRST - before any HTML output
if (isset($_GET['download']) && ($_SESSION['authenticated'] ?? false)) {
    // Clear any output buffer
    ob_clean();
    
    $mysqldumpPath = findMysqlDump();
    $success = false;
    $method = '';
    
    if ($mysqldumpPath) {
        // Try mysqldump first
        $method = 'mysqldump';
        $command = sprintf(
            '"%s" --user=%s --password=%s --host=%s --single-transaction --routines --triggers %s',
            $mysqldumpPath,
            escapeshellarg($dbUser),
            escapeshellarg($dbPass),
            escapeshellarg($dbHost),
            escapeshellarg($dbName)
        );
        
        // Execute command and capture output
        exec($command . ' 2>&1', $output, $retval);
        
        if ($retval === 0) {
            // Command succeeded, save output to file
            $backupContent = implode("\n", $output);
            if (file_put_contents($dumpPath, $backupContent)) {
                $success = true;
            }
        }
    }
    
    // Fallback to PHP method if mysqldump failed
    if (!$success) {
        $method = 'PHP PDO';
        try {
            $success = createBackupWithPHP($pdo, $dumpPath);
        } catch (Exception $e) {
            error_log("Backup error: " . $e->getMessage());
        }
    }
    
    if ($success && file_exists($dumpPath) && filesize($dumpPath) > 100) {
        // Success - send file for download
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . basename($dumpFile) . '"');
        header('Content-Length: ' . filesize($dumpPath));
        readfile($dumpPath);
        unlink($dumpPath); // Remove file after download
        exit;
    } else {
        // Download failed - redirect back with error
        $_SESSION['download_error'] = "Failed to create database backup using both mysqldump and PHP methods.";
        if ($mysqldumpPath) {
            $_SESSION['download_error'] .= " (Tried: $method)";
        } else {
            $_SESSION['download_error'] .= " (mysqldump not found, tried PHP method only)";
        }
        
        // Clean up failed backup file
        if (file_exists($dumpPath)) {
            unlink($dumpPath);
        }
        
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

/**
 * Find mysqldump executable in common Windows/XAMPP locations
 */
function findMysqlDump() {
    $possiblePaths = [
        'C:\\xampp\\mysql\\bin\\mysqldump.exe',
        'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
        'C:\\Program Files\\MySQL\\MySQL Server 5.7\\bin\\mysqldump.exe',
        'C:\\wamp64\\bin\\mysql\\mysql8.0.21\\bin\\mysqldump.exe',
        'mysqldump', // If in PATH
    ];
    
    foreach ($possiblePaths as $path) {
        if (file_exists($path) || $path === 'mysqldump') {
            // Test if the command works
            $testCommand = sprintf('"%s" --version 2>nul', $path);
            exec($testCommand, $output, $returnCode);
            if ($returnCode === 0) {
                return $path;
            }
        }
    }
    return null;
}

/**
 * Create database backup using PHP PDO (fallback method)
 */
function createBackupWithPHP($pdo, $dumpPath) {
    try {
        $backup = "-- BAF Parade State System Database Backup\n";
        $backup .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n";
        $backup .= "-- Database: baf_parade_system\n\n";
        $backup .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
        
        // Get all tables
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($tables as $table) {
            // Get table structure
            $stmt = $pdo->query("SHOW CREATE TABLE `$table`");
            $createTable = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $backup .= "-- Table structure for `$table`\n";
            $backup .= "DROP TABLE IF EXISTS `$table`;\n";
            $backup .= $createTable['Create Table'] . ";\n\n";
            
            // Get table data
            $stmt = $pdo->query("SELECT * FROM `$table`");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($rows)) {
                $backup .= "-- Data for table `$table`\n";
                foreach ($rows as $row) {
                    $columns = array_keys($row);
                    $values = array_map(function($value) use ($pdo) {
                        return $value === null ? 'NULL' : $pdo->quote($value);
                    }, array_values($row));
                    
                    $backup .= "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
                }
                $backup .= "\n";
            }
        }
        
        $backup .= "SET FOREIGN_KEY_CHECKS=1;\n";
        $backup .= "-- Backup completed: " . date('Y-m-d H:i:s') . "\n";
        
        return file_put_contents($dumpPath, $backup) !== false;
    } catch (Exception $e) {
        error_log("PHP Backup error: " . $e->getMessage());
        return false;
    }
}

// Handle password submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === 'B@Fmist') {
        $_SESSION['authenticated'] = true;
    } else {
        $error = "Incorrect password.";
    }
}

// Check for download errors from session
if (isset($_SESSION['download_error'])) {
    $error = $_SESSION['download_error'];
    unset($_SESSION['download_error']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Backup - BAF System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .backup-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            background: white;
        }
        .backup-header {
            text-align: center;
            margin-bottom: 30px;
            color: #495057;
        }
        .backup-icon {
            font-size: 3rem;
            color: #007bff;
            margin-bottom: 15px;
        }
        .btn-download {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            color: white;
            padding: 12px 30px;
            font-size: 16px;
            border-radius: 8px;
        }
        .btn-download:hover {
            background: linear-gradient(45deg, #20c997, #28a745);
            color: white;
            transform: translateY(-1px);
        }
        .system-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 12px;
            color: #6c757d;
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

    <div class="container">
        <div class="backup-container">
            <div class="backup-header">
                <div class="backup-icon">
                    <i class="fas fa-database"></i>
                </div>
                <h2>Database Backup</h2>
                <p class="text-muted">Secure database export for BAF Parade State System</p>
            </div>

            <?php if (!($_SESSION['authenticated'] ?? false)): ?>
                <form method="post" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i> Enter Authentication Code:
                        </label>
                        <input type="password" name="password" id="password" class="form-control" required 
                               placeholder="Enter secure access code">
                        <div class="invalid-feedback">
                            Please enter the authentication code.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="download_dir" class="form-label">
                            <i class="fas fa-folder"></i> Download Directory:
                        </label>
                        <input type="text" name="download_dir" id="download_dir" class="form-control" 
                               value="<?php echo htmlspecialchars($downloadDir); ?>" required
                               placeholder="Enter full path to download directory">
                        <div class="form-text">
                            Example: C:\Users\YourName\Downloads or C:\Backup
                        </div>
                        <div class="invalid-feedback">
                            Please enter a valid directory path.
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-sign-in-alt"></i> Authenticate
                    </button>
                </form>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger mt-3" role="alert">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <div class="system-info">
                    <strong>System Information:</strong><br>
                    Database: <?php echo htmlspecialchars($dbName); ?><br>
                    Host: <?php echo htmlspecialchars($dbHost); ?><br>
                    Date: <?php echo date('Y-m-d H:i:s'); ?>
                </div>
                
            <?php else: ?>
                <div class="text-center mb-4">
                    <div class="alert alert-success" role="alert">
                        <i class="fas fa-check-circle"></i>
                        Authentication successful! Ready to download backup.
                    </div>
                </div>

                <form method="post" class="mb-3">
                    <div class="mb-3">
                        <label for="download_dir_auth" class="form-label">
                            <i class="fas fa-folder"></i> Download Directory:
                        </label>
                        <div class="input-group">
                            <input type="text" name="download_dir" id="download_dir_auth" class="form-control" 
                                   value="<?php echo htmlspecialchars($downloadDir); ?>" required>
                            <button type="submit" class="btn btn-outline-secondary">
                                <i class="fas fa-save"></i> Update
                            </button>
                        </div>
                        <div class="form-text">
                            Change where the backup file will be saved
                        </div>
                    </div>
                </form>
                
                <form method="get" class="text-center">
                    <button type="submit" name="download" value="1" class="btn btn-download">
                        <i class="fas fa-download"></i> Download Database Backup
                    </button>
                </form>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger mt-3" role="alert">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Backup Failed:</strong><br>
                        <?php echo htmlspecialchars($error); ?>
                        
                        <hr>
                        <small>
                            <strong>Troubleshooting Tips:</strong><br>
                            • Ensure XAMPP/MySQL is running<br>
                            • Check if mysqldump is available in XAMPP installation<br>
                            • Verify database connection settings<br>
                            • Check file permissions in download directory
                        </small>
                    </div>
                <?php endif; ?>
                
                <div class="system-info">
                    <strong>Backup Information:</strong><br>
                    Database: <?php echo htmlspecialchars($dbName); ?><br>
                    Host: <?php echo htmlspecialchars($dbHost); ?><br>
                    Download Directory: <?php echo htmlspecialchars($downloadDir); ?><br>
                    Method: <?php echo findMysqlDump() ? 'mysqldump + PHP fallback' : 'PHP PDO only'; ?><br>
                    Filename: <?php echo htmlspecialchars($dumpFile); ?><br>
                    Date: <?php echo date('Y-m-d H:i:s'); ?>
                </div>
                
                <div class="text-center mt-3">
                    <a href="?logout=1" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Bootstrap form validation
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

        // Quick directory suggestions
        function setQuickDirectory(path) {
            const inputs = document.querySelectorAll('#download_dir, #download_dir_auth');
            inputs.forEach(input => {
                if (input) input.value = path;
            });
        }

        
        function getUserProfile() {
            // Detect common Windows user profile path
            return 'C:\\\\Users\\\\' + (window.navigator.userAgent.includes('Windows') ? 'YourName' : 'User');
        }

        // Directory validation
        function validateDirectory(input) {
            const path = input.value.trim();
            const windowsPathRegex = /^[A-Za-z]:[\\\/](?:[^<>:"|?*\\\/]+[\\\/])*[^<>:"|?*\\\/]*$/;
            
            if (windowsPathRegex.test(path)) {
                input.setCustomValidity('');
                input.classList.remove('is-invalid');
                input.classList.add('is-valid');
            } else {
                input.setCustomValidity('Please enter a valid Windows directory path (e.g., C:\\\\Users\\\\Name\\\\Downloads)');
                input.classList.remove('is-valid');
                input.classList.add('is-invalid');
            }
        }

        // Add real-time validation to directory inputs
        document.addEventListener('DOMContentLoaded', function() {
            const dirInputs = document.querySelectorAll('#download_dir, #download_dir_auth');
            dirInputs.forEach(input => {
                if (input) {
                    input.addEventListener('input', function() {
                        validateDirectory(this);
                    });
                    input.addEventListener('blur', function() {
                        validateDirectory(this);
                    });
                }
            });
        });
    </script>
</body>
</html>

<?php
// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// End output buffering and send HTML content
ob_end_flush();
?>