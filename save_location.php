<?php
require __DIR__ . '/config.php';

header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Giriş gerekli']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!csrf_verify($input['csrf'] ?? null)) {
    http_response_code(403);
    echo json_encode(['error' => 'CSRF hatası']);
    exit;
}

$lat = (float)($input['lat'] ?? 0);
$lng = (float)($input['lng'] ?? 0);

if ($lat === 0.0 || $lng === 0.0) {
    http_response_code(400);
    echo json_encode(['error' => 'Geçersiz konum']);
    exit;
}

$stmt = $pdo->prepare(
    'INSERT INTO user_locations (user_id, latitude, longitude)
     VALUES (?, ?, ?)
     ON DUPLICATE KEY UPDATE latitude = VALUES(latitude), longitude = VALUES(longitude)'
);
$stmt->execute([$_SESSION['user_id'], $lat, $lng]);

echo json_encode(['success' => true]);