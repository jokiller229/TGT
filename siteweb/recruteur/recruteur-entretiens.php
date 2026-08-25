<?php
$activePage = 'entretiens';
$pageTitle = 'Entretiens Visio - TGTravail';
require_once __DIR__ . '/../includes/auth.php';
requireRole('recruteur');

$db = getDB();
$companyId = getCurrentCompanyId();
if (!$companyId) {
    header("Location: ../index.php");
    exit;
}

// [BETA] Verification disabled during testing phase
// $stmtComp = getDB()->prepare("SELECT verifie FROM companies WHERE id = ?");
// $stmtComp->execute([$companyId]);
// $companyData = $stmtComp->fetch();
// if (!$companyData || !$companyData['verifie']) {
//     header('Location: ../recruteur/recruteur-dashboard.php');
//     exit;
// }

$userId = $_SESSION['user_id'];

// Get all candidates who applied to this company's jobs to populate the modal select
$candidatesStmt = $db->query("
  SELECT DISTINCT u.id, u.nom, u.email
  FROM applications a
  JOIN jobs j ON a.job_id = j.id
  JOIN users u ON a.candidate_id = u.id
  WHERE j.company_id = {$companyId}
");
$candidates = $candidatesStmt->fetchAll();

// Handle Form Submission (Schedule new interview)
$successMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plan_interview'])) {
    $candidateId = (int)$_POST['candidate_id'];
    $dateTime = $_POST['date_time'];
    $meetLink = 'https://meet.jit.si/TGTravail-' . bin2hex(random_bytes(8));
    
    if ($candidateId && !empty($dateTime)) {
        $stmt = $db->prepare("INSERT INTO interviews (recruiter_id, candidate_id, date_time, meet_link, status) VALUES (?, ?, ?, ?, 'planned')");
        $stmt->execute([$userId, $candidateId, $dateTime, $meetLink]);
        
        $notifStmt = $db->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $dtFormatted = date('d/m/Y H:i', strtotime($dateTime));
        $notifStmt->execute([
            $candidateId,
            "Un recruteur a planifié un entretien visio avec vous le $dtFormatted. Rejoindre : $meetLink"
        ]);
        $successMessage = "L'entretien a été planifié avec succès.";
    }
}

// Handle Cancel
if (isset($_GET['cancel_id'])) {
    $cancelId = (int)$_GET['cancel_id'];
    $db->prepare("UPDATE interviews SET status = 'cancelled' WHERE id = ? AND recruiter_id = ?")->execute([$cancelId, $userId]);
    header("Location: ../recruteur/recruteur-entretiens.php");
    exit;
}

