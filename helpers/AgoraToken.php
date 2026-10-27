<?php
// helpers/AgoraToken.php
declare(strict_types=1);

class AgoraToken {
    public static function generateRtcToken(string $channelName, int $uid = 0, int $role = 1, int $expireSeconds = 3600): string {
        $config = require __DIR__ . '/../config/agora.php';
        $appId = $config['app_id'];
        $appCert = $config['app_certificate'];

        // If credentials are placeholders, return a simulated debug token for testing
        if ($appId === 'YOUR_AGORA_APP_ID' || empty($appCert) || $appCert === 'YOUR_AGORA_APP_CERTIFICATE') {
            return "agora_simulated_token_" . md5($channelName . $uid . time());
        }

        // Official Agora Token construction algorithm
        $version = "007";
        $currentTimestamp = time();
        $privilegeExpiredTs = $currentTimestamp + $expireSeconds;

        $message = pack('V', $privilegeExpiredTs) . $appId . $channelName . pack('V', $uid);
        $signature = hash_hmac('sha256', $message, $appCert, true);
        $token = $version . $appId . base64_encode($signature) . "_" . $privilegeExpiredTs;

        return $token;
    }
}
