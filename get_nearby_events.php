<?php
require __DIR__ . '/config.php';

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

$userId = $_SESSION['user_id'];
$lat    = (float)($_GET['lat'] ?? 0);
$lng    = (float)($_GET['lng'] ?? 0);
$radius = (float)($_GET['radius'] ?? 10);

if ($lat === 0.0 || $lng === 0.0) {
    echo json_encode([]);
    exit;
}

$latDelta = $radius / 111;
$lngDelta = $radius / (111 * cos(deg2rad($lat)));

// MANTIK: Etkinlikleri, oluşturanın adı (u.username) ve grubun adıyla (g.name) birlikte çekiyoruz.
$sql = "
    SELECT e.*,
           u.username AS creator_name,
           g.name AS group_name,
           (SELECT COUNT(*) FROM event_participants ep WHERE ep.event_id = e.id AND ep.status = 'katiliyorum') AS participant_count,
           (SELECT COUNT(*) FROM event_participants ep2 WHERE ep2.event_id = e.id AND ep2.user_id = ?) AS is_joined
    FROM events e
    JOIN users u ON e.user_id = u.id
    LEFT JOIN `groups` g ON e.group_id = g.id
    WHERE e.event_date >= NOW()
      AND e.latitude  BETWEEN ? AND ?
      AND e.longitude BETWEEN ? AND ?
      AND (
          e.group_id IS NULL 
          OR EXISTS (
              SELECT 1 FROM group_members gm 
              WHERE gm.group_id = e.group_id AND gm.user_id = ? AND gm.status = 'approved'
          )
      )
    ORDER BY e.event_date ASC
    LIMIT 50
";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    $userId,
    $lat - $latDelta,
    $lat + $latDelta,
    $lng - $lngDelta,
    $lng + $lngDelta,
    $userId
]);

$events = $stmt->fetchAll();

header('Content-Type: application/json; charset=utf-8');
echo json_encode($events);