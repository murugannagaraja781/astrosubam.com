<?php
// api/astrologers/register.php - New Astrologer Registration API Endpoint
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/Response.php';

$input = Response::getJsonInput();

$name = trim((string)($input['name'] ?? ''));
$phone = preg_replace('/[^0-9]/', '', (string)($input['phone'] ?? ''));
$email = trim((string)($input['email'] ?? ''));
$password = (string)($input['password'] ?? 'subam123');
$languages = trim((string)($input['languages'] ?? 'Tamil, English'));
$specialities = trim((string)($input['specialities'] ?? 'Vedic Astrology • KP System'));
$experienceYears = max(1, (int)($input['experience_years'] ?? $input['experience'] ?? 5));
$audioRate = max(5.0, (float)($input['audio_rate_min'] ?? $input['audio_rate'] ?? 20.0));
$videoRate = max(10.0, (float)($input['video_rate_min'] ?? $input['video_rate'] ?? 35.0));
$chatRate = max(5.0, (float)($input['chat_rate_min'] ?? $input['chat_rate'] ?? 12.0));
$avatar = trim((string)($input['avatar'] ?? 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=300&q=80'));

if (empty($name)) {
    Response::json(false, 'Astrologer full name is required.', null, 400);
}

if (strlen($phone) < 10) {
    Response::json(false, 'Valid 10-digit mobile number is required.', null, 400);
}

$db = getDb();

// Check if phone number already exists
$stmt = $db->prepare("SELECT id FROM astrologers WHERE phone = ?");
$stmt->execute([$phone]);
if ($stmt->fetch()) {
    Response::json(false, 'An astrologer with this mobile number is already registered. Please log in.', null, 409);
}

$passwordHash = password_hash($password, PASSWORD_BCRYPT);

try {
    $ins = $db->prepare("INSERT INTO astrologers 
        (name, phone, email, password_hash, avatar, languages, specialities, experience_years, audio_rate_min, video_rate_min, chat_rate_min, call_status, rating, total_reviews)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'offline', 5.00, 1)");
    
    $ins->execute([
        $name,
        $phone,
        $email,
        $passwordHash,
        $avatar,
        $languages,
        $specialities,
        $experienceYears,
        $audioRate,
        $videoRate,
        $chatRate
    ]);
    
    $astroId = (int)$db->lastInsertId();
    
    Response::json(true, 'வணக்கம்! ஜோதிடர் பதிவு வெற்றிகரமாக முடிந்தது. நீங்கள் இப்போது லாகின் செய்யலாம்.', [
        'astrologer' => [
            'id' => $astroId,
            'name' => $name,
            'phone' => $phone,
            'email' => $email,
            'languages' => $languages,
            'specialities' => $specialities,
            'experience_years' => $experienceYears,
            'audio_rate_min' => $audioRate,
            'video_rate_min' => $videoRate,
            'chat_rate_min' => $chatRate,
            'status' => 'offline'
        ]
    ], 201);
} catch (Exception $e) {
    Response::json(false, 'Database error during registration: ' . $e->getMessage(), null, 500);
}
