<?php
declare(strict_types=1);

namespace App\Bot;

use App\Models\Apartment;
use App\Models\Bill;
use App\Models\Meter;
use App\Models\News;
use App\Models\RequestModel;
use App\Models\User;
use App\Services\NbuQrService;
use App\Services\TelegramService;
use Database;

require_once __DIR__ . '/Keyboard.php';

class BotHandler {
    private array $config;
    private string $webappBaseUrl;

    public function __construct() {
        $this->config = require dirname(__DIR__) . '/config/config.php';
        $this->webappBaseUrl = rtrim($this->config['app']['url'], '/') . '/webapp';
    }

    public function handleUpdate(array $update): void {
        if (isset($update['message'])) {
            $this->handleMessage($update['message']);
        } elseif (isset($update['callback_query'])) {
            $this->handleCallbackQuery($update['callback_query']);
        }
    }

    private function handleMessage(array $message): void {
        $chatId = $message['chat']['id'];
        $text = trim($message['text'] ?? '');
        $username = $message['from']['username'] ?? null;

        // Перевіряємо прив'язку користувача
        $user = User::findByTelegramChatId($chatId);

        // 1. Команда /start або /start <token>
        if (str_starts_with($text, '/start')) {
            $parts = explode(' ', $text, 2);
            $token = trim($parts[1] ?? '');

            // Якщо токен передано — пробуємо прив'язати
            if (!empty($token)) {
                $linkedUser = User::linkTelegram($token, $chatId, $username);
                if ($linkedUser) {
                    $aptNum = $linkedUser['apartment_number'] ?? 'не призначено';
                    $reply = "🎉 <b>Вітаємо, " . htmlspecialchars($linkedUser['full_name']) . "!</b>\n\n"
                           . "Ваш Telegram успішно прив'язано до <b>квартири №{$aptNum}</b>.\n"
                           . "Тепер ви можете передавати показники лічильників, перевіряти нарахування та отримувати важливі сповіщення від ОСББ.";
                    
                    $webappUrl = "{$this->webappBaseUrl}?chat_id={$chatId}";
                    TelegramService::sendMessage($chatId, $reply, Keyboard::mainMenu($webappUrl));
                    return;
                }
            }

            // Якщо користувач уже прив'язаний (або повторний /start)
            if ($user) {
                $webappUrl = "{$this->webappBaseUrl}?chat_id={$chatId}";
                TelegramService::sendMessage(
                    $chatId,
                    "👋 <b>З поверненням, {$user['full_name']}!</b>\nКвартира №" . ($user['apartment_number'] ?? 'не призначено') . ".\nОберіть дію в меню нижче:",
                    Keyboard::mainMenu($webappUrl)
                );
                return;
            }

            // Якщо не прив'язаний і токен не підійшов
            if (!empty($token)) {
                TelegramService::sendMessage(
                    $chatId,
                    "⚠️ <b>Помилка прив'язки:</b> недійсний токен <code>" . htmlspecialchars($token) . "</code>.\n\n"
                    . "Будь ласка, увійдіть в особистий кабінет на сайті ОСББ у розділ <b>«Telegram»</b> та скористайтесь актуальним посиланням."
                );
            } else {
                TelegramService::sendMessage(
                    $chatId,
                    "👋 <b>Вітаємо у боті «" . htmlspecialchars($this->config['osbb']['name']) . "»!</b>\n\n"
                    . "Щоб користуватися ботом, будь ласка, увійдіть у свій особистий кабінет на сайті ОСББ та у розділі <b>«Telegram»</b> натисніть кнопку підключення бота."
                );
            }
            return;
        }

        // Якщо користувач не авторизований
        if (!$user) {
            TelegramService::sendMessage(
                $chatId,
                "🔒 <b>Акаунт не знайдено.</b>\nБудь ласка, авторизуйтесь через посилання з особистого кабінету на сайті ОСББ."
            );
            return;
        }

        $aptId = (int)($user['apartment_id'] ?? 0);
        $webappUrl = "{$this->webappBaseUrl}?chat_id={$chatId}";

        // 2. Пункти головного меню
        switch ($text) {
            case '💰 Мій рахунок та QR':
            case '/balance':
                $this->sendBillInfo($chatId, $user, $aptId, $webappUrl);
                break;

            case '🚰 Подати показники':
            case '/meters':
                $this->sendMetersMenu($chatId, $aptId);
                break;

            case '🛠 Мої заявки':
            case '/requests':
                $this->sendRequestsList($chatId, $aptId);
                break;

            case '📢 Новини будинку':
            case '/news':
                $this->sendNewsList($chatId);
                break;

            case '/help':
                TelegramService::sendMessage(
                    $chatId,
                    "ℹ️ <b>Команди бота ОСББ:</b>\n\n"
                    . "💰 /balance — перевірити заборгованість та отримати QR для оплати\n"
                    . "🚰 /meters — переглянути та внести показники лічильників\n"
                    . "🛠 /requests — мої заявки на ремонт\n"
                    . "📢 /news — останні оголошення ОСББ\n"
                    . "📱 Відкрити кабінет (WebApp) — повний особистий кабінет прямо в Telegram",
                    Keyboard::mainMenu($webappUrl)
                );
                break;

            default:
                // Перевіряємо, чи користувач надіслав число (показник лічильника у форматі: "код число" або просто число)
                if (preg_match('/^(\d+)\s+([\d\.\,]+)$/', $text, $matches)) {
                    $meterId = (int)$matches[1];
                    $val = (float)str_replace(',', '.', $matches[2]);
                    Meter::addReading($meterId, $val, (int)$user['id'], 'telegram');
                    TelegramService::sendMessage($chatId, "✅ <b>Показник {$val} успішно збережено!</b>", Keyboard::mainMenu($webappUrl));
                } else {
                    TelegramService::sendMessage(
                        $chatId,
                        "Оберіть пункт із меню або натисніть /help для списку команд.",
                        Keyboard::mainMenu($webappUrl)
                    );
                }
                break;
        }
    }

