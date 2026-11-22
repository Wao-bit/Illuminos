<?php
// Небольшие функции, которые нужны и в контроллерах, и в шаблонах.

function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Путь к файлу из /assets с отметкой времени — браузер не держит старый кэш. */
function asset(string $path): string
{
    $file = ROOT . '/assets/' . $path;
    return '/assets/' . $path . '?v=' . (is_file($file) ? filemtime($file) : 1);
}

/** plural(5, 'день', 'дня', 'дней') */
function plural(int $n, string $one, string $few, string $many): string
{
    $n = abs($n) % 100;
    $last = $n % 10;
    if ($n > 10 && $n < 20) return $many;
    if ($last > 1 && $last < 5) return $few;
    if ($last === 1) return $one;
    return $many;
}

function item_url(array $item): string
{
    return ($item['type'] === 'serial' ? '/serial.php' : '/movie.php') . '?id=' . (int)$item['id'];
}

function type_label(string $type): string
{
    return $type === 'serial' ? 'Сериал' : 'Фильм';
}

function redirect(string $to): void
{
    header('Location: ' . $to);
    exit;
}

function json_out(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

/** Одноразовое сообщение, которое переживает редирект. */
function flash(string $text, string $kind = 'info'): void
{
    $_SESSION['flash'] = ['text' => $text, 'kind' => $kind];
}

function take_flash(): ?array
{
    $msg = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $msg;
}

function money(float|string $amount): string
{
    return number_format((float)$amount, 0, '', ' ') . ' ₽';
}

function human_date(string $sqlDate, bool $withTime = false): string
{
    return date($withTime ? 'd.m.Y, H:i' : 'd.m.Y', strtotime($sqlDate));
}

/** Токен защиты форм от подделки запросов (CSRF). */
function csrf_token(): string
{
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(16));
}

function csrf_ok(): bool
{
    $own = (string)($_SESSION['csrf'] ?? '');
    return $own !== '' && hash_equals($own, (string)($_POST['csrf'] ?? ''));
}

function role_label(array $user, ?array $subscription = null): string
{
    if (($user['role'] ?? '') === 'admin') return 'Администратор';
    if (($user['role'] ?? '') === 'subscriber') {
        if ($subscription === null) {
            $subscription = Account::subscription((int)$user['id']);
        }
        return $subscription['tariff_name'] ?? 'Подписчик';
    }
    return 'Пользователь';
}