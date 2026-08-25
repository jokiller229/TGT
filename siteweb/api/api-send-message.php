<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

$userId = $_SESSION['user_id'] ?? null;
if (!$userId || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized or invalid method']);
    exit;
}

// Support for both JSON body and standard Form data
$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$convId = (int)($data['conv_id'] ?? 0);
$message = trim($data['message'] ?? '');

if (!$convId || $message === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Missing data']);
    exit;
}

$db = getDB();

// Verifier appartenance
$stmt = $db->prepare("SELECT id FROM conversations WHERE id = ? AND (user1_id = ? OR user2_id = ?)");
$stmt->execute([$convId, $userId, $userId]);
if (!$stmt->fetch()) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$db->beginTransaction();
try {
    $ins = $db->prepare("INSERT INTO messages (conversation_id, sender_id, message) VALUES (?, ?, ?)");
    $ins->execute([$convId, $userId, $message]);
    $newMsgId = $db->lastInsertId();

    $upd = $db->prepare("UPDATE conversations SET updated_at = NOW() WHERE id = ?");
    $upd->execute([$convId]);

    $db->commit();

    $stmt = $db->prepare("SELECT created_at FROM messages WHERE id = ?");
    $stmt->execute([$newMsgId]);
    $createdAt = $stmt->fetchColumn();

    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'id' => $newMsgId,
        'message' => $message,
        'time' => date('H:i', strtotime($createdAt)),
        'created_at' => $createdAt
    ]);

} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}




