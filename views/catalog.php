<?php
// Каталог (Фильмы / Сериалы).
// Параметры: $heading, $action, $filters, $genres, $results, $isFiltered, $favSet.
?>
<section class="container catalog-page">
    <header class="page-head">
        <h1 class="display"><?= e($heading) ?></h1>
        <p class="muted"><?= count($results) ?> <?= plural(count($results), 'название', 'названия', 'названий') ?> в каталоге.</p>
    </header>

    <div class="catalog">
        <form class="filters" method="get" action="<?= e($action) ?>" data-component="filters">
            <label class="field">
                <span class="field__label">Название</span>
                <input class="field__input" type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Например, «Небесный»">
            </label>
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
                <?php if ($isFiltered): ?><a class="link-quiet" href="<?= e($action) ?>">Сбросить</a><?php endif; ?>
            </div>
        </form>

        <div class="catalog__results">
            <?php if ($results): ?>
                <div class="grid grid--3 reveal-group">
                    <?php foreach ($results as $item): ?><?php View::partial('card', ['item' => $item, 'favSet' => $favSet]); ?><?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty">
                    <p>Ничего не найдено.</p>
                    <p class="muted">Измените запрос или выберите другой жанр.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>