<?php
// Данные пользователя: избранное, история, подписка, платежи, профиль.
final class Account
{
    public static function favoriteIds(int $uid): array
    {
        if ($uid <= 0) return [];
        $rows = Database::all('SELECT content_id FROM favorites WHERE user_id = ?', [$uid]);
        return array_map('intval', array_column($rows, 'content_id'));
    }

    public static function favorites(int $uid): array
    {
        return Database::all(
            'SELECT c.*, g.name AS genre FROM favorites f
             JOIN content c ON c.id = f.content_id
             JOIN genres g ON g.id = c.genre_id
             WHERE f.user_id = ? ORDER BY f.created_at DESC',
            [$uid]
        );
    }

    public static function toggleFavorite(int $uid, int $contentId): ?bool
    {
        if (!Database::one('SELECT id FROM content WHERE id = ?', [$contentId])) return null;
        $row = Database::one('SELECT id FROM favorites WHERE user_id = ? AND content_id = ?', [$uid, $contentId]);
        if ($row) {
            Database::run('DELETE FROM favorites WHERE id = ?', [$row['id']]);
            return false;
        }
        Database::run('INSERT IGNORE INTO favorites (user_id, content_id) VALUES (?, ?)', [$uid, $contentId]);
        return true;
    }

    public static function recordView(int $uid, int $contentId): void
    {
        if ($uid <= 0) return;
        $recent = Database::one(
            'SELECT id FROM watch_history
             WHERE user_id = ? AND content_id = ? AND watched_at > (NOW() - INTERVAL 10 MINUTE) LIMIT 1',
            [$uid, $contentId]
        );
        if (!$recent) {
            Database::run('INSERT INTO watch_history (user_id, content_id) VALUES (?, ?)', [$uid, $contentId]);
        }
    }

    public static function history(int $uid, int $limit = 20): array
    {
        return Database::all(
            'SELECT h.watched_at, c.id, c.title, c.type FROM watch_history h
             JOIN content c ON c.id = h.content_id
             WHERE h.user_id = ? ORDER BY h.watched_at DESC LIMIT ' . $limit,
            [$uid]
        );
    }

    public static function clearHistory(int $uid): void
    {
        Database::run('DELETE FROM watch_history WHERE user_id = ?', [$uid]);
    }

    public static function subscription(int $uid): ?array
    {
        $sub = Database::one(
            'SELECT us.*, t.name AS tariff_name, t.price, t.duration_days FROM user_subscriptions us
             JOIN tariffs t ON t.id = us.tariff_id
             WHERE us.user_id = ? AND us.end_date >= CURDATE()
             ORDER BY us.end_date DESC LIMIT 1',
            [$uid]
        );
        if (!$sub) return null;

        $end   = new DateTime($sub['end_date']);
        $start = new DateTime($sub['start_date']);
        $today = new DateTime('today');
        $left  = max(0, (int)$today->diff($end)->format('%r%a'));
        $total = max(1, (int)$start->diff($end)->days);
        $sub['days_left'] = $left;
        $sub['percent']   = round(min(100, $left / $total * 100), 1);
        return $sub;
    }

    public static function payments(int $uid, int $limit = 10): array
    {
        return Database::all(
            'SELECT p.*, t.name AS tariff_name, t.price AS tariff_price
             FROM payments p JOIN tariffs t ON t.id = p.tariff_id
             WHERE p.user_id = ? ORDER BY p.created_at DESC LIMIT ' . $limit,
            [$uid]
        );
    }

    public static function tariffs(): array
    {
        return Database::all('SELECT * FROM tariffs ORDER BY price');
    }

    public static function tariff(int $id): ?array
    {
        return Database::one('SELECT * FROM tariffs WHERE id = ?', [$id]);
    }

    public static function upgradeQuote(array $sub, array $tariff): ?array
    {
        $left = (int)$sub['days_left'];
        if ($left <= 0 || (float)$tariff['price'] <= (float)$sub['price']) {
            return null;
        }
        $perDay = (float)$tariff['price'] / max(1, (int)$tariff['duration_days'])
                - (float)$sub['price'] / max(1, (int)$sub['duration_days']);
        return [
            'surcharge' => max(1, (int)round($perDay * $left)),
            'days_left' => $left,
        ];
    }

    /** Создать платёж в статусе «new». $oldTariffId != null — это доплата при апгрейде. */
    public static function createPendingPayment(int $uid, array $tariff, float $amount, ?int $oldTariffId): int
    {
        Database::run(
            "INSERT INTO payments (user_id, tariff_id, amount, status, old_tariff_id)
             VALUES (?, ?, ?, 'new', ?)",
            [$uid, $tariff['id'], $amount, $oldTariffId]
        );
        return (int)Database::pdo()->lastInsertId();
    }

