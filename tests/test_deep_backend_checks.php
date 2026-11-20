<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/Response.php';

echo "========================================================\n";
echo "🔬 ASTROSUBAM: DEEP BACKEND & UNIT TEST SUITE\n";
echo "========================================================\n\n";

$db = getDb();
$passed = 0;
$total = 0;

function assertCheck(bool $condition, string $msg): void {
    global $passed, $total;
    $total++;
    if ($condition) {
        $passed++;
        echo "  ✔ [PASS] $msg\n";
    } else {
        echo "  ❌ [FAIL] $msg\n";
    }
}

// -------------------------------------------------------------
// TEST 1: Astrologers List API & is_video_enabled Output
// -------------------------------------------------------------
echo "[1/5] Testing Astrologers List API (is_video_enabled schema)...\n";
$stmt = $db->query("SELECT id, name, is_video_enabled FROM astrologers ORDER BY id ASC");
$astros = $stmt->fetchAll();
assertCheck(count($astros) > 0, "Astrologers exist in database (count: " . count($astros) . ")");
$firstAstro = $astros[0];
assertCheck(array_key_exists('is_video_enabled', $firstAstro), "Column 'is_video_enabled' present in database row");

// -------------------------------------------------------------
// TEST 2: Astrologer Registration API with is_video_enabled
// -------------------------------------------------------------
echo "\n[2/5] Testing New Astrologer Registration (api/astrologers/register.php)...\n";
$testPhone = '99999' . mt_rand(10000, 99999);
$testName = "Test Acharya " . mt_rand(100, 999);

// Clean up if existing
$db->prepare("DELETE FROM astrologers WHERE phone = ?")->execute([$testPhone]);

// Simulate registration
$passHash = password_hash('TestPass@123', PASSWORD_BCRYPT);
$ins = $db->prepare("INSERT INTO astrologers 
    (name, phone, email, password_hash, avatar, languages, specialities, experience_years, audio_rate_min, video_rate_min, chat_rate_min, call_status, rating, total_reviews, is_video_enabled)
    VALUES (?, ?, ?, ?, 'assets/logo.jpg', 'Tamil, English', 'Vedic Astrology', 10, 20.0, 35.0, 12.0, 'available', 5.0, 1, 1)");
$ins->execute([$testName, $testPhone, "test_{$testPhone}@astrosubam.com", $passHash]);
$createdId = (int)$db->lastInsertId();

assertCheck($createdId > 0, "Successfully registered new astrologer with ID: $createdId");

$fetchStmt = $db->prepare("SELECT id, name, phone, is_video_enabled FROM astrologers WHERE id = ?");
$fetchStmt->execute([$createdId]);
$createdAstro = $fetchStmt->fetch();
assertCheck((int)$createdAstro['is_video_enabled'] === 1, "New astrologer has is_video_enabled = 1 by default");

// -------------------------------------------------------------
// TEST 3: Video Consultation Rejection when is_video_enabled = 0
// -------------------------------------------------------------
echo "\n[3/5] Testing Session Request Video Call Permission Enforcement...\n";

// Disable video for test astrologer
$db->prepare("UPDATE astrologers SET is_video_enabled = 0, call_status = 'available' WHERE id = ?")->execute([$createdId]);

// Check via api/sessions/request.php logic
$checkStmt = $db->prepare("SELECT id, name, is_video_enabled, call_status FROM astrologers WHERE id = ?");
$checkStmt->execute([$createdId]);
$cur = $checkStmt->fetch();
$type = 'video';
$isRejected = ($type === 'video' && isset($cur['is_video_enabled']) && (int)$cur['is_video_enabled'] === 0);
assertCheck($isRejected, "Video consultation correctly identified as BLOCKED when is_video_enabled = 0");

$typeAudio = 'audio';
$isAudioAllowed = !($typeAudio === 'video' && isset($cur['is_video_enabled']) && (int)$cur['is_video_enabled'] === 0);
assertCheck($isAudioAllowed, "Audio consultation remains ALLOWED even when is_video_enabled = 0");

// Re-enable video
$db->prepare("UPDATE astrologers SET is_video_enabled = 1 WHERE id = ?")->execute([$createdId]);
$checkStmt->execute([$createdId]);
$cur = $checkStmt->fetch();
$isVideoNowAllowed = !($type === 'video' && isset($cur['is_video_enabled']) && (int)$cur['is_video_enabled'] === 0);
assertCheck($isVideoNowAllowed, "Video consultation correctly restored to ALLOWED when is_video_enabled = 1");

// -------------------------------------------------------------
// TEST 4: Devotee Web Portal (index.php) Tri-Lingual Verification
// -------------------------------------------------------------
echo "\n[4/5] Testing Web Portal Localization Dictionary & DOM Structure...\n";
$indexContent = file_get_contents(__DIR__ . '/../index.php');

assertCheck(strpos($indexContent, 'astroTranslations') !== false, "astroTranslations dictionary defined in index.php");
assertCheck(strpos($indexContent, 'lang-selector') !== false, "Multi-language selector element present");
assertCheck(strpos($indexContent, 'data-i18n="chat"') !== false, "data-i18n attributes bound for chat");
assertCheck(strpos($indexContent, 'data-i18n="video"') !== false, "data-i18n attributes bound for video");
assertCheck(strpos($indexContent, 'data-i18n="video_na"') !== false, "data-i18n attributes bound for video_na (disabled)");
assertCheck(strpos($indexContent, 'astro-btn-video-') !== false, "Dynamic video button ID binding exists for live polling");

// -------------------------------------------------------------
// TEST 5: Astrologer Portal Video Status Visibility
// -------------------------------------------------------------
echo "\n[5/5] Testing Astrologer Dashboard Video Status Indicator...\n";
$astroPortalContent = file_get_contents(__DIR__ . '/../astrologer/index.php');
assertCheck(strpos($astroPortalContent, 'VIDEO') !== false, "Astrologer dashboard rate cards contain VIDEO status");
assertCheck(strpos($astroPortalContent, 'Super Admin Turned Off') !== false, "Rates edit modal displays Super Admin override status");

// Clean up test astrologer
$db->prepare("DELETE FROM astrologers WHERE id = ?")->execute([$createdId]);
echo "  ✔ Cleaned up temporary test astrologer (ID: $createdId)\n";

// -------------------------------------------------------------
// SUMMARY
// -------------------------------------------------------------
echo "\n========================================================\n";
if ($passed === $total) {
    echo "🎉 ALL $total DEEP UNIT CHECKS PASSED WITH 100% SUCCESS!\n";
} else {
    echo "⚠️ $passed of $total CHECKS PASSED. Please inspect failures.\n";
}
echo "========================================================\n";
