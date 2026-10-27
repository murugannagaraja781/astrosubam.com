<?php
// helpers/JWT.php
declare(strict_types=1);

class JWT {
    private static string $secretKey = 'astrosubam_super_secure_divine_secret_key_2026';

    public static function encode(array $payload, int $expirySeconds = 86400 * 30): string {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload['exp'] = time() + $expirySeconds;
        $payload['iat'] = time();

        $base64Header = self::base64UrlEncode($header);
        $base64Payload = self::base64UrlEncode(json_encode($payload));
        $signature = hash_hmac('sha256', "{$base64Header}.{$base64Payload}", self::$secretKey, true);
        $base64Signature = self::base64UrlEncode($signature);

        return "{$base64Header}.{$base64Payload}.{$base64Signature}";
    }

    public static function decode(string $token): ?array {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$headerB64, $payloadB64, $sigB64] = $parts;
        $signature = self::base64UrlDecode($sigB64);
        $expectedSig = hash_hmac('sha256', "{$headerB64}.{$payloadB64}", self::$secretKey, true);

        if (!hash_equals($expectedSig, $signature)) {
            return null;
        }

        $payload = json_decode(self::base64UrlDecode($payloadB64), true);
        if (!is_array($payload) || (isset($payload['exp']) && $payload['exp'] < time())) {
            return null;
        }

        return $payload;
    }

    public static function getAuthUser(): ?array {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (empty($authHeader) && function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        }

        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return self::decode($matches[1]);
        }

        return null;
    }

    private static function base64UrlEncode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
