<?php
require __DIR__ . '/app/bootstrap.php';
$uid = Auth::id();
$filters = Catalog::filters($_GET);
$filters['type'] = 'serial';

View::page('catalog', [
    'heading'    => 'Сериалы',
    'action'     => '/series.php',
    'filters'    => $filters,
    'genres'     => Catalog::genres(),
    'results'    => Catalog::search($filters),
    'isFiltered' => $filters['genre'] > 0 || $filters['q'] !== '' || $filters['sort'] !== 'rating',
    'favSet'     => array_flip(Account::favoriteIds($uid)),
], ['title' => 'Сериалы — Illuminos', 'active' => 'serial']);