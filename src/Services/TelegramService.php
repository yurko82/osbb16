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
    public static function apiCall(string $method, array $params = []): ?array {
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

        if ($response && $httpCode === 200) {
            $data = json_decode($response, true);
            return $data['ok'] ? $data['result'] : null;
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
        return self::apiCall('sendMessage', $params);
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
     * Розсилка важливого оголошення всім підключеним мешканцям
     */
    public static function broadcastNews(string $title, string $body, string $type = 'info'): int {
        $pdo = Database::getConnection();
        $stmt = $pdo->query('SELECT telegram_chat_id FROM users WHERE telegram_chat_id IS NOT NULL AND is_active = 1');
        $users = $stmt->fetchAll();

        $emoji = match ($type) {
            'emergency' => '🚨 <b>ТЕРМІНОВО!</b>',
            'warning' => '⚠️ <b>УВАГА!</b>',
            default => '📢 <b>Оголошення ОСББ</b>',
        };

        $text = "{$emoji}\n\n<b>" . htmlspecialchars($title) . "</b>\n\n" . htmlspecialchars($body);

        $sent = 0;
        foreach ($users as $u) {
            if (!empty($u['telegram_chat_id'])) {
                self::sendMessage($u['telegram_chat_id'], $text);
                $sent++;
            }
        }
        return $sent;
    }
}
