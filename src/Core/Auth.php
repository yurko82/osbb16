<?php
declare(strict_types=1);

namespace App\Core;

use Database;
use PDO;

class Auth {
    public static function init(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function check(): bool {
        self::init();
        return !empty($_SESSION['user_id']);
    }

    public static function user(): ?array {
        self::init();
        if (!self::check()) {
            return null;
        }

        static $cachedUser = null;
        if ($cachedUser !== null) {
            return $cachedUser;
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('
            SELECT u.*, r.apartment_id, a.number as apartment_number, a.area_m2, a.account_number
            FROM users u
            LEFT JOIN residents r ON r.user_id = u.id
            LEFT JOIN apartments a ON a.id = r.apartment_id
            WHERE u.id = ? AND u.is_active = 1
        ');
        $stmt->execute([$_SESSION['user_id']]);
        $cachedUser = $stmt->fetch();
        return $cachedUser ?: null;
    }

    public static function id(): ?int {
        self::init();
        return $_SESSION['user_id'] ?? null;
    }

    public static function role(): ?string {
        $u = self::user();
        return $u['role'] ?? null;
    }

    public static function hasRole(string ...$roles): bool {
        $r = self::role();
        return $r !== null && in_array($r, $roles, true);
    }

    public static function isAdmin(): bool {
        return self::hasRole('admin');
    }

    public static function isAccountant(): bool {
        return self::hasRole('admin', 'accountant');
    }

    public static function isMaster(): bool {
        return self::hasRole('admin', 'master');
    }

    public static function isResident(): bool {
        return self::hasRole('resident');
    }

    public static function attempt(string $email, string $password): bool {
        self::init();
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? AND is_active = 1');
        $stmt->execute([trim($email)]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['role'] = $user['role'];

            // Оновлюємо last_login
            $upd = $pdo->prepare('UPDATE users SET last_login = NOW() WHERE id = ?');
            $upd->execute([$user['id']]);
            return true;
        }

        return false;
    }

    public static function logout(): void {
        self::init();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
    }
}
