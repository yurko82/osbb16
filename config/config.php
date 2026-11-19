<?php
declare(strict_types=1);

/**
 * Завантажувач конфігурації та .env файлу
 */

if (!function_exists('loadEnv')) {
    function loadEnv(string $path): void {
        if (!file_exists($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (str_contains($line, '=')) {
                [$name, $value] = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);

                // Очищення лапок
                if (
                    (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                    (str_starts_with($value, "'") && str_ends_with($value, "'"))
                ) {
                    $value = substr($value, 1, -1);
                }

                if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                    putenv(sprintf('%s=%s', $name, $value));
                    $_ENV[$name] = $value;
                    $_SERVER[$name] = $value;
                }
            }
        }
    }
}

// Завантажуємо .env з кореня проєкту
loadEnv(dirname(__DIR__) . '/.env');

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed {
        $val = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        if ($val === false || $val === null) {
            return $default;
        }
        $lower = strtolower((string)$val);
        if ($lower === 'true' || $lower === '(true)') return true;
        if ($lower === 'false' || $lower === '(false)') return false;
        if ($lower === 'empty' || $lower === '(empty)') return '';
        if ($lower === 'null' || $lower === '(null)') return null;
        return $val;
    }
}

return [
    'app' => [
        'name' => env('APP_NAME', 'МійДім ОСББ'),
        'env' => env('APP_ENV', 'production'),
        'debug' => env('APP_DEBUG', false),
        'url' => env('APP_URL', 'http://localhost:8000'),
        'secret' => env('APP_SECRET', 'default_secret_please_change'),
    ],
    'db' => [
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => (int)env('DB_PORT', 3306),
        'database' => env('DB_DATABASE', 'osbb_db'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => env('DB_CHARSET', 'utf8mb4'),
    ],
    'osbb' => [
        'name' => env('OSBB_NAME', 'ОСББ Затишний Дім'),
        'edrpou' => env('OSBB_EDRPOU', '38947211'),
        'iban' => env('OSBB_IBAN', 'UA213223130000026007233566123'),
        'bank' => env('OSBB_BANK', 'АТ Укрексімбанк'),
        'phone' => env('OSBB_PHONE', '+38 (050) 111-22-33'),
        'email' => env('OSBB_EMAIL', 'info@mydom.ua'),
        'address' => env('OSBB_ADDRESS', 'м. Київ, вул. Героїв Дніпра, 42'),
    ],
    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN', ''),
        'bot_username' => env('TELEGRAM_BOT_USERNAME', 'MyDomOsbbBot'),
        'webhook_url' => env('TELEGRAM_WEBHOOK_URL', ''),
    ],
];
