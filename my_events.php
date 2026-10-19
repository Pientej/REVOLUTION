<?php
require __DIR__ . '/config.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

// 1. ADIM: Zamanı geçmiş etkinlikleri anında veritabanından temizle
$cleanup = $pdo->prepare('DELETE FROM events WHERE event_date < NOW()');
$cleanup->execute();

// 2. ADIM: Sadece aktif (gelecekteki) etkinlikleri listele
$stmt = $pdo->prepare('SELECT * FROM events WHERE user_id = ? AND event_date >= NOW() ORDER BY event_date ASC');
$stmt->execute([$userId]);
$events = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Révolution | Etkinliklerim</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="home.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>
<body class="home-body">

<header class="topbar">
    <div class="topbar-left">
        <i class="fa-solid fa-crown" style="color:var(--yellow);"></i>
        <span class="topbar-brand">RÉVOLUTION</span>
    </div>
    <div class="topbar-right">
        <span class="topbar-user"><i class="fa-regular fa-user"></i> <?= htmlspecialchars($userName) ?></span>
        <a href="logout.php" class="topbar-logout"><i class="fa-solid fa-right-from-bracket"></i></a>
    </div>
</header>

<div class="home-layout">
    <aside class="sidebar">
        <nav class="side-nav">
            <a href="home.php" class="side-link"><i class="fa-solid fa-map-location-dot"></i><span>Harita</span></a>
            <a href="my_events.php" class="side-link active"><i class="fa-solid fa-calendar-days"></i><span>Etkinliklerim</span></a>
            <a href="groups.php" class="side-link"><i class="fa-solid fa-users"></i><span>Gruplarım</span></a>
            <a href="profile.php" class="side-link"><i class="fa-regular fa-circle-user"></i><span>Profil</span></a>
        </nav>
    </aside>

    <main class="map-area" style="background:#0d0b0b; padding: 40px; overflow-y:auto; display:block;">
        <h2 style="color:var(--yellow); margin-bottom:20px;">Etkinliklerim</h2>
        
        <?php if (isset($_SESSION['event_success'])): ?>
            <div class="alert alert-success" style="max-width:600px;"><?= htmlspecialchars($_SESSION['event_success']) ?></div>
            <?php unset($_SESSION['event_success']); ?>
        <?php endif; ?>

        <?php if (count($events) === 0): ?>
            <p style="color:#888;">Henüz aktif bir etkinliğiniz yok.</p>
        <?php else: ?>
            <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap:20px;">
                <?php foreach ($events as $ev): ?>
                    <div style="background:#1a1a1a; border:1px solid #ff123840; border-radius:15px; padding:20px;">
                        <h3 style="color:#fff; margin-bottom:10px;"><?= htmlspecialchars($ev['title']) ?></h3>
                        <p style="color:var(--yellow); font-size:12px; margin-bottom:5px;">
                            <i class="fa-solid fa-tag"></i> <?= htmlspecialchars($ev['event_type']) ?>
                        </p>
                        
                        <p style="color:#aaa; font-size:12px; margin-bottom:15px;">
                            <i class="fa-regular fa-clock"></i> <?= date('d.m.Y H:i', strtotime($ev['event_date'])) ?>
                        </p>
                        
                        <p style="color:#ccc; font-size:13px; margin-bottom:20px;"><?= nl2br(htmlspecialchars($ev['description'])) ?></p>
                        
                        <a href="event_delete.php?id=<?= $ev['id'] ?>" onclick="return confirm('Bu etkinliği silmek istediğinize emin misiniz?');" style="display:inline-flex; align-items:center; gap:8px; background:#ff123820; color:#ff1238; padding:8px 15px; border-radius:8px; text-decoration:none; font-size:12px; font-weight:bold;">
                            <i class="fa-solid fa-trash"></i> Etkinliği Sil
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</div>

</body>
</html>