<?php
declare(strict_types=1);

/**
 * ============================================================
 *  Telegram Webhook Endpoint для продакшн сервера
 *  URL: https://domain/bot-webhook.php
 * ============================================================
 */

header('Content-Type: application/json; charset=utf-8');

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
require_once dirname(__DIR__) . '/bot/Keyboard.php';
require_once dirname(__DIR__) . '/bot/BotHandler.php';

use App\Bot\BotHandler;

$rawInput = file_get_contents('php://input');
if (empty($rawInput)) {
    echo json_encode(['status' => 'empty_input']);
    exit;
}

$update = json_decode($rawInput, true);
if (is_array($update)) {
    $handler = new BotHandler();
    $handler->handleUpdate($update);
}

echo json_encode(['ok' => true]);
