<?php
declare(strict_types=1);

namespace App\Models;

use Database;
use PDO;

class Meter {
    public static function getMetersByApartment(int $apartmentId): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            SELECT am.*, mt.code, mt.name as meter_name, mt.unit, mt.icon, mt.default_tariff,
                   (SELECT reading_value FROM meter_readings 
                    WHERE apartment_meter_id = am.id 
                    ORDER BY reading_date DESC, id DESC LIMIT 1) as last_reading,
                   (SELECT reading_date FROM meter_readings 
                    WHERE apartment_meter_id = am.id 
                    ORDER BY reading_date DESC, id DESC LIMIT 1) as last_reading_date
            FROM apartment_meters am
            JOIN meter_types mt ON mt.id = am.meter_type_id
            WHERE am.apartment_id = ? AND am.is_active = 1
            ORDER BY mt.id ASC
        ');
        $stmt->execute([$apartmentId]);
        return $stmt->fetchAll();
    }

    public static function getReadingsHistory(int $apartmentId, int $limit = 20): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            SELECT mr.*, am.serial_number, mt.name as meter_name, mt.unit, u.full_name as submitted_by_name
            FROM meter_readings mr
            JOIN apartment_meters am ON am.id = mr.apartment_meter_id
            JOIN meter_types mt ON mt.id = am.meter_type_id
            LEFT JOIN users u ON u.id = mr.submitted_by
            WHERE am.apartment_id = ?
            ORDER BY mr.reading_date DESC, mr.id DESC
            LIMIT ?
        ');
        $stmt->bindValue(1, $apartmentId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function addReading(
        int $apartmentMeterId,
        float $readingValue,
        ?int $submittedBy = null,
        string $source = 'web',
        ?string $photoUrl = null,
        ?string $notes = null
    ): int {
        $pdo = Database::getConnection();

        // Останній показник для розрахунку споживання
        $lastStmt = $pdo->prepare('
            SELECT reading_value FROM meter_readings 
            WHERE apartment_meter_id = ? 
            ORDER BY reading_date DESC, id DESC LIMIT 1
        ');
        $lastStmt->execute([$apartmentMeterId]);
        $prev = $lastStmt->fetchColumn();

        if ($prev === false) {
            $initStmt = $pdo->prepare('SELECT initial_reading FROM apartment_meters WHERE id = ?');
            $initStmt->execute([$apartmentMeterId]);
            $prev = $initStmt->fetchColumn() ?: 0.0;
        }

        $consumption = max(0.0, $readingValue - (float)$prev);

        $stmt = $pdo->prepare('
            INSERT INTO meter_readings 
            (apartment_meter_id, reading_value, consumption, reading_date, submitted_by, source, photo_url, notes)
            VALUES (?, ?, ?, CURDATE(), ?, ?, ?, ?)
        ');
        $stmt->execute([
            $apartmentMeterId,
            $readingValue,
            $consumption,
            $submittedBy,
            $source,
            $photoUrl,
            $notes
        ]);

        return (int)$pdo->lastInsertId();
    }
}
