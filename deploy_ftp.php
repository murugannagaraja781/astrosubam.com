<?php
// deploy_ftp.php - Automated FTP Deployment Script for AstroSubam to Hostinger
declare(strict_types=1);

$ftpHost = $argv[1] ?? '217.21.94.159';
$ftpPort = isset($argv[2]) ? (int)$argv[2] : 21;
$ftpUser = $argv[3] ?? 'u170490412.subam.navanagari.com';
$ftpPass = $argv[4] ?? 'aGreen@2026a';
$targetDir = $argv[5] ?? 'public_html';

echo "========================================================\n";
echo "🚀 ASTROSUBAM - HOSTINGER LIVE FTP DEPLOYMENT\n";
echo "========================================================\n";
echo "Target Host : {$ftpHost}:{$ftpPort}\n";
echo "FTP User    : {$ftpUser}\n\n";

echo "[1/4] Connecting to FTP Server...\n";
$conn = @ftp_ssl_connect($ftpHost, $ftpPort, 15);
if (!$conn) {
    $conn = @ftp_connect($ftpHost, $ftpPort, 15);
}

if (!$conn) {
    echo "❌ ERROR: Could not connect to {$ftpHost}:{$ftpPort}\n";
    exit(1);
}

echo "✅ Connected! Logging in...\n";
$login = @ftp_login($conn, $ftpUser, $ftpPass);
if (!$login) {
    echo "❌ Authentication failed for user '{$ftpUser}'.\n";
    ftp_close($conn);
    exit(1);
}

echo "✅ Logged in successfully!\n";
ftp_pasv($conn, true);

$currentDir = ftp_pwd($conn);
echo "Current directory on server: {$currentDir}\n";

$existingList = ftp_nlist($conn, '.') ?: [];
echo "Existing files/folders: " . implode(', ', $existingList) . "\n";

// Check if we need to chdir into public_html
if (in_array('public_html', $existingList) || in_array('./public_html', $existingList)) {
    echo "Entering public_html...\n";
    ftp_chdir($conn, 'public_html');
    echo "Now in directory: " . ftp_pwd($conn) . "\n";
} else {
    echo "Already in website root directory.\n";
}

// Remove default.php if present so index.php takes over
if (@ftp_delete($conn, 'default.php')) {
    echo "✔ Removed Hostinger default.php placeholder page.\n";
}

// Files & Directories to deploy
$items = [
    'index.php',
    '.htaccess',
    '.env',
    'assets',
    'api',
    'admin',
    'config',
    'database',
    'helpers'
];

echo "\n[2/4] Uploading website files & directories...\n";

function uploadRecursive($conn, $localPath, $remotePath) {
    if (is_dir($localPath)) {
        @ftp_mkdir($conn, $remotePath);
        $files = scandir($localPath);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || $file === '.git') continue;
            uploadRecursive($conn, $localPath . '/' . $file, $remotePath . '/' . $file);
        }
    } else if (is_file($localPath)) {
        $upload = @ftp_put($conn, $remotePath, $localPath, FTP_BINARY);
        if ($upload) {
            echo "  ✔ Uploaded: {$remotePath}\n";
        } else {
            echo "  ✖ Failed to upload: {$remotePath}\n";
        }
    }
}

foreach ($items as $item) {
    $local = __DIR__ . '/' . $item;
    if (file_exists($local)) {
        echo "Uploading {$item}...\n";
        uploadRecursive($conn, $local, $item);
    }
}

ftp_close($conn);

echo "\n========================================================\n";
echo "🎉 DEPLOYMENT 100% COMPLETE!\n";
echo "🌐 Live URL: http://subam.navanagari.com/\n";
echo "========================================================\n";
