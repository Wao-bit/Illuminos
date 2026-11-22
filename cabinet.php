<?php
// Личный кабинет: избранное, история, подписка, платежи.
require __DIR__ . '/app/bootstrap.php';
Auth::requireLogin();

$uid  = Auth::id();
$user = Database::one('SELECT * FROM users WHERE id = ?', [$uid]);
if (!$user) {            // пользователя удалили, а сессия осталась
    Auth::signOut();
    redirect('/login.php');
}

View::page('account', [
    'profile'      => $user,
    'favorites'    => Account::favorites($uid),
    'history'      => Account::history($uid),
    'subscription' => Account::subscription($uid),
    'payments'     => Account::payments($uid),
], ['title' => 'Личный кабинет — Illuminos']);
