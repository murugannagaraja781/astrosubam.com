<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/Response.php';
require_once __DIR__ . '/../../helpers/JWT.php';

$input = Response::getJsonInput();
$phoneOrEmail = trim($input['phone_or_email'] ?? $input['phone'] ?? '');
$password = $input['password'] ?? '';

if (empty($phoneOrEmail) || empty($password)) {
    Response::json(false, 'Phone/Email and Password are required.', null, 400);
}

$db = getDb();
$stmt = $db->prepare("SELECT id, name, phone, email, password_hash, gender, wallet_balance, role FROM users WHERE phone = ? OR email = ?");
$stmt->execute([$phoneOrEmail, $phoneOrEmail]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    Response::json(false, 'Invalid credentials.', null, 401);
}

unset($user['password_hash']);
$token = JWT::encode([
    'user_id' => $user['id'],
    'phone'   => $user['phone'],
    'name'    => $user['name'],
    'role'    => $user['role']
]);

Response::json(true, 'Login successful.', [
    'user'  => $user,
    'token' => $token
]);
