<?php
// Тонкая обёртка над PDO: одно соединение на запрос и три коротких метода.
final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            $c = (require ROOT . '/config/app.php')['db'];
            try {
                self::$pdo = new PDO(
                    "mysql:host={$c['host']};dbname={$c['name']};charset={$c['charset']}",
                    $c['user'],
                    $c['pass'],
                    [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                    ]
                );
            } catch (PDOException $e) {
                http_response_code(500);
                exit('Не удалось подключиться к базе данных. Проверьте config/app.php.');
            }
        }
        return self::$pdo;
    }

    public static function run(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function all(string $sql, array $params = []): array
    {
        return self::run($sql, $params)->fetchAll();
    }

    public static function one(string $sql, array $params = []): ?array
    {
        $row = self::run($sql, $params)->fetch();
        return $row === false ? null : $row;
    }
}
