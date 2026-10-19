<?php
require __DIR__ . '/config.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

$error = $_SESSION['profile_error'] ?? null;
unset($_SESSION['profile_error']);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Révolution | Profili Düzenle</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="home.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>
<body class="home-body">

<header class="topbar">
    <div class="topbar-left"><i class="fa-solid fa-crown" style="color:var(--yellow);"></i><span class="topbar-brand">RÉVOLUTION</span></div>
    <div class="topbar-right"><a href="profile.php" style="color:#aaa; text-decoration:none; font-size:14px;"><i class="fa-solid fa-arrow-left"></i> Profile Dön</a></div>
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

    <main class="map-area" style="background:#0d0b0b; padding: 40px; overflow-y:auto; display:block;">
        <div style="max-width: 600px; margin: 0 auto; background: #1a1a1a; padding: 30px; border-radius: 20px; border: 1px solid #ff123840;">
            <h2 style="color:var(--yellow); margin-bottom: 20px;">Profili Düzenle</h2>
            
            <?php if($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form action="profile_update_process.php" method="POST">
                <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                
                <div class="modal-field">
                    <label>Takma Ad</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                </div>
                
                <div class="modal-field">
                    <label>E-posta Adresi</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>

                <div class="modal-field">
                    <label>Hakkımda (Biyografi)</label>
                    <textarea name="bio" rows="4" placeholder="Kendinden kısaca bahset..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                </div>

                <div class="modal-field">
                    <label>Cinsiyet (İsteğe Bağlı)</label>
                    <select name="gender" style="width:100%; padding:11px; border-radius:10px; background:#111; color:#fff; border:1px solid #333;">
                        <option value="">Belirtmek İstemiyorum</option>
                        <option value="erkek" <?= $user['gender'] == 'erkek' ? 'selected' : '' ?>>Erkek</option>
                        <option value="kadin" <?= $user['gender'] == 'kadin' ? 'selected' : '' ?>>Kadın</option>
                        <option value="diger" <?= $user['gender'] == 'diger' ? 'selected' : '' ?>>Diğer</option>
                    </select>
                </div>

                <button type="submit" class="modal-submit" style="margin-top:20px;">Değişiklikleri Kaydet</button>
            </form>
        </div>
    </main>
</div>

</body>
</html>