<?php
final class View
{
    public static function page(string $view, array $data = [], array $meta = []): void
    {
        $content = self::capture(ROOT . '/views/' . $view . '.php', $data);
        $title   = $meta['title'] ?? 'Illuminos — онлайн-кинотеатр';
        $active  = $meta['active'] ?? '';
        $genresMenu = Catalog::genres();
        $ticker  = Catalog::fresh(6);
        $user    = Auth::user();
        $roleLabel = $user ? role_label($user) : '';
        $notice  = take_flash();
        require ROOT . '/views/layout.php';
    }

    public static function partial(string $name, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        require ROOT . '/views/partials/' . $name . '.php';
    }

    private static function capture(string $file, array $data): string
    {
        extract($data, EXTR_SKIP);
        ob_start();
        require $file;
        return ob_get_clean();
    }
}