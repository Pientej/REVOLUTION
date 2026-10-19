<?php
require __DIR__ . '/config.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Panel | Révolution</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="page-container" style="display:flex;justify-content:center;">
        <div class="login-card" style="text-align:center;max-width:500px;">
            <h2 style="margin-bottom:16px;">Hoş geldin, <span style="color:var(--yellow);"><?= htmlspecialchars($_SESSION['user_name']) ?></span> 👑</h2>
            <p style="color:var(--muted);margin-bottom:24px;"><?= htmlspecialchars($_SESSION['user_email']) ?></p>
            <a href="logout.php" class="login-button" style="text-decoration:none;">Çıkış Yap</a>
        </div>
    </main>
</body>
</html>