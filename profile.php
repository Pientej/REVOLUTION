<?php
require __DIR__ . '/config.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$myId = $_SESSION['user_id'];
$myName = $_SESSION['user_name'];

// Profiline bakılacak kullanıcının ID'si (Linkten gelir, gelmezse kendi profilin)
$profileId = isset($_GET['id']) ? (int)$_GET['id'] : $myId;
$isMyProfile = ($profileId === $myId);

// Kullanıcı bilgilerini çek
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$profileId]);
$user = $stmt->fetch();

if (!$user) {
    die("Kullanıcı bulunamadı. <a href='home.php'>Geri Dön</a>");
}

// Kullanıcının katıldığı etkinlikler
$stmt = $pdo->prepare('
    SELECT e.* FROM events e 
    JOIN event_participants ep ON e.id = ep.event_id 
    WHERE ep.user_id = ? AND ep.status = "katiliyorum" AND e.event_date >= NOW()
    ORDER BY e.event_date ASC
');
$stmt->execute([$profileId]);
$events = $stmt->fetchAll();

// Kullanıcının üye olduğu gruplar
$stmt = $pdo->prepare('
    SELECT g.* FROM `groups` g 
    JOIN group_members gm ON g.id = gm.group_id 
    WHERE gm.user_id = ? AND gm.status = "approved"
');
$stmt->execute([$profileId]);
$groups = $stmt->fetchAll();

// Baş harf
$initial = mb_strtoupper(mb_substr($user['username'], 0, 1, 'UTF-8'), 'UTF-8');
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Révolution | <?= htmlspecialchars($user['username']) ?> Profili</title>
    
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="home.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    
    <style>
        /* ====== PROFİL SAYFASI İÇİN ÖZEL KAYDIRMA ÇÖZÜMÜ ====== */
        body, html {
            height: 100%;
            margin: 0;
            overflow: hidden; /* Ana gövdenin kaymasını engelle */
        }
        
        .home-layout {
            height: calc(100vh - 60px); /* 60px üst bar yüksekliği */
            display: flex;
            overflow: hidden;
        }

        /* SAĞ TARAFTAKİ İÇERİK ALANININ KAYDIRILABİLİR OLMASI */
        .profile-content-area {
            flex: 1;
            height: 100%;
            overflow-y: auto; /* Kaydırma çubuğu burada çıkacak */
            padding: 40px;
            background: #0d0b0b;
        }

        /* TASARIM */
        .profile-header { 
            display: flex; 
            align-items: center; 
            gap: 30px; 
            background: #1a1a1a; 
            padding: 40px; 
            border-radius: 20px; 
            border: 1px solid #ff123840; 
            margin-bottom: 30px; 
        }
        .profile-avatar { 
            width: 120px; 
            height: 120px; 
            border-radius: 50%; 
            background: linear-gradient(135deg, var(--red), var(--yellow)); 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            font-size: 50px; 
            font-weight: 800; 
            color: #fff; 
            border: 4px solid #0d0b0b; 
            box-shadow: 0 0 20px rgba(255,18,56,0.5); 
            flex-shrink: 0; 
        }
        .profile-info h1 { font-size: 32px; color: #fff; margin-bottom: 5px; }
        .profile-info p { color: #aaa; font-size: 14px; margin-bottom: 10px; }
        .profile-bio { color: #ccc; font-style: italic; font-size: 14px; border-left: 3px solid var(--yellow); padding-left: 10px; margin-top: 15px; }
        .section-title { color: var(--yellow); font-size: 20px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        
        .card-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .card { background: #1a1a1a; border: 1px solid #333; border-radius: 15px; padding: 20px; transition: 0.3s; }
        .card:hover { border-color: var(--red); }
        .card h3 { color: #fff; margin-bottom: 10px; font-size: 16px; }
        .card p { color: #888; font-size: 12px; }

        /* MOBİL UYUMLULUK */
        @media (max-width: 800px) {
            .home-layout { flex-direction: column; }
            .sidebar {
                position: fixed; bottom: 0; left: 0; width: 100%; height: 65px;
                background: #0d0b0b; border-top: 1px solid #ff123840; border-right: none;
                display: flex; flex-direction: row; justify-content: space-around; align-items: center;
                padding: 0; z-index: 1000;
            }
            .side-nav { flex-direction: row; width: 100%; justify-content: space-around; gap: 0; }
            .side-link { flex-direction: column; gap: 4px; font-size: 10px; padding: 8px; background: transparent !important; }
            .side-link.active { color: var(--yellow); }
            .side-link i { font-size: 18px; }
            
            /* Mobilde alt menü için boşluk */
            .profile-content-area { padding: 20px; padding-bottom: 85px; }
            
            .profile-header { flex-direction: column; text-align: center; padding: 25px; }
            .profile-bio { text-align: left; }
            .profile-info h1 { font-size: 24px; }
        }
    </style>
</head>
<body class="home-body">

<header class="topbar">
    <div class="topbar-left"><i class="fa-solid fa-crown" style="color:var(--yellow);"></i><span class="topbar-brand">RÉVOLUTION</span></div>
    <div class="topbar-right">
        <span class="topbar-user"><i class="fa-regular fa-user"></i> <?= htmlspecialchars($myName) ?></span>
        <a href="logout.php" class="topbar-logout"><i class="fa-solid fa-right-from-bracket"></i></a>
    </div>
</header>

<div class="home-layout">
    <aside class="sidebar">
        <nav class="side-nav">
            <a href="home.php" class="side-link"><i class="fa-solid fa-map-location-dot"></i><span>Harita</span></a>
            <a href="my_events.php" class="side-link"><i class="fa-solid fa-calendar-days"></i><span>Etkinliklerim</span></a>
            <a href="groups.php" class="side-link"><i class="fa-solid fa-users"></i><span>Gruplarım</span></a>
            <a href="profile.php" class="side-link active"><i class="fa-regular fa-circle-user"></i><span>Profil</span></a>
        </nav>
    </aside>

    <!-- KAYDIRILABİLİR İÇERİK ALANI -->
    <main class="profile-content-area">
        
        <!-- PROFİL ÜST BİLGİ -->
        <div class="profile-header">
            <div class="profile-avatar"><?= $initial ?></div>
            <div class="profile-info">
                <h1><?= htmlspecialchars($user['username']) ?></h1>
                <p><i class="fa-regular fa-envelope"></i> <?= htmlspecialchars($user['email']) ?></p>
                <p><i class="fa-regular fa-calendar"></i> Katılım: <?= date('d.m.Y', strtotime($user['created_at'])) ?></p>
                
                <?php if(!empty($user['bio'])): ?>
                    <div class="profile-bio">"<?= htmlspecialchars($user['bio']) ?>"</div>
                <?php endif; ?>
            </div>
            
            <?php if ($isMyProfile): ?>
                <div style="margin-left: auto;">
                    <a href="profile_edit.php" style="background:#ffd500; color:#000; padding:10px 20px; border-radius:8px; text-decoration:none; font-weight:bold; font-size:14px; display:inline-block; white-space:nowrap;"><i class="fa-solid fa-pen"></i> Profili Düzenle</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- KATILDIĞI ETKİNLİKLER -->
        <h2 class="section-title"><i class="fa-solid fa-calendar-check"></i> Katıldığı Etkinlikler</h2>
        <?php if (count($events) === 0): ?>
            <p style="color:#666; margin-bottom:30px;">Henüz katıldığı bir etkinlik yok.</p>
        <?php else: ?>
            <div class="card-grid">
                <?php foreach ($events as $ev): ?>
                    <div class="card">
                        <h3><?= htmlspecialchars($ev['title']) ?></h3>
                        <p><i class="fa-solid fa-tag"></i> <?= htmlspecialchars($ev['event_type']) ?></p>
                        <p style="margin-top:5px; color:var(--yellow);"><i class="fa-regular fa-clock"></i> <?= date('d.m.Y H:i', strtotime($ev['event_date'])) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- ÜYE OLDUĞU GRUPLAR -->
        <h2 class="section-title"><i class="fa-solid fa-users"></i> Üye Olduğu Gruplar</h2>
        <?php if (count($groups) === 0): ?>
            <p style="color:#666;">Henüz bir gruba üye değil.</p>
        <?php else: ?>
            <div class="card-grid">
                <?php foreach ($groups as $g): ?>
                    <div class="card">
                        <h3><?= htmlspecialchars($g['name']) ?></h3>
                        <p><?= htmlspecialchars($g['description']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </main>
</div>

</body>
</html>