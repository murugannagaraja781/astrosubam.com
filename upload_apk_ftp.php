<?php
// upload_apk_ftp.php
$c = ftp_connect('217.21.94.159', 21, 30);
if (!$c) { echo "FTP connect failed\n"; exit(1); }
if (!ftp_login($c, 'u170490412.subam.navanagari.com', 'aGreen@2026a')) { echo "FTP login failed\n"; exit(1); }
ftp_pasv($c, true);

echo "Uploading AstroSubam.apk to server...\n";
$res = ftp_put($c, 'AstroSubam.apk', __DIR__ . '/AstroSubam.apk', FTP_BINARY);
if ($res) {
    echo "✔ Successfully uploaded AstroSubam.apk to web root!\n";
} else {
    echo "✖ Failed to upload AstroSubam.apk\n";
}
ftp_close($c);
