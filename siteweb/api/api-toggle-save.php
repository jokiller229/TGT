<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn() || getCurrentRole() !== 'candidat') {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$jobId = $data['job_id'] ?? null;

if (!$jobId) {
    echo json_encode(['success' => false, 'message' => 'ID de l\'offre manquant']);
    exit;
}

$userId = $_SESSION['user_id'];
$pdo = getDB();

try {
    // Check if already saved
    $stmt = $pdo->prepare("SELECT id FROM saved_jobs WHERE user_id = ? AND job_id = ?");
    $stmt->execute([$userId, $jobId]);
    $saved = $stmt->fetch();

    if ($saved) {
        // Remove from favorites
        $stmt = $pdo->prepare("DELETE FROM saved_jobs WHERE user_id = ? AND job_id = ?");
        $stmt->execute([$userId, $jobId]);
        echo json_encode(['success' => true, 'action' => 'removed']);
    } else {
        // Add to favorites
        $stmt = $pdo->prepare("INSERT INTO saved_jobs (user_id, job_id) VALUES (?, ?)");
        $stmt->execute([$userId, $jobId]);
        echo json_encode(['success' => true, 'action' => 'added']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données']);
}




