<?php
require __DIR__ . '/config.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];
$search = trim($_GET['search'] ?? '');

// KULLANICININ ZATEN BİR GRUBU VAR MI?
$stmtOwner = $pdo->prepare('SELECT id FROM `groups` WHERE owner_id = ?');
$stmtOwner->execute([$userId]);
$hasGroup = $stmtOwner->fetch(); 

$stmt = $pdo->prepare('SELECT g.* FROM `groups` g JOIN group_members gm ON g.id = gm.group_id WHERE gm.user_id = ? AND gm.status = "approved"');
$stmt->execute([$userId]);
$myGroups = $stmt->fetchAll();

if ($search !== '') {
    $stmt = $pdo->prepare('SELECT g.*, gm.status as my_status FROM `groups` g LEFT JOIN group_members gm ON g.id = gm.group_id AND gm.user_id = ? WHERE g.name LIKE ? ORDER BY g.created_at DESC');
    $stmt->execute([$userId, "%$search%"]);
    $searchResults = $stmt->fetchAll();
} else {
    $stmt = $pdo->prepare('SELECT g.*, gm.status as my_status FROM `groups` g LEFT JOIN group_members gm ON g.id = gm.group_id AND gm.user_id = ? ORDER BY g.created_at DESC LIMIT 10');
    $stmt->execute([$userId]);
    $searchResults = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Révolution | Gruplarım</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="home.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        .group-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .group-card { background: #1a1a1a; border: 1px solid #ff123840; border-radius: 15px; padding: 20px; }
        .group-card h3 { color: var(--yellow); margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
        .group-card p { color: #aaa; font-size: 13px; margin-bottom: 15px; }
        .search-box { display: flex; gap: 10px; margin-bottom: 30px; max-width: 500px; }
        .search-box input { flex: 1; padding: 12px; border-radius: 8px; border: 1px solid #ffffff20; background: #ffffff04; color: #fff; outline: none; }
        .search-box button { padding: 12px 20px; border-radius: 8px; border: none; background: var(--red); color: #fff; font-weight: bold; cursor: pointer; }
        .btn-action { display: inline-block; padding: 8px 15px; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: bold; text-align: center; }
        .btn-primary { background: #ffd500; color: #000; }
        .btn-secondary { background: #22c55e20; color: #22c55e; border: 1px solid #22c55e; }
        .btn-danger { background: #ff123820; color: #ff1238; border: 1px solid #ff1238; }
        .btn-disabled { background: #ffffff10; color: #888; cursor: not-allowed; }
        #modalMap { height: 200px; width: 100%; border-radius: 10px; margin-top: 10px; z-index: 1; }
    </style>
</head>
<body class="home-body">

<header class="topbar">
    <div class="topbar-left"><i class="fa-solid fa-crown" style="color:var(--yellow);"></i><span class="topbar-brand">RÉVOLUTION</span></div>
    <div class="topbar-right"><span class="topbar-user"><i class="fa-regular fa-user"></i> <?= htmlspecialchars($userName) ?></span><a href="logout.php" class="topbar-logout"><i class="fa-solid fa-right-from-bracket"></i></a></div>
</header>

<div class="home-layout">
    <aside class="sidebar">
        <nav class="side-nav">
            <a href="home.php" class="side-link"><i class="fa-solid fa-map-location-dot"></i><span>Harita</span></a>
            <a href="my_events.php" class="side-link"><i class="fa-solid fa-calendar-days"></i><span>Etkinliklerim</span></a>
            <a href="groups.php" class="side-link active"><i class="fa-solid fa-users"></i><span>Gruplarım</span></a>
            <a href="profile.php" class="side-link"><i class="fa-regular fa-circle-user"></i><span>Profil</span></a>
        </nav>
        
        <!-- BURASI DEĞİŞTİ: Grubu yoksa buton gözükür -->
        <?php if (!$hasGroup): ?>
            <button class="create-event-btn" onclick="openCreateModal()">
                <i class="fa-solid fa-plus"></i> Grup Oluştur
            </button>
        <?php endif; ?>
    </aside>

    <main class="map-area" style="background:#0d0b0b; padding: 40px; overflow-y:auto; display:block;">
        
        <h2 style="color:#fff; margin-bottom: 20px;">Gruplarım</h2>
        <?php if (count($myGroups) === 0): ?>
            <p style="color:#888; margin-bottom:30px;">Henüz bir gruba üye değilsin.</p>
        <?php else: ?>
            <div class="group-grid" style="margin-bottom: 50px;">
                <?php foreach ($myGroups as $g): ?>
                    <div class="group-card">
                        <h3>
                            <?= htmlspecialchars($g['name']) ?>
                            <?php if ($g['owner_id'] == $userId): ?>
                                <i class="fa-solid fa-crown" title="Yönetici" style="color:var(--yellow);"></i>
                            <?php endif; ?>
                        </h3>
                        <p><?= htmlspecialchars($g['description']) ?></p>
                        
                        <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:15px;">
                            <a href="group_chat.php?id=<?= $g['id'] ?>" class="btn-action btn-primary">Sohbete Git</a>
                            <?php if ($g['owner_id'] == $userId): ?>
                                <a href="group_manage.php?id=<?= $g['id'] ?>" class="btn-action btn-secondary">Yönet</a>
                                <a href="group_delete.php?id=<?= $g['id'] ?>" class="btn-action btn-danger" onclick="return confirm('Bu grubu tamamen silmek istediğinize emin misiniz?')">Grubu Sil</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <hr style="border-color:#333; margin-bottom: 30px;">

        <h2 style="color:#fff; margin-bottom: 20px;">Keşfet / Grup Ara</h2>
        <form method="GET" class="search-box">
            <input type="text" name="search" placeholder="Grup adı ara..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit"><i class="fa-solid fa-search"></i> Ara</button>
        </form>

        <div class="group-grid">
            <?php foreach ($searchResults as $g): ?>
                <div class="group-card">
                    <h3><?= htmlspecialchars($g['name']) ?></h3>
                    <p><?= htmlspecialchars($g['description']) ?></p>
                    
                    <?php if ($g['owner_id'] == $userId): ?>
                        <span class="btn-action btn-disabled">Yöneticisi Sensin</span>
                    <?php elseif ($g['my_status'] == 'approved'): ?>
                        <span class="btn-action btn-disabled">Zaten Üyesin</span>
                    <?php elseif ($g['my_status'] == 'pending'): ?>
                        <span class="btn-action btn-disabled">Onay Bekliyor</span>
                    <?php else: ?>
                        <a href="group_request.php?id=<?= $g['id'] ?>&action=join" class="btn-action btn-secondary">Davet İste (Katıl)</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</div>

<!-- GRUP OLUŞTURMA MODAL -->
<div class="modal-overlay" id="createGroupModal" style="display:none; pointer-events:auto;">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Yeni Grup Kur</h3>
            <button class="modal-close" onclick="document.getElementById('createGroupModal').style.display='none'">&times;</button>
        </div>
        <form action="group_create_process.php" method="POST">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <div class="modal-field">
                <label>Grup Adı (Özel ve Tek Olmalı)</label>
                <input type="text" name="name" required maxlength="100" placeholder="Örn: Yazılımcılar Kulübü">
            </div>
            <div class="modal-field">
                <label>Açıklama</label>
                <textarea name="description" rows="3" required></textarea>
            </div>
            <div class="modal-field">
                <label>Gizli Toplanma Noktası (Haritadan Seçin)</label>
                <div id="modalMap"></div>
                <input type="hidden" name="latitude" id="groupLat" required>
                <input type="hidden" name="longitude" id="groupLng" required>
                <small id="mapCoordsText" style="color:#ffd500; display:block; margin-top:5px;">Lütfen haritadan bir nokta seçin.</small>
            </div>
            <button type="submit" class="modal-submit">Grubu Oluştur</button>
        </form>
    </div>
</div>

<script>
    let modalMap, modalMarker;
    function openCreateModal() {
        document.getElementById('createGroupModal').style.display = 'flex';
        if (!modalMap) {
            modalMap = L.map('modalMap').setView([41.0082, 28.9784], 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(modalMap);
            modalMap.on('click', function(e) {
                document.getElementById('groupLat').value = e.latlng.lat;
                document.getElementById('groupLng').value = e.latlng.lng;
                document.getElementById('mapCoordsText').innerHTML = `Seçildi: ${e.latlng.lat.toFixed(5)}, ${e.latlng.lng.toFixed(5)}`;
                if (modalMarker) modalMap.removeLayer(modalMarker);
                modalMarker = L.marker([e.latlng.lat, e.latlng.lng]).addTo(modalMap);
            });
        }
        setTimeout(() => { modalMap.invalidateSize(); }, 100);
    }
</script>
</body>
</html>