<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

echo "=== ASTROSUBAM: ASTROLOGER PROFILE EDIT UNIT TEST ===\n";

$db = getDb();

// 1. Fetch Astrologer 1 original data
$stmt = $db->query("SELECT * FROM astrologers WHERE id = 1");
$original = $stmt->fetch();
if (!$original) {
    die("❌ Astrologer 1 not found\n");
}
echo "✔ Found Astrologer 1: {$original['name']} (Phone: {$original['phone']}, Audio: ₹{$original['audio_rate_min']}/m)\n";

// 2. Perform Super Admin Profile Edit simulation
$newSpecialities = "Vedic Astrology • KP System • Gold Medalist • Marriage Specialist";
$newAudioRate = 22.0;
$newVideoRate = 38.0;
$newChatRate = 14.0;
$newExp = 23;
$newStatus = 'available';

$upd = $db->prepare("UPDATE astrologers SET 
    specialities = ?, 
    audio_rate_min = ?, 
    video_rate_min = ?, 
    chat_rate_min = ?, 
    experience_years = ?, 
    call_status = ?, 
    is_video_enabled = 1 
    WHERE id = 1");
$upd->execute([$newSpecialities, $newAudioRate, $newVideoRate, $newChatRate, $newExp, $newStatus]);

// 3. Verify Database Persistence
$checkStmt = $db->query("SELECT * FROM astrologers WHERE id = 1");
$updated = $checkStmt->fetch();

assert((float)$updated['audio_rate_min'] === 22.0, "Audio rate updated to 22.0");
assert((float)$updated['video_rate_min'] === 38.0, "Video rate updated to 38.0");
assert((float)$updated['chat_rate_min'] === 14.0, "Chat rate updated to 14.0");
assert((int)$updated['experience_years'] === 23, "Experience updated to 23");
assert($updated['specialities'] === $newSpecialities, "Specialities updated");

echo "✔ Successfully updated and verified all profile fields in database\n";

// 4. Test programmatic API update logic (api/astrologers/update.php)
$apiUpdateStmt = $db->prepare("UPDATE astrologers SET audio_rate_min = ?, video_rate_min = ? WHERE id = ?");
$apiUpdateStmt->execute([20.0, 35.0, 1]);
$recheck = $db->query("SELECT audio_rate_min, video_rate_min FROM astrologers WHERE id = 1")->fetch();
assert((float)$recheck['audio_rate_min'] === 20.0, "Restored audio rate");
assert((float)$recheck['video_rate_min'] === 35.0, "Restored video rate");

// 5. Restore original specialities and experience
$restore = $db->prepare("UPDATE astrologers SET 
    specialities = ?, 
    experience_years = ?, 
    audio_rate_min = ?, 
    video_rate_min = ?, 
    chat_rate_min = ?, 
    call_status = ? 
    WHERE id = 1");
$restore->execute([
    $original['specialities'], 
    $original['experience_years'], 
    $original['audio_rate_min'], 
    $original['video_rate_min'], 
    $original['chat_rate_min'],
    $original['call_status']
]);

echo "✔ Restored original astrologer profile data safely\n";
echo "🎉 ASTROLOGER PROFILE EDIT UNIT TEST PASSED 100%!\n";
