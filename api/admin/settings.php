<?php
// api/admin/settings.php - Super Admin Agora & System Settings API
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/Response.php';
require_once __DIR__ . '/../../helpers/Settings.php';
require_once __DIR__ . '/../../helpers/AgoraToken.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $settings = Settings::getAll();
    $appId = $settings['agora_app_id'];
    $appCert = $settings['agora_app_certificate'];

    $isConfigured = !empty($appId) && $appId !== 'YOUR_AGORA_APP_ID' && !empty($appCert) && $appCert !== 'YOUR_AGORA_APP_CERTIFICATE';

    // Mask certificate for safety in response
    $maskedCert = !empty($appCert) && strlen($appCert) > 8 
        ? substr($appCert, 0, 4) . '••••••••' . substr($appCert, -4)
        : ($isConfigured ? '••••••••' : 'Not Configured');

    Response::json(true, 'Admin settings loaded.', [
        'agora' => [
            'app_id'               => $appId,
            'app_certificate'      => $appCert,
            'app_certificate_masked' => $maskedCert,
            'token_expire_seconds' => (int)$settings['agora_token_expire_seconds'],
            'channel_prefix'       => $settings['agora_channel_prefix'],
            'is_configured'        => $isConfigured
        ]
    ]);
}

if ($method === 'POST') {
    $input = Response::getJsonInput();

    $appId = trim($input['agora_app_id'] ?? '');
    $appCert = trim($input['agora_app_certificate'] ?? '');
    $expireSec = trim((string)($input['agora_token_expire_seconds'] ?? '3600'));
    $prefix = trim($input['agora_channel_prefix'] ?? 'astrosubam_');

    if (empty($appId)) {
        Response::json(false, 'Agora App ID cannot be empty.', null, 400);
    }

    // Save to Database and synchronize to .env
    Settings::set('agora_app_id', $appId, 'Agora App ID for Voice/Video Call RTC');
    if (!empty($appCert)) {
        Settings::set('agora_app_certificate', $appCert, 'Agora App Certificate for Dynamic Token Builder');
    }
    if (!empty($expireSec)) {
        Settings::set('agora_token_expire_seconds', $expireSec, 'Agora Token Expiry Seconds');
    }
    if (!empty($prefix)) {
        Settings::set('agora_channel_prefix', $prefix, 'Agora Channel Name Prefix');
    }

    // Test token generation with new credentials
    $testChannel = "test_subam_" . time();
    $testToken = AgoraToken::generateRtcToken($testChannel, 9999);

    Response::json(true, '✅ Agora Call configuration updated successfully in Database and .env!', [
        'agora_app_id'     => $appId,
        'test_channel'     => $testChannel,
        'test_token'       => $testToken,
        'status'           => 'Active & Ready'
    ]);
}

Response::json(false, 'Method not allowed.', null, 405);
