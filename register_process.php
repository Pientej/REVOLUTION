<?php
require __DIR__ . '/config.php';

// Sadece POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

// CSRF kontrolü
if (!csrf_verify($_POST['csrf'] ?? null)) {
    $_SESSION['register_error'] = 'Güvenlik doğrulaması başarısız. Tekrar dene.';
    header('Location: register.php');
    exit;
}

// Girdileri al
$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm  = $_POST['password_confirm'] ?? '';
$terms    = !empty($_POST['terms']);

// Eski değerleri sakla (hata olursa forma geri doldurulsun)
$_SESSION['register_old'] = [
    'username' => $username,
    'email'    => $email,
];

// --- DOĞRULAMALAR ---

// 1) Boş alan
if ($username === '' || $email === '' || $password === '' || $confirm === '') {
    $_SESSION['register_error'] = 'Lütfen tüm alanları doldur.';
    header('Location: register.php');
    exit;
}

// 2) Kullanım şartları
if (!$terms) {
    $_SESSION['register_error'] = 'Devam etmek için kullanım şartlarını kabul etmelisin.';
    header('Location: register.php');
    exit;
}

// 3) Takma ad uzunluğu (3-30)
if (mb_strlen($username) < 3 || mb_strlen($username) > 30) {
    $_SESSION['register_error'] = 'Takma ad 3 ile 30 karakter arasında olmalı.';
    header('Location: register.php');
    exit;
}

// 4) Takma ad karakter seti: harf, rakam, alt çizgi
if (!preg_match('/^[a-zA-Z0-9_]+$/u', $username)) {
    $_SESSION['register_error'] = 'Takma ad sadece harf, rakam ve alt çizgi (_) içerebilir.';
    header('Location: register.php');
    exit;
}

// 5) E-posta formatı
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['register_error'] = 'Geçerli bir e-posta adresi gir.';
    header('Location: register.php');
    exit;
}

// 6) Şifre uzunluğu
if (strlen($password) < 8) {
    $_SESSION['register_error'] = 'Şifre en az 8 karakter olmalı.';
    header('Location: register.php');
    exit;
}

// 7) Şifre eşleşmesi
if ($password !== $confirm) {
    $_SESSION['register_error'] = 'Şifreler eşleşmiyor.';
    header('Location: register.php');
    exit;
}

// 8) Şifre gücü (en az 1 harf + 1 rakam)
if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
    $_SESSION['register_error'] = 'Şifre en az bir harf ve bir rakam içermeli.';
    header('Location: register.php');
    exit;
}

// --- VERİTABANI KONTROLLERİ ---

try {
    // E-posta zaten var mı?
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $_SESSION['register_error'] = 'Bu e-posta adresi zaten kayıtlı.';
        header('Location: register.php');
        exit;
    }

    // Takma ad zaten var mı?
    $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        $_SESSION['register_error'] = 'Bu takma ad zaten alınmış.';
        header('Location: register.php');
        exit;
    }

    // Şifreyi hashle
    $hash = password_hash($password, PASSWORD_DEFAULT);

    // Kayıt ekle
    $stmt = $pdo->prepare(
        'INSERT INTO users (username, email, password) VALUES (?, ?, ?)'
    );
    $stmt->execute([$username, $email, $hash]);

    // Başarı: kullanıcıyı login sayfasına yönlendir
    $_SESSION['login_success'] = 'Hesabın başarıyla oluşturuldu! Şimdi giriş yapabilirsin.';
    unset($_SESSION['register_old']);
    header('Location: login.php');
    exit;

} catch (PDOException $e) {
    $_SESSION['register_error'] = 'Bir sistem hatası oluştu. Lütfen sonra tekrar dene.';
    header('Location: register.php');
    exit;
}