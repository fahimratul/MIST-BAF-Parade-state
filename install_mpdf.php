<?php
/**
 * Auto mPDF Installer
 * Place this file in your project root and run it via browser or command line
 * Author: Auto-generated installer
 */

// Prevent direct access from non-localhost (security)
if (!in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1', 'localhost'])) {
    die('This installer can only be run from localhost for security reasons.');
}

set_time_limit(300); // 5 minutes timeout
ini_set('memory_limit', '512M');

?>
<!DOCTYPE html>
<html>
<head>
    <title>mPDF Auto Installer</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 800px; 
            margin: 20px auto; 
            padding: 20px; 
            background: #f5f5f5; 
        }
        .container { 
            background: white; 
            padding: 30px; 
            border-radius: 8px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
        }
        .success { 
            background: #d4edda; 
            color: #155724; 
            padding: 15px; 
            border: 1px solid #c3e6cb; 
            border-radius: 4px; 
            margin: 10px 0; 
        }
        .error { 
            background: #f8d7da; 
            color: #721c24; 
            padding: 15px; 
            border: 1px solid #f5c6cb; 
            border-radius: 4px; 
            margin: 10px 0; 
        }
        .warning { 
            background: #fff3cd; 
            color: #856404; 
            padding: 15px; 
            border: 1px solid #ffeaa7; 
            border-radius: 4px; 
            margin: 10px 0; 
        }
        .info { 
            background: #cce7ff; 
            color: #004085; 
            padding: 15px; 
            border: 1px solid #b3d9ff; 
            border-radius: 4px; 
            margin: 10px 0; 
        }
        .btn {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
        }
        .btn:hover { background: #0056b3; }
        .btn-success { background: #28a745; }
        .btn-danger { background: #dc3545; }
        pre { 
            background: #f8f9fa; 
            padding: 15px; 
            border-radius: 4px; 
            overflow-x: auto; 
            font-size: 12px;
        }
        .progress {
            background: #e9ecef;
            border-radius: 4px;
            height: 20px;
            margin: 10px 0;
        }
        .progress-bar {
            background: #007bff;
            height: 100%;
            border-radius: 4px;
            text-align: center;
            line-height: 20px;
            color: white;
            transition: width 0.3s;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 mPDF Auto Installer</h1>
        <p>This installer will automatically download and set up mPDF for your project.</p>

<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'install') {
        installMPDF();
    } elseif ($action === 'test') {
        testMPDF();
    } elseif ($action === 'cleanup') {
        cleanup();
    }
} else {
    showMainInterface();
}

function showMainInterface() {
    $composerExists = file_exists('composer.json');
    $vendorExists = file_exists('vendor/autoload.php');
    $mpdfExists = file_exists('vendor/mpdf/mpdf/src/Mpdf.php');
    
    echo '<h2>📋 System Check</h2>';
    
    // Check PHP version
    $phpVersion = PHP_VERSION;
    if (version_compare($phpVersion, '7.0', '>=')) {
        echo '<div class="success">✅ PHP Version: ' . $phpVersion . ' (Compatible)</div>';
    } else {
        echo '<div class="error">❌ PHP Version: ' . $phpVersion . ' (mPDF requires PHP 7.0+)</div>';
        return;
    }
    
    // Check extensions
    $requiredExtensions = ['gd', 'mbstring', 'curl', 'zip'];
    $missingExtensions = [];
    
    foreach ($requiredExtensions as $ext) {
        if (extension_loaded($ext)) {
            echo '<div class="success">✅ Extension: ' . $ext . '</div>';
        } else {
            echo '<div class="error">❌ Extension: ' . $ext . ' (Required)</div>';
            $missingExtensions[] = $ext;
        }
    }
    
    if (!empty($missingExtensions)) {
        echo '<div class="warning">⚠️ Missing extensions: ' . implode(', ', $missingExtensions) . '</div>';
        echo '<div class="info">Please install missing PHP extensions before proceeding.</div>';
        return;
    }
    
    // Check file permissions
    if (is_writable('.')) {
        echo '<div class="success">✅ Directory is writable</div>';
    } else {
        echo '<div class="error">❌ Directory is not writable</div>';
        return;
    }
    
    // Check existing installations
    if ($mpdfExists) {
        echo '<div class="success">✅ mPDF is already installed!</div>';
        echo '<form method="post" style="display: inline;"><input type="hidden" name="action" value="test"><button type="submit" class="btn btn-success">🧪 Test mPDF</button></form>';
        echo '<form method="post" style="display: inline;"><input type="hidden" name="action" value="cleanup"><button type="submit" class="btn btn-danger">🗑️ Reinstall</button></form>';
    } else {
        echo '<div class="warning">⚠️ mPDF not found</div>';
        
        echo '<h2>🔧 Installation Options</h2>';
        
        if (isComposerAvailable()) {
            echo '<div class="info">✅ Composer is available on your system</div>';
            echo '<form method="post"><input type="hidden" name="action" value="install"><input type="hidden" name="method" value="composer"><button type="submit" class="btn">📦 Install via Composer (Recommended)</button></form>';
        } else {
            echo '<div class="warning">⚠️ Composer not found in system PATH</div>';
        }
        
        echo '<form method="post"><input type="hidden" name="action" value="install"><input type="hidden" name="method" value="download"><button type="submit" class="btn">📥 Direct Download & Install</button></form>';
    }
    
    echo '<h2>📖 Manual Installation</h2>';
    echo '<div class="info">';
    echo '<strong>If automatic installation fails, use these commands:</strong><br>';
    echo '<pre>cd ' . getcwd() . '
# Method 1: Composer (Recommended)
composer require mpdf/mpdf

# Method 2: Download manually
wget https://github.com/mpdf/mpdf/archive/v8.1.4.zip
unzip v8.1.4.zip
mv mpdf-8.1.4 mpdf</pre>';
    echo '</div>';
}

function installMPDF() {
    $method = $_POST['method'] ?? 'composer';
    
    echo '<h2>🔧 Installing mPDF...</h2>';
    echo '<div class="progress"><div class="progress-bar" style="width: 10%">Starting...</div></div>';
    
    if ($method === 'composer') {
        installViaComposer();
    } else {
        installViaDownload();
    }
}

function installViaComposer() {
    echo '<div class="info">📦 Installing via Composer...</div>';
    
    // Create composer.json if it doesn't exist
    if (!file_exists('composer.json')) {
        $composerJson = [
            "require" => [
                "mpdf/mpdf" => "^8.1"
            ]
        ];
        file_put_contents('composer.json', json_encode($composerJson, JSON_PRETTY_PRINT));
        echo '<div class="success">✅ Created composer.json</div>';
    } else {
        // Update existing composer.json
        $composer = json_decode(file_get_contents('composer.json'), true);
        $composer['require']['mpdf/mpdf'] = '^8.1';
        file_put_contents('composer.json', json_encode($composer, JSON_PRETTY_PRINT));
        echo '<div class="success">✅ Updated composer.json</div>';
    }
    
    echo '<div class="progress"><div class="progress-bar" style="width: 30%">Running composer install...</div></div>';
    
    // Run composer install
    $output = [];
    $returnCode = 0;
    
    if (isComposerAvailable()) {
        exec('composer install --no-dev 2>&1', $output, $returnCode);
    } else {
        // Try to download and use composer.phar
        downloadComposerPhar();
        exec('php composer.phar install --no-dev 2>&1', $output, $returnCode);
    }
    
    echo '<div class="progress"><div class="progress-bar" style="width: 80%">Processing...</div></div>';
    
    if ($returnCode === 0) {
        echo '<div class="success">✅ mPDF installed successfully via Composer!</div>';
        echo '<div class="progress"><div class="progress-bar" style="width: 100%">Complete!</div></div>';
        
        // Test installation
        if (file_exists('vendor/mpdf/mpdf/src/Mpdf.php')) {
            echo '<div class="success">✅ Installation verified!</div>';
            echo '<form method="post" style="margin-top: 20px;"><input type="hidden" name="action" value="test"><button type="submit" class="btn btn-success">🧪 Test mPDF</button></form>';
        }
    } else {
        echo '<div class="error">❌ Composer installation failed. Trying direct download...</div>';
        echo '<pre>' . implode("\n", $output) . '</pre>';
        installViaDownload();
    }
}

function installViaDownload() {
    echo '<div class="info">📥 Installing via direct download...</div>';
    echo '<div class="progress"><div class="progress-bar" style="width: 20%">Preparing...</div></div>';
    
    $mpdfVersion = '8.1.4';
    $downloadUrl = "https://github.com/mpdf/mpdf/archive/v{$mpdfVersion}.zip";
    $zipFile = "mpdf-{$mpdfVersion}.zip";
    $extractDir = "mpdf-{$mpdfVersion}";
    
    // Create vendor directory structure
    if (!file_exists('vendor')) mkdir('vendor');
    if (!file_exists('vendor/mpdf')) mkdir('vendor/mpdf');
    
    echo '<div class="progress"><div class="progress-bar" style="width: 40%">Downloading...</div></div>';
    
    // Download mPDF
    $ch = curl_init($downloadUrl);
    $fp = fopen($zipFile, 'w');
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'mPDF-Installer/1.0');
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    fclose($fp);
    
    if ($result && $httpCode === 200) {
        echo '<div class="success">✅ Downloaded successfully</div>';
        echo '<div class="progress"><div class="progress-bar" style="width: 60%">Extracting...</div></div>';
        
        // Extract ZIP
        $zip = new ZipArchive();
        if ($zip->open($zipFile) === TRUE) {
            $zip->extractTo('.');
            $zip->close();
            
            // Move to vendor directory
            if (file_exists($extractDir)) {
                rename($extractDir, 'vendor/mpdf/mpdf');
                echo '<div class="success">✅ Extracted and moved to vendor/mpdf/mpdf</div>';
            }
            
            // Cleanup
            unlink($zipFile);
            
            echo '<div class="progress"><div class="progress-bar" style="width: 80%">Creating autoloader...</div></div>';
            
            // Create simple autoloader
            createAutoloader();
            
            echo '<div class="progress"><div class="progress-bar" style="width: 100%">Complete!</div></div>';
            echo '<div class="success">✅ mPDF installed successfully via direct download!</div>';
            echo '<form method="post" style="margin-top: 20px;"><input type="hidden" name="action" value="test"><button type="submit" class="btn btn-success">🧪 Test mPDF</button></form>';
            
        } else {
            echo '<div class="error">❌ Failed to extract ZIP file</div>';
        }
    } else {
        echo '<div class="error">❌ Failed to download mPDF (HTTP Code: ' . $httpCode . ')</div>';
    }
}

function createAutoloader() {
    $autoloaderContent = '<?php
// Simple autoloader for mPDF
spl_autoload_register(function ($class) {
    if (strpos($class, "Mpdf\\") === 0) {
        $file = __DIR__ . "/vendor/mpdf/mpdf/src/" . str_replace("\\", "/", substr($class, 5)) . ".php";
        if (file_exists($file)) {
            require_once $file;
        }
    }
});
';
    file_put_contents('vendor/autoload.php', $autoloaderContent);
    echo '<div class="success">✅ Created autoloader</div>';
}

function downloadComposerPhar() {
    if (!file_exists('composer.phar')) {
        echo '<div class="info">📥 Downloading composer.phar...</div>';
        $ch = curl_init('https://getcomposer.org/composer.phar');
        $fp = fopen('composer.phar', 'w');
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
    }
}

function testMPDF() {
    echo '<h2>🧪 Testing mPDF Installation</h2>';
    
    try {
        // Try to load mPDF
        if (file_exists('vendor/autoload.php')) {
            require_once 'vendor/autoload.php';
        } else {
            throw new Exception('Autoloader not found');
        }
        
        $mpdf = new \Mpdf\Mpdf(['format' => 'A4']);
        echo '<div class="success">✅ mPDF loaded successfully!</div>';
        
        // Generate test PDF
        $html = '<h1 style="color: red;">mPDF Test</h1><p>This is a test PDF with <span style="background-color: yellow;">colors</span> and formatting.</p>';
        $mpdf->WriteHTML($html);
        
        $testFile = 'mpdf_test.pdf';
        $mpdf->Output($testFile, 'F');
        
        if (file_exists($testFile)) {
            echo '<div class="success">✅ Test PDF generated successfully!</div>';
            echo '<a href="' . $testFile . '" target="_blank" class="btn">📄 View Test PDF</a>';
            echo '<p><small>You can delete ' . $testFile . ' after testing.</small></p>';
        }
        
        echo '<div class="info">
            <strong>✅ mPDF is ready to use!</strong><br>
            You can now use mPDF in your projects with:<br>
            <pre>require_once "vendor/autoload.php";
$mpdf = new \Mpdf\Mpdf();
$mpdf->WriteHTML($html);
$mpdf->Output("document.pdf", "I");</pre>
        </div>';
        
    } catch (Exception $e) {
        echo '<div class="error">❌ mPDF test failed: ' . $e->getMessage() . '</div>';
        echo '<div class="warning">Try reinstalling mPDF or check the installation.</div>';
    }
    
    echo '<a href="?" class="btn">🔙 Back to Main</a>';
}

function cleanup() {
    echo '<h2>🗑️ Cleaning up for fresh installation</h2>';
    
    $foldersToDelete = ['vendor/mpdf', 'vendor/autoload.php'];
    $filesToDelete = ['composer.phar', 'mpdf_test.pdf'];
    
    foreach ($foldersToDelete as $folder) {
        if (file_exists($folder)) {
            if (is_dir($folder)) {
                removeDirectory($folder);
                echo '<div class="success">✅ Removed directory: ' . $folder . '</div>';
            } else {
                unlink($folder);
                echo '<div class="success">✅ Removed file: ' . $folder . '</div>';
            }
        }
    }
    
    foreach ($filesToDelete as $file) {
        if (file_exists($file)) {
            unlink($file);
            echo '<div class="success">✅ Removed file: ' . $file . '</div>';
        }
    }
    
    echo '<div class="info">✅ Cleanup complete. You can now reinstall mPDF.</div>';
    echo '<a href="?" class="btn">🔙 Back to Main</a>';
}

function removeDirectory($dir) {
    if (!is_dir($dir)) return;
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        is_dir($path) ? removeDirectory($path) : unlink($path);
    }
    rmdir($dir);
}

function isComposerAvailable() {
    $output = [];
    exec('composer --version 2>&1', $output, $returnCode);
    return $returnCode === 0;
}

?>

        <div style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 4px;">
            <h3>📚 What is mPDF?</h3>
            <p>mPDF is a PHP library that generates PDF files from UTF-8 encoded HTML. It's perfect for creating reports, invoices, and documents with full CSS support and color rendering.</p>
            
            <h3>🔧 Why switch from TCPDF?</h3>
            <ul>
                <li>✅ Better CSS and color support</li>
                <li>✅ More reliable HTML rendering</li>
                <li>✅ Simpler API and better documentation</li>
                <li>✅ Active development and community</li>
            </ul>
        </div>
    </div>
</body>
</html>