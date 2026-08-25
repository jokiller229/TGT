<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (!isLoggedIn() || getCurrentRole() !== 'recruteur') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès refusé']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$app_id = isset($_POST['app_id']) ? (int)$_POST['app_id'] : 0;
$status = isset($_POST['status']) ? $_POST['status'] : '';

if ($app_id <= 0 || !in_array($status, ['retenu', 'refuse'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
    exit;
}

$db = getDB();
$userId = getCurrentUserId();

// Verify the application belongs to a job posted by the recruiter's company
$checkStmt = $db->prepare("
    SELECT a.id, j.id as job_id
    FROM applications a
    JOIN jobs j ON a.job_id = j.id
    WHERE a.id = ? AND j.company_id = (SELECT id FROM companies WHERE user_id = ?)
");
$checkStmt->execute([$app_id, $userId]);
if ($checkStmt->rowCount() === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Candidature introuvable ou non autorisée']);
    exit;
}

// Update status
$updateStmt = $db->prepare("UPDATE applications SET statut = ? WHERE id = ?");
$updateStmt->execute([$status, $app_id]);

echo json_encode(['success' => true]);




