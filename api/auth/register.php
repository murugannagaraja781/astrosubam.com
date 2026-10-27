<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/Response.php';
require_once __DIR__ . '/../../helpers/JWT.php';

$input = Response::getJsonInput();
$name = trim($input['name'] ?? '');
$phone = trim($input['phone'] ?? '');
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';
$gender = $input['gender'] ?? 'Male';

if (empty($name) || empty($phone) || empty($password)) {
    Response::json(false, 'Name, Phone, and Password are required.', null, 400);
}

$db = getDb();

// Check existing
$stmt = $db->prepare("SELECT id FROM users WHERE phone = ? OR (email != '' AND email = ?)");
$stmt->execute([$phone, $email]);
if ($stmt->fetch()) {
    Response::json(false, 'User with this phone or email already exists.', null, 409);
}

$hash = password_hash($password, PASSWORD_BCRYPT);
$ins = $db->prepare("INSERT INTO users (name, phone, email, password_hash, gender, wallet_balance) VALUES (?, ?, ?, ?, ?, 100.00)");
$ins->execute([$name, $phone, $email, $hash, $gender]);
$userId = (int)$db->lastInsertId();

$token = JWT::encode(['user_id' => $userId, 'phone' => $phone, 'name' => $name, 'role' => 'client']);

Response::json(true, 'Registration successful. ₹100 welcome bonus added to your wallet!', [
    'user' => [
        'id' => $userId,
        'name' => $name,
        'phone' => $phone,
        'email' => $email,
        'gender' => $gender,
        'wallet_balance' => 100.00
    ],
    'token' => $token
], 201);
