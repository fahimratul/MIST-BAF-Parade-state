<?php
/**
 * One-Click Composer Installer
 * This will install Composer and then use it to install mPDF or Dompdf properly
 */

set_time_limit(600);
ini_set('memory_limit', '512M');

?>
<!DOCTYPE html>
<html>
<head>
    <title>One-Click Composer Installer</title>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 20px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin: 10px 0; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 4px; margin: 10px 0; }
        .info { background: #cce7ff; color: #004085; padding: 15px; border-radius: 4px; margin: 10px 0; }
        .btn { background: #007bff; color: white; padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; font-size: 16px; }
        .btn:hover { background: #0056b3; }
        .btn-success { background: #28a745; }
        .btn-warning { background: #ffc107; color: #000; }
        .progress { background: #e9ecef; border-radius: 4px; height: 30px; margin: 15px 0; position: relative; overflow: hidden; }
        .progress-bar { background: #28a745; height: 100%; border-radius: 4px; text-align: center; line-height: 30px; color: white; transition: width 0.3s; font-weight: bold; position: absolute; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 11px; max-height: 400px; }
        h1 { color: #333; }
        h2 { color: #555; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        .option-card { border: 2px solid #ddd; padding: 20px; margin: 15px 0; border-radius: 8px; }
        .option-card:hover { border-color: #007bff; }
        .recommended { border-color: #28a745; background: #f0fff4; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 PDF Library Installer</h1>
        <p>The easiest way to install mPDF or Dompdf with all dependencies.</p>

<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'install_composer') {
        installComposer();
    } elseif ($action === 'install_mpdf') {
        installViaPhar('mpdf/mpdf');
    } elseif ($action === 'install_dompdf') {
        installViaPhar('dompdf/dompdf');
    } elseif ($action === 'test') {
        testInstallation();
    }
} else {
    showMainInterface();
}

function showMainInterface() {
    echo '<h2>📋 Choose Your Installation Method</h2>';
    
    // Check what's installed
    $composerPharExists = file_exists('composer.phar');
    $vendorExists = file_exists('vendor/autoload.php');
    $mpdfExists = file_exists('vendor/mpdf/mpdf/src/Mpdf.php');
    $dompdfExists = file_exists('vendor/dompdf/dompdf/src/Dompdf.php');
    
    if ($mpdfExists || $dompdfExists) {
        echo '<div class="success">';
        echo '<h3>✅ PDF Library Already Installed!</h3>';
        if ($mpdfExists) echo '✅ mPDF is installed<br>';
        if ($dompdfExists) echo '✅ Dompdf is installed<br>';
        echo '</div>';
        
        echo '<form method="post"><input type="hidden" name="action" value="test"><button type="submit" class="btn btn-success">🧪 Test Installation</button></form>';
        echo '<a href="generate_report.php" class="btn">📄 Go to Report Generator</a>';
        return;
    }
    
    // Option 1: Automatic (Recommended)
    echo '<div class="option-card recommended">';
    echo '<h3>⭐ Option 1: Automatic Installation (Recommended)</h3>';
    echo '<p>This will automatically download Composer and install mPDF with all dependencies.</p>';
    echo '<strong>Pros:</strong> Easiest, works 99% of the time, installs everything correctly<br>';
    echo '<strong>Cons:</strong> Requires internet connection<br><br>';
    echo '<form method="post">';
    echo '<input type="hidden" name="action" value="install_mpdf">';
    echo '<button type="submit" class="btn btn-success">🚀 Install mPDF Automatically</button>';
    echo '</form>';
    echo '<form method="post" style="display: inline;">';
    echo '<input type="hidden" name="action" value="install_dompdf">';
    echo '<button type="submit" class="btn btn-warning">📦 Install Dompdf Instead</button>';
    echo '</form>';
    echo '</div>';
    
    // Option 2: Manual Composer
    echo '<div class="option-card">';
    echo '<h3>💻 Option 2: Use Existing Composer</h3>';
    echo '<p>If you have Composer installed on your system, run these commands:</p>';
    echo '<pre>';
    echo 'cd ' . getcwd() . "\n";
    echo 'composer require mpdf/mpdf' . "\n";
    echo '# OR' . "\n";
    echo 'composer require dompdf/dompdf';
    echo '</pre>';
    echo '</div>';
    
    // Option 3: Manual Download
    echo '<div class="option-card">';
    echo '<h3>📥 Option 3: Manual Download</h3>';
    echo '<p>Download and extract manually (not recommended due to dependencies):</p>';
    echo '<ol>';
    echo '<li>Download mPDF: <a href="https://github.com/mpdf/mpdf/releases" target="_blank">GitHub Releases</a></li>';
    echo '<li>Extract to <code>vendor/mpdf/mpdf/</code></li>';
    echo '<li>Install all dependencies manually (complex!)</li>';
    echo '</ol>';
    echo '</div>';
}

function installComposer() {
    echo '<h2>📥 Installing Composer...</h2>';
    echo '<div class="progress"><div class="progress-bar" style="width: 30%">Downloading Composer...</div></div>';
    
    $composerUrl = 'https://getcomposer.org/composer.phar';
    
    if (downloadFile($composerUrl, 'composer.phar')) {
        echo '<div class="success">✅ Composer downloaded successfully</div>';
        return true;
    } else {
        echo '<div class="error">❌ Failed to download Composer</div>';
        return false;
    }
}

function installViaPhar($package) {
    echo '<h2>🔧 Installing ' . $package . '...</h2>';
    
    // Step 1: Install Composer if needed
    if (!file_exists('composer.phar')) {
        echo '<div class="info">Installing Composer first...</div>';
        if (!installComposer()) {
            return;
        }
    }
    
    // Step 2: Create composer.json
    echo '<div class="progress"><div class="progress-bar" style="width: 40%">Creating composer.json...</div></div>';
    
    $composerJson = [
        "require" => [
            $package => "^8.0|^2.0"
        ],
        "config" => [
            "optimize-autoloader" => true
        ]
    ];
    
    file_put_contents('composer.json', json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    echo '<div class="success">✅ composer.json created</div>';
    
    // Step 3: Run composer install
    echo '<div class="progress"><div class="progress-bar" style="width: 60%">Running composer install...</div></div>';
    echo '<div class="info">This may take 1-2 minutes. Please wait...</div>';
    
    $output = [];
    $returnCode = 0;
    
    // Use different PHP binary paths for Windows
    $phpBinary = 'php';
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // Try to find PHP in XAMPP
        $possiblePaths = [
            'C:\\xampp\\php\\php.exe',
            'C:\\wamp64\\bin\\php\\php' . PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.0\\php.exe',
            'C:\\wamp\\bin\\php\\php' . PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.0\\php.exe',
            PHP_BINARY
        ];
        
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $phpBinary = '"' . $path . '"';
                break;
            }
        }
    }
    
    $command = $phpBinary . ' composer.phar install --no-dev --optimize-autoloader 2>&1';
    
    exec($command, $output, $returnCode);
    
    echo '<div class="progress"><div class="progress-bar" style="width: 90%">Processing...</div></div>';
    
    // Show output
    echo '<details style="margin: 15px 0;">';
    echo '<summary style="cursor: pointer; padding: 10px; background: #f8f9fa; border-radius: 4px;">📋 View Installation Log</summary>';
    echo '<pre>' . implode("\n", $output) . '</pre>';
    echo '</details>';
    
    if ($returnCode === 0) {
        echo '<div class="progress"><div class="progress-bar" style="width: 100%">Complete!</div></div>';
        echo '<div class="success">';
        echo '<h3>🎉 Installation Successful!</h3>';
        echo $package . ' has been installed with all dependencies.';
        echo '</div>';
        
        // Verify installation
        if (file_exists('vendor/autoload.php')) {
            echo '<div class="success">✅ Autoloader found</div>';
            
            if (strpos($package, 'mpdf') !== false && file_exists('vendor/mpdf/mpdf/src/Mpdf.php')) {
                echo '<div class="success">✅ mPDF verified</div>';
                showMpdfUsage();
            } elseif (strpos($package, 'dompdf') !== false && file_exists('vendor/dompdf/dompdf/src/Dompdf.php')) {
                echo '<div class="success">✅ Dompdf verified</div>';
                showDompdfUsage();
            }
            
            echo '<form method="post" style="margin-top: 20px;"><input type="hidden" name="action" value="test"><button type="submit" class="btn btn-success">🧪 Test Installation</button></form>';
            echo '<a href="generate_report.php" class="btn">📄 Go to Report Generator</a>';
        }
        
    } else {
        echo '<div class="error">';
        echo '<h3>❌ Installation Failed</h3>';
        echo '<p>Error code: ' . $returnCode . '</p>';
        echo '<p><strong>Common solutions:</strong></p>';
        echo '<ul>';
        echo '<li>Check your internet connection</li>';
        echo '<li>Make sure PHP has write permissions</li>';
        echo '<li>Try running from command line: <code>composer install</code></li>';
        echo '<li>Check if firewall is blocking Composer</li>';
        echo '</ul>';
        echo '</div>';
    }
}

function downloadFile($url, $destination) {
    $ch = curl_init($url);
    $fp = fopen($destination, 'wb');
    
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 300);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    curl_close($ch);
    fclose($fp);
    
    return $result && $httpCode === 200;
}

function testInstallation() {
    echo '<h2>🧪 Testing Installation...</h2>';
    
    try {
        require_once 'vendor/autoload.php';
        
        // Test mPDF
        if (class_exists('Mpdf\\Mpdf')) {
            echo '<div class="success">✅ Testing mPDF...</div>';
            $mpdf = new \Mpdf\Mpdf(['format' => 'A4']);
            $html = '<h1 style="color: red;">mPDF Test</h1><p style="background-color: yellow; padding: 10px;">Colors work perfectly!</p>';
            $mpdf->WriteHTML($html);
            $mpdf->Output('test_mpdf.pdf', 'F');
            
            if (file_exists('test_mpdf.pdf')) {
                echo '<div class="success">✅ mPDF test PDF created successfully!</div>';
                echo '<a href="test_mpdf.pdf" target="_blank" class="btn">📄 View Test PDF</a>';
            }
        }
        
        // Test Dompdf
        if (class_exists('Dompdf\\Dompdf')) {
            echo '<div class="success">✅ Testing Dompdf...</div>';
            $dompdf = new \Dompdf\Dompdf();
            $html = '<h1 style="color: blue;">Dompdf Test</h1><p style="background-color: lightgreen; padding: 10px;">Colors work perfectly!</p>';
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4');
            $dompdf->render();
            file_put_contents('test_dompdf.pdf', $dompdf->output());
            
            if (file_exists('test_dompdf.pdf')) {
                echo '<div class="success">✅ Dompdf test PDF created successfully!</div>';
                echo '<a href="test_dompdf.pdf" target="_blank" class="btn">📄 View Test PDF</a>';
            }
        }
        
        echo '<br><br><a href="?" class="btn">🔙 Back to Main</a>';
        echo '<a href="generate_report.php" class="btn btn-success">📄 Go to Report Generator</a>';
        
    } catch (Exception $e) {
        echo '<div class="error">❌ Test failed: ' . $e->getMessage() . '</div>';
    }
}

function showMpdfUsage() {
    echo '<h3>📖 mPDF Usage</h3>';
    echo '<pre>' . htmlspecialchars('<?php
require_once "vendor/autoload.php";

$mpdf = new \Mpdf\Mpdf([
    "orientation" => "L",
    "format" => "A4"
]);

$mpdf->WriteHTML($html);
$mpdf->Output("report.pdf", "I");
?>') . '</pre>';
}

function showDompdfUsage() {
    echo '<h3>📖 Dompdf Usage</h3>';
    echo '<pre>' . htmlspecialchars('<?php
require_once "vendor/autoload.php";

use Dompdf\Dompdf;

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper("A4", "landscape");
$dompdf->render();
$dompdf->stream("report.pdf");
?>') . '</pre>';
}

?>

    </div>
</body>
</html>