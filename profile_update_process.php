<?php
require __DIR__ . '/config.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: profile_edit.php');
    exit;
}

if (!csrf_verify($_POST['csrf'] ?? null)) {
    die("CSRF Hatası");
}

$userId = $_SESSION['user_id'];
$username = trim($_POST['username']);
$email = trim($_POST['email']);
$bio = trim($_POST['bio']);
$gender = $_POST['gender'] ?? null;

// Doğrulama
if (strlen($username) < 3) {
    $_SESSION['profile_error'] = 'Takma ad en az 3 karakter olmalıdır.';
    header('Location: profile_edit.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['profile_error'] = 'Geçerli bir e-posta adresi girin.';
    header('Location: profile_edit.php');
    exit;
}

// Başkası bu ismi veya e-postayı almış mı?
$stmt = $pdo->prepare('SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?');
$stmt->execute([$username, $email, $userId]);
if ($stmt->fetch()) {
    $_SESSION['profile_error'] = 'Bu takma ad veya e-posta adresi başka bir kullanıcı tarafından kullanılıyor.';
    header('Location: profile_edit.php');
    exit;
}

// Güncelle
$stmt = $pdo->prepare('UPDATE users SET username = ?, email = ?, bio = ?, gender = ? WHERE id = ?');
$stmt->execute([$username, $email, $bio, $gender, $userId]);

// Session'daki ismi güncelle ki sağ üstte yeni isim görünsün
$_SESSION['user_name'] = $username;

header('Location: profile.php');
exit;