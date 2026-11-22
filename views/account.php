<?php
// Личный кабинет. Параметры: $profile, $favorites, $history, $subscription, $payments.
$fio = trim($profile['last_name'] . ' ' . $profile['first_name'] . ' ' . $profile['patronymic']);
$initial = mb_strtoupper(mb_substr($profile['first_name'], 0, 1, 'UTF-8'), 'UTF-8');
$favSet = array_flip(array_map(fn($f) => (int)$f['id'], $favorites));
$statuses = ['paid' => 'Оплачен', 'new' => 'Ожидает оплаты', 'cancelled' => 'Отменён'];
$methods  = ['card' => 'Карта', 'sbp' => 'СБП', 'yookassa' => 'ЮKassa'];
$roleLabel = role_label($profile, $subscription);
?>
<section class="container account" data-component="tabs">
    <header class="account__head">
        <div class="account__avatar" aria-hidden="true"><?= e($initial) ?></div>
        <div>
            <h1 class="display">Личный кабинет</h1>
            <p><?= e($fio) ?> <span class="role role--<?= e($profile['role']) ?>"><?= e($roleLabel) ?></span></p>
            <p class="mono muted">Логин: <?= e($profile['login']) ?> · E-mail: <?= e($profile['email']) ?></p>
        </div>
    </header>

    <div class="tabs" role="tablist" aria-label="Разделы кабинета">
        <button class="tabs__tab" role="tab" type="button" aria-controls="panel-favorites">Избранное <span class="tabs__count" data-count-favorites><?= count($favorites) ?></span></button>
        <button class="tabs__tab" role="tab" type="button" aria-controls="panel-history">История <span class="tabs__count" data-count-history><?= count($history) ?></span></button>
        <button class="tabs__tab" role="tab" type="button" aria-controls="panel-subscription">Подписка</button>
        <button class="tabs__tab" role="tab" type="button" aria-controls="panel-payments">Платежи <span class="tabs__count"><?= count($payments) ?></span></button>
        <button class="tabs__tab" role="tab" type="button" aria-controls="panel-settings">Настройки</button>
    </div>

    <!-- Избранное -->
    <section class="tabs__panel" role="tabpanel" id="panel-favorites" aria-label="Избранное">
        <?php if ($favorites): ?>
            <div class="grid grid--4" data-favorites-grid>
                <?php foreach ($favorites as $item): ?><?php View::partial('card', ['item' => $item, 'favSet' => $favSet]); ?><?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty">
                <p>В избранном пока пусто.</p>
                <p class="muted">Нажмите на закладку у фильма или сериала, чтобы сохранить его здесь.</p>
                <a class="btn btn--primary btn--sm" href="/index.php">Выбрать что посмотреть</a>
            </div>
        <?php endif; ?>
    </section>

    <!-- История просмотра -->
    <section class="tabs__panel" role="tabpanel" id="panel-history" aria-label="История просмотра" data-history>
        <?php if ($history): ?>
            <div class="section__head">
                <h2 class="section-title">История просмотра</h2>
                <button class="link-danger" type="button" data-clear-history>Очистить историю</button>
            </div>
            <ul class="list" data-history-list>
                <?php foreach ($history as $h): ?>
                <li>
                    <a href="/<?= $h['type'] === 'serial' ? 'serial' : 'movie' ?>.php?id=<?= (int)$h['id'] ?>"><?= e($h['title']) ?></a>
                    <span class="chip"><?= e(type_label($h['type'])) ?></span>
                    <time class="mono muted" datetime="<?= e($h['watched_at']) ?>"><?= e(human_date($h['watched_at'], true)) ?></time>
                </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="empty">История пуста.</p>
        <?php endif; ?>
    </section>

    <!-- Подписка -->
    <section class="tabs__panel" role="tabpanel" id="panel-subscription" aria-label="Подписка">
        <h2 class="section-title">Подписка</h2>
        <?php if ($subscription): ?>
            <p>Тариф «<?= e($subscription['tariff_name']) ?>» активен до <?= e(human_date($subscription['end_date'])) ?>.</p>
            <div class="meter meter--wide" role="progressbar" aria-label="Оставшийся срок подписки" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= $subscription['percent'] ?>"><span style="--w:<?= $subscription['percent'] ?>%"></span></div>
            <p class="mono muted">Осталось <?= (int)$subscription['days_left'] ?> <?= plural((int)$subscription['days_left'], 'день', 'дня', 'дней') ?>.</p>
            <p><a href="/payment.php">Повысить тариф с доплатой за остаток срока</a></p>
        <?php else: ?>
            <p>Подписка не оформлена. <a href="/payment.php">Оформить →</a></p>
        <?php endif; ?>
    </section>

    <!-- Платежи -->
    <section class="tabs__panel" role="tabpanel" id="panel-payments" aria-label="Платежи">
        <h2 class="section-title">Платежи</h2>
        <?php if ($payments): ?>
            <ul class="list">
                <?php foreach ($payments as $p): ?>
                <li>
                    <span>
                        <?= (float)$p['amount'] < (float)$p['tariff_price'] ? 'Доплата до тарифа' : 'Тариф' ?> «<?= e($p['tariff_name']) ?>»
                        <?php if (!empty($p['payment_method']) && isset($methods[$p['payment_method']])): ?>
                            <span class="chip"><?= e($methods[$p['payment_method']]) ?></span>
                        <?php endif; ?>
                    </span>
                    <time class="mono muted"><?= e(human_date($p['created_at'], true)) ?></time>
                    <span class="list__end"><?= e(money($p['amount'])) ?> <span class="status status--<?= e($p['status']) ?>"><?= e($statuses[$p['status']] ?? $p['status']) ?></span></span>
                </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?><p class="empty">Платежей пока нет.</p><?php endif; ?>
    </section>

    <!-- Настройки -->
    <section class="tabs__panel" role="tabpanel" id="panel-settings" aria-label="Настройки">
        <h2 class="section-title">Настройки профиля</h2>
        <?php View::partial('settings-form', ['profile' => $profile]); ?>
    </section>
</section>
