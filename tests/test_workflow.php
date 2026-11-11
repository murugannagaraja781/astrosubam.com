<?php
// tests/test_workflow.php - Fast Automated Workflow Diagnostic for AI Agents & Developers
declare(strict_types=1);

$baseUrl = 'https://subam.navanagari.com/api';

function runApi(string $method, string $endpoint, array $data = []): array {
    global $baseUrl;
    $url = $baseUrl . $endpoint;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $headers = ['Accept: application/json'];
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        $payload = json_encode($data);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Content-Length: ' . strlen($payload);
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $res = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) return ['ok' => false, 'error' => $err];
    $json = json_decode($res, true);
    return is_array($json) ? $json : ['ok' => false, 'raw' => $res];
}

echo "========================================================\n";
echo "🤖 ASTROSUBAM - RAPID AI WORKFLOW HEALTH CHECK\n";
echo "========================================================\n\n";

$allPassed = true;

// 1. Astrologer Dashboard Check
$dash = runApi('GET', '/astrologers/dashboard.php?astrologer_id=1&phone=9876543210');
if ($dash['ok'] ?? false) {
    echo "✔ [1/5] Astrologer Dashboard Online & Ready (Astro ID 1: Guruji)\n";
} else {
    echo "✖ [1/5] Astrologer Dashboard Check Failed: " . json_encode($dash) . "\n";
    $allPassed = false;
}

// 2. Consultation Creation
$req = runApi('POST', '/sessions/request.php', [
    'user_id' => 2,
    'astrologer_id' => 1,
    'type' => 'chat',
    'user_name' => 'AI Test Devotee',
    'user_phone' => '9876543299'
]);
$sid = $req['data']['session_id'] ?? null;
if ($sid) {
    echo "✔ [2/5] Session Creation & Live Queue Placement ($sid)\n";
} else {
    echo "✖ [2/5] Session Creation Failed: " . json_encode($req) . "\n";
    $allPassed = false;
}

// 3. Astrologer Accept & User Handshake
if ($sid) {
    $acc = runApi('POST', '/sessions/accept.php', ['session_id' => $sid, 'astrologer_id' => 1]);
    $uAcc = runApi('POST', '/sessions/user_accept.php', ['session_id' => $sid, 'user_id' => 2]);
    $chk = runApi('GET', '/sessions/check_status.php?session_id=' . urlencode($sid));
    if (($chk['data']['status'] ?? '') === 'active') {
        echo "✔ [3/5] Handshake Complete (status: active)\n";
    } else {
        echo "✖ [3/5] Handshake Failed. Current status: " . ($chk['data']['status'] ?? 'unknown') . "\n";
        $allPassed = false;
    }
}

// 4. Two-Way Live Chat Transmission
if ($sid) {
    $s1 = runApi('POST', '/chat/send.php', ['session_id' => $sid, 'sender_type' => 'user', 'sender_id' => 2, 'content' => 'AI Check User Msg']);
    $s2 = runApi('POST', '/chat/send.php', ['session_id' => $sid, 'sender_type' => 'astrologer', 'sender_id' => 1, 'content' => 'AI Check Astro Reply']);
    $msgs = runApi('GET', '/chat/messages.php?session_id=' . urlencode($sid));
    $count = count($msgs['data']['messages'] ?? []);
    if ($count >= 2) {
        echo "✔ [4/5] Two-Way Real-time Chat Sync ($count messages verified)\n";
    } else {
        echo "✖ [4/5] Chat Sync Failed: count=$count\n";
        $allPassed = false;
    }
}

// 5. Heartbeat Billing & Session Cleanup
if ($sid) {
    $hb = runApi('POST', '/sessions/heartbeat.php', ['session_id' => $sid]);
    $end = runApi('POST', '/sessions/end.php', ['session_id' => $sid, 'rating' => 5]);
    if (($end['data']['status'] ?? '') === 'completed') {
        echo "✔ [5/5] Real-Time Billing & Graceful Session Completion\n";
    } else {
        echo "✖ [5/5] End Session Failed: " . json_encode($end) . "\n";
        $allPassed = false;
    }
}

echo "\n--------------------------------------------------------\n";
if ($allPassed) {
    echo "🎉 ALL SYSTEM WORKFLOWS OPERATING AT 100% HEALTH!\n";
    exit(0);
} else {
    echo "⚠️ ONE OR MORE WORKFLOWS FAILED. CHECK SYSTEM LOGS.\n";
    exit(1);
}
