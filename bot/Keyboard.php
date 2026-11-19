<?php
declare(strict_types=1);

namespace App\Bot;

class Keyboard {
    /**
     * Головне меню з кнопками дій та кнопкою WebApp
     */
    public static function mainMenu(string $webappUrl = ''): array {
        $keyboard = [
            [['text' => '💰 Мій рахунок та QR'], ['text' => '🚰 Подати показники']],
            [['text' => '🛠 Мої заявки'], ['text' => '📢 Новини будинку']],
        ];

        if (!empty($webappUrl)) {
            $keyboard[] = [
                ['text' => '📱 Відкрити кабінет (WebApp)', 'web_app' => ['url' => $webappUrl]]
            ];
        }

        return [
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'persistent' => true
        ];
    }

    /**
     * Інлайн клавіатура для оплати та дій
     */
    public static function billActions(string $billUrl = '', string $webappUrl = ''): array {
        $buttons = [];
        if (!empty($billUrl)) {
            $buttons[] = ['text' => '📄 Детальна квитанція', 'url' => $billUrl];
        }
        if (!empty($webappUrl)) {
            $buttons[] = ['text' => '📱 Відкрити WebApp', 'web_app' => ['url' => $webappUrl]];
        }
        return [
            'inline_keyboard' => [$buttons]
        ];
    }

    /**
     * Кнопки вибору лічильника для внесення показників
     */
    public static function metersList(array $meters): array {
        $buttons = [];
        foreach ($meters as $m) {
            $buttons[] = [
                ['text' => "{$m['meter_name']} ({$m['serial_number']})", 'callback_data' => "meter_{$m['id']}"]
            ];
        }
        return [
            'inline_keyboard' => $buttons
        ];
    }
}
