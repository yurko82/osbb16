<?php
declare(strict_types=1);

namespace App\Models;

use Database;
use PDO;

class User {
    public static function findById(int $id): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findByEmail(string $email): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([trim($email)]);
        return $stmt->fetch() ?: null;
    }

    public static function findByTelegramChatId(int|string $chatId): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            SELECT u.*, a.id as apartment_id, a.number as apartment_number, a.area_m2, a.account_number
            FROM users u
            LEFT JOIN residents r ON r.user_id = u.id
            LEFT JOIN apartments a ON a.id = r.apartment_id
            WHERE u.telegram_chat_id = ? AND u.is_active = 1
        ');
        $stmt->execute([$chatId]);
        return $stmt->fetch() ?: null;
    }

    public static function linkTelegram(string $token, int|string $chatId, ?string $username = null): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE telegram_token = ? AND is_active = 1');
        $stmt->execute([trim($token)]);
        $user = $stmt->fetch();

        if ($user) {
            $upd = $pdo->prepare('UPDATE users SET telegram_chat_id = ?, telegram_username = ?, telegram_token = NULL WHERE id = ?');
            $upd->execute([$chatId, $username, $user['id']]);
            return self::findById((int)$user['id']);
        }

        return null;
    }

    public static function unlinkTelegram(int $userId): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE users SET telegram_chat_id = NULL, telegram_username = NULL WHERE id = ?');
        return $stmt->execute([$userId]);
    }

    public static function all(): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->query('
            SELECT u.*, a.number as apartment_number
            FROM users u
            LEFT JOIN residents r ON r.user_id = u.id
            LEFT JOIN apartments a ON a.id = r.apartment_id
            ORDER BY u.id DESC
        ');
        return $stmt->fetchAll();
    }
}
