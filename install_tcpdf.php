<?php
// install_tcpdf.php - Quick TCPDF Installation Script
// Run this script once to download and setup TCPDF

echo "<h2>TCPDF Installation Script</h2>";

$tcpdf_url = 'https://github.com/tecnickcom/TCPDF/archive/refs/heads/main.zip';
$download_path = 'tcpdf_download.zip';
$extract_path = 'tcpdf/';

// Check if TCPDF already exists
if (file_exists('tcpdf/tcpdf.php')) {
    echo "<p style='color: green;'>✅ TCPDF is already installed!</p>";
    echo "<p><a href='generate_report.php?date=" . date('Y-m-d') . "' target='_blank'>Test PDF Generation</a></p>";
    exit;
}

echo "<p>📥 Downloading TCPDF library...</p>";

// Download TCPDF
$tcpdf_content = file_get_contents($tcpdf_url);
if ($tcpdf_content === false) {
    die("<p style='color: red;'>❌ Failed to download TCPDF. Please download manually from https://tcpdf.org/</p>");
}

// Save zip file
file_put_contents($download_path, $tcpdf_content);
echo "<p>✅ Download completed.</p>";

// Extract ZIP
echo "<p>📂 Extracting files...</p>";
$zip = new ZipArchive;
if ($zip->open($download_path) === TRUE) {
    $zip->extractTo('./');
    $zip->close();
    
    // Rename extracted folder
    if (is_dir('TCPDF-main')) {
        rename('TCPDF-main', 'tcpdf');
    }
    
    // Clean up
    unlink($download_path);
    
    echo "<p style='color: green;'>✅ TCPDF installation completed successfully!</p>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ul>";
    echo "<li>TCPDF is now installed in the 'tcpdf/' directory</li>";
    echo "<li><a href='generate_report.php?date=" . date('Y-m-d') . "' target='_blank'>Test PDF Generation</a></li>";
    echo "<li>You can delete this install_tcpdf.php file</li>";
    echo "</ul>";
} else {
    echo "<p style='color: red;'>❌ Failed to extract ZIP file. Please extract manually.</p>";
}
?>