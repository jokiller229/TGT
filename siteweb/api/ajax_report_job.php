<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$job_id = intval($input['job_id'] ?? 0);
$motif = trim($input['motif'] ?? '');
$details = trim($input['details'] ?? '');
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if ($job_id <= 0 || empty($motif)) {
    echo json_encode(['success' => false, 'message' => 'Données invalides. Le motif est obligatoire.']);
    exit;
}

require_once __DIR__ . '/../config/db.php';
$db = getDB();

try {
    // Vérifier si le job existe
    $stmt = $db->prepare("SELECT id FROM jobs WHERE id = ?");
    $stmt->execute([$job_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Cette offre n\'existe pas.']);
        exit;
    }

    $stmtInsert = $db->prepare("INSERT INTO signalements (job_id, user_id, motif, details, statut, created_at) VALUES (?, ?, ?, ?, 'en attente', NOW())");
    $stmtInsert->execute([$job_id, $user_id, $motif, $details]);

    echo json_encode(['success' => true, 'message' => 'Votre signalement a été envoyé avec succès à notre équipe.']);

} catch (Exception $e) {
    error_log("Erreur signalement : " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Une erreur interne est survenue.']);
}




