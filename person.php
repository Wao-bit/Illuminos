<?php
require __DIR__ . '/app/bootstrap.php';

$id = (int)($_GET['id'] ?? 0);
$person = Person::find($id);
if (!$person) {
    http_response_code(404);
    View::page('notfound', ['message' => 'Персона не найдена.'], ['title' => 'Не найдено — Illuminos']);
    exit;
}

$directed = Person::directedBy($id);
$acted    = Person::actedBy($id);

View::page('person', [
    'person'   => $person,
    'directed' => $directed,
    'acted'    => $acted,
    'favSet'   => array_flip(Account::favoriteIds(Auth::id())),
], ['title' => $person['name'] . ' — Illuminos']);