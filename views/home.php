<?php
// Главная. Данные приходят из index.php.
$padNum = fn(int $n): string => str_pad((string)$n, 2, '0', STR_PAD_LEFT);
?>
<h1 class="visually-hidden">Illuminos — онлайн-кинотеатр</h1>

<?php if ($featured): ?>
<!-- Афиша недели: список слева управляет большой панелью справа -->
<section class="featured container" data-component="featured" aria-labelledby="featured-title">
    <div class="featured__list">
        <h2 class="section-label" id="featured-title">Афиша недели</h2>
        <ol role="tablist" aria-label="Новинки">
            <?php foreach ($featured as $i => $f): ?>
            <li>
                <button class="featured__tab" type="button" role="tab" id="tab-<?= (int)$f['id'] ?>"
                        aria-controls="panel-<?= (int)$f['id'] ?>" aria-selected="<?= $i === 0 ? 'true' : 'false' ?>"
                        tabindex="<?= $i === 0 ? '0' : '-1' ?>">
                    <span class="featured__num"><?= $padNum($i + 1) ?></span>
                    <span class="featured__name"><?= e($f['title']) ?></span>
                    <span class="featured__sub"><?= e(type_label($f['type'])) ?> · <?= e($f['genre']) ?> · <?= e($f['year']) ?></span>
                </button>
            </li>
            <?php endforeach; ?>
        </ol>
    </div>
    <div class="featured__stage">
        <?php foreach ($featured as $i => $f): ?>
        <article class="featured__panel<?= $i === 0 ? ' is-active' : '' ?>" role="tabpanel" id="panel-<?= (int)$f['id'] ?>"
                 aria-labelledby="tab-<?= (int)$f['id'] ?>"<?= $i === 0 ? '' : ' hidden' ?>>
            <a class="featured__poster" href="<?= item_url($f) ?>" tabindex="-1" aria-hidden="true"><?= Poster::html($f) ?></a>
            <div class="featured__body">
                <p class="mono muted"><?= e(type_label($f['type'])) ?> · <?= e($f['genre']) ?> · <?= e($f['year']) ?> · ★ <?= e($f['rating']) ?></p>
                <h3 class="display"><?= e($f['title']) ?></h3>
                <p class="featured__text"><?= e($f['description']) ?></p>
                <div class="row-gap">
                    <a class="btn btn--primary" href="<?= item_url($f) ?>">Подробнее</a>
                    <?php View::partial('bookmark', ['id' => $f['id'], 'on' => isset($favSet[(int)$f['id']]), 'label' => true]); ?>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- Новинки -->
<section class="section container">
    <h2 class="section-title">Топ новинки</h2>
    <?php if ($latest): ?>
        <div class="grid grid--4 reveal-group">
            <?php foreach ($latest as $item): ?><?php View::partial('card', ['item' => $item, 'favSet' => $favSet]); ?><?php endforeach; ?>
        </div>
    <?php else: ?><p class="empty">Ничего не найдено.</p><?php endif; ?>
</section>

<!-- Популярное: рейтинговый список -->
<section class="section container">
    <h2 class="section-title">Популярные фильмы и сериалы</h2>
    <?php if ($popular): ?>
    <ol class="ranking reveal-group">
        <?php foreach ($popular as $i => $item): ?>
        <li class="ranking__item">
            <span class="ranking__pos" aria-hidden="true"><?= $i + 1 ?></span>
            <a class="ranking__link" href="<?= item_url($item) ?>">
                <span class="ranking__thumb"><?= Poster::art($item) ?></span>
                <span class="ranking__text">
                    <strong><?= e($item['title']) ?></strong>
                    <span class="mono muted"><?= e(type_label($item['type'])) ?> · <?= e($item['genre']) ?> · <?= e($item['year']) ?></span>
                </span>
            </a>
            <span class="ranking__score mono">★ <?= e($item['rating']) ?></span>
            <?php View::partial('bookmark', ['id' => $item['id'], 'on' => isset($favSet[(int)$item['id']])]); ?>
        </li>
        <?php endforeach; ?>
    </ol>
    <?php else: ?><p class="empty">Ничего не найдено.</p><?php endif; ?>
</section>

<!-- Каталог: фильтры слева, результаты справа -->
<section class="section container" id="catalog">
    <h2 class="section-title">Категории</h2>
    <div class="catalog">
        <form class="filters" method="get" action="/index.php#catalog" data-component="filters">
            <label class="field">
                <span class="field__label">Название</span>
                <input class="field__input" type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Например, «Небесный»">
            </label>
            <fieldset class="filters__group">
                <legend class="field__label">Тип</legend>
                <div class="segmented">
                    <?php foreach (['' => 'Всё', 'film' => 'Фильмы', 'serial' => 'Сериалы'] as $val => $name): ?>
                        <label><input type="radio" name="type" value="<?= e($val) ?>"<?= $filters['type'] === $val ? ' checked' : '' ?>><span><?= e($name) ?></span></label>
                    <?php endforeach; ?>
                </div>
            </fieldset>
            <fieldset class="filters__group">
                <legend class="field__label">Жанр</legend>
                <div class="pills">
                    <label><input type="radio" name="genre" value="0"<?= $filters['genre'] === 0 ? ' checked' : '' ?>><span>Все</span></label>
                    <?php foreach ($genres as $g): ?>
                        <label><input type="radio" name="genre" value="<?= (int)$g['id'] ?>"<?= $filters['genre'] === (int)$g['id'] ? ' checked' : '' ?>><span><?= e($g['name']) ?></span></label>
                    <?php endforeach; ?>
                </div>
            </fieldset>
            <label class="field">
                <span class="field__label">Сортировка</span>
                <select class="field__input" name="sort">
                    <option value="rating"<?= $filters['sort'] === 'rating' ? ' selected' : '' ?>>По рейтингу</option>
                    <option value="year"<?= $filters['sort'] === 'year' ? ' selected' : '' ?>>Сначала новые</option>
                    <option value="title"<?= $filters['sort'] === 'title' ? ' selected' : '' ?>>По названию</option>
                </select>
            </label>
            <div class="row-gap">
                <button class="btn btn--primary btn--sm" type="submit">Показать</button>
                <?php if ($isFiltered): ?><a class="link-quiet" href="/index.php#catalog">Сбросить</a><?php endif; ?>
            </div>
        </form>

        <div class="catalog__results">
            <?php if ($filters['q'] !== ''): ?>
                <p class="muted">Результаты поиска по запросу «<?= e($filters['q']) ?>»:</p>
            <?php endif; ?>
            <?php if ($results): ?>
                <p class="mono muted" aria-live="polite">Показано: <?= count($results) ?></p>
                <div class="grid grid--3 reveal-group">
                    <?php foreach ($results as $item): ?><?php View::partial('card', ['item' => $item, 'favSet' => $favSet]); ?><?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty">
                    <p>Ничего не найдено.</p>
                    <p class="muted">Измените запрос или выберите другой жанр.</p>
                    <?php if ($isFiltered): ?><a class="btn btn--ghost btn--sm" href="/index.php#catalog">Сбросить фильтры</a><?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Рекомендации -->
<section class="section container">
    <h2 class="section-title"><?= e($picksTitle) ?></h2>
    <div class="grid grid--4 reveal-group">
        <?php foreach ($picks as $item): ?><?php View::partial('card', ['item' => $item, 'favSet' => $favSet]); ?><?php endforeach; ?>
    </div>
</section>
