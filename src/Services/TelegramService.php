<?php
declare(strict_types=1);

namespace App\Services;

use Database;
use PDO;

class TelegramService {
    private static ?string $token = null;

    private static function getToken(): string {
        if (self::$token === null) {
            $config = require dirname(__DIR__, 2) . '/config/config.php';
            self::$token = (string)($config['telegram']['bot_token'] ?? '');
        }
        return self::$token;
    }

    public static function isConfigured(): bool {
        $token = self::getToken();
        return !empty($token) && $token !== 'YOUR_TELEGRAM_BOT_TOKEN_HERE';
    }

    /**
     * Відправка запиту до Telegram Bot API
     */
    public static function apiCall(string $method, array $params = []): mixed {
        if (!self::isConfigured()) {
            return null;
        }

        $url = "https://api.telegram.org/bot" . self::getToken() . "/{$method}";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response && ($httpCode === 200 || $httpCode === 201)) {
            $data = json_decode($response, true);
            if (is_array($data) && !empty($data['ok'])) {
                return $data['result'] ?? true;
            }
        }

        return null;
    }

    public static function sendMessage(int|string $chatId, string $text, ?array $replyMarkup = null): ?array {
        $params = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ];
        if ($replyMarkup) {
            $params['reply_markup'] = $replyMarkup;
        }
        $res = self::apiCall('sendMessage', $params);
        return is_array($res) ? $res : null;
    }

    public static function pinChatMessage(int|string $chatId, int $messageId, bool $disableNotification = false): bool {
        $res = self::apiCall('pinChatMessage', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'disable_notification' => $disableNotification,
        ]);
        return $res === true || is_array($res);
    }

    public static function deleteMessage(int|string $chatId, int $messageId): bool {
        $res = self::apiCall('deleteMessage', [
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ]);
        return $res === true || is_array($res);
    }

    public static function answerCallbackQuery(string $callbackQueryId, string $text = '', bool $showAlert = false): bool {
        $res = self::apiCall('answerCallbackQuery', [
            'callback_query_id' => $callbackQueryId,
            'text' => $text,
            'show_alert' => $showAlert,
        ]);
        return $res === true || is_array($res);
    }

    /**
     * Створює одноразовий deep-link токен для підключення акаунта
     */
    public static function generateLinkToken(int $userId): string {
        $token = bin2hex(random_bytes(16));
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE users SET telegram_token = ? WHERE id = ?');
        $stmt->execute([$token, $userId]);
        return $token;
    }

    /**
     * Повертає посилання на бота з deep-link для прив'язки
     */
    public static function getBotDeepLink(int $userId): string {
        $config = require dirname(__DIR__, 2) . '/config/config.php';
        $botUsername = $config['telegram']['bot_username'] ?? 'MyDomOsbbBot';

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT telegram_token FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $token = $stmt->fetchColumn();

        if (empty($token)) {
            $token = self::generateLinkToken($userId);
        }

        return "https://t.me/{$botUsername}?start={$token}";
    }

    /**
     * Розсилка важливого оголошення в групу будинку та всім підключеним мешканцям
     */
    public static function broadcastNews(string $title, string $body, string $type = 'info'): int {
        $pdo = Database::getConnection();

        $emoji = match ($type) {
            'emergency' => '🚨 <b>ТЕРМІНОВО!</b>',
            'warning' => '⚠️ <b>УВАГА!</b>',
            default => '📢 <b>Оголошення ОСББ</b>',
        };

        $text = "{$emoji}\n\n<b>" . htmlspecialchars($title) . "</b>\n\n" . htmlspecialchars($body);

        $sent = 0;

        // 1. Надсилаємо в офіційну групу будинку (якщо бот доданий у групу)
        $stmtB = $pdo->query('SELECT telegram_chat_id FROM buildings WHERE id = 1');
        $groupChatId = $stmtB->fetchColumn();
        if (!empty($groupChatId)) {
            $config = require dirname(__DIR__, 2) . '/config/config.php';
            $botUsername = $config['telegram']['bot_username'] ?? 'OSBB_peremogi_16_bot';
            $portalUrl = rtrim($config['app']['url'] ?? '', '/');
            $groupMsg = self::sendMessage($groupChatId, $text, \App\Bot\Keyboard::groupCabinetLink($botUsername, $portalUrl));
            if ($groupMsg && $type === 'emergency' && !empty($groupMsg['message_id'])) {
                self::pinChatMessage($groupChatId, (int)$groupMsg['message_id']);
            }
            $sent++;
        }

        // 2. Персональна розсилка всім підключеним мешканцям
        $stmt = $pdo->query('SELECT telegram_chat_id FROM users WHERE telegram_chat_id IS NOT NULL AND is_active = 1');
        $users = $stmt->fetchAll();

        foreach ($users as $u) {
            if (!empty($u['telegram_chat_id'])) {
                self::sendMessage($u['telegram_chat_id'], $text);
                $sent++;
            }
        }
        return $sent;
    }
}
