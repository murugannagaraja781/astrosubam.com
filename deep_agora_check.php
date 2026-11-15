<?php
// deep_agora_check.php - Full Agora Voice Call System Verification
declare(strict_types=1);

require_once __DIR__ . '/helpers/AgoraToken.php';
require_once __DIR__ . '/helpers/AccessToken2.php';

echo "=== DEEP AGORA VOICE CALL SYSTEM CHECK ===\n\n";

// 1. App Credentials
$config = require __DIR__ . '/config/agora.php';
$appId   = $config['app_id'];
$appCert = $config['app_certificate'];
echo "1. CREDENTIALS\n";
echo "   App ID      : {$appId}\n";
echo "   Cert Length : " . strlen($appCert) . " chars (need 32)\n";
echo "   Cert Valid  : " . (strlen($appCert) === 32 && ctype_xdigit($appCert) ? '✅ YES' : '❌ NO') . "\n\n";

// 2. UID Mapping
echo "2. UID MAPPING\n";
$cases = [
    ['user_id' => 2,  'expect_uid' => 100002],
    ['user_id' => 3,  'expect_uid' => 100003],
    ['astro_id' => 1, 'expect_uid' => 200001],
    ['astro_id' => 2, 'expect_uid' => 200002],
];
foreach ($cases as $c) {
    if (isset($c['user_id'])) {
        $got = AgoraToken::getUserUid($c['user_id']);
        echo "   getUserUid({$c['user_id']}) = {$got} " . ($got === $c['expect_uid'] ? '✅' : '❌ WRONG (expected '.$c['expect_uid'].')') . "\n";
    } else {
        $got = AgoraToken::getAstroUid($c['astro_id']);
        echo "   getAstroUid({$c['astro_id']}) = {$got} " . ($got === $c['expect_uid'] ? '✅' : '❌ WRONG (expected '.$c['expect_uid'].')') . "\n";
    }
}
echo "\n";

// 3. Token generation with correct UIDs
echo "3. TOKEN GENERATION (correct UID embedding)\n";
$channel = 'astrosubam_test_' . time();
$userTok  = AgoraToken::generateUserToken($channel, 2);   // userId=2 -> uid=100002
$astroTok = AgoraToken::generateAstroToken($channel, 2);  // astroId=2 -> uid=200002

foreach ([
    ['label' => 'User Token  (userId=2)',  'token' => $userTok,  'expect_uid' => '100002'],
    ['label' => 'Astro Token (astroId=2)', 'token' => $astroTok, 'expect_uid' => '200002'],
] as $t) {
    if (empty($t['token'])) {
        echo "   {$t['label']}: ❌ EMPTY TOKEN - Certificate invalid!\n";
        continue;
    }
    $a = new AccessToken2();
    $a->parse($t['token']);
    $svcs = $a->getServices(ServiceRtc::SERVICE_TYPE);
    if (empty($svcs)) {
        echo "   {$t['label']}: ❌ Could not parse ServiceRtc from token\n";
        continue;
    }
    $svc = $svcs[0];
    $uidMatch = ($svc->uid === $t['expect_uid']);
    $privs    = $svc->privileges;
    $hasAudio = isset($privs[ServiceRtc::PRIVILEGE_PUBLISH_AUDIO_STREAM]);
    echo "   {$t['label']}\n";
    echo "     Token Length : " . strlen($t['token']) . " chars\n";
    echo "     Embedded UID : [{$svc->uid}] " . ($uidMatch ? '✅' : '❌ MISMATCH! Expected ['.$t['expect_uid'].']') . "\n";
    echo "     Channel      : [{$svc->channelName}]\n";
    echo "     Audio Priv   : " . ($hasAudio ? '✅ YES' : '❌ MISSING') . "\n";
    echo "     Privileges   : " . json_encode($privs) . "\n";
}
echo "\n";

// 4. Token NOT same (UIDs must differ)
echo "4. TOKEN UNIQUENESS (user UID ≠ astro UID)\n";
$a1 = new AccessToken2(); $a1->parse($userTok);
$a2 = new AccessToken2(); $a2->parse($astroTok);
$uid1 = $a1->getServices(ServiceRtc::SERVICE_TYPE)[0]->uid ?? '';
$uid2 = $a2->getServices(ServiceRtc::SERVICE_TYPE)[0]->uid ?? '';
echo "   User UID  : {$uid1}\n";
echo "   Astro UID : {$uid2}\n";
echo "   Different : " . ($uid1 !== $uid2 ? '✅ YES (no collision)' : '❌ SAME UID = audio will NOT work!') . "\n\n";

