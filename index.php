<?php
// Главная: афиша новинок, популярное, каталог с фильтрами, рекомендации.
require __DIR__ . '/app/bootstrap.php';

$uid     = Auth::id();
$filters = Catalog::filters($_GET);
$isFiltered = $filters['type'] !== '' || $filters['genre'] > 0 || $filters['q'] !== '' || $filters['sort'] !== 'rating';

$latest = Catalog::fresh(8);
if ($uid > 0) {
    $picks = Catalog::recommendedFor($uid);
    $picksTitle = 'Рекомендуем вам';
    if (!$picks) {                      // в избранном пока пусто — показываем лучшее по оценкам
        $picks = Catalog::topRated();
        $picksTitle = 'Высоко оценённое';
    }
} else {
    $picks = Catalog::topRated();
    $picksTitle = 'Высоко оценённое';
}

$active = 'home';
if ($filters['type'] === 'film')       $active = 'film';
elseif ($filters['type'] === 'serial') $active = 'serial';
elseif ($filters['genre'] > 0)         $active = 'genre';
elseif ($filters['q'] !== '')          $active = '';

View::page('home', [
    'featured'   => array_slice($latest, 0, 5),
    'latest'     => $latest,
    'popular'    => Catalog::popular(),
    'results'    => Catalog::search($filters),
    'genres'     => Catalog::genres(),
    'filters'    => $filters,
    'isFiltered' => $isFiltered,
    'picks'      => $picks,
    'picksTitle' => $picksTitle,
    'favSet'     => array_flip(Account::favoriteIds($uid)),
], ['title' => 'Illuminos — онлайн-кинотеатр', 'active' => $active]);
