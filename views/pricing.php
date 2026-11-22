<?php
// Тарифы. Параметры: $tariffs, $subscription (или null), $quotes (id тарифа => расчёт доплаты).
$featuredIdx = count($tariffs) >= 3 ? intdiv(count($tariffs), 2) : -1;
?>
<section class="container section">
    <header class="page-head">
        <h1 class="display">Выберите тариф</h1>
        <p class="muted">Оплата через платёжную систему (ЮKassa / Robokassa).</p>
        <?php if ($subscription): ?>
            <p class="mono">Сейчас активен тариф «<?= e($subscription['tariff_name']) ?>» до <?= e(human_date($subscription['end_date'])) ?>.</p>
        <?php endif; ?>
    </header>

    <div class="tickets" data-component="pricing">
        <?php foreach ($tariffs as $i => $t):
            $perks = array_values(array_filter(array_map('trim', explode(',', (string)$t['description'])), 'strlen'));
            $own = $subscription && (int)$subscription['tariff_id'] === (int)$t['id'];
            $quote = $quotes[(int)$t['id']] ?? null;
        ?>
        <article class="ticket<?= $i === $featuredIdx ? ' ticket--hot' : '' ?>">
            <?php if ($own): ?><span class="ticket__flag">Ваш тариф</span>
            <?php elseif ($i === $featuredIdx): ?><span class="ticket__flag">Популярный</span><?php endif; ?>
            <h2 class="ticket__name"><?= e($t['name']) ?></h2>
            <p class="ticket__price"><?= e(money($t['price'])) ?> <small>/ мес</small></p>
            <?php if ($quote): ?>
                <p class="ticket__upgrade">Доплата сейчас: <strong><?= e(money($quote['surcharge'])) ?></strong> за <?= (int)$quote['days_left'] ?> <?= e(plural((int)$quote['days_left'], 'день', 'дня', 'дней')) ?> до конца срока</p>
            <?php endif; ?>
            <?php if ($perks): ?>
                <ul class="ticket__perks"><?php foreach ($perks as $perk): ?><li><?= e($perk) ?></li><?php endforeach; ?></ul>
            <?php else: ?><p class="muted"><?= e($t['description']) ?></p><?php endif; ?>
            <div class="ticket__cut" aria-hidden="true"></div>
            <?php if (Auth::check()): ?>
                <form method="post" action="/payment.php" data-pay-form data-name="<?= e($t['name']) ?>" data-price="<?= e(money($quote ? $quote['surcharge'] : $t['price'])) ?>"<?php if ($quote): ?> data-note="Доплата за <?= (int)$quote['days_left'] ?> <?= e(plural((int)$quote['days_left'], 'день', 'дня', 'дней')) ?> до <?= e(human_date($subscription['end_date'])) ?>. Дата окончания подписки не изменится."<?php endif; ?>>
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="tariff_id" value="<?= (int)$t['id'] ?>">
                    <?php if ($quote): ?><input type="hidden" name="mode" value="upgrade"><?php endif; ?>
                    <button class="btn <?= $i === $featuredIdx ? 'btn--primary' : 'btn--dark' ?> btn--block" type="submit"><?= $quote ? 'Доплатить и перейти' : 'Купить' ?></button>
                </form>
            <?php else: ?>
                <a class="btn btn--ghost btn--block" href="/login.php">Войти для покупки</a>
            <?php endif; ?>
        </article>
        <?php endforeach; ?>
    </div>

    <dialog class="dialog" id="pay-dialog" aria-labelledby="pay-title">
        <form method="dialog">
            <h2 id="pay-title" class="dialog__title">Подтвердите покупку</h2>
            <p>Тариф «<span data-pay-name></span>»</p>
            <p class="dialog__sum" data-pay-price></p>
            <p class="muted" data-pay-note>Подписка будет активирована сразу после оплаты.</p>
            <div class="row-gap">
                <button class="btn btn--ghost" value="cancel">Отмена</button>
                <button class="btn btn--primary" value="ok" data-pay-confirm>Подтвердить и оплатить</button>
            </div>
        </form>
    </dialog>
</section>
