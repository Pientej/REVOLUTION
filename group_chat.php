<?php
require __DIR__ . '/config.php';
if (empty($_SESSION['user_id'])) exit;

$uid = $_SESSION['user_id'];
$groupId = (int)($_GET['id'] ?? 0);

// Kullanıcı bu grubun onaylı üyesi mi?
$stmt = $pdo->prepare('SELECT g.*, gm.status FROM `groups` g JOIN group_members gm ON g.id = gm.group_id WHERE g.id = ? AND gm.user_id = ?');
$stmt->execute([$groupId, $uid]);
$group = $stmt->fetch();

if (!$group || $group['status'] !== 'approved') {
    die("Bu gruba erişim yetkiniz yok. <a href='groups.php'>Geri Dön</a>");
}

// Mesaj Gönder
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['csrf'] ?? null)) die("CSRF Hatası");
    $msg = trim($_POST['message']);
    $media = trim($_POST['media_url']);

    if ($msg !== '' || $media !== '') {
        $stmt = $pdo->prepare('INSERT INTO group_messages (group_id, user_id, message, media_url) VALUES (?, ?, ?, ?)');
        $stmt->execute([$groupId, $uid, $msg, $media]);
    }
    header("Location: group_chat.php?id=$groupId");
    exit;
}

// Mesajları çek
$stmt = $pdo->prepare('SELECT m.*, u.username FROM group_messages m JOIN users u ON m.user_id = u.id WHERE m.group_id = ? ORDER BY m.created_at ASC');
$stmt->execute([$groupId]);
$messages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($group['name']) ?> - Sohbet</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        .chat-container { display: flex; flex-direction: column; height: 100vh; max-height: 100vh; }
        .chat-header { background: #0d0b0b; padding: 20px; border-bottom: 1px solid #ff123840; }
        .secret-loc { background: #ffd50020; border: 1px solid #ffd500; color: var(--yellow); padding: 10px; border-radius: 8px; margin-top: 10px; font-size: 13px; display: inline-block; }
        .chat-messages { flex: 1; padding: 20px; overflow-y: auto; background: #080808; }
        .msg-box { margin-bottom: 15px; display: flex; flex-direction: column; }
        .msg-box.mine { align-items: flex-end; }
        .msg-bubble { background: #1a1a1a; padding: 10px 15px; border-radius: 10px; max-width: 70%; color: #fff; font-size: 14px; }
        .msg-box.mine .msg-bubble { background: #ff123820; border: 1px solid #ff1238; }
        .msg-user { font-size: 11px; color: #888; margin-bottom: 4px; }
        .msg-media { max-width: 100%; border-radius: 8px; margin-top: 8px; display: block; }
        .chat-input-area { padding: 20px; background: #0d0b0b; display: flex; gap: 10px; border-top: 1px solid #ffffff10; }
        .chat-input-area input[type="text"] { padding: 12px; border-radius: 8px; border: 1px solid #333; background: #111; color: #fff; outline: none; }
        .chat-input-area input[name="message"] { flex: 1; }
        .chat-input-area input[name="media_url"] { width: 200px; font-size: 12px; }
        .chat-input-area button { background: var(--red); color: #fff; border: none; padding: 0 20px; border-radius: 8px; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body class="home-body">
<div class="chat-container">
    
    <div class="chat-header">
        <h2 style="color:var(--yellow); margin:0;"><i class="fa-solid fa-users"></i> <?= htmlspecialchars($group['name']) ?></h2>
        <p style="color:#aaa; font-size:13px; margin:5px 0;"><?= htmlspecialchars($group['description']) ?></p>
        
        <!-- BURASI ÖNEMLİ: Gizli konum sadece bu sayfayı (yani üyeleri) görebilir -->
        <?php if (!empty($group['latitude']) && !empty($group['longitude'])): ?>
            <div class="secret-loc">
                <i class="fa-solid fa-location-dot"></i> <strong>Gizli Buluşma Noktası:</strong> <?= $group['latitude'] ?>, <?= $group['longitude'] ?>
                <br><small>(Bu konum sadece grup üyelerine gösterilir)</small>
            </div>
        <?php endif; ?>
        
        <div style="margin-top:15px;">
            <a href="groups.php" style="color:#aaa; text-decoration:none; font-size:12px;">← Gruplara Dön</a>
            <?php if($group['owner_id'] == $uid): ?>
                <a href="group_manage.php?id=<?= $groupId ?>" style="color:var(--yellow); text-decoration:none; font-size:12px; margin-left:15px;"><i class="fa-solid fa-gear"></i> Yönetim Paneli</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="chat-messages" id="chatMessages">
        <?php foreach($messages as $m): ?>
            <div class="msg-box <?= $m['user_id'] == $uid ? 'mine' : '' ?>">
                <div class="msg-user"><?= htmlspecialchars($m['username']) ?> - <?= date('H:i', strtotime($m['created_at'])) ?></div>
                <div class="msg-bubble">
                    <?= nl2br(htmlspecialchars($m['message'])) ?>
                    
                    <?php if(!empty($m['media_url'])): ?>
                        <?php 
                            $ext = strtolower(pathinfo($m['media_url'], PATHINFO_EXTENSION));
                            if(in_array($ext, ['jpg','jpeg','png','gif','webp'])): 
                        ?>
                            <img src="<?= htmlspecialchars($m['media_url']) ?>" class="msg-media" alt="Paylaşılan Fotoğraf">
                        <?php else: ?>
                            <a href="<?= htmlspecialchars($m['media_url']) ?>" target="_blank" style="display:block; margin-top:8px; color:var(--yellow); font-size:12px;">
                                <i class="fa-solid fa-link"></i> Paylaşılan Medyayı Gör (Video/Dosya)
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <form method="POST" class="chat-input-area">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
        <input type="text" name="media_url" placeholder="Foto/Video Linki">
        <input type="text" name="message" placeholder="Mesaj yaz..." autocomplete="off">
        <button type="submit"><i class="fa-solid fa-paper-plane"></i></button>
    </form>

</div>

<script>
    const chat = document.getElementById('chatMessages');
    chat.scrollTop = chat.scrollHeight;
</script>

</body>
</html>