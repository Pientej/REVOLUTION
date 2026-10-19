<?php
require __DIR__ . '/config.php';
if (empty($_SESSION['user_id'])) exit;

$uid = $_SESSION['user_id'];
$groupId = (int)($_GET['id'] ?? 0);

// Yönetici mi kontrol et
$stmt = $pdo->prepare('SELECT * FROM `groups` WHERE id = ? AND owner_id = ?');
$stmt->execute([$groupId, $uid]);
$group = $stmt->fetch();
if (!$group) die("Yetkiniz yok veya grup bulunamadı. <a href='groups.php'>Geri Dön</a>");

// Bekleyen istekler
$stmt = $pdo->prepare('SELECT u.id, u.username, u.email FROM users u JOIN group_members gm ON u.id = gm.user_id WHERE gm.group_id = ? AND gm.status = "pending"');
$stmt->execute([$groupId]);
$pending = $stmt->fetchAll();

// Onaylı üyeler
$stmt = $pdo->prepare('SELECT u.id, u.username, u.email FROM users u JOIN group_members gm ON u.id = gm.user_id WHERE gm.group_id = ? AND gm.status = "approved"');
$stmt->execute([$groupId]);
$members = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Grup Yönetimi - <?= htmlspecialchars($group['name']) ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>
<body class="home-body">
<header class="topbar"><div class="topbar-left"><span class="topbar-brand">YÖNETİM: <?= htmlspecialchars($group['name']) ?></span></div></header>
<div class="home-layout">
    <main class="map-area" style="background:#0d0b0b; padding: 40px; display:block; overflow-y:auto;">
        <h2 style="color:var(--yellow);">Bekleyen Katılım İstekleri</h2>
        <?php if(count($pending)==0): ?><p style="color:#888;">İstek yok.</p><?php endif; ?>
        <?php foreach($pending as $p): ?>
            <div style="background:#1a1a1a; padding:15px; margin-bottom:10px; border-radius:10px; display:flex; justify-content:space-between; align-items:center;">
                <span><?= htmlspecialchars($p['username']) ?> (<?= htmlspecialchars($p['email']) ?>)</span>
                <a href="group_request.php?id=<?= $groupId ?>&action=approve&user_id=<?= $p['id'] ?>" style="background:#22c55e; color:#fff; padding:8px 15px; border-radius:5px; text-decoration:none;">Onayla</a>
            </div>
        <?php endforeach; ?>

        <h2 style="color:var(--yellow); margin-top: 40px;">Mevcut Üyeler</h2>
        <?php foreach($members as $m): ?>
            <div style="background:#1a1a1a; padding:15px; margin-bottom:10px; border-radius:10px; display:flex; justify-content:space-between; align-items:center;">
                <span><?= htmlspecialchars($m['username']) ?> <?= $m['id'] == $uid ? '(Sen)' : '' ?></span>
                <?php if($m['id'] != $uid): ?>
                    <a href="group_request.php?id=<?= $groupId ?>&action=kick&user_id=<?= $m['id'] ?>" style="background:#ff1238; color:#fff; padding:8px 15px; border-radius:5px; text-decoration:none;" onclick="return confirm('Bu üyeyi atmak istediğinize emin misiniz?')">Gruptan At</a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <br><a href="groups.php" style="color:var(--yellow);">← Gruplarıma Dön</a>
    </main>
</div>
</body>
</html>