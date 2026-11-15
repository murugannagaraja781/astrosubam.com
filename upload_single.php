<?php
declare(strict_types=1);

$file = $argv[1] ?? 'astrologer/index.php';
$localPath = __DIR__ . '/' . $file;
if (!file_exists($localPath)) {
    echo "❌ File not found: $localPath\n";
    exit(1);
}

$conn = @ftp_connect('217.21.94.159', 21, 30);
if (!$conn || !@ftp_login($conn, 'u170490412.subam.navanagari.com', 'aGreen@2026a')) {
    echo "❌ FTP login failed\n";
    exit(1);
}
ftp_pasv($conn, true);

$pwd = ftp_pwd($conn);
if ($pwd !== '/public_html') {
    $list = ftp_nlist($conn, '.') ?: [];
    if (in_array('public_html', $list) || in_array('./public_html', $list)) {
        ftp_chdir($conn, 'public_html');
    }
}

$remoteDir = dirname($file);
if ($remoteDir !== '.' && $remoteDir !== '/') {
    @ftp_mkdir($conn, $remoteDir);
}

if (ftp_put($conn, $file, $localPath, FTP_BINARY)) {
    echo "✅ Successfully uploaded $file\n";
} else {
    echo "❌ Upload failed for $file\n";
}
ftp_close($conn);
