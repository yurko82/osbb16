<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use Database;

class AuthController {
    public function showLogin(Request $request): void {
        View::render('auth/login', [
            'title' => 'Вхід до кабінету — МійДім ОСББ',
            'error' => null
        ], 'layouts/auth');
    }

    public function login(Request $request): void {
        $csrf = $request->post('_csrf');
        if (!Csrf::validate($csrf)) {
            View::render('auth/login', [
                'title' => 'Вхід до кабінету',
                'error' => 'Помилка безпеки сесії (CSRF). Спробуйте знову.'
            ], 'layouts/auth');
            return;
        }

        $email = (string)$request->post('email');
        $password = (string)$request->post('password');

        if (Auth::attempt($email, $password)) {
            $user = Auth::user();
            if ($user['role'] === 'admin' || $user['role'] === 'accountant') {
                Response::redirect('/admin');
            } elseif ($user['role'] === 'master') {
                Response::redirect('/master');
            } else {
                Response::redirect('/dashboard');
            }
        } else {
            View::render('auth/login', [
                'title' => 'Вхід до кабінету',
                'error' => 'Невірний email або пароль'
            ], 'layouts/auth');
        }
    }

    public function logout(): void {
        Auth::logout();
        Response::redirect('/login');
    }
}
