<?php
declare(strict_types=1);

namespace App\Models;

use Database;
use PDO;

class Apartment {
    public static function findById(int $id): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM apartments WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findByNumber(int $number): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM apartments WHERE number = ?');
        $stmt->execute([$number]);
        return $stmt->fetch() ?: null;
    }

    public static function all(): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->query('
            SELECT a.*, 
                   GROUP_CONCAT(u.full_name SEPARATOR ", ") as residents_names,
                   (SELECT COALESCE(SUM(total_amount - paid_amount), 0) FROM bills WHERE apartment_id = a.id AND status != "paid") as current_debt
            FROM apartments a
            LEFT JOIN residents r ON r.apartment_id = a.id
            LEFT JOIN users u ON u.id = r.user_id
            GROUP BY a.id
            ORDER BY a.number ASC
        ');
        return $stmt->fetchAll();
    }

    public static function getApartmentForUser(int $userId): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            SELECT a.*, r.is_owner, r.ownership_share
            FROM apartments a
            JOIN residents r ON r.apartment_id = a.id
            WHERE r.user_id = ?
            LIMIT 1
        ');
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }
}
