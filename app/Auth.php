<?php
// Сессии, роли (admin / subscriber / user), вход и регистрация.
final class Auth
{
    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function id(): int
    {
        return (int)(self::user()['id'] ?? 0);
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            redirect('/login.php');
        }
    }

    public static function signIn(array $row): void
    {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'         => (int)$row['id'],
            'login'      => $row['login'],
            'first_name' => $row['first_name'],
            'role'       => $row['role'],
        ];
    }

    public static function signOut(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    public static function setRole(string $role): void
    {
        if (self::check()) {
            $_SESSION['user']['role'] = $role;
        }
    }

    public static function validLogin(string $login): bool
    {
        return (bool)preg_match('/^[a-zA-Z0-9_]{3,20}$/', $login);
    }

    public static function loginAvailable(string $login): bool
    {
        return Database::one('SELECT id FROM users WHERE login = ?', [$login]) === null;
    }

    /** Возвращает текст ошибки или null, если вход выполнен. */
    public static function attempt(string $login, string $password): ?string
    {
        $login = trim($login);
        if ($login === '' || $password === '') {
            return 'Введите логин и пароль.';
        }
        $row = Database::one('SELECT * FROM users WHERE login = ?', [$login]);
        if (!$row || !password_verify($password, $row['password_hash'])) {
            return 'Неверный логин или пароль.';
        }
        self::signIn($row);
        return null;
    }

    /** @return array{error: ?string, field: ?string} */
    public static function register(array $in): array
    {
        $first  = trim((string)($in['first_name'] ?? ''));
        $last   = trim((string)($in['last_name'] ?? ''));
        $middle = trim((string)($in['patronymic'] ?? ''));
        $login  = trim((string)($in['login'] ?? ''));
        $email  = trim((string)($in['email'] ?? ''));
        $pass   = (string)($in['password'] ?? '');
        $pass2  = (string)($in['password2'] ?? '');

        $bad = fn(string $field, string $text): array => ['error' => $text, 'field' => $field];

        if ($first === '') return $bad('first_name', 'Укажите имя и фамилию.');
        if ($last === '')  return $bad('last_name', 'Укажите имя и фамилию.');
        if (!self::validLogin($login)) return $bad('login', 'Логин: 3–20 символов (буквы, цифры, _).');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return $bad('email', 'Некорректный e-mail.');
        if (strlen($pass) < 6) return $bad('password', 'Пароль должен быть не короче 6 символов.');
        if ($pass !== $pass2)  return $bad('password2', 'Пароли не совпадают.');
        if (!isset($in['agree'])) return $bad('agree', 'Необходимо согласие с правилами регистрации.');

        $exists = Database::one('SELECT id FROM users WHERE login = ? OR email = ?', [$login, $email]);
        if ($exists) {
            return $bad('login', 'Пользователь с таким логином или e-mail уже существует.');
        }

        Database::run(
            "INSERT INTO users (first_name, last_name, patronymic, login, email, password_hash, role)
             VALUES (?, ?, ?, ?, ?, ?, 'user')",
            [$first, $last, $middle, $login, $email, password_hash($pass, PASSWORD_DEFAULT)]
        );
        self::signIn([
            'id' => Database::pdo()->lastInsertId(), 'login' => $login,
            'first_name' => $first, 'role' => 'user',
        ]);
        return ['error' => null, 'field' => null];
    }
}
