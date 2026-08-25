<?php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');

$user = getCurrentUser();
if (!$user) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$db = getDB();
$userId = $user['id'];

// Get unread count
$countStmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND lu = 0");
$countStmt->execute([$userId]);
$unreadCount = (int)$countStmt->fetchColumn();

// Get recent notifications
$notifStmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
$notifStmt->execute([$userId]);
$notifications = $notifStmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'unreadCount' => $unreadCount,
    'notifications' => $notifications
]);




