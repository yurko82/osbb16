<?php
declare(strict_types=1);

/**
 * ============================================================
 *  Long-Polling Worker для Telegram-бота ОСББ
 *  Запуск з консолі: php bot/worker.php
 * ============================================================
 */

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/src/Core/Auth.php';
require_once dirname(__DIR__) . '/src/Models/User.php';
require_once dirname(__DIR__) . '/src/Models/Apartment.php';
require_once dirname(__DIR__) . '/src/Models/Meter.php';
require_once dirname(__DIR__) . '/src/Models/Bill.php';
require_once dirname(__DIR__) . '/src/Models/RequestModel.php';
require_once dirname(__DIR__) . '/src/Models/News.php';
require_once dirname(__DIR__) . '/src/Services/NbuQrService.php';
require_once dirname(__DIR__) . '/src/Services/TelegramService.php';
require_once __DIR__ . '/Keyboard.php';
require_once __DIR__ . '/BotHandler.php';

use App\Bot\BotHandler;
use App\Services\TelegramService;

$config = require dirname(__DIR__) . '/config/config.php';
$token = $config['telegram']['bot_token'] ?? '';

if (empty($token) || $token === 'YOUR_TELEGRAM_BOT_TOKEN_HERE') {
    echo "============================================================\n";
    echo "⚠️  УВАГА: Токен бота не налаштовано!\n";
    echo "Відкрийте файл .env та вкажіть ваш TELEGRAM_BOT_TOKEN\n";
    echo "Отримати токен можна у @BotFather в Telegram.\n";
    echo "============================================================\n";
    exit(1);
}

echo "============================================================\n";
echo "🤖 Запуск Telegram Bot Worker (Long Polling)\n";
echo "ОСББ: {$config['osbb']['name']}\n";
echo "Бот: @{$config['telegram']['bot_username']}\n";
echo "Натисніть Ctrl+C для зупинки.\n";
echo "============================================================\n\n";

$botHandler = new BotHandler();
$offset = 0;

while (true) {
    try {
        $params = [
            'offset' => $offset,
            'timeout' => 25,
            'allowed_updates' => ['message', 'callback_query']
        ];

        $updates = TelegramService::apiCall('getUpdates', $params);

        if (is_array($updates)) {
            foreach ($updates as $update) {
                $updateId = $update['update_id'];
                $offset = $updateId + 1;

                echo "[" . date('H:i:s') . "] Нове оновлення #{$updateId}\n";
                $botHandler->handleUpdate($update);
            }
        }
    } catch (\Throwable $e) {
        echo "[" . date('H:i:s') . "] ❌ Помилка: " . $e->getMessage() . "\n";
        sleep(2);
    }

    usleep(200000); // 0.2 сек пауза
}
