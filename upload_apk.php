<?php
// upload_apk.php - Upload built APK to live server
declare(strict_types=1);

$file = $argv[1] ?? 'AstroSubam_debug.apk';
$filePath = __DIR__ . '/' . $file;

if (!file_exists($filePath)) {
    echo "❌ Error: File {$filePath} does not exist!\n";
    exit(1);
}

$fileSize = filesize($filePath);
$sizeMB = round($fileSize / (1024 * 1024), 2);
echo "Uploading {$file} ({$sizeMB} MB) to Hostinger live server via FTP...\n";

$conn = @ftp_connect('217.21.94.159', 21, 60);
if (!$conn) {
    echo "❌ FTP connect failed\n";
    exit(1);
}

if (!@ftp_login($conn, 'u170490412.subam.navanagari.com', 'aGreen@2026a')) {
    echo "❌ FTP login failed\n";
    ftp_close($conn);
    exit(1);
}

ftp_pasv($conn, true);
ftp_set_option($conn, FTP_TIMEOUT_SEC, 600);

$pwd = ftp_pwd($conn);
if ($pwd !== '/public_html') {
    $list = ftp_nlist($conn, '.') ?: [];
    if (in_array('public_html', $list) || in_array('./public_html', $list)) {
        ftp_chdir($conn, 'public_html');
    }
}
echo "Remote working directory: " . ftp_pwd($conn) . "\n";

$startTime = microtime(true);
if (ftp_put($conn, $file, $filePath, FTP_BINARY)) {
    $remoteSize = ftp_size($conn, $file);
    $elapsed = round(microtime(true) - $startTime, 2);
    echo "✅ Successfully uploaded {$file} in {$elapsed}s! Remote size: {$remoteSize} bytes\n";
} else {
    echo "❌ FTP upload failed for {$file}\n";
}

ftp_close($conn);
