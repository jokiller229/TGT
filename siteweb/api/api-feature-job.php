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
    SELECT j.id, j.titre, j.featured_until FROM jobs j
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
if ($job['featured_until'] && strtotime($job['featured_until']) > time()) {
    $expire = date('d/m/Y', strtotime($job['featured_until']));
    echo json_encode(['ok' => false, 'msg' => "Cette offre est deja mise en avant jusqu'au {$expire}"]);
    exit;
}

// Activate featured for 3 days
$featuredUntil = date('Y-m-d H:i:s', strtotime('+3 days'));
$db->prepare("UPDATE jobs SET featured_until = ? WHERE id = ?")->execute([$featuredUntil, $jobId]);

echo json_encode([
    'ok'   => true,
    'msg'  => "Offre mise en avant jusqu'au " . date('d/m/Y', strtotime($featuredUntil)),
    'until' => $featuredUntil
]);




