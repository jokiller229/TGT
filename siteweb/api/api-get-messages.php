<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$convId = (int)($_GET['conv_id'] ?? 0);
$lastId = (int)($_GET['last_id'] ?? 0);

if (!$convId) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing conv_id']);
    exit;
}

$db = getDB();

// Verifier que l'utilisateur participe à la conversation
$stmt = $db->prepare("SELECT id FROM conversations WHERE id = ? AND (user1_id = ? OR user2_id = ?)");
$stmt->execute([$convId, $userId, $userId]);
if (!$stmt->fetch()) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

// Récupérer les messages plus récents que $lastId
$stmt = $db->prepare("
  SELECT id, sender_id, message, created_at
  FROM messages
  WHERE conversation_id = ? AND id > ?
  ORDER BY id ASC
");
$stmt->execute([$convId, $lastId]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Marquer comme lus
if (!empty($messages)) {
    $db->prepare("UPDATE messages SET is_read = 1 WHERE conversation_id = ? AND sender_id != ? AND is_read = 0")->execute([$convId, $userId]);
}

$response = [];
foreach ($messages as $m) {
    $response[] = [
        'id' => $m['id'],
        'sender_id' => $m['sender_id'],
        'message' => $m['message'],
        'created_at' => $m['created_at'],
        'time' => date('H:i', strtotime($m['created_at']))
    ];
}

if (ob_get_length()) ob_clean();
header('Content-Type: application/json');
echo json_encode(['status' => 'success', 'messages' => $response]);




