<?php
// config/agora.php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/Settings.php';

return [
    'app_id'               => Settings::get('agora_app_id', 'YOUR_AGORA_APP_ID'),
    'app_certificate'      => Settings::get('agora_app_certificate', 'YOUR_AGORA_APP_CERTIFICATE'),
    'token_expire_seconds' => (int)Settings::get('agora_token_expire_seconds', 3600),
    'channel_prefix'       => Settings::get('agora_channel_prefix', 'astrosubam_')
];
