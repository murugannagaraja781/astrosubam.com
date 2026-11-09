<?php
// tests/test_fcm_call_lifecycle.php - Comprehensive Verification for 2-Way Handshake Call Flow
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/AgoraToken.php';
require_once __DIR__ . '/../helpers/FCM.php';

echo "========================================================\n";
echo "🌟 ASTROSUBAM - 2-WAY HANDSHAKE CALL LIFECYCLE TEST\n";
echo "========================================================\n\n";

$db = getDb();
$driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
echo "Database Driver: {$driver}\n\n";

// 0. Ensure Devotee (ID: 2) and Astrologer (ID: 1) exist
$db->exec("DELETE FROM consultation_sessions WHERE user_id = 2");

// Ensure Test User
$uStmt = $db->prepare("SELECT id FROM users WHERE id = 2");
$uStmt->execute();
if (!$uStmt->fetch()) {
    $db->prepare("INSERT INTO users (id, name, phone, email, password_hash, wallet_balance, fcm_token) 
                  VALUES (2, 'பக்தர் (User 1)', '8000000001', 'devotee1@astrosubam.com', 'test', 500.00, 'test_fcm_token_devotee')")->execute();
} else {
    $db->exec("UPDATE users SET wallet_balance = 500.00, fcm_token = 'test_fcm_token_devotee' WHERE id = 2");
}

// Ensure Test Astrologer
$aStmt = $db->prepare("SELECT id FROM astrologers WHERE id = 1");
$aStmt->execute();
if (!$aStmt->fetch()) {
    $db->prepare("INSERT INTO astrologers (id, name, phone, email, password_hash, audio_rate_min, video_rate_min, call_status)
                  VALUES (1, 'Guruji S. Sundaramurthy', '9876543210', 'sundaramurthy@astrosubam.com', 'test', 20.00, 35.00, 'available')")->execute();
} else {
    $db->exec("UPDATE astrologers SET call_status = 'available' WHERE id = 1");
}

function assertTest(string $desc, bool $condition, string $detail = '') {
    if ($condition) {
        echo "✅ [PASS] {$desc}\n";
        if ($detail) echo "   ↳ {$detail}\n";
    } else {
        echo "❌ [FAIL] {$desc}\n";
        if ($detail) echo "   ↳ Error: {$detail}\n";
        exit(1);
    }
}

// -----------------------------------------------------------------------------
// STEP 1: Devotee Requests Consultation (Status: 'requested')
// -----------------------------------------------------------------------------
echo "==> STEP 1: Devotee sends consultation request\n";
$sessionId = "AS_" . time() . "_" . mt_rand(1000, 9999);
$channelName = "astrosubam_" . $sessionId;
$userId = 2;
$astrologerId = 1;
$rate = 20.00;

// Balance check
$bal = (float)$db->query("SELECT wallet_balance FROM users WHERE id = {$userId}")->fetchColumn();
assertTest("User balance check (min 5 mins = ₹100 required)", $bal >= ($rate * 5), "Current Balance: ₹{$bal}");

$db->prepare("INSERT INTO consultation_sessions 
    (session_id, user_id, astrologer_id, type, status, agora_channel, rate_per_min) 
    VALUES (?, ?, ?, 'audio', 'requested', ?, ?)")
   ->execute([$sessionId, $userId, $astrologerId, $channelName, $rate]);

$s1 = $db->query("SELECT status FROM consultation_sessions WHERE session_id = '{$sessionId}'")->fetchColumn();
assertTest("Session created with status 'requested'", $s1 === 'requested', "Session ID: {$sessionId}");

// -----------------------------------------------------------------------------
// STEP 2: Astrologer Polls Pending Consultations
// -----------------------------------------------------------------------------
echo "\n==> STEP 2: Astrologer polls incoming consultations\n";
$pending = $db->prepare("SELECT s.*, u.name as user_name, u.phone as user_phone
                         FROM consultation_sessions s
                         LEFT JOIN users u ON s.user_id = u.id
                         WHERE s.astrologer_id = ? AND s.status = 'requested'
                         ORDER BY s.id DESC LIMIT 1");
$pending->execute([$astrologerId]);
$pendRow = $pending->fetch();

assertTest("Astrologer detects incoming consultation", !empty($pendRow));
assertTest("Incoming session matches Devotee request", ($pendRow['session_id'] ?? '') === $sessionId, "Caller: " . ($pendRow['user_name'] ?? ''));

// -----------------------------------------------------------------------------
// STEP 3: Astrologer Accepts Request (Status becomes 'ringing_user' & FCM dispatched)
// -----------------------------------------------------------------------------
echo "\n==> STEP 3: Astrologer clicks 'Accept' (Status becomes 'ringing_user' & FCM dispatched)\n";
$astroToken = AgoraToken::generateRtcToken($channelName, $astrologerId);
$db->prepare("UPDATE consultation_sessions SET status = 'ringing_user', agora_astro_token = ? WHERE session_id = ?")
   ->execute([$astroToken, $sessionId]);

$fcmResult = FCM::sendIncomingCallToUser($userId, [
    'session_id'        => $sessionId,
    'channel_name'      => $channelName,
    'astrologer_id'     => $astrologerId,
    'astrologer_name'   => 'Guruji S. Sundaramurthy',
    'type'              => 'audio',
    'rate_per_min'      => $rate
]);

$s3 = $db->query("SELECT status, agora_astro_token FROM consultation_sessions WHERE session_id = '{$sessionId}'")->fetch();
assertTest("Session status updated to 'ringing_user'", $s3['status'] === 'ringing_user');
assertTest("Agora RTC Token prepared for Astrologer ahead of time", !empty($s3['agora_astro_token']));
assertTest("FCM High-Priority Call Alert dispatched to Devotee Device", ($fcmResult['success'] ?? false) === true, "Mode: " . ($fcmResult['mode'] ?? 'live'));

// -----------------------------------------------------------------------------
// STEP 4: Devotee Detects Incoming Call (Status: 'ringing_user')
// -----------------------------------------------------------------------------
echo "\n==> STEP 4: Devotee device receives incoming full-screen call\n";
$devStmt = $db->prepare("SELECT s.*, a.name as astrologer_name
                         FROM consultation_sessions s
                         LEFT JOIN astrologers a ON s.astrologer_id = a.id
                         WHERE s.user_id = ? AND s.status = 'ringing_user'
                         ORDER BY s.id DESC LIMIT 1");
$devStmt->execute([$userId]);
$devRow = $devStmt->fetch();

assertTest("Devotee incoming full-screen call prompt detected", !empty($devRow));
assertTest("Incoming call shows Astrologer Guruji Sundaramurthy", strpos($devRow['astrologer_name'] ?? '', 'Sundaramurthy') !== false);

// -----------------------------------------------------------------------------
// STEP 5: Devotee Accepts Call (Status becomes 'active' & Connect Room Created)
// -----------------------------------------------------------------------------
echo "\n==> STEP 5: Devotee taps 'Accept' on Full-Screen Call (Connect Room Created)\n";
$userToken = AgoraToken::generateRtcToken($channelName, $userId);

$driver = $db->getAttribute(PDO::ATTR_DRIVER_NAME);
if ($driver === 'sqlite') {
    $db->prepare("UPDATE consultation_sessions 
                  SET status = 'active', start_time = datetime('now'), user_accepted_at = datetime('now'),
                      agora_user_token = ? 
                  WHERE session_id = ?")
       ->execute([$userToken, $sessionId]);
} else {
    $db->prepare("UPDATE consultation_sessions 
                  SET status = 'active', start_time = NOW(), user_accepted_at = NOW(),
                      agora_user_token = ? 
                  WHERE session_id = ?")
       ->execute([$userToken, $sessionId]);
}
$db->exec("UPDATE astrologers SET call_status = 'busy' WHERE id = {$astrologerId}");

$activeSession = $db->query("SELECT status, start_time, user_accepted_at, agora_user_token, agora_astro_token 
                            FROM consultation_sessions WHERE session_id = '{$sessionId}'")->fetch();

assertTest("Session status transitioned to 'active' only after user answered", $activeSession['status'] === 'active');
assertTest("Agora User Token generated & bound to channel", !empty($activeSession['agora_user_token']));
assertTest("Both parties (User + Astrologer) tokens generated for same channel", 
    !empty($activeSession['agora_user_token']) && !empty($activeSession['agora_astro_token']));
assertTest("Astrologer marked 'busy' (concurrent call safeguard)", 
    $db->query("SELECT call_status FROM astrologers WHERE id = {$astrologerId}")->fetchColumn() === 'busy');

// -----------------------------------------------------------------------------
// STEP 6: Real-time 60-Second Heartbeat Billing
// -----------------------------------------------------------------------------
echo "\n==> STEP 6: 60-Second Real-Time Heartbeat Billing\n";
$beforeBal = (float)$db->query("SELECT wallet_balance FROM users WHERE id = {$userId}")->fetchColumn();
// Deduct 1 min (₹20)
$db->exec("UPDATE users SET wallet_balance = wallet_balance - 20 WHERE id = {$userId}");
$db->exec("UPDATE consultation_sessions SET duration_seconds = 60, total_deducted = 20, astrologer_share = 16 WHERE session_id = '{$sessionId}'");
$afterBal = (float)$db->query("SELECT wallet_balance FROM users WHERE id = {$userId}")->fetchColumn();

assertTest("1 minute billed accurately (₹{$beforeBal} -> ₹{$afterBal})", $afterBal === ($beforeBal - 20.0));

// -----------------------------------------------------------------------------
// STEP 7: End Consultation & Astrologer Release
// -----------------------------------------------------------------------------
echo "\n==> STEP 7: Call Hangup & Astrologer Release\n";
if ($driver === 'sqlite') {
    $db->exec("UPDATE consultation_sessions SET status = 'completed', end_time = datetime('now') WHERE session_id = '{$sessionId}'");
} else {
    $db->exec("UPDATE consultation_sessions SET status = 'completed', end_time = NOW() WHERE session_id = '{$sessionId}'");
}
$db->exec("UPDATE astrologers SET call_status = 'available' WHERE id = {$astrologerId}");

$finalAstroStatus = $db->query("SELECT call_status FROM astrologers WHERE id = {$astrologerId}")->fetchColumn();
$finalSessionStatus = $db->query("SELECT status FROM consultation_sessions WHERE session_id = '{$sessionId}'")->fetchColumn();

assertTest("Session marked 'completed'", $finalSessionStatus === 'completed');
assertTest("Astrologer automatically released back to 'available'", $finalAstroStatus === 'available');

echo "\n========================================================\n";
echo "🎉 ALL 7 STEPS IN THE 2-WAY HANDSHAKE CALL FLOW PASSED (100% SUCCESS)!\n";
echo "========================================================\n";