// Get all interviews
$interviewsStmt = $db->prepare("
  SELECT i.*, u.nom AS candidate_nom, u.email AS candidate_email, u.avatar AS candidate_avatar
  FROM interviews i
  JOIN users u ON i.candidate_id = u.id
  WHERE i.recruiter_id = ?
  ORDER BY i.date_time ASC
");
$interviewsStmt->execute([$userId]);
$interviews = $interviewsStmt->fetchAll();

$hideHeader = true;
include __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/recruiter_sidebar.php'; ?>

  <main class="dashboard-content-main">
    <div class="dashboard-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
      <div class="dashboard-header-left" style="display:flex; align-items:center; gap:1rem;">
        <button class="dashboard-mobile-btn" onclick="document.querySelector('.dashboard-sidebar-dark').classList.toggle('open'); event.stopPropagation();">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>
        <div>
        <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--color-primary-dark);">Entretiens Visio</h1>
        <p style="color: var(--text-muted); margin-top: 0.25rem;">Gérez vos rendez-vous et lancez vos appels vidéo.</p>
      </div>
      </div>
      <button class="btn-primary" onclick="document.getElementById('plan-modal').style.display='flex'">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 0.5rem;"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        Planifier un entretien
      </button>
    </div>

    <?php if ($successMessage): ?>
    <div style="background: #ECFCCB; border: 1px solid #BEF264; color: #3F6212; padding: 1rem 1.25rem; border-radius: 12px; margin-bottom: 1.5rem; font-weight: 500;">
      ✓ <?= htmlspecialchars($successMessage) ?>
    </div>
    <?php endif; ?>

    <?php if (empty($interviews)): ?>
      <div style="background: #FFF; border: 1px solid var(--border-light); border-radius: 16px; padding: 4rem 2rem; text-align: center;">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity: 0.4; margin-bottom: 1rem; color: var(--color-primary-dark);"><path d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14v-4z"></path><rect x="3" y="6" width="12" height="12" rx="2" ry="2"></rect></svg>
        <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--color-primary-dark); margin-bottom: 0.5rem;">Aucun entretien planifié</h3>
        <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Vous n'avez pas encore d'entretien vidéo programmé avec un candidat.</p>
        <button class="btn-outline" onclick="document.getElementById('plan-modal').style.display='flex'">Planifier maintenant</button>
      </div>
    <?php else: ?>
      <div class="job-cards-list">
        <?php foreach ($interviews as $i): 
          $isPast = strtotime($i['date_time']) < time();
          $statusColor = $i['status'] === 'planned' ? ($isPast ? '#F59E0B' : '#3B82F6') : ($i['status'] === 'cancelled' ? '#EF4444' : '#10B981');
          $statusText = $i['status'] === 'planned' ? ($isPast ? 'En attente/Passé' : 'À venir') : ($i['status'] === 'cancelled' ? 'Annulé' : 'Terminé');
        ?>
          <div style="background: #FFF; border: 1px solid var(--border-light); border-radius: 16px; padding: 1.5rem; display: flex; flex-direction: column; gap: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
              <div style="display: flex; gap: 1rem; align-items: center;">
                <div style="width: 50px; height: 50px; border-radius: 50%; background: #F1F5F9; display: flex; align-items: center; justify-content: center; overflow: hidden; font-weight: 700; color: #64748B;">
                  <?php if (!empty($i['candidate_avatar']) && file_exists(__DIR__ . '/' . $i['candidate_avatar'])): ?>
                    <img src="<?= htmlspecialchars($i['candidate_avatar']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                  <?php else: ?>
                    <?= strtoupper(substr($i['candidate_nom'], 0, 2)) ?>
                  <?php endif; ?>
                </div>
                <div>
                  <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--color-primary-dark);"><?= htmlspecialchars($i['candidate_nom']) ?></h3>
                  <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.2rem;">Candidat</div>
                </div>
              </div>
              <span style="background: <?= $statusColor ?>20; color: <?= $statusColor ?>; padding: 0.35rem 0.85rem; border-radius: 20px; font-size: 0.75rem; font-weight: 700;">
                <?= $statusText ?>
              </span>
            </div>

            <div style="background: #F8FAFC; border-radius: 12px; padding: 1rem; display: flex; align-items: center; gap: 1rem; border: 1px solid #E2E8F0;">
              <div style="background: #FFF; padding: 0.5rem; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); border: 1px solid #E2E8F0;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#64748B" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
              </div>
              <div>
                <div style="font-weight: 600; color: var(--color-primary-dark); font-size: 0.95rem;">
                  <?= date('d/m/Y à H:i', strtotime($i['date_time'])) ?>
                </div>
                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.1rem;">Date & Heure</div>
              </div>
            </div>

            <div style="display: flex; gap: 1rem; align-items: center; border-top: 1px solid var(--border-light); padding-top: 1.25rem;">
              <?php if ($i['status'] === 'planned'): ?>
                <a href="<?= htmlspecialchars($i['meet_link']) ?>" target="_blank" class="btn-primary" style="flex: 1; text-align: center; justify-content: center; display: inline-flex;">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 0.5rem;"><path d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14v-4z"></path><rect x="3" y="6" width="12" height="12" rx="2" ry="2"></rect></svg>
                  Rejoindre la visio
                </a>
                <a href="cancel_id=<?= $i['id'] ?>" class="btn-outline" style="color: #EF4444; border-color: #FECACA; background: #FEF2F2;" onclick="return confirm('Êtes-vous sûr de vouloir annuler cet entretien ?');">Annuler</a>
              <?php else: ?>
                <button class="btn-outline" style="flex: 1;" disabled>Lien désactivé</button>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>
</div>

<!-- Modal Planifier -->
<div id="plan-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15, 23, 42, 0.6); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(4px);">
  <div style="background:#FFF; width:100%; max-width:500px; border-radius:24px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); overflow:hidden;">
    <div style="padding:1.5rem 2rem; border-bottom:1px solid #E2E8F0; display:flex; justify-content:space-between; align-items:center;">
      <h3 style="font-size:1.25rem; font-weight:800; color:#0F172A; margin:0;">Planifier un entretien</h3>
      <button onclick="document.getElementById('plan-modal').style.display='none'" style="background:none; border:none; color:#64748B; cursor:pointer; padding:0.5rem;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
      </button>
    </div>
    
    <div style="padding:2rem;">
      <form method="POST" action="../recruteur/recruteur-entretiens.php">
        <input type="hidden" name="plan_interview" value="1">
        
        <div style="margin-bottom:1.5rem;">
          <label style="display:block; font-size:0.875rem; font-weight:700; color:#1E293B; margin-bottom:0.5rem;">Candidat</label>
          <select name="candidate_id" required style="width:100%; padding:0.875rem 1rem; border:1px solid #CBD5E1; border-radius:12px; font-family:inherit; font-size:0.95rem; color:#0F172A; outline:none; background:#FFF;">
            <option value="">Sélectionnez un candidat</option>
            <?php foreach ($candidates as $c): ?>
              <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nom']) ?> (<?= htmlspecialchars($c['email']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>

        <div style="margin-bottom:2rem;">
          <label style="display:block; font-size:0.875rem; font-weight:700; color:#1E293B; margin-bottom:0.5rem;">Date et Heure</label>
          <input type="datetime-local" name="date_time" required style="width:100%; padding:0.875rem 1rem; border:1px solid #CBD5E1; border-radius:12px; font-family:inherit; font-size:0.95rem; color:#0F172A; outline:none;">
        </div>

        <button type="submit" class="btn-primary" style="width:100%; justify-content:center; padding:1rem;">Confirmer et générer le lien</button>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>




