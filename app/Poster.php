<?php
// Постеры-заглушки: плоская графика из двух-трёх фигур.
// Цвета зависят от жанра, композиция — от id, поэтому одна и та же карточка всегда выглядит одинаково.
final class Poster
{
    private const PALETTES = [
        ['#D9432B', '#F3EEE3', '#1A1713'],
        ['#0F5C58', '#F2C94C', '#F3EEE3'],
        ['#1A1713', '#F2C94C', '#D9432B'],
        ['#6B2D5C', '#F4B6C2', '#F3EEE3'],
        ['#2F4B7C', '#F7A44C', '#F3EEE3'],
        ['#5C6B2D', '#F3EEE3', '#D9432B'],
    ];

    /** Только картинка (без названия) — для миниатюр. */
    public static function art(array $item): string
    {
        [$bg, $a, $b] = self::PALETTES[((int)($item['genre_id'] ?? 0)) % count(self::PALETTES)];
        $shapes = match ((int)($item['id'] ?? 0) % 5) {
            0 => "<circle cx='100' cy='92' r='58' fill='$a'/><rect y='150' width='200' height='90' fill='$b'/>"
               . "<rect y='168' width='200' height='6' fill='$a'/><rect y='186' width='200' height='6' fill='$a'/><rect y='204' width='200' height='6' fill='$a'/>",
            1 => "<path d='M40 240V100a60 60 0 0 1 120 0v140z' fill='$a'/><circle cx='100' cy='96' r='22' fill='$b'/>",
            2 => "<polygon points='0,240 200,90 200,240' fill='$a'/><circle cx='58' cy='70' r='30' fill='$b'/>",
            3 => "<g fill='none' stroke='$a' stroke-width='10'><circle cx='100' cy='110' r='26'/><circle cx='100' cy='110' r='50'/><circle cx='100' cy='110' r='74'/></g><circle cx='100' cy='110' r='10' fill='$b'/>",
            default => "<rect x='24' y='40' width='152' height='22' fill='$a'/><rect x='24' y='78' width='110' height='22' fill='$b'/>"
                     . "<rect x='24' y='116' width='132' height='22' fill='$a'/><rect x='24' y='154' width='70' height='22' fill='$b'/><rect x='24' y='192' width='120' height='22' fill='$a'/>",
        };
        return "<svg class='poster__art' viewBox='0 0 200 240' preserveAspectRatio='xMidYMid slice' aria-hidden='true' focusable='false'>"
             . "<rect width='200' height='240' fill='$bg'/>$shapes</svg>";
    }

    /** Полный постер: картинка, метка «Сериал», оценка и название. */
    public static function html(array $item, string $modifier = ''): string
    {
        $class = 'poster' . ($modifier !== '' ? ' poster--' . e($modifier) : '');
        $tag = ($item['type'] ?? '') === 'serial' ? '<span class="poster__tag">Сериал</span>' : '';
        return "<div class=\"$class\"><div class=\"poster__frame\">" . self::art($item) . $tag
             . '<span class="poster__score" title="Рейтинг ' . e($item['rating']) . '">' . e($item['rating']) . '</span></div>'
             . '<div class="poster__band"><span class="poster__title">' . e($item['title']) . '</span></div></div>';
    }
}