    public static function findPayment(int $id, int $uid): ?array
    {
        return Database::one('SELECT * FROM payments WHERE id = ? AND user_id = ?', [$id, $uid]);
    }

    /** @return array{ok: bool, error: ?string} */
    public static function confirmPayment(int $paymentId, string $method): array
    {
        $pdo = Database::pdo();
        try {
            $pdo->beginTransaction();
            $payment = Database::one(
                "SELECT * FROM payments WHERE id = ? AND status = 'new' FOR UPDATE",
                [$paymentId]
            );
            if (!$payment) {
                $pdo->rollBack();
                return ['ok' => false, 'error' => 'Платёж не найден или уже оплачен.'];
            }
            $tariff = self::tariff((int)$payment['tariff_id']);
            if (!$tariff) {
                $pdo->rollBack();
                return ['ok' => false, 'error' => 'Тариф не найден.'];
            }

            Database::run(
                "UPDATE payments SET status = 'paid', payment_method = ? WHERE id = ?",
                [$method, $paymentId]
            );

            if ($payment['old_tariff_id'] !== null) {
                Database::run(
                    'UPDATE user_subscriptions SET tariff_id = ?
                     WHERE user_id = ? AND tariff_id = ?
                     ORDER BY id DESC LIMIT 1',
                    [$tariff['id'], $payment['user_id'], $payment['old_tariff_id']]
                );
            } else {
                Database::run(
                    "UPDATE users SET role = 'subscriber' WHERE id = ? AND role = 'user'",
                    [$payment['user_id']]
                );
                Database::run(
                    'INSERT INTO user_subscriptions (user_id, tariff_id, start_date, end_date)
                     VALUES (?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL ? DAY))',
                    [$payment['user_id'], $tariff['id'], $tariff['duration_days']]
                );
            }
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            return ['ok' => false, 'error' => 'Ошибка при обработке платежа.'];
        }

        if ($payment['old_tariff_id'] === null && (Auth::user()['role'] ?? '') === 'user') {
            Auth::setRole('subscriber');
        }
        return ['ok' => true, 'error' => null];
    }

    /** @return array{error: ?string, field: ?string} */
    public static function updateProfile(int $uid, array $in): array
    {
        $first  = trim((string)($in['first_name'] ?? ''));
        $last   = trim((string)($in['last_name'] ?? ''));
        $middle = trim((string)($in['patronymic'] ?? ''));
        $login  = trim((string)($in['login'] ?? ''));
        $email  = trim((string)($in['email'] ?? ''));
        $curPw  = (string)($in['current_password'] ?? '');
        $newPw  = (string)($in['new_password'] ?? '');
        $newPw2 = (string)($in['new_password2'] ?? '');

        $bad = fn(string $f, string $t): array => ['error' => $t, 'field' => $f];

        if ($first === '') return $bad('first_name', 'Укажите имя.');
        if ($last === '')  return $bad('last_name', 'Укажите фамилию.');
        if (!Auth::validLogin($login)) return $bad('login', 'Логин: 3–20 символов (буквы, цифры, _).');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return $bad('email', 'Некорректный e-mail.');

        $exists = Database::one(
            'SELECT id FROM users WHERE (login = ? OR email = ?) AND id != ?',
            [$login, $email, $uid]
        );
        if ($exists) return $bad('login', 'Логин или e-mail уже заняты.');

        $pwChanged = false;
        if ($curPw !== '' || $newPw !== '' || $newPw2 !== '') {
            if ($curPw === '') return $bad('current_password', 'Введите текущий пароль.');
            $user = Database::one('SELECT password_hash FROM users WHERE id = ?', [$uid]);
            if (!$user || !password_verify($curPw, $user['password_hash'])) {
                return $bad('current_password', 'Неверный текущий пароль.');
            }
            if (strlen($newPw) < 6) return $bad('new_password', 'Пароль не короче 6 символов.');
            if ($newPw !== $newPw2) return $bad('new_password2', 'Пароли не совпадают.');
            $pwChanged = true;
        }

        $sql = 'UPDATE users SET first_name=?, last_name=?, patronymic=?, login=?, email=?';
        $params = [$first, $last, $middle !== '' ? $middle : null, $login, $email];
        if ($pwChanged) {
            $sql .= ', password_hash=?';
            $params[] = password_hash($newPw, PASSWORD_DEFAULT);
        }
        $sql .= ' WHERE id=?';
        $params[] = $uid;
        Database::run($sql, $params);

        $_SESSION['user']['login']      = $login;
        $_SESSION['user']['first_name'] = $first;

        return ['error' => null, 'field' => null];
    }
}