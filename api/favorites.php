<?php
// Переключатель «в избранное». Ответ: {ok, favorite}.
require __DIR__ . '/../app/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['ok' => false], 405);
}
if (!Auth::check()) {
    json_out(['ok' => false, 'error' => 'auth'], 403);
}
$state = Account::toggleFavorite(Auth::id(), (int)($_POST['content_id'] ?? 0));
if ($state === null) {
    json_out(['ok' => false, 'error' => 'not_found'], 404);
}
json_out(['ok' => true, 'favorite' => $state]);
