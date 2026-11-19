<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\RequestModel;
use Database;

class MasterController {
    public function dashboard(Request $request): void {
        $user = Auth::user();
        $allRequests = RequestModel::all();

        // Фільтруємо заявки: призначені мені або нові без призначення
        $myRequests = array_filter($allRequests, function($r) use ($user) {
            return (int)$r['assigned_to'] === (int)$user['id'] || ($r['status'] === 'new' && empty($r['assigned_to']));
        });

        View::render('master/dashboard', [
            'title' => 'Кабінет майстра',
            'requests' => $myRequests,
            'success' => $request->get('success'),
        ]);
    }

    public function updateStatus(Request $request, array $params): void {
        if (!Csrf::validate($request->post('_csrf'))) {
            Response::redirect('/master');
        }

        $id = (int)($params['id'] ?? 0);
        $status = (string)$request->post('status');
        $resolution = trim((string)$request->post('resolution'));
        $user = Auth::user();

        RequestModel::updateStatus($id, $status, $resolution ?: null, (int)$user['id']);

        Response::redirect('/master?success=Статус+заявки+успішно+змінено');
    }
}
