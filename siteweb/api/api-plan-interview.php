<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

$userId = $_SESSION['user_id'] ?? null;
if (!$userId || $_SERVER['REQUEST_METHOD'] !== 'POST' || ($_SESSION['role'] ?? '') !== 'recruteur') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$candidateId = (int)($data['candidate_id'] ?? 0);
$jobId = (int)($data['job_id'] ?? 0);
$dateTime = trim($data['date_time'] ?? '');

if (!$candidateId || !$dateTime) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing data']);
    exit;
}

// Generate Jitsi Link
$meetLink = 'https://meet.jit.si/TGTravail-' . bin2hex(random_bytes(8));

$db = getDB();
$stmt = $db->prepare("INSERT INTO interviews (recruiter_id, candidate_id, job_id, date_time, meet_link, status) VALUES (?, ?, ?, ?, ?, 'planned')");

try {
    $stmt->execute([$userId, $candidateId, $jobId ?: null, $dateTime, $meetLink]);
    $interviewId = $db->lastInsertId();

    // Create notification for candidate
    $notifStmt = $db->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
    $notifStmt->execute([
        $candidateId,
        'Un recruteur a planifié un entretien visio avec vous. Consultez "Mes Entretiens".'
    ]);

    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'interview_id' => $interviewId, 'meet_link' => $meetLink]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error', 'details' => $e->getMessage()]);
}




