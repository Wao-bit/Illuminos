<?php
// Страница персоны. Параметры: $person, $directed, $acted, $favSet.
$total = count($directed) + count($acted);
$types = [];
if ($directed) $types[] = 'режиссёр';
if ($acted)    $types[] = 'актёр';
?>
<section class="container person-page">
    <header class="person-hero">
        <div class="person-hero__avatar" aria-hidden="true"><?= e(mb_substr($person['name'], 0, 1, 'UTF-8')) ?></div>
        <div>
            <h1 class="person-hero__name"><?= e($person['name']) ?></h1>
            <p class="person-hero__meta">
                <?= e(implode(' и ', $types)) ?: 'Персона' ?>
                · <?= $total ?> <?= plural($total, 'работа', 'работы', 'работ') ?>
            </p>
        </div>
    </header>

    <?php if ($directed): ?>
    <section class="section">
        <h2 class="section-title">Как режиссёр</h2>
        <div class="grid grid--4 reveal-group">
            <?php foreach ($directed as $item): ?><?php View::partial('card', ['item' => $item, 'favSet' => $favSet]); ?><?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($acted): ?>
    <section class="section">
        <h2 class="section-title">Как актёр</h2>
        <div class="grid grid--4 reveal-group">
            <?php foreach ($acted as $item): ?><?php View::partial('card', ['item' => $item, 'favSet' => $favSet]); ?><?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</section>