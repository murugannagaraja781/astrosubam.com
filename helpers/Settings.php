<?php
// helpers/Settings.php - Database Settings with .env Fallback
declare(strict_types=1);

require_once __DIR__ . '/Env.php';
require_once __DIR__ . '/../config/database.php';

class Settings {
    private static array $cache = [];

    public static function get(string $key, $default = null) {
        Env::load();

        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        try {
            $db = getDb();
            $stmt = $db->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
            $stmt->execute([$key]);
            $row = $stmt->fetch();

            if ($row && !empty($row['setting_value'])) {
                self::$cache[$key] = $row['setting_value'];
                return $row['setting_value'];
            }
        } catch (Exception $e) {
            // DB table might not exist yet, fallback to .env
        }

        // Fallback to .env / getenv()
        $envKey = strtoupper($key);
        $val = Env::get($envKey, Env::get($key, $default));
        self::$cache[$key] = $val;
        return $val;
    }

    public static function set(string $key, string $value, string $description = ''): bool {
        try {
            $db = getDb();
            $stmt = $db->prepare("INSERT INTO system_settings (setting_key, setting_value, description) 
                                  VALUES (?, ?, ?) 
                                  ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
            $stmt->execute([$key, $value, $description]);

            self::$cache[$key] = $value;

            // Also synchronize with .env file
            Env::updateKey(strtoupper($key), $value);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function getAll(): array {
        Env::load();
        $settings = [
            'agora_app_id'               => self::get('agora_app_id', Env::get('AGORA_APP_ID', '')),
            'agora_app_certificate'      => self::get('agora_app_certificate', Env::get('AGORA_APP_CERTIFICATE', '')),
            'agora_token_expire_seconds' => self::get('agora_token_expire_seconds', Env::get('AGORA_TOKEN_EXPIRE_SECONDS', '3600')),
            'agora_channel_prefix'       => self::get('agora_channel_prefix', Env::get('AGORA_CHANNEL_PREFIX', 'astrosubam_')),
        ];
        return $settings;
    }
}
