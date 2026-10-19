<?php
require __DIR__ . '/config.php';
if (empty($_SESSION['user_id'])) exit;

$uid = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';
$groupId = (int)($_GET['id'] ?? 0);
$targetUser = (int)($_GET['user_id'] ?? 0);

if ($groupId > 0 && $action === 'join') {
    // İstek gönder (pending)
    $stmt = $pdo->prepare('INSERT IGNORE INTO group_members (group_id, user_id, status) VALUES (?, ?, "pending")');
    $stmt->execute([$groupId, $uid]);
    header("Location: groups.php");
    exit;
}

if ($action === 'approve' && $groupId > 0 && $targetUser > 0) {
    // Sadece yönetici onaylayabilir
    $stmt = $pdo->prepare('SELECT id FROM `groups` WHERE id = ? AND owner_id = ?');
    $stmt->execute([$groupId, $uid]);
    if ($stmt->fetch()) {
        $upd = $pdo->prepare('UPDATE group_members SET status = "approved" WHERE group_id = ? AND user_id = ?');
        $upd->execute([$groupId, $targetUser]);
    }
    header("Location: group_manage.php?id=$groupId");
    exit;
}

if ($action === 'kick' && $groupId > 0 && $targetUser > 0) {
    // Sadece yönetici atabilir
    $stmt = $pdo->prepare('SELECT id FROM `groups` WHERE id = ? AND owner_id = ?');
    $stmt->execute([$groupId, $uid]);
    if ($stmt->fetch()) {
        $del = $pdo->prepare('DELETE FROM group_members WHERE group_id = ? AND user_id = ? AND user_id != ?');
        $del->execute([$groupId, $targetUser, $uid]); // Kendini atamaz
    }
    header("Location: group_manage.php?id=$groupId");
    exit;
}