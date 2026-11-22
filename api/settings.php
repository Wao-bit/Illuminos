<?php
// Обновление профиля пользователя.
require __DIR__ . '/../app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['ok' => false], 405);
if (!Auth::check()) json_out(['ok' => false, 'error' => 'auth'], 403);

$result = Account::updateProfile(Auth::id(), $_POST);
if ($result['error'] !== null) {
    json_out(['ok' => false, 'error' => $result['error'], 'field' => $result['field']], 422);
}
json_out(['ok' => true]);