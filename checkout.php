<?php
// Страница оплаты: выбор способа, реквизиты, подтверждение.
require __DIR__ . '/app/bootstrap.php';
Auth::requireLogin();

$uid = Auth::id();
$paymentId = (int)($_GET['payment'] ?? $_POST['payment_id'] ?? 0);
$payment = Account::findPayment($paymentId, $uid);

if (!$payment) {
    flash('Платёж не найден.', 'error');
    redirect('/payment.php');
}
if ($payment['status'] !== 'new') {
    flash('Этот платёж уже обработан.', 'error');
    redirect('/cabinet.php');
}

$tariff    = Account::tariff((int)$payment['tariff_id']);
$isUpgrade = $payment['old_tariff_id'] !== null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_ok()) {
        flash('Страница устарела. Обновите её и повторите.', 'error');
        redirect('/checkout.php?payment=' . $paymentId);
    }
    $method = (string)($_POST['method'] ?? '');
    $v = Payment::validateDetails($method, $_POST);
    if ($v['error']) {
        flash($v['error'], 'error');
        redirect('/checkout.php?payment=' . $paymentId);
    }
    $res = Account::confirmPayment((int)$payment['id'], $method);
    if (!$res['ok']) {
        flash($res['error'], 'error');
        redirect('/checkout.php?payment=' . $paymentId);
    }
    flash(
        $isUpgrade ? 'Тариф изменён. Приятного просмотра!' : 'Оплата прошла успешно. Приятного просмотра!',
        'success'
    );
    redirect('/cabinet.php');
}

View::page('checkout', [
    'payment'   => $payment,
    'tariff'    => $tariff,
    'isUpgrade' => $isUpgrade,
    'amount'    => (float)$payment['amount'],
], ['title' => 'Оплата — Illuminos']);