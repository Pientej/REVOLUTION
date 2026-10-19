<?php
require __DIR__ . '/config.php';
if (empty($_SESSION['user_id'])) exit;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf'] ?? null)) die("CSRF Hatası");

    $uid = $_SESSION['user_id'];
    
    // KURAL: KULLANICININ ZATEN BİR GRUBU VAR MI?
    $stmtCheck = $pdo->prepare('SELECT id FROM `groups` WHERE owner_id = ?');
    $stmtCheck->execute([$uid]);
    if ($stmtCheck->fetch()) {
        die("Hata: Sadece 1 grup kurabilirsiniz. Zaten mevcut bir grubunuz var. <a href='groups.php'>Geri Dön</a>");
    }

    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);
    $lat = (float)($_POST['latitude'] ?? 0);
    $lng = (float)($_POST['longitude'] ?? 0);

    // Aynı isim var mı kontrol et
    $stmt = $pdo->prepare('SELECT id FROM `groups` WHERE name = ?');
    $stmt->execute([$name]);
    if ($stmt->fetch()) {
        die("Hata: Bu isimde bir grup zaten var. Lütfen başka bir isim seçin. <a href='groups.php'>Geri Dön</a>");
    }

    // Grubu oluştur
    $stmt = $pdo->prepare('INSERT INTO `groups` (owner_id, name, description, latitude, longitude) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$uid, $name, $desc, $lat, $lng]);
    $groupId = $pdo->lastInsertId();

    // Yöneticiyi otomatik üye yap
    $stmt = $pdo->prepare('INSERT INTO group_members (group_id, user_id, status) VALUES (?, ?, "approved")');
    $stmt->execute([$groupId, $uid]);

    header("Location: group_chat.php?id=$groupId");
    exit;
}