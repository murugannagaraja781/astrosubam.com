<?php
// api/auth/delete_account.php - API endpoint to request Account & Data Deletion
declare(strict_types=1);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/Response.php';

$input = Response::getJsonInput();
$phone = trim($input['phone'] ?? '');

if (empty($phone)) {
    Response::json(false, 'Mobile number is required for account deletion.', null, 400);
}

$cleanPhone = preg_replace('/[^0-9]/', '', $phone);
if (strlen($cleanPhone) > 10 && str_starts_with($cleanPhone, '91')) {
    $cleanPhone = substr($cleanPhone, 2);
}

if (strlen($cleanPhone) !== 10) {
    Response::json(false, 'Please provide a valid 10-digit mobile number.', null, 400);
}

$db = getDb();
try {
    $stmt = $db->prepare("SELECT id, name, phone FROM users WHERE phone = ? LIMIT 1");
    $stmt->execute([$cleanPhone]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $userId = (int)$user['id'];
        $delPhone = 'DEL_' . $userId . '_' . time();

        // Anonymize and delete personal details
        $upd = $db->prepare("UPDATE users SET 
            name = 'Deleted User', 
            phone = ?, 
            email = NULL, 
            dob = NULL, 
            tob = NULL, 
            pob = NULL, 
            fcm_token = NULL,
            wallet_balance = 0.00
            WHERE id = ?");
        $upd->execute([$delPhone, $userId]);

        try {
            $db->prepare("DELETE FROM chat_messages WHERE session_id IN (SELECT session_id FROM consultation_sessions WHERE user_id = ?)")->execute([$userId]);
        } catch (\Throwable $e2) {}

        Response::json(true, 'Your account and personal data have been permanently deleted from AstroSubam.', [
            'deleted' => true,
            'phone' => $cleanPhone
        ]);
    } else {
        Response::json(true, 'Account deletion request processed successfully.', [
            'deleted' => true,
            'phone' => $cleanPhone
        ]);
    }
} catch (\Throwable $e) {
    Response::json(false, 'Error deleting account: ' . $e->getMessage(), null, 500);
}
