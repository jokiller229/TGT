<?php
require_once __DIR__ . '/../includes/auth.php';
$db = getDB();

header('Content-Type: application/json');

$userId = getCurrentUserId();
if ($userId === 0 || getCurrentRole() !== 'candidat') {
    echo json_encode(['success' => false, 'message' => 'Vous devez être connecté en tant que candidat.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    $jobId = (int)($data['job_id'] ?? 0);
    $motif = trim($data['motif'] ?? '');
    $details = trim($data['details'] ?? '');
    
    if (!$jobId || empty($motif)) {
        echo json_encode(['success' => false, 'message' => 'Données incomplètes.']);
        exit;
    }
    
    // Check if already reported
    $check = $db->prepare("SELECT id FROM reports WHERE job_id = ? AND user_id = ?");
    $check->execute([$jobId, $userId]);
    if ($check->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Vous avez déjà signalé cette offre.']);
        exit;
    }
    
    $stmt = $db->prepare("INSERT INTO reports (job_id, user_id, motif, details, created_at) VALUES (?, ?, ?, ?, NOW())");
    if ($stmt->execute([$jobId, $userId, $motif, $details])) {
        echo json_encode(['success' => true, 'message' => 'Signalement envoyé avec succès.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement.']);
    }
}




