<?php
session_start();
require_once '../siteweb/config/db.php';
$db = getDB();

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

$pageTitle = 'Abonnements - Administration TGTravail';
$activePage = 'abonnements';

// --- Traitement du formulaire de modification d'abonnement ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_subscription') {
    $targetId = (int)$_POST['target_id'];
    $targetType = $_POST['target_type']; // 'Recruteur' ou 'Candidat'
    $plan = $_POST['plan'];
    $duration = (int)$_POST['duration']; // en mois
    $add_credits = (int)($_POST['add_credits'] ?? 0);
    
    // Si gratuit, pas de date de fin
    if ($plan === 'gratuit') {
        $end_date = null;
    } else {
        $end_date = date('Y-m-d H:i:s', strtotime("+$duration months"));
    }
    
    if ($targetType === 'Recruteur') {
        $stmt = $db->prepare("UPDATE companies SET subscription_plan = ?, subscription_end = ?, job_credits = GREATEST(0, COALESCE(job_credits, 0) + ?) WHERE id = ?");
        $stmt->execute([$plan, $end_date, $add_credits, $targetId]);
    } else {
        $stmt = $db->prepare("UPDATE candidate_profiles SET subscription_plan = ?, subscription_end = ? WHERE id = ?");
        $stmt->execute([$plan, $end_date, $targetId]);
    }
    $msg_success = "L'abonnement a été mis à jour avec succès.";
}

// --- Récupération des KPIs ---
$totalCompanies = $db->query("SELECT COUNT(*) FROM companies")->fetchColumn();
$totalCandidates = $db->query("SELECT COUNT(*) FROM candidate_profiles")->fetchColumn();

// Compter les abonnements actifs
$activePremiumComp = $db->query("SELECT COUNT(*) FROM companies WHERE subscription_plan = 'premium' AND (subscription_end > NOW() OR subscription_end IS NULL)")->fetchColumn();
$activeBasicComp = $db->query("SELECT COUNT(*) FROM companies WHERE subscription_plan = 'basique' AND (subscription_end > NOW() OR subscription_end IS NULL)")->fetchColumn();
$activePremiumCand = $db->query("SELECT COUNT(*) FROM candidate_profiles WHERE subscription_plan = 'premium' AND (subscription_end > NOW() OR subscription_end IS NULL)")->fetchColumn();

$totalPremium = $activePremiumComp + $activePremiumCand;
$totalBasic = $activeBasicComp;

// Calcul du MRR estimé (Basique: 5000, Premium Recruteur: 15000, Premium Candidat: 2000)
$mrr = ($activeBasicComp * 5000) + ($activePremiumComp * 15000) + ($activePremiumCand * 2000);

// --- Récupération unifiée (Entreprises + Candidats) ---
$sql = "
    SELECT 'Recruteur' as user_type, c.id, c.nom, u.email as user_email, c.subscription_plan, c.subscription_end, c.created_at, c.job_credits
    FROM companies c 
    LEFT JOIN users u ON c.user_id = u.id 
    
    UNION ALL 
    
    SELECT 'Candidat' as user_type, cp.id, u.nom, u.email as user_email, cp.subscription_plan, cp.subscription_end, cp.created_at, 0 as job_credits
    FROM candidate_profiles cp 
    LEFT JOIN users u ON cp.user_id = u.id 
    
    ORDER BY created_at DESC
";
$stmt = $db->query($sql);
$allUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once 'includes/header.php';
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 1.8rem; font-weight: 700; color: #0F172A; margin: 0;">Gestion des Abonnements</h1>
        <p style="color: #64748B; margin-top: 0.5rem;">Suivez et gérez les forfaits des recruteurs et des candidats.</p>
    </div>
</div>

<?php if (isset($msg_success)): ?>
<div style="background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 8px; margin-bottom: 2rem; display:flex; align-items:center; gap:0.5rem; font-weight:600;">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
    <?= htmlspecialchars($msg_success) ?>
</div>
<?php endif; ?>

