<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\Apartment;
use App\Models\Bill;
use App\Models\Meter;
use App\Models\News;
use App\Models\Poll;
use App\Models\RequestModel;
use App\Services\NbuQrService;
use App\Services\TelegramService;

class ResidentController {
    private ?array $user;
    private ?array $apartment;

    public function __construct() {
        $this->user = Auth::user();
        if ($this->user && !empty($this->user['apartment_id'])) {
            $this->apartment = Apartment::findById((int)$this->user['apartment_id']);
        } else {
            $this->apartment = null;
        }
    }

    public function dashboard(Request $request): void {
        $aptId = (int)($this->apartment['id'] ?? 0);

        $debt = $aptId ? Bill::getCurrentDebt($aptId) : 0.0;
        $latestBill = $aptId ? Bill::getLatestBill($aptId) : null;
        $meters = $aptId ? Meter::getMetersByApartment($aptId) : [];
        $recentRequests = $aptId ? array_slice(RequestModel::getForApartment($aptId), 0, 3) : [];
        $news = News::getPublished(4);
        $polls = Poll::getActivePolls((int)$this->user['id']);

        // QR для останнього неоплаченого рахунку
        $qrData = null;
        if ($latestBill && ($latestBill['status'] !== 'paid')) {
            $config = require dirname(__DIR__, 2) . '/config/config.php';
            $qrData = NbuQrService::generateForBill($latestBill, $this->apartment, $config['osbb']);
        }

        View::render('resident/dashboard', [
            'title' => 'Особистий кабінет — МійДім ОСББ',
            'apartment' => $this->apartment,
            'debt' => $debt,
            'latestBill' => $latestBill,
            'qrData' => $qrData,
            'meters' => $meters,
            'recentRequests' => $recentRequests,
            'news' => $news,
            'polls' => $polls,
        ]);
    }

    public function meters(Request $request): void {
        $aptId = (int)($this->apartment['id'] ?? 0);
        $meters = $aptId ? Meter::getMetersByApartment($aptId) : [];
        $history = $aptId ? Meter::getReadingsHistory($aptId, 25) : [];

        View::render('resident/meters', [
            'title' => 'Показники лічильників',
            'apartment' => $this->apartment,
            'meters' => $meters,
            'history' => $history,
            'success' => $request->get('success'),
            'error' => $request->get('error'),
        ]);
    }

    public function submitMeter(Request $request): void {
        if (!Csrf::validate($request->post('_csrf'))) {
            Response::redirect('/meters?error=Помилка+безпеки+сесії');
        }

        $meterId = (int)$request->post('apartment_meter_id');
        $value = (float)str_replace(',', '.', (string)$request->post('reading_value'));
        $notes = trim((string)$request->post('notes'));

        if ($meterId <= 0 || $value <= 0) {
            Response::redirect('/meters?error=Введіть+коректне+значення+показника');
        }

        Meter::addReading(
            $meterId,
            $value,
            (int)$this->user['id'],
            'web',
            null,
            $notes ?: null
        );

        Response::redirect('/meters?success=Показник+успішно+збережено!');
    }

    public function bills(Request $request): void {
        $aptId = (int)($this->apartment['id'] ?? 0);
        $bills = $aptId ? Bill::getBillsForApartment($aptId, 24) : [];
        $debt = $aptId ? Bill::getCurrentDebt($aptId) : 0.0;

        View::render('resident/bills', [
            'title' => 'Рахунки та оплата',
            'apartment' => $this->apartment,
            'bills' => $bills,
            'debt' => $debt,
        ]);
    }

    public function billDetail(Request $request, array $params): void {
        $billId = (int)($params['id'] ?? 0);
        $bill = Bill::findById($billId);

        if (!$bill || ((int)$bill['apartment_id'] !== (int)($this->apartment['id'] ?? 0) && !Auth::isAdmin())) {
            Response::redirect('/bills');
        }

        $items = Bill::getBillItems($billId);
        $config = require dirname(__DIR__, 2) . '/config/config.php';
        $qrData = NbuQrService::generateForBill($bill, $this->apartment, $config['osbb']);

        View::render('resident/bill_detail', [
            'title' => 'Рахунок № ' . $bill['bill_number'],
            'bill' => $bill,
            'items' => $items,
            'qrData' => $qrData,
            'apartment' => $this->apartment,
            'osbb' => $config['osbb'],
        ]);
    }

    public function requests(Request $request): void {
        $aptId = (int)($this->apartment['id'] ?? 0);
        $requests = $aptId ? RequestModel::getForApartment($aptId) : [];

        View::render('resident/requests', [
            'title' => 'Заявки на ремонт та звернення',
            'requests' => $requests,
            'apartment' => $this->apartment,
            'success' => $request->get('success'),
            'error' => $request->get('error'),
        ]);
    }

    public function submitRequest(Request $request): void {
        if (!Csrf::validate($request->post('_csrf'))) {
            Response::redirect('/requests?error=Помилка+безпеки+сесії');
        }

        $aptId = (int)($this->apartment['id'] ?? 0);
        $category = (string)$request->post('category', 'other');
        $title = trim((string)$request->post('title'));
        $description = trim((string)$request->post('description'));
        $priority = (string)$request->post('priority', 'normal');

        if (empty($title) || empty($description)) {
            Response::redirect('/requests?error=Будь+ласка,+заповніть+тему+та+опис+заявки');
        }

        RequestModel::create($aptId, (int)$this->user['id'], $category, $title, $description, $priority);

        Response::redirect('/requests?success=Заявку+успішно+створено!+Майстер+отримає+сповіщення.');
    }

    public function vote(Request $request): void {
        if (!Csrf::validate($request->post('_csrf'))) {
            Response::redirect('/dashboard?error=Помилка+сесії');
        }

        $pollId = (int)$request->post('poll_id');
        $optionId = (int)$request->post('poll_option_id');

        if ($pollId > 0 && $optionId > 0) {
            Poll::vote($pollId, $optionId, (int)$this->user['id']);
        }

        Response::redirect('/dashboard#polls');
    }

    public function telegram(Request $request): void {
        $deepLink = TelegramService::getBotDeepLink((int)$this->user['id']);
        $qrUrl = NbuQrService::getQrImageUrl($deepLink, 240);

        View::render('resident/telegram', [
            'title' => 'Підключення Telegram',
            'deepLink' => $deepLink,
            'qrUrl' => $qrUrl,
            'isLinked' => !empty($this->user['telegram_chat_id']),
            'telegramUsername' => $this->user['telegram_username'] ?? null,
            'success' => $request->get('success'),
        ]);
    }

    public function unlinkTelegram(Request $request): void {
        if (!Csrf::validate($request->post('_csrf'))) {
            Response::redirect('/telegram');
        }

        \App\Models\User::unlinkTelegram((int)$this->user['id']);
        Response::redirect('/telegram?success=Telegram+відключено');
    }
}
