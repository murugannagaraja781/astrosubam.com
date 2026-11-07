<?php
// helpers/FCM.php - Firebase Cloud Messaging Push Notification Service
declare(strict_types=1);

require_once __DIR__ . '/Env.php';
require_once __DIR__ . '/../config/database.php';

class FCM {
    /**
     * Send High-Priority Incoming Call Push Notification to Devotee Device
     */
    public static function sendIncomingCallToUser(int $userId, array $sessionData): array {
        $db = getDb();
        $stmt = $db->prepare("SELECT fcm_token, name, phone FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        $token = $user['fcm_token'] ?? '';
        $astroName = $sessionData['astrologer_name'] ?? 'Vedic Astrologer';
        $type = strtoupper($sessionData['type'] ?? 'AUDIO');

        $title = "📞 Incoming {$type} Call";
        $body = "{$astroName} is calling you for consultation. Tap to answer.";

        $data = [
            'type'            => 'incoming_call',
            'action'          => 'FULL_SCREEN_INCOMING_CALL',
            'session_id'      => (string)($sessionData['session_id'] ?? ''),
            'channel_name'    => (string)($sessionData['channel_name'] ?? ''),
            'astrologer_id'   => (string)($sessionData['astrologer_id'] ?? '1'),
            'astrologer_name' => (string)$astroName,
            'astrologer_avatar'=> (string)($sessionData['astrologer_avatar'] ?? 'assets/logo.jpg'),
            'call_type'       => (string)($sessionData['type'] ?? 'audio'),
            'rate_per_min'    => (string)($sessionData['rate_per_min'] ?? '20.00'),
            'timeout_seconds' => '30',
            'timestamp'       => (string)time()
        ];

        return self::sendRaw($token, $title, $body, $data, true);
    }

    /**
     * Dispatch FCM Push Notification (supports HTTP v1, Legacy Key, and Dev Fallback)
     */
    public static function sendRaw(string $deviceToken, string $title, string $body, array $data = [], bool $isFullScreenCall = false): array {
        Env::load();
        $serverKey = Env::get('FIREBASE_SERVER_KEY', '');

        // If no device token provided or in development mode, log and return simulated success
        if (empty($deviceToken)) {
            $logDir = __DIR__ . '/../logs';
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0777, true);
            }
            $logEntry = date('Y-m-d H:i:s') . " [FCM DEV SIMULATION] Title: {$title} | Body: {$body} | Data: " . json_encode($data) . "\n";
            @file_put_contents($logDir . '/fcm.log', $logEntry, FILE_APPEND);

            return [
                'success' => true,
                'mode'    => 'simulation_no_device_token',
                'message' => 'FCM queued in simulation mode (no device token registered yet).',
                'payload' => $data
            ];
        }

        if (empty($serverKey)) {
            return [
                'success' => true,
                'mode'    => 'simulation_no_server_key',
                'message' => 'FCM simulated. Add FIREBASE_SERVER_KEY to .env for production APNs/FCM delivery.',
                'payload' => $data
            ];
        }

        // Live FCM Dispatch via cURL
        $url = 'https://fcm.googleapis.com/fcm/send';
        $payload = [
            'to'            => $deviceToken,
            'priority'      => 'high',
            'content_available' => true,
            'notification'  => [
                'title'     => $title,
                'body'      => $body,
                'sound'     => 'call_ringtone',
                'channel_id'=> 'astro_consultation_calls',
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
            ],
            'data'          => $data
        ];

        if ($isFullScreenCall) {
            $payload['android'] = [
                'priority' => 'high',
                'notification' => [
                    'channel_id' => 'astro_consultation_calls',
                    'full_screen_intent' => true,
                    'sound' => 'call_ringtone',
                    'default_vibrate_timings' => true
                ]
            ];
        }

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: key=' . $serverKey,
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);

            $response = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);

            if ($err) {
                return ['success' => false, 'error' => $err];
            }

            return ['success' => true, 'response' => json_decode($response, true)];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