<!-- Section KPIs -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <!-- KPI 1 -->
    <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid #E2E8F0;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1rem;">
            <div style="color: #64748B; font-size: 0.9rem; font-weight: 500;">Revenu Mensuel (MRR)</div>
            <div style="width:36px; height:36px; border-radius:8px; background:#EFF6FF; color:#3B82F6; display:flex; align-items:center; justify-content:center;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
            </div>
        </div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #0F172A;"><?= number_format($mrr, 0, ',', ' ') ?> F</div>
    </div>
    <!-- KPI 2 -->
    <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid #E2E8F0;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1rem;">
            <div style="color: #64748B; font-size: 0.9rem; font-weight: 500;">Abonnés Premium (Tous)</div>
            <div style="width:36px; height:36px; border-radius:8px; background:#FEF3C7; color:#D97706; display:flex; align-items:center; justify-content:center;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
            </div>
        </div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #0F172A;"><?= $totalPremium ?></div>
    </div>
    <!-- KPI 3 -->
    <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid #E2E8F0;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1rem;">
            <div style="color: #64748B; font-size: 0.9rem; font-weight: 500;">Abonnés Basique</div>
            <div style="width:36px; height:36px; border-radius:8px; background:#ECFCCB; color:#4D7C0F; display:flex; align-items:center; justify-content:center;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            </div>
        </div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #0F172A;"><?= $totalBasic ?></div>
    </div>
    <!-- KPI 4 -->
    <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid #E2E8F0;">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1rem;">
            <div style="color: #64748B; font-size: 0.9rem; font-weight: 500;">Total Inscrits</div>
            <div style="width:36px; height:36px; border-radius:8px; background:#F1F5F9; color:#475569; display:flex; align-items:center; justify-content:center;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            </div>
        </div>
        <div style="font-size: 1.75rem; font-weight: 700; color: #0F172A;"><?= ($totalCompanies + $totalCandidates) ?></div>
    </div>
</div>

