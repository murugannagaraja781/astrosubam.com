<?php
// tests/test_sms_and_env.php - Verification Suite for Ping4SMS, .env Editor, and Video/Audio Consultations
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/SMS.php';
require_once __DIR__ . '/../helpers/Settings.php';
require_once __DIR__ . '/../helpers/AgoraToken.php';

echo "========================================================\n";
echo "🌟 ASTROSUBAM - SMS, .ENV & CALL VERIFICATION SUITE\n";
echo "========================================================\n\n";

$passed = 0;
$failed = 0;

function check(string $name, bool $result, string $details): void {
    global $passed, $failed;
    if ($result) {
        $passed++;
        echo "  [PASS] {$name}: {$details}\n";
    } else {
        $failed++;
        echo "  [FAIL] {$name}: {$details}\n";
    }
}

// -------------------------------------------------------------
// 1. PING4SMS & DLT BYPASS VERIFICATION
// -------------------------------------------------------------
echo "==> Testing 1: Ping4SMS DLT Gateway & Test Accounts Bypass\n";

// Test numbers bypass: 8000000001, 9000000001, 8000000000, 9000000000
foreach (SMS::TEST_NUMBERS as $num) {
    $res = SMS::sendOtp($num, '9999');
    $isBypass = !empty($res['bypass']) && $res['otp'] === SMS::STATIC_OTP;
    check("SMS Bypass for {$num}", $isBypass, "Bypass correctly returned static OTP {$res['otp']}");
}

// Check real number normalization & credentials
$apiKey = getenv('PING4SMS_API_KEY') ?: Settings::get('ping4sms_api_key', 'e1fc54301c0da93b95d3502a3151f420');
$senderId = getenv('PING4SMS_SENDER_ID') ?: Settings::get('ping4sms_sender_id', 'ASTELV');
$route = getenv('PING4SMS_ROUTE') ?: Settings::get('ping4sms_route', '4');
$templateId = getenv('PING4SMS_TEMPLATE_ID') ?: Settings::get('ping4sms_template_id', '1677100000000387344');

check("Ping4SMS API Key", !empty($apiKey) && strlen($apiKey) === 32, "API Key: {$apiKey}");
check("Ping4SMS Sender ID", $senderId === 'ASTELV', "DLT Sender ID: {$senderId}");
check("Ping4SMS Route", $route === '4', "Route: {$route} (Transactional/OTP)");
check("Ping4SMS Template ID", $templateId === '1677100000000387344', "DLT Template ID: {$templateId}");

// -------------------------------------------------------------
// 2. .ENV EDITOR & BACKUP SAFETY
// -------------------------------------------------------------
echo "\n==> Testing 2: .env Editor, Parsing & Backup Rollback\n";

$envPath = dirname(__DIR__) . '/.env';
$envBakPath = dirname(__DIR__) . '/.env.bak';

check(".env Existence", file_exists($envPath), "Found .env at {$envPath}");
check(".env Writable", is_writable($envPath), ".env file is writable by webserver / admin");

$raw = file_get_contents($envPath);
check(".env Content Non-empty", !empty($raw) && strlen($raw) > 50, "File size: " . strlen($raw) . " bytes");

// Backup test
$backupSuccess = copy($envPath, $envBakPath);
check(".env.bak Automatic Backup", $backupSuccess && file_exists($envBakPath), "Backup saved to .env.bak");

// Visual parser test
$lines = explode("\n", $raw);
$parsedVars = [];
foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#')) continue;
    $parts = explode('=', $line, 2);
    if (count($parts) === 2) {
        $parsedVars[trim($parts[0])] = trim($parts[1]);
    }
}
check("Parser: DB_NAME", ($parsedVars['DB_NAME'] ?? '') === 'u170490412_subamadmin', "Parsed DB_NAME = {$parsedVars['DB_NAME']}");
check("Parser: AGORA_APP_ID", ($parsedVars['AGORA_APP_ID'] ?? '') === '8c0ca8c1b8bc403d814f8f86653e07c3', "Parsed AGORA_APP_ID");
check("Parser: PING4SMS_API_KEY", ($parsedVars['PING4SMS_API_KEY'] ?? '') === 'e1fc54301c0da93b95d3502a3151f420', "Parsed PING4SMS_API_KEY");

// -------------------------------------------------------------
// 3. AGORA VIDEO & AUDIO TOKEN GENERATION
// -------------------------------------------------------------
echo "\n==> Testing 3: Agora RTC Video & Audio Calling Engine\n";

$testChannel = "astrosubam_AS_video_test_" . time();
$testUid = 101;
$token = AgoraToken::generateRtcToken($testChannel, $testUid, 3600);

check("Agora Video/Audio Token Generation", !empty($token) && strlen($token) > 20, "Generated Agora RTC Token for channel '{$testChannel}' (length: " . strlen($token) . ")");

// -------------------------------------------------------------
// SUMMARY
// -------------------------------------------------------------
echo "\n========================================================\n";
echo "📊 RESULTS: Total Passed: {$passed} | Total Failed: {$failed}\n";
echo "========================================================\n";

if ($failed > 0) {
    exit(1);
}

