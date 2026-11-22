<?php
// Вход по логину и паролю (проверка — через api/auth.php).
require __DIR__ . '/app/bootstrap.php';
if (Auth::check()) redirect('/index.php');

View::page('signin', [], ['title' => 'Вход — Illuminos']);
