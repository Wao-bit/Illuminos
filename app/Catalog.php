<?php
// Всё, что читает каталог: новинки, поиск, карточка фильма, сезоны.
final class Catalog
{
    private const SELECT = 'SELECT c.*, g.name AS genre FROM content c JOIN genres g ON g.id = c.genre_id';
    private const SORTS = [
        'rating' => 'c.rating DESC',
        'year'   => 'c.year DESC, c.rating DESC',
        'title'  => 'c.title ASC',
    ];

    public static function genres(): array
    {
        return Database::all('SELECT id, name FROM genres ORDER BY name');
    }

    public static function fresh(int $limit = 8): array
    {
        return Database::all(self::SELECT . ' WHERE c.is_new = 1 ORDER BY c.year DESC, c.rating DESC LIMIT ' . $limit);
    }

    public static function popular(int $limit = 8): array
    {
        return Database::all(self::SELECT . ' WHERE c.is_popular = 1 ORDER BY c.rating DESC LIMIT ' . $limit);
    }

    public static function topRated(int $limit = 8): array
    {
        return Database::all(self::SELECT . ' ORDER BY c.rating DESC LIMIT ' . $limit);
    }

    /** Жанры из избранного пользователя, без уже сохранённого. */
    public static function recommendedFor(int $uid, int $limit = 8): array
    {
        return Database::all(
            self::SELECT . '
             WHERE c.genre_id IN (SELECT c2.genre_id FROM favorites f JOIN content c2 ON c2.id = f.content_id WHERE f.user_id = ?)
               AND c.id NOT IN (SELECT content_id FROM favorites WHERE user_id = ?)
             ORDER BY c.rating DESC LIMIT ' . $limit,
            [$uid, $uid]
        );
    }

    public static function find(int $id, string $type): ?array
    {
        return Database::one(self::SELECT . ' WHERE c.id = ? AND c.type = ?', [$id, $type]);
    }

    public static function similar(array $item, int $limit = 4): array
    {
        return Database::all(
            'SELECT DISTINCT c.*, g.name AS genre FROM content c
             JOIN genres g ON g.id = c.genre_id
             WHERE c.id != ? AND c.type = ?
               AND c.id IN (
                 SELECT cp2.content_id FROM content_people cp1
                 JOIN content_people cp2
                   ON cp1.person_id = cp2.person_id AND cp1.content_id != cp2.content_id
                 WHERE cp1.content_id = ?
               )
             ORDER BY c.rating DESC LIMIT ' . $limit,
            [$item['id'], $item['type'], $item['id']]
        );
    }

    /** Приводит GET-параметры к безопасному виду. */
    public static function filters(array $query): array
    {
        $type = $query['type'] ?? '';
        $sort = $query['sort'] ?? 'rating';
        return [
            'type'  => in_array($type, ['film', 'serial'], true) ? $type : '',
            'genre' => max(0, (int)($query['genre'] ?? 0)),
            'q'     => trim((string)($query['q'] ?? '')),
            'sort'  => isset(self::SORTS[$sort]) ? $sort : 'rating',
        ];
    }

    public static function search(array $f, int $limit = 24): array
    {
        $sql = self::SELECT . ' WHERE 1 = 1';
        $args = [];
        if ($f['type'] !== '')  { $sql .= ' AND c.type = ?';     $args[] = $f['type']; }
        if ($f['genre'] > 0)    { $sql .= ' AND c.genre_id = ?'; $args[] = $f['genre']; }
        if ($f['q'] !== '')     { $sql .= ' AND c.title LIKE ?'; $args[] = self::like($f['q']); }
        return Database::all($sql . ' ORDER BY ' . self::SORTS[$f['sort']] . ' LIMIT ' . $limit, $args);
    }

    public static function suggest(string $q, int $limit = 6): array
    {
        return Database::all(self::SELECT . ' WHERE c.title LIKE ? ORDER BY c.rating DESC LIMIT ' . $limit, [self::like($q)]);
    }

    /** Сезоны с вложенным списком эпизодов. */
    public static function seasons(int $contentId): array
    {
        $seasons = Database::all('SELECT * FROM seasons WHERE content_id = ? ORDER BY number', [$contentId]);
        foreach ($seasons as &$season) {
            $season['episodes'] = Database::all('SELECT * FROM episodes WHERE season_id = ? ORDER BY number', [$season['id']]);
        }
        return $seasons;
    }

    private static function like(string $text): string
    {
        return '%' . addcslashes($text, '%_\\') . '%';
    }
}
