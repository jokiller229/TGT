<?php
/**
 * API - Boost d'offre (7 jours)
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
    SELECT j.id, j.titre, j.boosted_until FROM jobs j
    JOIN companies c ON j.company_id = c.id
    WHERE j.id = ? AND c.user_id = ? AND j.statut = 'active'
");
$stmt->execute([$jobId, $userId]);
$job = $stmt->fetch();

if (!$job) {
    echo json_encode(['ok' => false, 'msg' => 'Offre introuvable ou non active']);
    exit;
}

// Check if already boosted
if ($job['boosted_until'] && strtotime($job['boosted_until']) > time()) {
    $expire = date('d/m/Y', strtotime($job['boosted_until']));
    echo json_encode(['ok' => false, 'msg' => "Cette offre est deja boostee jusqu'au {$expire}"]);
    exit;
}

// Activate boost for 7 days
$boostedUntil = date('Y-m-d H:i:s', strtotime('+7 days'));
$db->prepare("UPDATE jobs SET boosted_until = ? WHERE id = ?")->execute([$boostedUntil, $jobId]);

// Log transaction (beta = 0 FCFA)
try {
    $db->prepare("INSERT INTO transactions (user_id, type, montant, description, created_at) VALUES (?, 'boost', 0, ?, NOW())")
       ->execute([$userId, "Boost offre #{$jobId} - {$job['titre']} (phase beta)"]);
} catch(Exception $e) { /* transactions table may differ */ }

echo json_encode([
    'ok'   => true,
    'msg'  => "Offre boostee jusqu'au " . date('d/m/Y', strtotime($boostedUntil)),
    'until' => $boostedUntil
]);




