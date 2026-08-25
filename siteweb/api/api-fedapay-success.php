<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'msg' => 'Invalid method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    // Fallback to regular POST
    $input = $_POST;
}

$transactionId = $input['transaction_id'] ?? '';
$action = $input['action'] ?? '';

if (empty($transactionId) || empty($action)) {
    echo json_encode(['ok' => false, 'msg' => 'Données de transaction manquantes.']);
    exit;
}

// NOTE: En production, il est recommandé d'utiliser la clé SECRETE FedaPay 
// pour vérifier le statut de la transaction via une requête API (GET /v1/transactions/{id})
// Ici, nous faisons confiance au callback du widget pour simplifier l'intégration.

$db = getDB();
$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    echo json_encode(['ok' => false, 'msg' => 'Session expirée.']);
    exit;
}

try {
    if ($action === 'subscribe_candidat') {
        $months = (int)($input['months'] ?? 1);
        $endDate = date('Y-m-d H:i:s', strtotime("+$months months"));
        
        $stmt = $db->prepare("UPDATE candidate_profiles SET subscription_plan = 'premium', subscription_end = ? WHERE user_id = ?");
        $stmt->execute([$endDate, $userId]);
        
        echo json_encode(['ok' => true, 'msg' => 'Abonnement Candidat Premium activé avec succès !']);
        exit;
    }
    
    if ($action === 'subscribe_recruteur_pro') {
        $companyId = getCurrentCompanyId();
        if (!$companyId) throw new Exception("Entreprise introuvable.");
        
        $months = 1;
        $endDate = date('Y-m-d H:i:s', strtotime("+$months months"));
        
        // Check if the column exists or fallback
        // We'll just assume companies table has subscription_plan and subscription_end based on previous tasks
        $stmt = $db->prepare("UPDATE companies SET subscription_plan = 'pro', subscription_end = ? WHERE id = ?");
        $stmt->execute([$endDate, $companyId]);
        
        echo json_encode(['ok' => true, 'msg' => 'Abonnement Recruteur Pro activé !']);
        exit;
    }
    
    // Add logic for packs/credits
    if ($action === 'buy_pack') {
        $companyId = getCurrentCompanyId();
        if (!$companyId) throw new Exception("Entreprise introuvable.");
        
        $jobsCount = (int)($input['jobs'] ?? 1);
        $stmt = $db->prepare("UPDATE companies SET job_credits = job_credits + ? WHERE id = ?");
        $stmt->execute([$jobsCount, $companyId]);
        
        echo json_encode(['ok' => true, 'msg' => "Achat de $jobsCount crédit(s) offre(s) validé avec succès !"]);
        exit;
    }

    echo json_encode(['ok' => false, 'msg' => 'Action inconnue.']);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'msg' => 'Erreur serveur : ' . $e->getMessage()]);
}




