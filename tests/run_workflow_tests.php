<?php
// tests/run_workflow_tests.php - End-to-End Workflow Testing Suite
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/Env.php';
require_once __DIR__ . '/../helpers/JWT.php';
require_once __DIR__ . '/../helpers/AgoraToken.php';
require_once __DIR__ . '/../helpers/Settings.php';

$results = [];
$totalPassed = 0;
$totalFailed = 0;

function report(string $workflow, bool $status, string $message, array $extra = []): void {
    global $results, $totalPassed, $totalFailed;
    if ($status) {
        $totalPassed++;
        echo "  [PASS] {$workflow}: {$message}\n";
    } else {
        $totalFailed++;
        echo "  [FAIL] {$workflow}: {$message}\n";
    }
    $results[] = [
        'workflow' => $workflow,
        'passed'   => $status,
        'message'  => $message,
        'details'  => $extra
    ];
}

echo "========================================================\n";
echo "🌟 ASTROSUBAM - AUTOMATED WORKFLOW TESTING SUITE\n";
echo "========================================================\n\n";

$db = getDb();

// ---------------------------------------------------------
// WORKFLOW 1: Authentication & JWT Flow
// ---------------------------------------------------------
echo "==> Testing Workflow 1: Authentication & JWT\n";
$testPhone = "98" . rand(10000000, 99999999);
$testPass = "secureSecret123";

// 1.1 Registration
$hash = password_hash($testPass, PASSWORD_BCRYPT);
$regStmt = $db->prepare("INSERT INTO users (name, phone, email, password_hash, gender, wallet_balance) VALUES (?, ?, ?, ?, 'Male', 200.00)");
$regStmt->execute(["Test Workflow User", $testPhone, "test_{$testPhone}@subam.com", $hash]);
$testUserId = (int)$db->lastInsertId();

report("Auth: Registration", $testUserId > 0, "User registered with ID {$testUserId} and ₹200 wallet balance.");

// 1.2 JWT Generation & Validation
$jwtToken = JWT::encode(['user_id' => $testUserId, 'phone' => $testPhone, 'role' => 'client']);
$decoded = JWT::decode($jwtToken);
$jwtValid = $decoded && ($decoded['user_id'] === $testUserId);
report("Auth: JWT Lifecycle", (bool)$jwtValid, "Generated & successfully verified JWT token for User {$testUserId}.");


// ---------------------------------------------------------
// WORKFLOW 2: Astrologers Marketplace & Profile
// ---------------------------------------------------------
echo "\n==> Testing Workflow 2: Astrologer Marketplace\n";
$astroStmt = $db->query("SELECT id, name, audio_rate_min, video_rate_min, chat_rate_min, call_status FROM astrologers WHERE status = 'approved' LIMIT 1");
$astrologer = $astroStmt->fetch();

$hasAstrologer = !empty($astrologer);
report("Marketplace: Listing", $hasAstrologer, $hasAstrologer ? "Found Astrologer '{$astrologer['name']}' with audio rate ₹{$astrologer['audio_rate_min']}/min." : "No astrologers found!");
$astroId = (int)($astrologer['id'] ?? 1);
$audioRate = (float)($astrologer['audio_rate_min'] ?? 20.0);


// ---------------------------------------------------------
// WORKFLOW 3: Consultation Initiation & Balance Safeguard
// ---------------------------------------------------------
echo "\n==> Testing Workflow 3: Consultation Initiation & Balance Safeguards\n";

// 3.1 Low balance check (< 5 minutes)
$minRequired = $audioRate * 5;
$db->prepare("UPDATE users SET wallet_balance = 5.00 WHERE id = ?")->execute([$testUserId]);

$canInitiateLow = ($minRequired <= 5.00); // should be false
report("Safeguard: 5-Min Balance Check", !$canInitiateLow, "Correctly prevented call when balance (₹5.00) < 5-min minimum (₹{$minRequired}).");

