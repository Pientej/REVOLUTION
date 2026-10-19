<?php
require __DIR__ . '/config.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$eventId = (int)($_GET['id'] ?? 0);
$action  = $_GET['action'] ?? '';
$userId  = $_SESSION['user_id'];

if ($eventId > 0 && in_array($action, ['join', 'leave'])) {
    if ($action === 'join') {
        // Katılıyorum
        $stmt = $pdo->prepare('
            INSERT INTO event_participants (event_id, user_id, status) 
            VALUES (?, ?, "katiliyorum") 
            ON DUPLICATE KEY UPDATE status = "katiliyorum"
        ');
        $stmt->execute([$eventId, $userId]);
    } else if ($action === 'leave') {
        // Ayrıl (Katılmıyorum)
        $stmt = $pdo->prepare('DELETE FROM event_participants WHERE event_id = ? AND user_id = ?');
        $stmt->execute([$eventId, $userId]);
    }
}

header('Location: home.php');
exit;