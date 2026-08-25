<?php
/**
 * API - Mise en avant (homepage banner 3 jours)
 * POST: job_id
 * En phase beta: activation immediate sans paiement
 */
session_start();
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !hasRole('recruteur')) {
    echo json_encode(['ok' => false, 'msg' => 'Non autorise']);
    exit;
}

$db = getDB();
$userId = $_SESSION['user_id'];
$jobId = (int)($_POST['job_id'] ?? 0);

if (!$jobId) {
    echo json_encode(['ok' => false, 'msg' => 'Offre invalide']);
    exit;
}

// Verify this job belongs to the recruiter's company
$stmt = $db->prepare("
    SELECT j.id, j.titre, j.pack FROM jobs j
    JOIN companies c ON j.company_id = c.id
    WHERE j.id = ? AND c.user_id = ? AND j.statut = 'active'
");
$stmt->execute([$jobId, $userId]);
$job = $stmt->fetch();

if (!$job) {
    echo json_encode(['ok' => false, 'msg' => 'Offre introuvable ou non active']);
    exit;
}

// Check if already featured
if ($job['pack'] === 'alaune') {
    echo json_encode(['ok' => false, 'msg' => "Cette offre est déjà mise en avant avec le pack à la une."]);
    exit;
}

// Activate featured (pack = alaune)
$db->prepare("UPDATE jobs SET pack = 'alaune' WHERE id = ?")->execute([$jobId]);

echo json_encode([
    'ok'   => true,
    'msg'  => "Offre mise en avant avec succès !",
    'until' => 'Illimité'
]);