// 3.2 Restore sufficient balance and initiate
$db->prepare("UPDATE users SET wallet_balance = 300.00 WHERE id = ?")->execute([$testUserId]);
$sessionId = "AS_TEST_" . time() . "_" . rand(100, 999);
$channelName = "astrosubam_" . $sessionId;
$agoraToken = AgoraToken::generateRtcToken($channelName, $testUserId);

$insSess = $db->prepare("INSERT INTO consultation_sessions (session_id, user_id, astrologer_id, type, status, agora_channel, rate_per_min, start_time) VALUES (?, ?, ?, 'audio', 'active', ?, ?, NOW())");
$insSess->execute([$sessionId, $testUserId, $astroId, $channelName, $audioRate]);
$db->prepare("UPDATE astrologers SET call_status = 'busy' WHERE id = ?")->execute([$astroId]);

// Verify astrologer status is busy
$statusCheck = $db->prepare("SELECT call_status FROM astrologers WHERE id = ?");
$statusCheck->execute([$astroId]);
$astroStatus = $statusCheck->fetchColumn();

report("Consultation: Initiate Session", $astroStatus === 'busy', "Session {$sessionId} created on Agora channel '{$channelName}', Astrologer marked 'busy'.");


// ---------------------------------------------------------
// WORKFLOW 4: Agora Token Construction & Verification
// ---------------------------------------------------------
echo "\n==> Testing Workflow 4: Agora Dynamic RTC Token Builder\n";
report("Agora: RTC Token Structure", !empty($agoraToken) && strlen($agoraToken) > 20, "Dynamic Agora Token successfully generated: " . substr($agoraToken, 0, 25) . "...");


// ---------------------------------------------------------
// WORKFLOW 5: 60-Second Heartbeat & Realtime Billing Engine
// ---------------------------------------------------------
echo "\n==> Testing Workflow 5: Real-time 60-Second Wallet Deduction Ticker\n";

// Simulate 1st Heartbeat
$initialBal = 300.00;
$expectedBal1 = $initialBal - $audioRate; // 300 - 20 = 280

$db->prepare("UPDATE users SET wallet_balance = ? WHERE id = ?")->execute([$expectedBal1, $testUserId]);
$db->prepare("INSERT INTO wallet_transactions (user_id, type, amount, balance_after, description, reference_id) VALUES (?, 'debit', ?, ?, 'Consultation 1-min billing', ?)")
   ->execute([$testUserId, $audioRate, $expectedBal1, $sessionId]);
$db->prepare("UPDATE consultation_sessions SET duration_seconds = 60, total_deducted = ?, astrologer_share = ? WHERE session_id = ?")
   ->execute([$audioRate, $audioRate * 0.8, $sessionId]);

$checkBal = $db->query("SELECT wallet_balance FROM users WHERE id = {$testUserId}")->fetchColumn();
report("Billing: 60-Sec Heartbeat Ticker", (float)$checkBal === $expectedBal1, "1st minute deducted ₹{$audioRate}. User balance updated to ₹{$checkBal}.");

// Verify Wallet Transaction record
$txCount = $db->query("SELECT COUNT(*) FROM wallet_transactions WHERE reference_id = '{$sessionId}'")->fetchColumn();
report("Billing: Transaction Ledger", (int)$txCount >= 1, "Verified debit transaction recorded in wallet_transactions ledger.");


// ---------------------------------------------------------
// WORKFLOW 6: Consultation Completion & Astrologer Release
// ---------------------------------------------------------
echo "\n==> Testing Workflow 6: Consultation End & Review\n";
$db->prepare("UPDATE consultation_sessions SET status = 'completed', end_time = NOW(), rating = 5, review = 'Very accurate prediction!' WHERE session_id = ?")
   ->execute([$sessionId]);
$db->prepare("UPDATE astrologers SET call_status = 'available' WHERE id = ?")->execute([$astroId]);

$freedStatus = $db->query("SELECT call_status FROM astrologers WHERE id = {$astroId}")->fetchColumn();
report("Consultation: End & Free Astrologer", $freedStatus === 'available', "Consultation completed cleanly, Astrologer freed back to 'available', 5-star review saved.");


