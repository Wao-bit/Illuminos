<?php
// Кнопка «в избранное». Параметры: $id, $on (bool), $label (bool, необязательно).
$on = !empty($on);
$guest = !Auth::check();
$text = $on ? 'В избранном' : 'В избранное';
?>
<button type="button" class="bookmark<?= $on ? ' is-on' : '' ?><?= !empty($label) ? ' bookmark--labeled' : '' ?>"
        data-favorite="<?= (int)$id ?>"<?= $guest ? ' data-guest' : '' ?> aria-pressed="<?= $on ? 'true' : 'false' ?>"
        aria-label="<?= e($on ? 'Убрать из избранного' : 'Добавить в избранное') ?>">
    <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path d="M6 3h12v18l-6-4.5L6 21z"/></svg>
    <?php if (!empty($label)): ?><span class="bookmark__text" data-bookmark-text><?= e($text) ?></span><?php endif; ?>
</button>
