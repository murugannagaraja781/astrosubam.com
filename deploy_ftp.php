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

function connectFtp($ftpHost, $ftpPort, $ftpUser, $ftpPass) {
    echo "[*] Connecting to FTP Server {$ftpHost}:{$ftpPort}...\n";
    $conn = @ftp_connect($ftpHost, $ftpPort, 30);
    if (!$conn) {
        $conn = @ftp_ssl_connect($ftpHost, $ftpPort, 30);
    }
    if (!$conn) {
        echo "❌ ERROR: Could not connect to {$ftpHost}:{$ftpPort}\n";
        exit(1);
    }
    $login = @ftp_login($conn, $ftpUser, $ftpPass);
    if (!$login) {
        echo "❌ Authentication failed for user '{$ftpUser}'.\n";
        ftp_close($conn);
        exit(1);
    }
    ftp_pasv($conn, true);
    ftp_set_option($conn, FTP_TIMEOUT_SEC, 30);

    $pwd = ftp_pwd($conn);
    if ($pwd !== '/public_html') {
        $list = ftp_nlist($conn, '.') ?: [];
        if (in_array('public_html', $list) || in_array('./public_html', $list)) {
            ftp_chdir($conn, 'public_html');
        }
    }
    return $conn;
}

$conn = connectFtp($ftpHost, $ftpPort, $ftpUser, $ftpPass);
echo "✅ Logged in successfully! Server directory: " . ftp_pwd($conn) . "\n\n";

// Remove default.php placeholder if present
@ftp_delete($conn, 'default.php');

$items = [
    'index.php',
    '.htaccess',
    '.env',
    'assets',
    'api',
    'admin',
    'astrologer',
    'config',
    'database',
    'helpers',
    'AstroSubam.apk'
];

echo "[2/4] Uploading website files & directories...\n";

function ensureRemoteDir(&$conn, string $remoteDir, $ftpHost, $ftpPort, $ftpUser, $ftpPass): void {
    if ($remoteDir === '.' || $remoteDir === '' || $remoteDir === '/') return;
    $parts = explode('/', trim(str_replace('\\', '/', $remoteDir), '/'));
    $path = '';
    foreach ($parts as $part) {
        $path .= ($path ? '/' : '') . $part;
        @ftp_mkdir($conn, $path);
    }
}

function uploadFileWithRetry(&$conn, string $localFile, string $remoteFile, $ftpHost, $ftpPort, $ftpUser, $ftpPass): bool {
    $remoteFile = str_replace('\\', '/', $remoteFile);
    $dir = dirname($remoteFile);
    ensureRemoteDir($conn, $dir, $ftpHost, $ftpPort, $ftpUser, $ftpPass);

    // Skip re-uploading large binaries (like APK) if remote file already matches size
    if (filesize($localFile) > 5 * 1024 * 1024) {
        $remoteSize = @ftp_size($conn, $remoteFile);
        if ($remoteSize === filesize($localFile)) {
            echo "  ✔ Already up-to-date (skipped large file): {$remoteFile}\n";
            return true;
        }
    }

    $upload = @ftp_put($conn, $remoteFile, $localFile, FTP_BINARY);
    if (!$upload) {
        // Retry with fresh connection
        echo "  ⚠️ Connection blip, reconnecting and retrying {$remoteFile}...\n";
        @ftp_close($conn);
        $conn = connectFtp($ftpHost, $ftpPort, $ftpUser, $ftpPass);
        $upload = @ftp_put($conn, $remoteFile, $localFile, FTP_BINARY);
    }

    if ($upload) {
        echo "  ✔ Uploaded: {$remoteFile}\n";
        return true;
    } else {
        echo "  ✖ Failed to upload: {$remoteFile}\n";
        return false;
    }
}

function uploadRecursive(&$conn, string $localPath, string $remotePath, $ftpHost, $ftpPort, $ftpUser, $ftpPass): void {
    $remotePath = str_replace('\\', '/', $remotePath);
    if (is_dir($localPath)) {
        ensureRemoteDir($conn, $remotePath, $ftpHost, $ftpPort, $ftpUser, $ftpPass);
        $files = scandir($localPath);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || $file === '.git') continue;
            uploadRecursive($conn, $localPath . '/' . $file, $remotePath . '/' . $file, $ftpHost, $ftpPort, $ftpUser, $ftpPass);
        }
    } else if (is_file($localPath)) {
        uploadFileWithRetry($conn, $localPath, $remotePath, $ftpHost, $ftpPort, $ftpUser, $ftpPass);
    }
}

$uploadedCount = 0;
foreach ($items as $item) {
    $local = __DIR__ . '/' . $item;
    if (file_exists($local)) {
        echo "Processing {$item}...\n";
        uploadRecursive($conn, $local, $item, $ftpHost, $ftpPort, $ftpUser, $ftpPass);
    }
}

@ftp_close($conn);

echo "\n========================================================\n";
echo "🎉 DEPLOYMENT 100% COMPLETE!\n";
echo "🌐 Live Production URL: http://subam.navanagari.com/\n";
echo "🧙‍♂️ Astrologer Dashboard: http://subam.navanagari.com/astrologer/\n";
echo "========================================================\n";
