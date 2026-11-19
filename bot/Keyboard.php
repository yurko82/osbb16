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
            [['text' => '🛠 Залишити заявку'], ['text' => '📢 Новини будинку']],
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
     * Меню керування заявками (створити + відкрити WebApp)
     */
    public static function requestsMenu(string $webappUrl = ''): array {
        $buttons = [
            [['text' => '➕ Написати нову заявку', 'callback_data' => 'new_request']]
        ];

        if (!empty($webappUrl)) {
            $buttons[] = [
                ['text' => '📱 Створити через WebApp', 'web_app' => ['url' => $webappUrl . '/requests']]
            ];
        }

        return [
            'inline_keyboard' => $buttons
        ];
    }

    /**
     * Вибір категорії заявки
     */
    public static function requestCategories(): array {
        return [
            'inline_keyboard' => [
                [
                    ['text' => '🚰 Сантехніка', 'callback_data' => 'cat_plumbing'],
                    ['text' => '⚡️ Електрика', 'callback_data' => 'cat_electric']
                ],
                [
                    ['text' => '🛗 Ліфт', 'callback_data' => 'cat_elevator'],
                    ['text' => '🚪 Домофон / Двері', 'callback_data' => 'cat_intercom']
                ],
                [
                    ['text' => '🧹 Двір / Під\'їзд', 'callback_data' => 'cat_cleaning'],
                    ['text' => '❓ Інше питання', 'callback_data' => 'cat_other']
                ],
                [
                    ['text' => '❌ Скасувати', 'callback_data' => 'cat_cancel']
                ]
            ]
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

    /**
     * Кнопка для переходу в кабінет з групи
     */
    public static function groupCabinetLink(string $botUsername, string $portalUrl = ''): array {
        $rows = [
            [['text' => '📱 Мій особистий кабінет', 'url' => "https://t.me/{$botUsername}?start=cabinet"]]
        ];
        if (!empty($portalUrl)) {
            $rows[] = [['text' => '🌐 Веб-портал будинку', 'url' => $portalUrl]];
        }
        return [
            'inline_keyboard' => $rows
        ];
    }

    /**
     * Привітальне повідомлення для нових учасників групи
     */
    public static function groupWelcomeKeyboard(string $botUsername): array {
        return [
            'inline_keyboard' => [
                [['text' => '📲 Підключити мою квартиру', 'url' => "https://t.me/{$botUsername}?start=join"]],
                [['text' => '📜 Правила будинкового чату', 'callback_data' => 'group_rules']]
            ]
        ];
    }

    /**
     * Закріплене головне меню в групі
     */
    public static function groupPinnedKeyboard(string $botUsername, string $portalUrl): array {
        return [
            'inline_keyboard' => [
                [['text' => '📱 Відкрити кабінет у боті', 'url' => "https://t.me/{$botUsername}?start=cabinet"]],
                [['text' => '🛠 Виклик майстра / Заявка', 'url' => "https://t.me/{$botUsername}?start=request"]],
                [
                    ['text' => '☎️ Аварійні контакти', 'callback_data' => 'group_contacts'],
                    ['text' => '📜 Правила чату', 'callback_data' => 'group_rules']
                ],
                [['text' => '🌐 Веб-сайт ОСББ', 'url' => $portalUrl]]
            ]
        ];
    }
}
