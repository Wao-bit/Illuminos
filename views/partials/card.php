<?php
// Карточка контента. Параметры: $item, $favSet (массив id => индекс).
?>
<article class="card">
    <a class="card__link" href="<?= item_url($item) ?>" aria-label="<?= e($item['title']) ?>">
        <?= Poster::html($item) ?>
    </a>
    <div class="card__foot">
        <span class="card__meta"><?= e($item['year']) ?> · <?= e($item['genre']) ?></span>
        <?php View::partial('bookmark', ['id' => $item['id'], 'on' => isset($favSet[(int)$item['id']])]); ?>
    </div>
</article>
