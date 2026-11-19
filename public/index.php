<?php
declare(strict_types=1);

/**
 * ============================================================
 *  МійДім ОСББ — Front Controller (public/index.php)
 * ============================================================
 */

// Для вбудованого веб-сервера PHP: повертаємо статичні файли як є
if (php_sapi_name() === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if ($path !== '/' && file_exists(__DIR__ . $path)) {
        return false;
    }
}

// Завантаження конфігурації та бази
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';

// Автозавантажувач класів (PSR-4 App\ -> src/)
spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__) . '/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\View;

// Ініціалізація
View::init(dirname(__DIR__) . '/views');
Auth::init();

$request = new Request();
$router = new Router();

// ==========================================
//  МАРШРУТИ АВТОРИЗАЦІЇ
// ==========================================
$router->get('/login', [\App\Controllers\AuthController::class, 'showLogin'], ['guest']);
$router->post('/login', [\App\Controllers\AuthController::class, 'login'], ['guest']);
$router->get('/logout', [\App\Controllers\AuthController::class, 'logout']);

// Головна -> редирект на дашборд
$router->get('/', function() {
    Response::redirect('/dashboard');
});

// ==========================================
//  МАРШРУТИ МЕШКАНЦЯ (ОСОБИСТИЙ КАБІНЕТ)
// ==========================================
$router->get('/dashboard', [\App\Controllers\ResidentController::class, 'dashboard'], ['auth']);
$router->get('/meters', [\App\Controllers\ResidentController::class, 'meters'], ['auth']);
$router->post('/meters', [\App\Controllers\ResidentController::class, 'submitMeter'], ['auth']);
$router->get('/bills', [\App\Controllers\ResidentController::class, 'bills'], ['auth']);
$router->get('/bills/{id}', [\App\Controllers\ResidentController::class, 'billDetail'], ['auth']);
$router->get('/requests', [\App\Controllers\ResidentController::class, 'requests'], ['auth']);
$router->post('/requests', [\App\Controllers\ResidentController::class, 'submitRequest'], ['auth']);
$router->post('/vote', [\App\Controllers\ResidentController::class, 'vote'], ['auth']);
$router->get('/telegram', [\App\Controllers\ResidentController::class, 'telegram'], ['auth']);
$router->post('/telegram/unlink', [\App\Controllers\ResidentController::class, 'unlinkTelegram'], ['auth']);

// ==========================================
//  МАРШРУТИ АДМІНІСТРАТОРА ТА БУХГАЛТЕРА
// ==========================================
$router->get('/admin', [\App\Controllers\AdminController::class, 'dashboard'], ['accountant']);
$router->post('/admin/bills/generate', [\App\Controllers\AdminController::class, 'generateBills'], ['accountant']);
$router->get('/admin/requests', [\App\Controllers\AdminController::class, 'requests'], ['admin']);
$router->post('/admin/requests/{id}/status', [\App\Controllers\AdminController::class, 'updateRequestStatus'], ['admin']);
$router->get('/admin/news', [\App\Controllers\AdminController::class, 'news'], ['admin']);
$router->post('/admin/news', [\App\Controllers\AdminController::class, 'createNews'], ['admin']);
$router->get('/admin/tariffs', [\App\Controllers\AdminController::class, 'tariffs'], ['accountant']);
$router->post('/admin/tariffs/update', [\App\Controllers\AdminController::class, 'updateTariff'], ['accountant']);

// ==========================================
//  МАРШРУТИ МАЙСТРА
// ==========================================
$router->get('/master', [\App\Controllers\MasterController::class, 'dashboard'], ['master']);
$router->post('/master/requests/{id}/status', [\App\Controllers\MasterController::class, 'updateStatus'], ['master']);

// ==========================================
//  TELEGRAM WEBAPP
// ==========================================
$router->get('/webapp', [\App\Controllers\WebappController::class, 'index']);

// ==========================================
//  REST API ЕНДПОІНТИ
// ==========================================
$router->get('/api/meters', [\App\Controllers\ApiController::class, 'meters']);
$router->post('/api/meters/reading', [\App\Controllers\ApiController::class, 'submitMeter']);
$router->get('/api/bills/latest', [\App\Controllers\ApiController::class, 'latestBill']);

// Запуск маршрутизації
$router->dispatch($request);
