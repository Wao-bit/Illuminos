<?php
// Параметры: $item, $seasons, $similar, $favSet, $crumb, $similarTitle, $similarEmpty,
//            $directors, $actors.
$score = round(max(0, min(10, (float)$item['rating'])) * 10, 1);
$inFav = isset($favSet[(int)$item['id']]);
?>
<article class="container title-page">
    <nav class="crumbs" aria-label="Хлебные крошки">
        <a href="/index.php">Главная</a><span>/</span><a href="<?= e($crumb[1]) ?>"><?= e($crumb[0]) ?></a><span>/</span><span aria-current="page"><?= e($item['title']) ?></span>
    </nav>

    <div class="title-page__grid">
        <div class="title-page__poster"><?= Poster::html($item, 'large') ?></div>

        <div class="title-page__info">
            <h1 class="display"><?= e($item['title']) ?></h1>
            <p class="mono muted"><?= e($item['year']) ?> · <?= e($item['genre']) ?> ·
                оценка зрителей: <strong class="score">★ <?= e($item['rating']) ?></strong></p>
            <div class="meter" role="img" aria-label="Рейтинг <?= e($item['rating']) ?> из 10"><span style="--w:<?= $score ?>%"></span></div>

            <p class="title-page__text"><?= nl2br(e($item['description'])) ?></p>

            <dl class="facts">
                <dt>Режиссёр:</dt>
                <dd>
                    <?php if ($directors): ?>
                        <?php foreach ($directors as $i => $d): ?>
                            <?= $i > 0 ? ', ' : '' ?><a class="link-quiet" href="/person.php?id=<?= (int)$d['id'] ?>"><?= e($d['name']) ?></a>
                        <?php endforeach; ?>
                    <?php else: ?>—<?php endif; ?>
                </dd>
                <dt>В ролях:</dt>
                <dd>
                    <?php if ($actors): ?>
                        <?php foreach ($actors as $i => $a): ?>
                            <?= $i > 0 ? ', ' : '' ?><a class="link-quiet" href="/person.php?id=<?= (int)$a['id'] ?>"><?= e($a['name']) ?></a>
                        <?php endforeach; ?>
                    <?php else: ?>—<?php endif; ?>
                </dd>
            </dl>

            <div class="row-gap">
                <?php if (!empty($item['trailer_url'])): ?><a class="btn btn--primary" href="#trailer">Смотреть трейлер</a><?php endif; ?>
                <?php View::partial('bookmark', ['id' => $item['id'], 'on' => $inFav, 'label' => true]); ?>
                <button class="btn btn--ghost" type="button" data-share>Поделиться</button>
            </div>
        </div>
    </div>

    <?php if ($item['type'] === 'serial'): ?>
    <section class="section" data-component="seasons">
        <div class="section__head">
            <h2 class="section-title">Сезоны и эпизоды</h2>
            <?php if (count($seasons) > 1): ?><button class="btn btn--ghost btn--sm" type="button" data-seasons-toggle>Развернуть все</button><?php endif; ?>
        </div>
        <?php foreach ($seasons as $i => $s):
            $count = count($s['episodes']);
            $minutes = array_sum(array_map(fn($ep) => (int)$ep['duration_min'], $s['episodes']));
            $span = $minutes >= 60 ? intdiv($minutes, 60) . ' ч ' . ($minutes % 60) . ' мин' : $minutes . ' мин';
        ?>
        <details class="season"<?= $i === 0 ? ' open' : '' ?>>
            <summary>
                <span>Сезон <?= (int)$s['number'] ?></span>
                <span class="mono muted"><?= $count ?> <?= plural($count, 'эпизод', 'эпизода', 'эпизодов') ?> · <?= e($span) ?></span>
            </summary>
            <ol class="episodes">
                <?php foreach ($s['episodes'] as $ep): ?>
                <li><span>Эпизод <?= (int)$ep['number'] ?>. <?= e($ep['title']) ?></span><span class="mono muted"><?= (int)$ep['duration_min'] ?> мин</span></li>
                <?php endforeach; ?>
            </ol>
        </details>
        <?php endforeach; ?>
        <?php if (!$seasons): ?><p class="empty">Информация о сезонах появится позже.</p><?php endif; ?>
    </section>
    <?php endif; ?>

    <?php if (!empty($item['trailer_url'])): ?>
    <section class="section" id="trailer">
        <h2 class="section-title">Трейлер</h2>
        <div class="screen">
            <iframe src="<?= e($item['trailer_url']) ?>" title="Трейлер: <?= e($item['title']) ?>" loading="lazy" allowfullscreen></iframe>
        </div>
    </section>
    <?php endif; ?>

    <section class="section">
        <h2 class="section-title"><?= e($similarTitle) ?></h2>
        <?php if ($similar): ?>
            <div class="grid grid--4 reveal-group">
                <?php foreach ($similar as $s): ?><?php View::partial('card', ['item' => $s, 'favSet' => $favSet]); ?><?php endforeach; ?>
            </div>
        <?php else: ?><p class="empty"><?= e($similarEmpty) ?></p><?php endif; ?>
    </section>
</article>
