<?php
// api/astrologers/update.php - Update Astrologer Profile API Endpoint
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/Response.php';

$input = Response::getJsonInput();

$id = (int)($input['id'] ?? ($input['astrologer_id'] ?? 0));
$name = trim((string)($input['name'] ?? ''));
$phone = preg_replace('/[^0-9]/', '', (string)($input['phone'] ?? ''));
if (strlen($phone) > 10 && str_starts_with($phone, '91')) $phone = substr($phone, 2);
$email = trim((string)($input['email'] ?? ''));
$password = (string)($input['password'] ?? '');
$specialities = trim((string)($input['specialities'] ?? ''));
$languages = trim((string)($input['languages'] ?? ''));
$experience = isset($input['experience_years']) ? (int)$input['experience_years'] : (isset($input['experience']) ? (int)$input['experience'] : 0);
$audioRate = isset($input['audio_rate_min']) ? (float)$input['audio_rate_min'] : (isset($input['audio_rate']) ? (float)$input['audio_rate'] : 0.0);
$videoRate = isset($input['video_rate_min']) ? (float)$input['video_rate_min'] : (isset($input['video_rate']) ? (float)$input['video_rate'] : 0.0);
$chatRate = isset($input['chat_rate_min']) ? (float)$input['chat_rate_min'] : (isset($input['chat_rate']) ? (float)$input['chat_rate'] : 0.0);
$hasVideoEnabled = isset($input['is_video_enabled']);
$isVideoEnabled = $hasVideoEnabled ? ($input['is_video_enabled'] == 1 || $input['is_video_enabled'] === true || $input['is_video_enabled'] === '1' ? 1 : 0) : 1;
$callStatus = trim((string)($input['call_status'] ?? ($input['status'] ?? '')));
$rating = isset($input['rating']) ? (float)$input['rating'] : 0.0;
$avatar = trim((string)($input['avatar'] ?? ''));

if ($id <= 0) {
    Response::json(false, 'Astrologer ID is required.', null, 400);
}

$db = getDb();

// Check if astrologer exists
$stmt = $db->prepare("SELECT * FROM astrologers WHERE id = ?");
$stmt->execute([$id]);
$astro = $stmt->fetch();
if (!$astro) {
    Response::json(false, 'Astrologer not found.', null, 404);
}

// Check phone uniqueness if phone is being changed
if (!empty($phone) && $phone !== $astro['phone']) {
    $chk = $db->prepare("SELECT id FROM astrologers WHERE phone = ? AND id != ?");
    $chk->execute([$phone, $id]);
    if ($chk->fetch()) {
        Response::json(false, "An astrologer with phone {$phone} already exists.", null, 409);
    }
}

// Prepare update fields dynamically
$updates = [];
$params = [];

if (!empty($name)) {
    $updates[] = "name = ?";
    $params[] = $name;
}
if (!empty($phone)) {
    $updates[] = "phone = ?";
    $params[] = $phone;
}
if (isset($input['email'])) {
    $updates[] = "email = ?";
    $params[] = $email;
}
if (!empty($password)) {
    $updates[] = "password_hash = ?";
    $params[] = password_hash($password, PASSWORD_BCRYPT);
}
if (!empty($specialities)) {
    $updates[] = "specialities = ?";
    $params[] = $specialities;
}
if (!empty($languages)) {
    $updates[] = "languages = ?";
    $params[] = $languages;
}
if ($experience > 0) {
    $updates[] = "experience_years = ?";
    $params[] = $experience;
}
if ($audioRate > 0) {
    $updates[] = "audio_rate_min = ?";
    $params[] = $audioRate;
}
if ($videoRate > 0) {
    $updates[] = "video_rate_min = ?";
    $params[] = $videoRate;
}
if ($chatRate > 0) {
    $updates[] = "chat_rate_min = ?";
    $params[] = $chatRate;
}
if ($hasVideoEnabled) {
    $updates[] = "is_video_enabled = ?";
    $params[] = $isVideoEnabled;
}
if (!empty($callStatus) && in_array($callStatus, ['available', 'busy', 'offline'], true)) {
    $updates[] = "call_status = ?";
    $params[] = $callStatus;
}
if ($rating > 0) {
    $updates[] = "rating = ?";
    $params[] = max(1.0, min(5.0, $rating));
}
if (!empty($avatar)) {
    $updates[] = "avatar = ?";
    $params[] = $avatar;
}

if (empty($updates)) {
    Response::json(false, 'No fields provided to update.', null, 400);
}

$params[] = $id;
$sql = "UPDATE astrologers SET " . implode(', ', $updates) . " WHERE id = ?";
$upd = $db->prepare($sql);
$upd->execute($params);

// Fetch updated profile
$fetchStmt = $db->prepare("SELECT id, name, phone, email, specialities, languages, experience_years, audio_rate_min, video_rate_min, chat_rate_min, is_video_enabled, call_status, rating, avatar FROM astrologers WHERE id = ?");
$fetchStmt->execute([$id]);
$updatedAstro = $fetchStmt->fetch();

Response::json(true, 'Astrologer profile updated successfully.', [
    'astrologer' => $updatedAstro
]);