    private function sendBillInfo(int|string $chatId, array $user, int $aptId, string $webappUrl): void {
        $debt = Bill::getCurrentDebt($aptId);
        $latestBill = Bill::getLatestBill($aptId);
        $apartment = Apartment::findById($aptId);

        if (!$latestBill) {
            TelegramService::sendMessage($chatId, "✅ <b>Нарахувань не знайдено.</b> Заборгованість відсутня!");
            return;
        }

        $statusEmoji = ($latestBill['status'] === 'paid') ? '🟢 Оплачено' : '🔴 Не сплачено';
        $toPay = max(0.0, (float)$latestBill['total_amount'] - (float)$latestBill['paid_amount']);

        $qrData = NbuQrService::generateForBill($latestBill, $apartment, $this->config['osbb']);

        $text = "🏢 <b>Рахунок за ЖКП (кв. {$user['apartment_number']})</b>\n\n"
              . "📅 Період: <b>{$latestBill['billing_month']}/{$latestBill['billing_year']}</b>\n"
              . "🔢 Номер: <code>{$latestBill['bill_number']}</code>\n"
              . "💵 Нараховано: <b>{$latestBill['total_amount']} грн</b>\n"
              . "💳 До сплати: <b>{$toPay} грн</b>\n"
              . "📊 Статус: <b>{$statusEmoji}</b>\n\n"
              . "📌 <b>Реквізити для оплати за IBAN:</b>\n"
              . "Отримувач: {$this->config['osbb']['name']}\n"
              . "ЄДРПОУ: <code>{$this->config['osbb']['edrpou']}</code>\n"
              . "IBAN: <code>{$this->config['osbb']['iban']}</code>\n"
              . "Призначення: <code>{$qrData['purpose']}</code>\n\n"
              . "📲 <i>Ви можете відсканувати QR-код у додатку вашого банку (Приват24, Monobank):</i>\n" . $qrData['qr_url'];

        $billUrl = rtrim($this->config['app']['url'], '/') . "/bills/{$latestBill['id']}";
        TelegramService::sendMessage($chatId, $text, Keyboard::billActions($billUrl, $webappUrl));
    }

    private function sendMetersMenu(int|string $chatId, int $aptId): void {
        $meters = Meter::getMetersByApartment($aptId);

        if (empty($meters)) {
            TelegramService::sendMessage($chatId, "ℹ️ За вашою квартирою ще не закріплено жодного лічильника.");
            return;
        }

        $text = "🚰 <b>Лічильники квартири:</b>\n\n";
        foreach ($meters as $m) {
            $last = $m['last_reading'] !== null ? $m['last_reading'] : 'немає';
            $text .= "🔹 <b>{$m['meter_name']}</b>\n"
                   . "   Серійний номер: <code>{$m['serial_number']}</code>\n"
                   . "   Останній показник: <b>{$last} {$m['unit']}</b>\n\n";
        }

        $text .= "💡 <i>Щоб внести показник, надішліть ID лічильника та значення, наприклад:</i>\n<code>{$meters[0]['id']} 135.5</code>";

        TelegramService::sendMessage($chatId, $text, Keyboard::metersList($meters));
    }

    private function sendRequestsList(int|string $chatId, int $aptId): void {
        $requests = RequestModel::getForApartment($aptId);

        if (empty($requests)) {
            TelegramService::sendMessage($chatId, "🛠 <b>У вас немає активних заявок на ремонт.</b>");
            return;
        }

        $text = "🛠 <b>Ваші заявки:</b>\n\n";
        foreach (array_slice($requests, 0, 5) as $r) {
            $statusEmoji = match ($r['status']) {
                'new' => '🟡 Нова',
                'in_progress' => '🔵 В роботі',
                'done' => '🟢 Виконано',
                default => '⚪ ' . $r['status']
            };
            $text .= "• <b>{$r['title']}</b> ({$statusEmoji})\n"
                   . "  <i>" . htmlspecialchars(mb_substr($r['description'], 0, 60)) . "...</i>\n\n";
        }

        TelegramService::sendMessage($chatId, $text);
    }

    private function sendNewsList(int|string $chatId): void {
        $news = News::getPublished(3);
        if (empty($news)) {
            TelegramService::sendMessage($chatId, "📢 Наразі нових оголошень немає.");
            return;
        }

        $text = "📢 <b>Останні новини та оголошення ОСББ:</b>\n\n";
        foreach ($news as $n) {
            $text .= "📌 <b>" . htmlspecialchars($n['title']) . "</b>\n"
                   . htmlspecialchars($n['body']) . "\n"
                   . "<i>" . substr($n['created_at'], 0, 10) . "</i>\n\n";
        }

        TelegramService::sendMessage($chatId, $text);
    }

    private function handleCallbackQuery(array $callbackQuery): void {
        $chatId = $callbackQuery['message']['chat']['id'];
        $data = $callbackQuery['data'] ?? '';

        if (str_starts_with($data, 'meter_')) {
            $meterId = (int)str_replace('meter_', '', $data);
            TelegramService::sendMessage(
                $chatId,
                "📝 <b>Внесення показника:</b>\nНадішліть новий показник у відповідь наступним повідомленням у форматі:\n<code>{$meterId} ВАШ_ПОКАЗНИК</code>\n(наприклад: <code>{$meterId} 135.5</code>)"
            );
        }
    }
}
