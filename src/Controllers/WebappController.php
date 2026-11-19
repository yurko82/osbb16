<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\Apartment;
use App\Models\Bill;
use App\Models\Meter;
use App\Models\News;
use App\Models\RequestModel;
use App\Models\User;
use App\Services\NbuQrService;

class WebappController {
    public function index(Request $request): void {
        // Якщо користувач не в сесії, перевіряємо параметр tg_token або chat_id
        $user = Auth::user();
        if (!$user) {
            $chatId = $request->get('chat_id');
            $token = $request->get('token');

            if ($token) {
                $pdo = \Database::getConnection();
                $stmt = $pdo->prepare('SELECT id FROM users WHERE telegram_token = ? AND is_active = 1');
                $stmt->execute([$token]);
                $foundId = $stmt->fetchColumn();
                if ($foundId) {
                    $_SESSION['user_id'] = (int)$foundId;
                    $user = Auth::user();
                }
            } elseif ($chatId) {
                $foundUser = User::findByTelegramChatId($chatId);
                if ($foundUser) {
                    $_SESSION['user_id'] = (int)$foundUser['id'];
                    $user = Auth::user();
                }
            }
        }

        if (!$user) {
            View::render('webapp/unauthorized', ['title' => 'ОСББ — Авторизація'], 'layouts/webapp');
            return;
        }

        $aptId = (int)($user['apartment_id'] ?? 0);
        $apartment = $aptId ? Apartment::findById($aptId) : null;
        $debt = $aptId ? Bill::getCurrentDebt($aptId) : 0.0;
        $latestBill = $aptId ? Bill::getLatestBill($aptId) : null;
        $meters = $aptId ? Meter::getMetersByApartment($aptId) : [];
        $requests = $aptId ? RequestModel::getForApartment($aptId) : [];
        $news = News::getPublished(5);

        $qrData = null;
        if ($latestBill && $latestBill['status'] !== 'paid' && $apartment) {
            $config = require dirname(__DIR__, 2) . '/config/config.php';
            $qrData = NbuQrService::generateForBill($latestBill, $apartment, $config['osbb']);
        }

        View::render('webapp/index', [
            'title' => 'МійДім ОСББ — WebApp',
            'user' => $user,
            'apartment' => $apartment,
            'debt' => $debt,
            'latestBill' => $latestBill,
            'qrData' => $qrData,
            'meters' => $meters,
            'requests' => $requests,
            'news' => $news,
        ], 'layouts/webapp');
    }
}
