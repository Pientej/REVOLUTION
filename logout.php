<?php
require __DIR__ . '/config.php';

// Tüm oturum verilerini temizle
$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}

// Hatırla cookie'sini de sil
setcookie('remember_token', '', time() - 3600, '/');

session_destroy();
header('Location: login.php');
exit;