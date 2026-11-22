<?php
// Общая точка входа: автозагрузка классов, сессия, вспомогательные функции.
define('ROOT', dirname(__DIR__));

spl_autoload_register(function (string $class): void {
    $file = ROOT . '/app/' . $class . '.php';
    if (is_file($file)) {
        require $file;
    }
});

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require ROOT . '/app/functions.php';
