<?php
require __DIR__ . '/config.php';
if (empty($_SESSION['user_id'])) exit;

$uid = $_SESSION['user_id'];

// Sadece üye olunan (approved) grupların gizli konumlarını getir
$stmt = $pdo->prepare('
    SELECT g.id, g.name, g.latitude, g.longitude 
    FROM `groups` g 
    JOIN group_members gm ON g.id = gm.group_id 
    WHERE gm.user_id = ? AND gm.status = "approved" 
    AND g.latitude IS NOT NULL 
    AND g.longitude IS NOT NULL
');
$stmt->execute([$uid]);
$groups = $stmt->fetchAll();

header('Content-Type: application/json');
echo json_encode($groups);