<!-- Liste des abonnements -->
<div style="background: white; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid #E2E8F0; overflow:hidden;">
    <div style="padding: 1.5rem; border-bottom: 1px solid #E2E8F0; display:flex; justify-content:space-between; align-items:center;">
        <h2 style="font-size: 1.1rem; font-weight: 700; margin: 0; color: #0F172A;">Liste des Comptes (Recruteurs & Candidats)</h2>
    </div>
    
    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #F8FAFC;">
                    <th style="padding: 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; color: #64748B; text-transform: uppercase; border-bottom:1px solid #E2E8F0;">Type</th>
                    <th style="padding: 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; color: #64748B; text-transform: uppercase; border-bottom:1px solid #E2E8F0;">Utilisateur</th>
                    <th style="padding: 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; color: #64748B; text-transform: uppercase; border-bottom:1px solid #E2E8F0;">Plan Actuel</th>
                    <th style="padding: 1rem; text-align: left; font-size: 0.75rem; font-weight: 600; color: #64748B; text-transform: uppercase; border-bottom:1px solid #E2E8F0;">Date de fin</th>
                    <th style="padding: 1rem; text-align: right; font-size: 0.75rem; font-weight: 600; color: #64748B; text-transform: uppercase; border-bottom:1px solid #E2E8F0;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allUsers as $c): 
                    $isExpired = false;
                    if ($c['subscription_plan'] !== 'gratuit' && $c['subscription_end']) {
                        if (strtotime($c['subscription_end']) < time()) {
                            $isExpired = true;
                        }
                    }
                    
                    $planColor = '#64748B'; // Gratuit (gris)
                    if ($c['subscription_plan'] === 'basique') $planColor = '#059669'; // Basique (vert)
                    if ($c['subscription_plan'] === 'pro') $planColor = '#3B82F6'; // Pro (bleu)
                    if ($c['subscription_plan'] === 'premium') $planColor = '#D97706'; // Premium (orange)
                ?>
                <tr style="border-bottom: 1px solid #F1F5F9; hover:background:#F8FAFC; transition:background 0.2s;">
                    <td style="padding: 1rem;">
                        <?php if ($c['user_type'] === 'Recruteur'): ?>
                            <span style="display:inline-block; padding: 0.2rem 0.5rem; border-radius: 4px; font-size:0.75rem; font-weight:600; background:#E0E7FF; color:#4338CA;">Entreprise</span>
                        <?php else: ?>
                            <span style="display:inline-block; padding: 0.2rem 0.5rem; border-radius: 4px; font-size:0.75rem; font-weight:600; background:#FEF08A; color:#854D0E;">Candidat</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 1rem;">
                        <div style="font-weight: 600; color:#0F172A; margin-bottom:0.2rem;"><?= htmlspecialchars($c['nom'] ?? 'Sans Nom') ?></div>
                        <div style="font-size: 0.8rem; color:#64748B;"><?= htmlspecialchars($c['user_email']) ?></div>
                    </td>
                    <td style="padding: 1rem;">
                        <span style="display:inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size:0.75rem; font-weight:600; background:<?= $planColor ?>15; color:<?= $planColor ?>; text-transform:uppercase;">
                            <?= htmlspecialchars($c['subscription_plan'] == 'pro' ? 'Pro' : $c['subscription_plan']) ?>
                        </span>
                        <?php if ($c['user_type'] === 'Recruteur'): ?>
                             <div style="font-size:0.75rem; margin-top:0.35rem; font-weight:600; color:#475569;">Crédits restants : <span style="color: <?= $c['job_credits'] > 0 ? '#059669' : '#DC2626' ?>"><?= (int)$c['job_credits'] ?></span></div>
                        <?php endif; ?>
                        <?php if ($isExpired): ?>
                            <span style="display:inline-block; margin-left:0.5rem; color:#EF4444; font-size:0.75rem; font-weight:600;">(Expiré)</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 1rem;">
                        <?php if ($c['subscription_plan'] === 'gratuit' || !$c['subscription_end']): ?>
                            <span style="color:#94A3B8; font-size:0.85rem;">À vie</span>
                        <?php else: ?>
                            <div style="font-size:0.85rem; color: <?= $isExpired ? '#EF4444' : '#1E293B' ?>; font-weight:500;">
                                <?= date('d/m/Y', strtotime($c['subscription_end'])) ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 1rem; text-align: right;">
                        <button onclick="openSubscriptionModal(<?= $c['id'] ?>, '<?= $c['user_type'] ?>', '<?= htmlspecialchars(addslashes($c['nom'])) ?>', '<?= $c['subscription_plan'] ?>')" style="background: white; border: 1px solid #CBD5E1; color: #0F172A; padding: 0.4rem 0.75rem; border-radius: 6px; font-weight: 500; font-size: 0.85rem; cursor: pointer; transition: all 0.2s;">
                            Modifier le plan
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($allUsers)): ?>
                <tr>
                    <td colspan="5" style="padding: 2rem; text-align:center; color:#64748B;">Aucun compte trouvé.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Modification Abonnement -->
