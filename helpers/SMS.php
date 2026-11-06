<?php
// helpers/SMS.php - Ping4SMS Gateway Integration for AstroSubam
declare(strict_types=1);

require_once __DIR__ . '/Settings.php';

class SMS {
    /**
     * Test accounts bypass list (DLT bypass for testing & App Store / Play Store review)
     */
    public const TEST_NUMBERS = [
        '8000000001',
        '9000000001',
        '8000000000',
        '9000000000',
        '9876543210',
        '9876543211',
        '9876543212'
    ];
    public const STATIC_OTP = '0101';

    /**
     * Send OTP via Ping4SMS Transactional Gateway
     * 
     * @param string $phone 10-digit mobile number
     * @param string $otp 4-digit or 6-digit OTP code
     * @return array Result details
     */
    public static function sendOtp(string $phone, string $otp): array {
        // Normalize 10-digit number
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($cleanPhone) > 10 && str_starts_with($cleanPhone, '91')) {
            $cleanPhone = substr($cleanPhone, 2);
        } elseif (strlen($cleanPhone) > 10 && str_starts_with($cleanPhone, '0')) {
            $cleanPhone = substr($cleanPhone, 1);
        }

        // 1. Check for Test Number Bypass
        if (in_array($cleanPhone, self::TEST_NUMBERS, true)) {
            return [
                'success' => true,
                'bypass'  => true,
                'phone'   => $cleanPhone,
                'otp'     => self::STATIC_OTP,
                'message' => 'Test account bypass active. Use static OTP: ' . self::STATIC_OTP
            ];
        }

        // 2. Fetch Gateway Credentials from environment or database settings
        $apiKey = getenv('PING4SMS_API_KEY') ?: Settings::get('ping4sms_api_key', 'e1fc54301c0da93b95d3502a3151f420');
        $senderId = getenv('PING4SMS_SENDER_ID') ?: Settings::get('ping4sms_sender_id', 'ASTELV');
        $route = getenv('PING4SMS_ROUTE') ?: Settings::get('ping4sms_route', '4');
        $templateId = getenv('PING4SMS_TEMPLATE_ID') ?: Settings::get('ping4sms_template_id', '1677100000000387344');

        // DLT registered message template
        $message = "Dear Customer,Your OTP for login on ASTRO ELEVEN is {$otp}.Do not share it with any One. https://astroeleven.com/";

        // 3. Prepare HTTP Request to Ping4SMS API
        $query = http_build_query([
            'key'        => $apiKey,
            'route'      => $route,
            'sender'     => $senderId,
            'number'     => $cleanPhone,
            'sms'        => $message,
            'templateid' => $templateId
        ]);

        $url = "https://site.ping4sms.com/api/smsapi?" . $query;

        // Perform curl or file_get_contents
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            $err = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($err) {
                return [
                    'success' => false,
                    'error'   => 'cURL Error: ' . $err,
                    'phone'   => $cleanPhone
                ];
            }

            return [
                'success'   => ($httpCode === 200),
                'http_code' => $httpCode,
                'response'  => $response,
                'phone'     => $cleanPhone
            ];
        } else {
            $response = @file_get_contents($url);
            return [
                'success'  => ($response !== false),
                'response' => $response,
                'phone'    => $cleanPhone
            ];
        }
    }
}

