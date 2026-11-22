<?php
// Тарифы и оплата. POST создаёт платёж в статусе «new» и редиректит на /checkout.php.
require __DIR__ . '/app/bootstrap.php';

$uid = Auth::id();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tariff_id'])) {
    Auth::requireLogin();

    if (!csrf_ok()) {
        flash('Страница устарела. Обновите её и повторите оплату.', 'error');
        redirect('/payment.php');
    }
    $tariff = Account::tariff((int)$_POST['tariff_id']);
    if (!$tariff) {
        flash('Тариф не найден.', 'error');
        redirect('/payment.php');
    }

    $isUpgrade = ($_POST['mode'] ?? '') === 'upgrade';
    if ($isUpgrade) {
        $sub   = Account::subscription($uid);
        $quote = $sub ? Account::upgradeQuote($sub, $tariff) : null;
        if (!$quote) {
            flash('Повышение тарифа недоступно.', 'error');
            redirect('/payment.php');
        }
        $paymentId = Account::createPendingPayment($uid, $tariff, (float)$quote['surcharge'], (int)$sub['tariff_id']);
    } else {
        $paymentId = Account::createPendingPayment($uid, $tariff, (float)$tariff['price'], null);
    }
    redirect('/checkout.php?payment=' . $paymentId);
}

$tariffs = Account::tariffs();
$sub     = $uid > 0 ? Account::subscription($uid) : null;
$quotes  = [];
foreach ($tariffs as $t) {
    if ($sub && ($q = Account::upgradeQuote($sub, $t))) {
        $quotes[(int)$t['id']] = $q;
    }
}

View::page('pricing', [
    'tariffs'      => $tariffs,
    'subscription' => $sub,
    'quotes'       => $quotes,
], ['title' => 'Оплата подписки — Illuminos', 'active' => 'pricing']);