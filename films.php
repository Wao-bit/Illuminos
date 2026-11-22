<?php
require __DIR__ . '/app/bootstrap.php';
$uid = Auth::id();
$filters = Catalog::filters($_GET);
$filters['type'] = 'film';

View::page('catalog', [
    'heading'    => 'Фильмы',
    'action'     => '/films.php',
    'filters'    => $filters,
    'genres'     => Catalog::genres(),
    'results'    => Catalog::search($filters),
    'isFiltered' => $filters['genre'] > 0 || $filters['q'] !== '' || $filters['sort'] !== 'rating',
    'favSet'     => array_flip(Account::favoriteIds($uid)),
], ['title' => 'Фильмы — Illuminos', 'active' => 'film']);