<?php
// Вход, регистрация, выход и проверка занятости логина.
require __DIR__ . '/../app/bootstrap.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'logout') {
    Auth::signOut();
    redirect('/index.php');
}

if ($action === 'check_login') {
    $login = trim((string)($_GET['login'] ?? ''));
    if (!Auth::validLogin($login)) {
        json_out(['ok' => false]);
    }
    json_out(['ok' => true, 'available' => Auth::loginAvailable($login)]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['ok' => false, 'error' => 'Неизвестное действие.'], 405);
}

if ($action === 'register') {
    $result = Auth::register($_POST);
    if ($result['error'] !== null) {
        json_out(['ok' => false, 'error' => $result['error'], 'field' => $result['field']], 422);
    }
    json_out(['ok' => true, 'redirect' => '/index.php']);
}

if ($action === 'login') {
    $error = Auth::attempt((string)($_POST['login'] ?? ''), (string)($_POST['password'] ?? ''));
    if ($error !== null) {
        json_out(['ok' => false, 'error' => $error, 'field' => 'password'], 422);
    }
    json_out(['ok' => true, 'redirect' => '/index.php']);
}

json_out(['ok' => false, 'error' => 'Неизвестное действие.'], 400);
