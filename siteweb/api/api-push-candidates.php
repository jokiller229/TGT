<?php
/**
 * API - Notification Push candidats
 * POST: job_id
 * Envoie une notification a tous les candidats dont les alertes
 * correspondent a la categorie de l'offre, ou tous les candidats
 * si pas d'alerte configuree.
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
    SELECT j.id, j.titre, j.categorie, j.lieu, j.push_sent_at, c.nom as company_nom
    FROM jobs j
    JOIN companies c ON j.company_id = c.id
    WHERE j.id = ? AND c.user_id = ? AND j.statut = 'active'
");
$stmt->execute([$jobId, $userId]);
$job = $stmt->fetch();

if (!$job) {
    echo json_encode(['ok' => false, 'msg' => 'Offre introuvable ou non active']);
    exit;
}

// Check if push already sent (cooldown 24h)
if ($job['push_sent_at'] && strtotime($job['push_sent_at']) > strtotime('-24 hours')) {
    echo json_encode(['ok' => false, 'msg' => 'Un push a deja ete envoye pour cette offre il y a moins de 24h']);
    exit;
}

// Get candidates to notify:
// Priority 1: Candidates with matching job alerts
// Priority 2: All active candidates if no alerts found
$candidatesStmt = $db->prepare("
    SELECT DISTINCT u.id
    FROM users u
    WHERE u.role = 'candidat'
    AND u.statut_compte = 'actif'
    AND u.id IN (
        SELECT ja.user_id FROM job_alerts ja
        WHERE (ja.categorie = :cat OR ja.categorie IS NULL OR ja.categorie = '')
        UNION
        SELECT u2.id FROM users u2
        WHERE u2.role = 'candidat' AND u2.statut_compte = 'actif'
        AND NOT EXISTS (SELECT 1 FROM job_alerts WHERE user_id = u2.id)
    )
    LIMIT 500
");
$candidatesStmt->execute([':cat' => $job['categorie']]);
$candidates = $candidatesStmt->fetchAll(PDO::FETCH_COLUMN);

if (empty($candidates)) {
    // Fallback: all active candidates
    $candidates = $db->query("SELECT id FROM users WHERE role = 'candidat' AND statut_compte = 'actif' LIMIT 200")->fetchAll(PDO::FETCH_COLUMN);
}

if (empty($candidates)) {
    echo json_encode(['ok' => false, 'msg' => 'Aucun candidat a notifier pour le moment']);
    exit;
}

// Insert notifications
$notifMsg = "Nouvelle offre : {$job['titre']} chez {$job['company_nom']} ({$job['lieu']}) — Postulez maintenant !";
$ins = $db->prepare("INSERT INTO notifications (user_id, job_id, message, lu, created_at) VALUES (?, ?, ?, 0, NOW())");

$count = 0;
foreach ($candidates as $candidateId) {
    try {
        $ins->execute([$candidateId, $jobId, $notifMsg]);
        $count++;
    } catch(Exception $e) {
        // Try without job_id if column missing
        try {
            $db->prepare("INSERT INTO notifications (user_id, message, lu, created_at) VALUES (?, ?, 0, NOW())")
               ->execute([$candidateId, $notifMsg]);
            $count++;
        } catch(Exception $e2) { /* skip */ }
    }
}

// Mark push as sent
$db->prepare("UPDATE jobs SET push_sent_at = NOW() WHERE id = ?")->execute([$jobId]);

echo json_encode([
    'ok'    => true,
    'msg'   => "Notification envoyee a {$count} candidat(s) !",
    'count' => $count
]);




