<?php
require __DIR__ . '/config.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$eventId = (int)($_GET['id'] ?? 0);
$userId = $_SESSION['user_id'];

if ($eventId > 0) {
    // Kontrol et: Bu etkinlik bu kullanıcıya mı ait?
    $stmt = $pdo->prepare('SELECT id FROM events WHERE id = ? AND user_id = ?');
    $stmt->execute([$eventId, $userId]);
    
    if ($stmt->fetch()) {
        // Sil (Veritabanında ON DELETE CASCADE olduğu için katılımcılar da silinir)
        $del = $pdo->prepare('DELETE FROM events WHERE id = ?');
        $del->execute([$eventId]);
        $_SESSION['event_success'] = 'Etkinlik başarıyla silindi.';
    } else {
        $_SESSION['event_error'] = 'Bu etkinliği silme yetkiniz yok.';
    }
}

header('Location: my_events.php');
exit;