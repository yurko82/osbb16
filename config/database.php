<?php
declare(strict_types=1);

/**
 * Підключення до бази даних через PDO (Singleton)
 */

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $config = require __DIR__ . '/config.php';
            $db = $config['db'];

            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $db['host'],
                $db['port'],
                $db['database'],
                $db['charset']
            );

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$db['charset']} COLLATE {$db['charset']}_unicode_ci",
            ];

            try {
                self::$instance = new PDO($dsn, $db['username'], $db['password'], $options);
            } catch (PDOException $e) {
                // Якщо дебаг увімкнено — показуємо детальну помилку
                if (!empty($config['app']['debug'])) {
                    die('Помилка підключення до БД: ' . htmlspecialchars($e->getMessage()));
                }
                die('Сервіс тимчасово недоступний (помилка з’єднання з базою даних). Спробуйте пізніше.');
            }
        }

        return self::$instance;
    }
}
