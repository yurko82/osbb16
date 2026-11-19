<?php
declare(strict_types=1);

namespace App\Core;

class View {
    private static string $viewsPath = '';

    public static function init(string $path): void {
        self::$viewsPath = rtrim($path, '/\\');
    }

    public static function render(string $template, array $data = [], ?string $layout = 'layouts/main'): void {
        $viewFile = self::$viewsPath . '/' . str_replace('.', '/', $template) . '.php';
        if (!file_exists($viewFile)) {
            throw new \RuntimeException("Шаблон [{$template}] не знайдено за шляхом {$viewFile}");
        }

        // Додаємо глобальні змінні
        $config = require dirname(self::$viewsPath) . '/config/config.php';
        $data['app'] = $config['app'];
        $data['osbb'] = $config['osbb'];
        $data['user'] = Auth::user();
        $data['csrf'] = Csrf::field();
        $data['csrfToken'] = Csrf::token();

        // Експортуємо змінні для шаблону
        extract($data);

        // Буферизуємо вміст шаблону
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if ($layout !== null) {
            $layoutFile = self::$viewsPath . '/' . str_replace('.', '/', $layout) . '.php';
            if (!file_exists($layoutFile)) {
                throw new \RuntimeException("Лейаут [{$layout}] не знайдено");
            }
            require $layoutFile;
        } else {
            echo $content;
        }
    }
}