// 5. Session lifecycle check (live DB)
echo "5. LIVE SESSION STATUS (last 5 audio sessions)\n";
require_once __DIR__ . '/config/database.php';
$db = getDb();
$rows = $db->query("SELECT session_id, user_id, astrologer_id, type, status, duration_seconds, total_deducted, agora_channel FROM consultation_sessions WHERE type IN ('audio','video') ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    $userUid  = AgoraToken::getUserUid((int)$r['user_id']);
    $astroUid = AgoraToken::getAstroUid((int)$r['astrologer_id']);
    echo "   [{$r['status']}] session={$r['session_id']}\n";
    echo "     Type={$r['type']} | userUID={$userUid} | astroUID={$astroUid} | dur={$r['duration_seconds']}s | billed=₹{$r['total_deducted']}\n";
}
echo "\n";

// 6. reject.php protection check
echo "6. REJECT.PHP - Active Session Protection\n";
$rejectCode = file_get_contents(__DIR__ . '/api/sessions/reject.php');
if (strpos($rejectCode, "status'] === 'active'") !== false) {
    echo "   ✅ Active sessions protected from timeout cancel\n";
} else {
    echo "   ❌ Active sessions NOT protected from cancel!\n";
}
echo "\n";

// 7. Reject.php does not cancel active sessions (code check)
echo "7. FLUTTER: Token passed to joinChannel\n";
$dart = file_get_contents(__DIR__ . '/flutter_app/lib/main.dart');
// Check token is from widget (not hardcoded empty)
if (strpos($dart, "token: tok") !== false) {
    echo "   ✅ Flutter passes token from widget prop to joinChannel\n";
} else {
    echo "   ❌ Flutter may not be passing token correctly\n";
}
// Check UID logic
if (strpos($dart, 'widget.agoraUid != null && widget.agoraUid! > 0') !== false) {
    echo "   ✅ Flutter uses server-provided agoraUid if > 0\n";
} else {
    echo "   ❌ Flutter UID logic missing\n";
}
// Check channelProfile broadcaster
if (strpos($dart, 'clientRoleBroadcaster') !== false) {
    echo "   ✅ Flutter uses ClientRoleBroadcaster (publishes audio)\n";
} else {
    echo "   ❌ Flutter NOT set as broadcaster - will not publish audio!\n";
}
// Check publishMicrophoneTrack
if (strpos($dart, 'publishMicrophoneTrack: true') !== false) {
    echo "   ✅ publishMicrophoneTrack: true\n";
} else {
    echo "   ❌ publishMicrophoneTrack missing!\n";
}
// Check autoSubscribeAudio
if (strpos($dart, 'autoSubscribeAudio: true') !== false) {
    echo "   ✅ autoSubscribeAudio: true\n";
} else {
    echo "   ❌ autoSubscribeAudio missing!\n";
}
// Check enableAudio
if (strpos($dart, '_engine!.enableAudio()') !== false) {
    echo "   ✅ enableAudio() called before joinChannel\n";
} else {
    echo "   ❌ enableAudio() NOT called!\n";
}
echo "\n";

// 8. Web Agora mode check
echo "8. WEB: Agora mode & publish\n";
$webCode = file_get_contents(__DIR__ . '/index.php');
$astroCode = file_get_contents(__DIR__ . '/astrologer/index.php');
if (strpos($webCode, 'mode: "live"') !== false) {
    echo "   Web devotee  : mode=live (host role) ✅\n";
}
if (strpos($astroCode, 'mode: "live"') !== false) {
    echo "   Web astrologer: mode=live (host role) ✅\n";
}
if (strpos($webCode, 'agoraClient.publish([localAudioTrack])') !== false) {
    echo "   Web devotee  : publishes audio track ✅\n";
} else {
    echo "   Web devotee  : ❌ no publish found\n";
}
if (strpos($astroCode, 'astroRtcClient.publish') !== false) {
    echo "   Web astrologer: publishes audio track ✅\n";
} else {
    echo "   Web astrologer: ❌ no publish found\n";
}
if (strpos($webCode, 'remoteAudioTrack.play()') !== false) {
    echo "   Web devotee  : plays remote audio on subscribe ✅\n";
}
if (strpos($astroCode, 'remoteAudioTrack.play()') !== false) {
    echo "   Web astrologer: plays remote audio on subscribe ✅\n";
}
// user-joined listener
if (strpos($webCode, '"user-joined"') !== false) {
    echo "   Web devotee  : user-joined fallback timer cancel ✅\n";
} else {
    echo "   Web devotee  : ❌ no user-joined listener\n";
}
if (strpos($astroCode, '"user-joined"') !== false) {
    echo "   Web astrologer: user-joined fallback timer cancel ✅\n";
} else {
    echo "   Web astrologer: ❌ no user-joined listener\n";
}
echo "\n";

echo "=== DEEP CHECK COMPLETE ===\n";
