<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('recruteur');

$recruiterId = getCurrentUserId();
$companyId = getCurrentCompanyId();
$candidateId = isset($_GET['candidate_id']) ? (int)$_GET['candidate_id'] : 0;

if (!$candidateId || !$companyId) {
    header("Location: ../recruteur/candidatures.php");
    exit;
}

$db = getDB();

// Vérifier si une conversation existe déjà
$stmtCheck = $db->prepare("SELECT id FROM conversations WHERE (user1_id = ? AND user2_id = ?) OR (user1_id = ? AND user2_id = ?)");
$stmtCheck->execute([$recruiterId, $candidateId, $candidateId, $recruiterId]);
$existingConv = $stmtCheck->fetch();

if ($existingConv) {
    // Rediriger vers la conversation existante
    header("Location: ../recruteur/messages.php?conv_id=" . $existingConv['id']);
    exit;
} else {
    // Créer une nouvelle conversation
    $stmtInsert = $db->prepare("INSERT INTO conversations (user1_id, user2_id, company_id) VALUES (?, ?, ?)");
    if ($stmtInsert->execute([$recruiterId, $candidateId, $companyId])) {
        $newConvId = $db->lastInsertId();
        
        // Optionnel: On peut envoyer un premier message automatique
        $welcomeMessage = "Bonjour, votre profil a retenu notre attention suite à votre candidature.";
        $stmtMsg = $db->prepare("INSERT INTO messages (conversation_id, sender_id, message, is_read, created_at) VALUES (?, ?, ?, 0, NOW())");
        $stmtMsg->execute([$newConvId, $recruiterId, $welcomeMessage]);

        // Rediriger vers la nouvelle conversation
        header("Location: ../recruteur/messages.php?conv_id=" . $newConvId);
        exit;
    } else {
        header("Location: ../recruteur/candidatures.php?error=1");
        exit;
    }
}