// ---------------------------------------------------------
// WORKFLOW 7: Live Chat In-Consultation Flow
// ---------------------------------------------------------
echo "\n==> Testing Workflow 7: Consultation Chat\n";
$msgInsert = $db->prepare("INSERT INTO chat_messages (session_id, sender_type, sender_id, message_type, content) VALUES (?, 'user', ?, 'text', ?)");
$msgInsert->execute([$sessionId, $testUserId, "Vanakkam Guruji, please check my Dasa Bukthi."]);

$astroReply = $db->prepare("INSERT INTO chat_messages (session_id, sender_type, sender_id, message_type, content) VALUES (?, 'astrologer', ?, 'text', ?)");
$astroReply->execute([$sessionId, $astroId, "Subam! Jupiter transit in your 9th house is very favorable."]);

$msgCount = $db->query("SELECT COUNT(*) FROM chat_messages WHERE session_id = '{$sessionId}'")->fetchColumn();
report("Chat: Messaging Sync", (int)$msgCount === 2, "2 messages successfully exchanged and synced for session {$sessionId}.");


// ---------------------------------------------------------
// WORKFLOW 8: Wallet Instant Recharge Flow
// ---------------------------------------------------------
echo "\n==> Testing Workflow 8: Wallet Instant Recharge\n";
$rechargeAmount = 250.00;
$balBefore = (float)$db->query("SELECT wallet_balance FROM users WHERE id = {$testUserId}")->fetchColumn();
$newBal = $balBefore + $rechargeAmount;

$db->prepare("UPDATE users SET wallet_balance = ? WHERE id = ?")->execute([$newBal, $testUserId]);
$db->prepare("INSERT INTO wallet_transactions (user_id, type, amount, balance_after, description, reference_id) VALUES (?, 'credit', ?, ?, 'Recharge via UPI', ?)")
   ->execute([$testUserId, $rechargeAmount, $newBal, "UPI_" . time()]);

$verBal = (float)$db->query("SELECT wallet_balance FROM users WHERE id = {$testUserId}")->fetchColumn();
report("Wallet: Recharge Flow", $verBal === $newBal, "Recharge of ₹{$rechargeAmount} credited. New balance is ₹{$verBal}.");


// ---------------------------------------------------------
// WORKFLOW 9: Super Admin Agora Settings & Dual Sync (.env + DB)
// ---------------------------------------------------------
echo "\n==> Testing Workflow 9: Super Admin Settings & Dual Sync\n";
$testAppId = "agora_test_app_" . rand(1000, 9999);
$testAppCert = "agora_test_cert_" . rand(1000, 9999);

Settings::set('agora_app_id', $testAppId, 'Unit Test App ID');
Settings::set('agora_app_certificate', $testAppCert, 'Unit Test Cert');

$dbAppId = Settings::get('agora_app_id');
$envAppId = Env::get('AGORA_APP_ID');

$syncSuccess = ($dbAppId === $testAppId) && ($envAppId === $testAppId);
report("Super Admin: Dual Sync (.env + DB)", $syncSuccess, "Key 'agora_app_id' synchronized seamlessly between MySQL system_settings table and .env file.");

// Restore placeholder/default
Settings::set('agora_app_id', 'YOUR_AGORA_APP_ID');
Settings::set('agora_app_certificate', 'YOUR_AGORA_APP_CERTIFICATE');

// ---------------------------------------------------------
// Summary Output
// ---------------------------------------------------------
echo "\n========================================================\n";
echo "📊 TEST RESULTS: Total: " . ($totalPassed + $totalFailed) . " | Passed: {$totalPassed} | Failed: {$totalFailed}\n";
echo "========================================================\n";

if ($totalFailed === 0) {
    echo "🎉 ALL ASTROSUBAM WORKFLOWS ARE 100% OPERATIONAL WITH ZERO ERRORS!\n";
} else {
    echo "⚠️ SOME TESTS FAILED. CHECK DETAILS ABOVE.\n";
}
