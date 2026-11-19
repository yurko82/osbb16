<?php
declare(strict_types=1);

namespace App\Core;

class Response {
    public static function json(mixed $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    public static function redirect(string $url): void {
        header("Location: {$url}");
        exit;
    }

    public static function error(string $message, int $status = 400): void {
        self::json(['success' => false, 'error' => $message], $status);
    }

    public static function success(mixed $data = null, string $message = 'Успішно'): void {
        self::json(['success' => true, 'message' => $message, 'data' => $data]);
    }
}
