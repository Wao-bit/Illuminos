<?php
// Подсказки для строки поиска (минимум 2 символа).
require __DIR__ . '/../app/bootstrap.php';

$q = trim((string)($_GET['q'] ?? ''));
$out = [];
if (mb_strlen($q, 'UTF-8') >= 2) {
    foreach (Catalog::suggest($q) as $row) {
        $out[] = [
            'title' => $row['title'],
            'meta'  => type_label($row['type']) . ' · ' . $row['year'] . ' · ★ ' . $row['rating'],
            'url'   => item_url($row),
            'art'   => Poster::art($row), // SVG строится на сервере из фиксированных фигур
        ];
    }
}
json_out($out);
