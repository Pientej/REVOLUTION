<?php
require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

if (!csrf_verify($_POST['csrf'] ?? null)) {
    $_SESSION['login_error'] = 'Güvenlik doğrulaması başarısız. Lütfen tekrar deneyin.';
    header('Location: login.php');
    exit;
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$remember = !empty($_POST['remember']);

if ($email === '' || $password === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['login_error'] = 'Geçerli bir e-posta ve şifre girin.';
    $_SESSION['old_email']   = $email;
    header('Location: login.php');
    exit;
}

$now = time();
$_SESSION['login_attempts'] = $_SESSION['login_attempts'] ?? 0;
$_SESSION['login_locked_until'] = $_SESSION['login_locked_until'] ?? 0;

if ($_SESSION['login_locked_until'] > $now) {
    $wait = ceil(($_SESSION['login_locked_until'] - $now) / 60);
    $_SESSION['login_error'] = "Çok fazla hatalı deneme. {$wait} dakika sonra tekrar dene.";
    header('Location: login.php');
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT id, email, password, username FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {

        session_regenerate_id(true);
        $_SESSION['user_id']  = (int)$user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name']  = $user['username'];

        if ($remember) {
            $token = bin2hex(random_bytes(32));
            setcookie('remember_token', $token, [
                'expires'  => time() + 60 * 60 * 24 * 30,
                'path'     => '/',
                'secure'   => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }

        $_SESSION['login_attempts']     = 0;
        $_SESSION['login_locked_until'] = 0;

        // DÜZELTİLDİ: Artık dashboard'a değil, home.php'ye gidiyor.
        header('Location: home.php');
        exit;

    } else {
        $_SESSION['login_attempts']++;
        if ($_SESSION['login_attempts'] >= 5) {
            $_SESSION['login_locked_until'] = time() + 300;
            $_SESSION['login_attempts']     = 0;
            $_SESSION['login_error']        = 'Çok fazla hatalı deneme. 5 dakika sonra tekrar dene.';
        } else {
            $_SESSION['login_error'] = 'E-posta veya şifre hatalı.';
        }
        $_SESSION['old_email'] = $email;
        header('Location: login.php');
        exit;
    }

} catch (PDOException $e) {
    $_SESSION['login_error'] = 'Bir sistem hatası oluştu. Lütfen sonra tekrar dene.';
    header('Location: login.php');
    exit;
}