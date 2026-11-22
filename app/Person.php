<?php
// Персоны (режиссёры и актёры) и их работы.
final class Person
{
    public static function find(int $id): ?array
    {
        return Database::one('SELECT * FROM people WHERE id = ?', [$id]);
    }

    public static function directorsFor(int $contentId): array
    {
        return Database::all(
            'SELECT p.* FROM people p
             JOIN content_people cp ON cp.person_id = p.id
             WHERE cp.content_id = ? AND cp.role = ? ORDER BY p.name',
            [$contentId, 'director']
        );
    }

    public static function actorsFor(int $contentId): array
    {
        return Database::all(
            'SELECT p.* FROM people p
             JOIN content_people cp ON cp.person_id = p.id
             WHERE cp.content_id = ? AND cp.role = ? ORDER BY p.name',
            [$contentId, 'actor']
        );
    }

    public static function directedBy(int $personId, int $limit = 24): array
    {
        return Database::all(
            'SELECT DISTINCT c.*, g.name AS genre FROM content c
             JOIN genres g ON g.id = c.genre_id
             JOIN content_people cp ON cp.content_id = c.id
             WHERE cp.person_id = ? AND cp.role = ?
             ORDER BY c.year DESC, c.rating DESC LIMIT ' . $limit,
            [$personId, 'director']
        );
    }

    public static function actedBy(int $personId, int $limit = 24): array
    {
        return Database::all(
            'SELECT DISTINCT c.*, g.name AS genre FROM content c
             JOIN genres g ON g.id = c.genre_id
             JOIN content_people cp ON cp.content_id = c.id
             WHERE cp.person_id = ? AND cp.role = ?
             ORDER BY c.year DESC, c.rating DESC LIMIT ' . $limit,
            [$personId, 'actor']
        );
    }
}