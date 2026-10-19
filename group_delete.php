<?php
require __DIR__ . '/config.php';
if (empty($_SESSION['user_id'])) exit;

$uid = $_SESSION['user_id'];
$groupId = (int)($_GET['id'] ?? 0);

// Sadece grup yöneticisi silebilir
$stmt = $pdo->prepare('SELECT id FROM `groups` WHERE id = ? AND owner_id = ?');
$stmt->execute([$groupId, $uid]);

if ($stmt->fetch()) {
    // Veritabanından sil (Foreign key CASCADE sayesinde mesajlar ve üyeler de silinir)
    $del = $pdo->prepare('DELETE FROM `groups` WHERE id = ?');
    $del->execute([$groupId]);
}

header("Location: groups.php");
exit;