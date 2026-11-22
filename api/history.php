<?php
// Очистка истории просмотра текущего пользователя.
require __DIR__ . '/../app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['ok' => false], 405);
}
if (!Auth::check()) {
    json_out(['ok' => false, 'error' => 'auth'], 403);
}
Account::clearHistory(Auth::id());
json_out(['ok' => true]);
