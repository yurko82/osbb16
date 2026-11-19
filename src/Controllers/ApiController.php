<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Models\Apartment;
use App\Models\Bill;
use App\Models\Meter;
use App\Models\RequestModel;
use App\Models\User;
use App\Services\NbuQrService;

class ApiController {
    private ?array $user = null;

    private function authenticate(Request $request): bool {
        // Перевіряємо сесію або Bearer token
        if (Auth::check()) {
            $this->user = Auth::user();
            return true;
        }

        $token = $request->bearerToken();
        if ($token) {
            $pdo = \Database::getConnection();
            $stmt = $pdo->prepare('SELECT * FROM users WHERE telegram_token = ? AND is_active = 1');
            $stmt->execute([$token]);
            $this->user = $stmt->fetch() ?: null;
            return $this->user !== null;
        }

        return false;
    }

    public function meters(Request $request): void {
        if (!$this->authenticate($request)) {
            Response::error('Неавторизовано', 401);
        }

        $aptId = (int)($this->user['apartment_id'] ?? 0);
        $meters = $aptId ? Meter::getMetersByApartment($aptId) : [];
        Response::success($meters);
    }

    public function submitMeter(Request $request): void {
        if (!$this->authenticate($request)) {
            Response::error('Неавторизовано', 401);
        }

        $meterId = (int)$request->input('apartment_meter_id');
        $value = (float)$request->input('reading_value');
        $source = (string)$request->input('source', 'api');

        if ($meterId <= 0 || $value <= 0) {
            Response::error('Некоректний показник', 422);
        }

        $id = Meter::addReading($meterId, $value, (int)$this->user['id'], $source);
        Response::success(['reading_id' => $id], 'Показник успішно зафіксовано');
    }

    public function latestBill(Request $request): void {
        if (!$this->authenticate($request)) {
            Response::error('Неавторизовано', 401);
        }

        $aptId = (int)($this->user['apartment_id'] ?? 0);
        $bill = $aptId ? Bill::getLatestBill($aptId) : null;
        if (!$bill) {
            Response::error('Рахунок не знайдено', 404);
        }

        $apartment = Apartment::findById($aptId);
        $config = require dirname(__DIR__, 2) . '/config/config.php';
        $qr = NbuQrService::generateForBill($bill, $apartment, $config['osbb']);

        Response::success([
            'bill' => $bill,
            'qr' => $qr
        ]);
    }
}
