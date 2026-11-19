<?php
declare(strict_types=1);

namespace App\Models;

use Database;
use PDO;

class RequestModel {
    public static function findById(int $id): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            SELECT r.*, a.number as apartment_number, u.full_name as author_name, u.phone as author_phone,
                   m.full_name as master_name
            FROM requests r
            JOIN apartments a ON a.id = r.apartment_id
            JOIN users u ON u.id = r.user_id
            LEFT JOIN users m ON m.id = r.assigned_to
            WHERE r.id = ?
        ');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function getForApartment(int $apartmentId): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            SELECT r.*, m.full_name as master_name
            FROM requests r
            LEFT JOIN users m ON m.id = r.assigned_to
            WHERE r.apartment_id = ?
            ORDER BY r.created_at DESC
        ');
        $stmt->execute([$apartmentId]);
        return $stmt->fetchAll();
    }

    public static function all(): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->query('
            SELECT r.*, a.number as apartment_number, u.full_name as author_name, u.phone as author_phone,
                   m.full_name as master_name
            FROM requests r
            JOIN apartments a ON a.id = r.apartment_id
            JOIN users u ON u.id = r.user_id
            LEFT JOIN users m ON m.id = r.assigned_to
            ORDER BY CASE r.priority WHEN "urgent" THEN 1 WHEN "normal" THEN 2 ELSE 3 END, r.created_at DESC
        ');
        return $stmt->fetchAll();
    }

    public static function create(
        int $apartmentId,
        int $userId,
        string $category,
        string $title,
        string $description,
        string $priority = 'normal',
        ?string $photoUrl = null
    ): int {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            INSERT INTO requests (apartment_id, user_id, category, title, description, priority, status, photo_url, created_at)
            VALUES (?, ?, ?, ?, ?, ?, "new", ?, NOW())
        ');
        $stmt->execute([$apartmentId, $userId, $category, $title, $description, $priority, $photoUrl]);
        return (int)$pdo->lastInsertId();
    }

    public static function updateStatus(int $id, string $status, ?string $resolution = null, ?int $assignedTo = null): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            UPDATE requests 
            SET status = ?, resolution = COALESCE(?, resolution), assigned_to = COALESCE(?, assigned_to)
            WHERE id = ?
        ');
        return $stmt->execute([$status, $resolution, $assignedTo, $id]);
    }
}
