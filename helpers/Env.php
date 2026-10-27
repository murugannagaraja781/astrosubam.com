<?php
// helpers/Env.php - Lightweight .env loader
declare(strict_types=1);

class Env {
    private static bool $loaded = false;

    public static function load(string $path = ''): void {
        if (self::$loaded) return;

        if (empty($path)) {
            $path = dirname(__DIR__) . '/.env';
        }

        if (!file_exists($path)) {
            self::$loaded = true;
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }

            $parts = explode('=', $line, 2);
            if (count($parts) === 2) {
                $key = trim($parts[0]);
                $val = trim($parts[1]);
                $val = trim($val, "\"'");

                if (!array_key_exists($key, $_SERVER) && !array_key_exists($key, $_ENV)) {
                    putenv("{$key}={$val}");
                    $_ENV[$key] = $val;
                    $_SERVER[$key] = $val;
                }
            }
        }

        self::$loaded = true;
    }

    public static function get(string $key, $default = null) {
        self::load();
        $val = getenv($key);
        if ($val === false || $val === null) {
            $val = $_ENV[$key] ?? $_SERVER[$key] ?? $default;
        }
        return $val;
    }

    public static function updateKey(string $key, string $value): bool {
        $path = dirname(__DIR__) . '/.env';
        if (!file_exists($path)) {
            file_put_contents($path, "{$key}={$value}\n");
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            return true;
        }

        $content = file_get_contents($path);
        $pattern = "/^{$key}=.*/m";
        if (preg_match($pattern, $content)) {
            $newContent = preg_replace($pattern, "{$key}={$value}", $content);
        } else {
            $newContent = rtrim($content) . "\n{$key}={$value}\n";
        }

        file_put_contents($path, $newContent);
        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
        return true;
    }
}
