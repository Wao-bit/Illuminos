<?php
// Регистрация: все поля проверяются в браузере без перезагрузки и повторно на сервере.
require __DIR__ . '/app/bootstrap.php';
if (Auth::check()) redirect('/index.php');

View::page('signup', [], ['title' => 'Регистрация — Illuminos']);
