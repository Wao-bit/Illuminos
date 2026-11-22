<?php
// Оплата. Параметры: $payment, $tariff, $isUpgrade, $amount.
$methods = Payment::METHODS;
$banks   = Payment::banks();
?>
<section class="container checkout">
    <h1 class="display" style="margin-bottom:1.5rem">Оплата</h1>

    <div class="checkout__summary">
        <p class="mono muted">
            <?= $isUpgrade ? 'Доплата до тарифа' : 'Подписка' ?> «<?= e($tariff['name']) ?>»
        </p>
        <p class="checkout__amount"><?= e(money($amount)) ?></p>
        <?php if ($isUpgrade): ?>
            <p class="muted">Срок подписки не изменится — вы платите только разницу за оставшиеся дни.</p>
        <?php else: ?>
            <p class="muted"><?= (int)$tariff['duration_days'] ?> <?= plural((int)$tariff['duration_days'], 'день', 'дня', 'дней') ?> доступа</p>
        <?php endif; ?>
    </div>

    <form method="post" action="/checkout.php" data-component="checkout" novalidate>
        <input type="hidden" name="payment_id" value="<?= (int)$payment['id'] ?>">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

        <fieldset style="border:0;padding:0;margin:0 0 1.5rem;min-width:0">
            <legend class="field__label" style="margin-bottom:.75rem">Способ оплаты</legend>
            <div class="methods">
                <?php foreach ($methods as $key => $m): ?>
                <label class="method">
                    <input type="radio" name="method" value="<?= e($key) ?>" required<?= $key === 'card' ? ' checked' : '' ?>>
                    <span class="method__icon" aria-hidden="true"><?= $m['icon'] ?></span>
                    <span class="method__name"><?= e($m['label']) ?></span>
                    <span class="method__hint"><?= e($m['hint']) ?></span>
                </label>
                <?php endforeach; ?>
            </div>
        </fieldset>

        <!-- Карта -->
        <div class="pay-fields" data-pay-section="card">
            <label class="field">
                <span class="field__label">Номер карты</span>
                <input class="field__input" name="card_number" inputmode="numeric"
                       placeholder="0000 0000 0000 0000" autocomplete="cc-number" maxlength="23">
            </label>
            <div class="pay-fields__row">
                <label class="field">
                    <span class="field__label">Срок действия</span>
                    <input class="field__input" name="card_expiry" placeholder="ММ/ГГ"
                           inputmode="numeric" autocomplete="cc-exp" maxlength="5">
                </label>
                <label class="field">
                    <span class="field__label">CVV</span>
                    <input class="field__input" name="card_cvv" type="password" inputmode="numeric"
                           maxlength="4" placeholder="•••" autocomplete="cc-csc">
                </label>
            </div>
        </div>

        <!-- СБП -->
        <div class="pay-fields" data-pay-section="sbp" hidden>
            <label class="field">
                <span class="field__label">Телефон</span>
                <input class="field__input" name="sbp_phone" type="tel" inputmode="numeric"
                       placeholder="+7 800 555-35-35" autocomplete="tel" maxlength="18"
                       data-phone-mask>
                <span class="field__hint">Только цифры. Если начнёте с 8 или 7, они автоматически заменятся на +7.</span>
            </label>
            <label class="field">
                <span class="field__label">Банк</span>
                <select class="field__input" name="sbp_bank">
                    <option value="">— выберите банк —</option>
                    <?php foreach ($banks as $b): ?><option value="<?= e($b) ?>"><?= e($b) ?></option><?php endforeach; ?>
                </select>
            </label>
        </div>

        <!-- ЮKassa -->
        <div class="pay-fields" data-pay-section="yookassa" hidden>
            <p class="pay-fields__note">После нажатия «Оплатить» вы будете перенаправлены на защищённую страницу ЮKassa. В демо-режиме платёж проходит сразу.</p>
        </div>

        <button class="btn btn--primary btn--block" type="submit">Оплатить <?= e(money($amount)) ?></button>
    </form>
</section>