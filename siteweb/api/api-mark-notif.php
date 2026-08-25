<?php
require_once __DIR__ . '/../includes/auth.php';

$user = getCurrentUser();
if ($user) {
    $db = getDB();
    $stmt = $db->prepare("UPDATE notifications SET lu = 1 WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}




