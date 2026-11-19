<?php
declare(strict_types=1);

namespace App\Services;

use Database;
use PDO;

class BillingCalculator {

    /**
     * Генерує нарахування для всіх активних квартир за вказаний рік і місяць
     */
    public static function generateMonthlyBills(int $year, int $month): array {
        $pdo = Database::getConnection();

        // Отримуємо активні тарифи
        $tariffsStmt = $pdo->query('SELECT * FROM tariffs WHERE is_active = 1');
        $tariffs = [];
        foreach ($tariffsStmt->fetchAll() as $t) {
            $tariffs[$t['service_code']] = (float)$t['rate'];
        }

        $maintenanceRate = $tariffs['maintenance'] ?? 8.50;
        $reserveFundRate = $tariffs['reserve_fund'] ?? 0.00;

        // Отримуємо всі квартири
        $aptsStmt = $pdo->query('SELECT * FROM apartments ORDER BY number ASC');
        $apartments = $aptsStmt->fetchAll();

        $generated = 0;
        $totalSum = 0.0;

        foreach ($apartments as $apt) {
            $aptId = (int)$apt['id'];

            // Перевіряємо чи вже існує рахунок за цей місяць
            $checkStmt = $pdo->prepare('SELECT id FROM bills WHERE apartment_id = ? AND billing_year = ? AND billing_month = ?');
            $checkStmt->execute([$aptId, $year, $month]);
            if ($checkStmt->fetch()) {
                continue; // вже згенеровано
            }

            // Розрахунок утримання будинку
            $area = (float)$apt['area_m2'];
            $maintenanceAmount = round($area * $maintenanceRate, 2);
            $reserveAmount = round($area * $reserveFundRate, 2);

            // Розрахунок споживання лічильників
            $metersAmount = 0.0;
            $meterItems = [];

            // Знаходимо лічильники квартири
            $metersStmt = $pdo->prepare('
                SELECT am.id, am.serial_number, mt.code, mt.name, mt.unit, mt.default_tariff
                FROM apartment_meters am
                JOIN meter_types mt ON mt.id = am.meter_type_id
                WHERE am.apartment_id = ? AND am.is_active = 1
            ');
            $metersStmt->execute([$aptId]);
            $meters = $metersStmt->fetchAll();

            foreach ($meters as $m) {
                // Останній показник за поточний період
                $currStmt = $pdo->prepare('
                    SELECT reading_value FROM meter_readings 
                    WHERE apartment_meter_id = ? 
                      AND YEAR(reading_date) = ? AND MONTH(reading_date) = ?
                    ORDER BY reading_date DESC LIMIT 1
                ');
                $currStmt->execute([$m['id'], $year, $month]);
                $currReading = $currStmt->fetchColumn();

                if ($currReading !== false) {
                    // Попередній показник
                    $prevStmt = $pdo->prepare('
                        SELECT reading_value FROM meter_readings 
                        WHERE apartment_meter_id = ? 
                          AND (YEAR(reading_date) < ? OR (YEAR(reading_date) = ? AND MONTH(reading_date) < ?))
                        ORDER BY reading_date DESC LIMIT 1
                    ');
                    $prevStmt->execute([$m['id'], $year, $year, $month]);
                    $prevReading = $prevStmt->fetchColumn();
                    $prevVal = ($prevReading !== false) ? (float)$prevReading : 0.0;

                    $consumption = max(0.0, (float)$currReading - $prevVal);
                    $rate = $tariffs[$m['code']] ?? (float)$m['default_tariff'];
                    $cost = round($consumption * $rate, 2);

                    if ($cost > 0) {
                        $metersAmount += $cost;
                        $meterItems[] = [
                            'service_name' => $m['name'] . ' (' . $m['serial_number'] . ')',
                            'rate' => $rate,
                            'unit' => $m['unit'],
                            'quantity' => $consumption,
                            'amount' => $cost,
                        ];
                    }
                }
            }

            // Перевіряємо борг за попередній місяць
            $debtStmt = $pdo->prepare('
                SELECT (total_amount - paid_amount) as debt 
                FROM bills 
                WHERE apartment_id = ? 
                ORDER BY billing_year DESC, billing_month DESC LIMIT 1
            ');
            $debtStmt->execute([$aptId]);
            $prevDebt = (float)($debtStmt->fetchColumn() ?: 0.0);
            if ($prevDebt < 0) $prevDebt = 0.0;

            $monthTotal = $maintenanceAmount + $reserveAmount + $metersAmount;
            $billTotal = $monthTotal + $prevDebt;
            $billNumber = sprintf('BILL-%04d-%02d-%04d', $year, $month, $apt['number']);
            $dueDate = date('Y-m-d', strtotime(sprintf('%04d-%02d-10 +1 month', $year, $month)));

            // Вставляємо рахунок
            $insBill = $pdo->prepare('
                INSERT INTO bills (apartment_id, bill_number, billing_year, billing_month, maintenance_amount, meters_amount, previous_debt, total_amount, paid_amount, status, due_date)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0.00, "unpaid", ?)
            ');
            $insBill->execute([
                $aptId,
                $billNumber,
                $year,
                $month,
                $maintenanceAmount + $reserveAmount,
                $metersAmount,
                $prevDebt,
                $billTotal,
                $dueDate
            ]);
            $billId = (int)$pdo->lastInsertId();

            // Вставляємо деталізацію
            $insItem = $pdo->prepare('
                INSERT INTO bill_items (bill_id, service_name, tariff_rate, unit, quantity, total_amount)
                VALUES (?, ?, ?, ?, ?, ?)
            ');

            // Утримання будинку
            $insItem->execute([$billId, 'Утримання будинку та території (' . $area . ' м²)', $maintenanceRate, 'м²', $area, $maintenanceAmount]);

            // Резервний фонд (якщо є)
            if ($reserveAmount > 0) {
                $insItem->execute([$billId, 'Резервний фонд (' . $area . ' м²)', $reserveFundRate, 'м²', $area, $reserveAmount]);
            }

            // Лічильники
            foreach ($meterItems as $it) {
                $insItem->execute([$billId, $it['service_name'], $it['rate'], $it['unit'], $it['quantity'], $it['amount']]);
            }

            $generated++;
            $totalSum += $billTotal;
        }

        return [
            'generated_count' => $generated,
            'total_sum' => $totalSum,
            'year' => $year,
            'month' => $month,
        ];
    }
}