<div id="subModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); backdrop-filter:blur(4px); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:white; border-radius:16px; width:100%; max-width:450px; padding:2rem; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
        <h3 style="font-size:1.25rem; font-weight:700; color:#0F172A; margin:0 0 0.5rem 0;">Modifier l'abonnement</h3>
        <p style="color:#64748B; font-size:0.9rem; margin-bottom:1.5rem;"><span id="modalTargetTypeLabel">Compte</span>: <strong id="modalTargetName" style="color:#0F172A;"></strong></p>
        
        <form method="POST" action="">
            <input type="hidden" name="action" value="update_subscription">
            <input type="hidden" name="target_id" id="modalTargetId">
            <input type="hidden" name="target_type" id="modalTargetType">
            
            <div style="margin-bottom:1.25rem;">
                <label style="display:block; font-weight:600; font-size:0.85rem; color:#1E293B; margin-bottom:0.5rem;">Forfait</label>
                <select name="plan" id="modalPlan" style="width:100%; padding:0.75rem; border:1px solid #CBD5E1; border-radius:8px; font-family:inherit; font-size:0.95rem; background:#FFF;" required>
                    <option value="gratuit">Gratuit</option>
                    <option value="basique" id="opt-basique">Basique (Recruteur)</option>
                    <option value="pro" id="opt-pro">Pro (Recruteur)</option>
                    <option value="premium" id="opt-premium">Premium (Candidat)</option>
                </select>
            </div>
            
            <div style="margin-bottom:2rem;" id="durationWrapper">
                <label style="display:block; font-weight:600; font-size:0.85rem; color:#1E293B; margin-bottom:0.5rem;">Durée à créditer</label>
                <select name="duration" style="width:100%; padding:0.75rem; border:1px solid #CBD5E1; border-radius:8px; font-family:inherit; font-size:0.95rem; background:#FFF;">
                    <option value="1">1 Mois</option>
                    <option value="3">3 Mois</option>
                    <option value="6">6 Mois</option>
                    <option value="12">1 An</option>
                </select>
                <div style="font-size:0.8rem; color:#64748B; margin-top:0.5rem;">La date de fin sera calculée à partir d'aujourd'hui.</div>
            </div>

            <div style="margin-bottom:2rem;" id="creditsWrapper">
                <label style="display:block; font-weight:600; font-size:0.85rem; color:#1E293B; margin-bottom:0.5rem;">Ajouter / Retirer des Crédits d'offres</label>
                <input type="number" name="add_credits" value="0" style="width:100%; padding:0.75rem; border:1px solid #CBD5E1; border-radius:8px; font-family:inherit; font-size:0.95rem; background:#FFF;">
                <div style="font-size:0.8rem; color:#64748B; margin-top:0.5rem;">Entrez une valeur positive pour ajouter, ou négative pour retirer (ex: 5 ou -1).</div>
            </div>
            
            <div style="display:flex; gap:1rem; justify-content:flex-end;">
                <button type="button" onclick="document.getElementById('subModal').style.display='none'" style="padding:0.75rem 1.25rem; border:1px solid #CBD5E1; background:white; color:#475569; border-radius:8px; font-weight:600; cursor:pointer;">Annuler</button>
                <button type="submit" style="padding:0.75rem 1.25rem; border:none; background:#2563EB; color:white; border-radius:8px; font-weight:600; cursor:pointer;">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<script>
const modalPlan = document.getElementById('modalPlan');
const durationWrapper = document.getElementById('durationWrapper');

modalPlan.addEventListener('change', function() {
    if (this.value === 'gratuit') {
        durationWrapper.style.display = 'none';
    } else {
        durationWrapper.style.display = 'block';
    }
});

function openSubscriptionModal(id, type, name, currentPlan) {
    document.getElementById('modalTargetId').value = id;
    document.getElementById('modalTargetType').value = type;
    document.getElementById('modalTargetTypeLabel').textContent = type;
    document.getElementById('modalTargetName').textContent = name;
    
    if (type === 'Candidat') {
        document.getElementById('opt-basique').style.display = 'none';
        document.getElementById('opt-pro').style.display = 'none';
        document.getElementById('opt-premium').style.display = 'block';
        document.getElementById('creditsWrapper').style.display = 'none';
    } else {
        document.getElementById('opt-basique').style.display = 'block';
        document.getElementById('opt-pro').style.display = 'block';
        document.getElementById('opt-premium').style.display = 'none';
        document.getElementById('creditsWrapper').style.display = 'block';
    }

    document.getElementById('modalPlan').value = currentPlan;
    
    if (currentPlan === 'gratuit') {
        durationWrapper.style.display = 'none';
    } else {
        durationWrapper.style.display = 'block';
    }
    
    document.getElementById('subModal').style.display = 'flex';
}
</script>

<?php require_once 'includes/footer.php'; ?>
