<?php
declare(strict_types=1);

namespace App\Models;

use Database;
use PDO;

class Poll {
    public static function getActivePolls(int $userId): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->query('
            SELECT p.*, 
                   (SELECT COUNT(*) FROM poll_votes WHERE poll_id = p.id) as total_votes
            FROM polls p
            WHERE p.is_active = 1
            ORDER BY p.created_at DESC
        ');
        $polls = $stmt->fetchAll();

        foreach ($polls as &$poll) {
            // Отримуємо варіанти
            $optStmt = $pdo->prepare('
                SELECT po.*, 
                       (SELECT COUNT(*) FROM poll_votes WHERE poll_option_id = po.id) as votes_count
                FROM poll_options po
                WHERE po.poll_id = ?
            ');
            $optStmt->execute([$poll['id']]);
            $poll['options'] = $optStmt->fetchAll();

            // Перевіряємо чи поточний юзер голосував
            $votedStmt = $pdo->prepare('SELECT poll_option_id FROM poll_votes WHERE poll_id = ? AND user_id = ?');
            $votedStmt->execute([$poll['id'], $userId]);
            $poll['user_voted_option_id'] = $votedStmt->fetchColumn() ?: null;
        }

        return $polls;
    }

    public static function vote(int $pollId, int $optionId, int $userId): bool {
        $pdo = Database::getConnection();

        // Перевіряємо чи активне
        $chk = $pdo->prepare('SELECT id FROM polls WHERE id = ? AND is_active = 1');
        $chk->execute([$pollId]);
        if (!$chk->fetch()) {
            return false;
        }

        // Вставляємо або оновлюємо голос
        $stmt = $pdo->prepare('
            INSERT INTO poll_votes (poll_id, poll_option_id, user_id, voted_at)
            VALUES (?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE poll_option_id = VALUES(poll_option_id), voted_at = NOW()
        ');
        return $stmt->execute([$pollId, $optionId, $userId]);
    }
}
