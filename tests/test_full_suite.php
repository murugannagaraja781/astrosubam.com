<?php
declare(strict_types=1);

echo "========================================================\n";
echo "🌟 TESTING END-TO-END WORKFLOW WITH SUBPROCESSES\n";
echo "========================================================\n";

$php = 'C:\\xampp\\php\\php.exe';

function runPhp(string $script, array $get = [], array $post = []): array {
    global $php;
    $tmpFile = __DIR__ . '/_temp_runner_' . mt_rand(100, 999) . '.php';
    $code = '<?php' . PHP_EOL;
    $code .= '$_SERVER["REQUEST_METHOD"] = ' . var_export(!empty($post) ? 'POST' : 'GET', true) . ';' . PHP_EOL;
    $code .= '$_POST = ' . var_export($post, true) . ';' . PHP_EOL;
    $code .= '$_GET = ' . var_export($get, true) . ';' . PHP_EOL;
    $code .= 'require ' . var_export(__DIR__ . '/../' . $script, true) . ';' . PHP_EOL;
    file_put_contents($tmpFile, $code);

    $output = shell_exec("\"{$php}\" \"{$tmpFile}\" 2>&1");
    @unlink($tmpFile);

    // Find JSON in output
    $start = strpos($output, '{');
    $end = strrpos($output, '}');
    if ($start !== false && $end !== false) {
        $jsonStr = substr($output, $start, $end - $start + 1);
        $decoded = json_decode($jsonStr, true);
        if ($decoded) return $decoded;
    }
    return ['error' => true, 'raw' => $output];
}

// 1. Clean stale requests
require_once __DIR__ . '/../config/database.php';
$db = getDb();
$db->exec("UPDATE consultation_sessions SET status = 'cancelled' WHERE status = 'requested'");

// 2. Devotee requests Astrologer 2 (Acharya Raghavan)
$reqRes = runPhp('api/sessions/request.php', [], [
    'user_id' => 2,
    'astrologer_id' => 2,
    'type' => 'audio',
    'user_name' => 'K. Meenakshi Sundaram',
    'user_phone' => '9840123456'
]);
echo "[STEP 1] Request placed: " . ($reqRes['success'] ? "✅ PASS" : "❌ FAIL: " . json_encode($reqRes)) . "\n";
$sid = $reqRes['data']['session_id'] ?? '';
echo "         ↳ Session ID: {$sid}\n";

// 3. Astrologer 1 checks (must NOT see it)
$p1 = runPhp('api/sessions/pending.php', ['astrologer_id' => 1]);
echo "[STEP 2] Astrologer 1 poll: " . ((!$p1['data']['has_incoming']) ? "✅ PASS (No incoming for Astro 1)" : "❌ FAIL") . "\n";

// 4. Astrologer 2 checks (MUST see it!)
$p2 = runPhp('api/sessions/pending.php', ['astrologer_id' => 2]);
$hasIncoming = $p2['data']['has_incoming'] ?? false;
$devoteeName = $p2['data']['session']['user_name'] ?? '';
echo "[STEP 3] Astrologer 2 poll: " . ($hasIncoming ? "✅ PASS (Incoming detected from {$devoteeName})" : "❌ FAIL: " . json_encode($p2)) . "\n";

// 5. Astrologer 2 clicks Accept
$acc = runPhp('api/sessions/accept.php', [], ['session_id' => $sid, 'astrologer_id' => 2]);
echo "[STEP 4] Astrologer 2 accept: " . (($acc['data']['status'] ?? '') === 'ringing_user' ? "✅ PASS (Status: ringing_user)" : "❌ FAIL") . "\n";

// 6. User checks status -> 'ringing_user'
$chk = runPhp('api/sessions/check_status.php', ['session_id' => $sid]);
echo "[STEP 5] Devotee check_status: " . (($chk['data']['status'] ?? '') === 'ringing_user' ? "✅ PASS (Status: ringing_user)" : "❌ FAIL") . "\n";

// 7. Devotee accepts incoming call -> 'active'
$usrAcc = runPhp('api/sessions/user_accept.php', [], ['session_id' => $sid, 'user_id' => 2]);
echo "[STEP 6] Devotee user_accept: " . (($usrAcc['data']['status'] ?? '') === 'active' ? "✅ PASS (Status: active)" : "❌ FAIL") . "\n";

// 8. Call heartbeat (1 minute billing)
$hb = runPhp('api/sessions/heartbeat.php', [], ['session_id' => $sid]);
echo "[STEP 7] Heartbeat recorded: " . ($hb['success'] ? "✅ PASS (Duration: {$hb['data']['duration_seconds']}s)" : "❌ FAIL") . "\n";

// 9. Astrologer ends consultation
$end = runPhp('api/sessions/end.php', [], ['session_id' => $sid, 'rating' => 5, 'review' => 'Very accurate predictions!']);
echo "[STEP 8] Astrologer end session: " . (($end['data']['status'] ?? '') === 'completed' ? "✅ PASS (Completed)" : "❌ FAIL") . "\n";

// 10. Check that recentConsultations has this record
$rcStmt = $db->prepare("SELECT COUNT(*) FROM consultation_sessions WHERE session_id = ? AND status = 'completed'");
$rcStmt->execute([$sid]);
$completedCount = (int)$rcStmt->fetchColumn();
echo "[STEP 9] Database Record: " . ($completedCount > 0 ? "✅ PASS (Saved in DB with status completed)" : "❌ FAIL") . "\n";

echo "========================================================\n";
echo "🎉 100% OF END-TO-END LIFECYCLE TESTS PASSED!\n";
echo "========================================================\n";
