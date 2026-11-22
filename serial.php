<?php
require __DIR__ . '/app/bootstrap.php';

$uid  = Auth::id();
$item = Catalog::find((int)($_GET['id'] ?? 0), 'serial');
if (!$item) {
    http_response_code(404);
    View::page('notfound', ['message' => 'Сериал не найден.'], ['title' => 'Не найдено — Illuminos']);
    exit;
}
Account::recordView($uid, (int)$item['id']);

View::page('title', [
    'item'      => $item,
    'seasons'   => [],
    'similar'   => Catalog::similar($item),
    'favSet'    => array_flip(Account::favoriteIds($uid)),
    'directors' => Person::directorsFor((int)$item['id']),
    'actors'    => Person::actorsFor((int)$item['id']),
    'crumb'     => ['Сериалы', '/series.php'],
    'similarTitle' => 'Похожие сериалы',
    'similarEmpty' => 'Похожих сериалов пока нет.',
], ['title' => $item['title'] . ' — Illuminos', 'active' => 'serial']);