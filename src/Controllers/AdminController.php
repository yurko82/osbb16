<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\Apartment;
use App\Models\News;
use App\Models\RequestModel;
use App\Models\User;
use App\Services\BillingCalculator;
use App\Services\TelegramService;
use Database;
use PDO;

class AdminController {
    public function dashboard(Request $request): void {
        $pdo = Database::getConnection();

        // Статистика
        $totalApts = (int)$pdo->query('SELECT COUNT(*) FROM apartments')->fetchColumn();
        $totalResidents = (int)$pdo->query('SELECT COUNT(*) FROM users WHERE role = "resident"')->fetchColumn();
        $totalDebt = (float)$pdo->query('SELECT COALESCE(SUM(total_amount - paid_amount), 0) FROM bills WHERE status != "paid"')->fetchColumn();
        $openRequests = (int)$pdo->query('SELECT COUNT(*) FROM requests WHERE status IN ("new", "in_progress")')->fetchColumn();

        $recentRequests = RequestModel::all();
        $apartments = Apartment::all();

        View::render('admin/dashboard', [
            'title' => 'Панель керування ОСББ',
            'totalApts' => $totalApts,
            'totalResidents' => $totalResidents,
            'totalDebt' => $totalDebt,
            'openRequests' => $openRequests,
            'recentRequests' => array_slice($recentRequests, 0, 5),
            'apartments' => $apartments,
            'success' => $request->get('success'),
            'error' => $request->get('error'),
        ]);
    }

    public function generateBills(Request $request): void {
        if (!Csrf::validate($request->post('_csrf'))) {
            Response::redirect('/admin?error=Помилка+безпеки+сесії');
        }

        $year = (int)$request->post('year', (int)date('Y'));
        $month = (int)$request->post('month', (int)date('n'));

        $res = BillingCalculator::generateMonthlyBills($year, $month);

        Response::redirect('/admin?success=Згенеровано+рахунків:+' . $res['generated_count'] . '+на+загальну+суму+' . number_format($res['total_sum'], 2) . '+грн.');
    }

    public function requests(Request $request): void {
        $requests = RequestModel::all();

        $pdo = Database::getConnection();
        $masters = $pdo->query('SELECT id, full_name FROM users WHERE role IN ("admin", "master")')->fetchAll();

        View::render('admin/requests', [
            'title' => 'Диспетчерська заявок',
            'requests' => $requests,
            'masters' => $masters,
            'success' => $request->get('success'),
        ]);
    }

    public function updateRequestStatus(Request $request, array $params): void {
        if (!Csrf::validate($request->post('_csrf'))) {
            Response::redirect('/admin/requests');
        }

        $id = (int)($params['id'] ?? 0);
        $status = (string)$request->post('status');
        $resolution = trim((string)$request->post('resolution'));
        $assignedTo = (int)$request->post('assigned_to') ?: null;

        RequestModel::updateStatus($id, $status, $resolution ?: null, $assignedTo);

        Response::redirect('/admin/requests?success=Статус+заявки+оновлено');
    }

    public function news(Request $request): void {
        $newsList = News::getPublished(30);

        View::render('admin/news', [
            'title' => 'Новини та сповіщення',
            'newsList' => $newsList,
            'success' => $request->get('success'),
        ]);
    }

    public function createNews(Request $request): void {
        if (!Csrf::validate($request->post('_csrf'))) {
            Response::redirect('/admin/news');
        }

        $title = trim((string)$request->post('title'));
        $body = trim((string)$request->post('body'));
        $type = (string)$request->post('type', 'info');
        $isPinned = (bool)$request->post('is_pinned');
        $broadcastTelegram = (bool)$request->post('broadcast_telegram');

        $user = Auth::user();
        News::create((int)$user['id'], $title, $body, $type, $isPinned);

        $sentCount = 0;
        if ($broadcastTelegram) {
            $sentCount = TelegramService::broadcastNews($title, $body, $type);
        }

        $msg = 'Оголошення опубліковано!';
        if ($broadcastTelegram) {
            $msg .= ' Розіслано в Telegram: ' . $sentCount . ' мешканцям.';
        }

        Response::redirect('/admin/news?success=' . urlencode($msg));
    }

    public function tariffs(Request $request): void {
        $pdo = Database::getConnection();
        $tariffs = $pdo->query('SELECT * FROM tariffs ORDER BY id ASC')->fetchAll();

        View::render('admin/tariffs', [
            'title' => 'Управління тарифами',
            'tariffs' => $tariffs,
            'success' => $request->get('success'),
        ]);
    }

    public function updateTariff(Request $request): void {
        if (!Csrf::validate($request->post('_csrf'))) {
            Response::redirect('/admin/tariffs');
        }

        $id = (int)$request->post('id');
        $rate = (float)str_replace(',', '.', (string)$request->post('rate'));

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE tariffs SET rate = ? WHERE id = ?');
        $stmt->execute([$rate, $id]);

        Response::redirect('/admin/tariffs?success=Тариф+успішно+оновлено');
    }
}
