<?php
// helpers/AgoraToken.php
declare(strict_types=1);

require_once __DIR__ . '/RtcTokenBuilder2.php';

class AgoraToken {
    public static function getUserUid(int $userId): int {
        return 100000 + $userId;
    }

    public static function getAstroUid(int $astroId): int {
        return 200000 + $astroId;
    }

    public static function generateUserToken(string $channelName, int $userId, int $role = 1, int $expireSeconds = 3600): string {
        $uid = self::getUserUid($userId);
        return self::generateRtcToken($channelName, $uid, $role, $expireSeconds);
    }

    public static function generateAstroToken(string $channelName, int $astroId, int $role = 1, int $expireSeconds = 3600): string {
        $uid = self::getAstroUid($astroId);
        return self::generateRtcToken($channelName, $uid, $role, $expireSeconds);
    }

    public static function generateRtcToken(string $channelName, int $uid = 0, int $role = 1, int $expireSeconds = 3600): string {
        $config = require __DIR__ . '/../config/agora.php';
        $appId = $config['app_id'];
        $appCert = $config['app_certificate'];

        // If credentials are placeholders, return a simulated debug token for testing
        if ($appId === 'YOUR_AGORA_APP_ID' || empty($appCert) || $appCert === 'YOUR_AGORA_APP_CERTIFICATE') {
            return "agora_simulated_token_" . md5($channelName . $uid . time());
        }

        // Official Agora AccessToken2 construction
        try {
            $agoraRole = ($role === 2) ? RtcTokenBuilder2::ROLE_SUBSCRIBER : RtcTokenBuilder2::ROLE_PUBLISHER;
            return RtcTokenBuilder2::buildTokenWithUid(
                $appId,
                $appCert,
                $channelName,
                $uid,
                $agoraRole,
                $expireSeconds,
                $expireSeconds
            );
        } catch (\Throwable $e) {
            error_log("[AgoraToken] Failed to generate token: " . $e->getMessage());
            return "";
        }
    }
}
