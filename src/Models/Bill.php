<?php
declare(strict_types=1);

namespace App\Models;

use Database;
use PDO;

class Bill {
    public static function findById(int $id): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            SELECT b.*, a.number as apartment_number, a.area_m2, a.account_number
            FROM bills b
            JOIN apartments a ON a.id = b.apartment_id
            WHERE b.id = ?
        ');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function getBillItems(int $billId): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM bill_items WHERE bill_id = ? ORDER BY id ASC');
        $stmt->execute([$billId]);
        return $stmt->fetchAll();
    }

    public static function getBillsForApartment(int $apartmentId, int $limit = 12): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            SELECT * FROM bills 
            WHERE apartment_id = ? 
            ORDER BY billing_year DESC, billing_month DESC 
            LIMIT ?
        ');
        $stmt->bindValue(1, $apartmentId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function getCurrentDebt(int $apartmentId): float {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            SELECT COALESCE(SUM(total_amount - paid_amount), 0) as debt
            FROM bills
            WHERE apartment_id = ? AND status != "paid"
        ');
        $stmt->execute([$apartmentId]);
        return (float)$stmt->fetchColumn();
    }

    public static function getLatestBill(int $apartmentId): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            SELECT * FROM bills 
            WHERE apartment_id = ? 
            ORDER BY billing_year DESC, billing_month DESC 
            LIMIT 1
        ');
        $stmt->execute([$apartmentId]);
        return $stmt->fetch() ?: null;
    }

    public static function recordPayment(int $apartmentId, float $amount, ?int $billId, ?int $userId, string $method, ?string $ref = null): int {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            INSERT INTO payments (bill_id, apartment_id, user_id, amount, payment_method, transaction_ref, status, payment_date)
            VALUES (?, ?, ?, ?, ?, ?, "confirmed", NOW())
        ');
        $stmt->execute([$billId, $apartmentId, $userId, $amount, $method, $ref]);
        $paymentId = (int)$pdo->lastInsertId();

        // Якщо вказано конкретний рахунок — оновлюємо paid_amount
        if ($billId) {
            $bill = self::findById($billId);
            if ($bill) {
                $newPaid = (float)$bill['paid_amount'] + $amount;
                $status = ($newPaid >= (float)$bill['total_amount']) ? 'paid' : 'partial';
                $upd = $pdo->prepare('UPDATE bills SET paid_amount = ?, status = ? WHERE id = ?');
                $upd->execute([$newPaid, $status, $billId]);
            }
        }

        return $paymentId;
    }
}
