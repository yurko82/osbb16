<?php
declare(strict_types=1);

namespace App\Models;

use Database;
use PDO;

class News {
    public static function getPublished(int $limit = 10): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            SELECT n.*, u.full_name as author_name
            FROM news n
            JOIN users u ON u.id = n.author_id
            WHERE n.is_published = 1
            ORDER BY n.is_pinned DESC, n.created_at DESC
            LIMIT ?
        ');
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function create(int $authorId, string $title, string $body, string $type = 'info', bool $isPinned = false): int {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            INSERT INTO news (author_id, title, body, type, is_pinned, is_published, created_at)
            VALUES (?, ?, ?, ?, ?, 1, NOW())
        ');
        $stmt->execute([$authorId, $title, $body, $type, $isPinned ? 1 : 0]);
        return (int)$pdo->lastInsertId();
    }
}
