<?php
$activePage = 'candidatures';
$pageTitle = 'Candidatures reçues - TGTravail';
require_once __DIR__ . '/../includes/auth.php';
requireRole('recruteur');

$db = getDB();
$companyId = getCurrentCompanyId();
if (!$companyId) { header("Location: ../index.php"); exit; }

// Require Verified Company
$stmtComp = getDB()->prepare("SELECT verifie FROM companies WHERE id = ?");
$stmtComp->execute([$companyId]);
$companyData = $stmtComp->fetch();
if (!$companyData || !$companyData['verifie']) {
    header('Location: ../recruteur/recruteur-dashboard.php');
    exit;
}


$user = getCurrentUser();
$stmtComp = $db->prepare("SELECT nom, logo FROM companies WHERE id = ?");
$stmtComp->execute([$companyId]);
$company = $stmtComp->fetch();
$companyName = $company['nom'] ?? 'Mon Entreprise';
$companyLogo = $company['logo'] ?? null;

$jobId = isset($_GET['job_id']) ? (int)$_GET['job_id'] : null;

// Validate job belongs to recruiter
if ($jobId) {
    $checkJob = $db->prepare("SELECT id FROM jobs WHERE id = ? AND company_id = ?");
    $checkJob->execute([$jobId, $companyId]);
    if (!$checkJob->fetch()) {
        $jobId = null;
    }
}

// Traitement changement de statut en POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_update_status'])) {
    $appId    = (int)$_POST['application_id'];
    $newStatus = $_POST['statut'];

    // Fetch candidate_id + job title before updating
    $appInfo = $db->prepare("
        SELECT a.candidate_id, j.titre AS job_titre
        FROM applications a
        JOIN jobs j ON a.job_id = j.id
        WHERE a.id = ?
    ");
    $appInfo->execute([$appId]);
    $appRow = $appInfo->fetch();

    $db->prepare("UPDATE applications SET statut = ? WHERE id = ?")->execute([$newStatus, $appId]);

    // Notifier le candidat selon le nouveau statut
    if ($appRow) {
        $statusMessages = [
            'vue'        => "Bonne nouvelle ! Votre candidature pour « {$appRow['job_titre']} » a été consultée par le recruteur.",
            'retenu'     => "Félicitations ! Vous avez été retenu(e) pour la suite du processus de « {$appRow['job_titre']} ».",
            'entretien'  => "Un entretien a été planifié pour votre candidature à « {$appRow['job_titre']} ». Consultez vos entretiens.",
            'embauche'   => "Excellente nouvelle ! Vous avez été sélectionné(e) pour le poste « {$appRow['job_titre']} ».",
            'refuse'     => "Votre candidature pour « {$appRow['job_titre']} » n'a pas été retenue. Continuez vos recherches !",
            'evaluation' => "Votre candidature pour « {$appRow['job_titre']} » est en cours d'évaluation.",
        ];
        if (isset($statusMessages[$newStatus])) {
            $db->prepare("INSERT INTO notifications (user_id, message, lu, created_at) VALUES (?, ?, 0, NOW())")
               ->execute([$appRow['candidate_id'], $statusMessages[$newStatus]]);
        }
    }

    header("Location: ../recruteur/candidatures.php?job_id={$jobId}&updated=1");
    exit;
}

// Export CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=candidatures_tgtravail.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Nom', 'Email', 'Téléphone', 'Expérience', 'Statut', 'Date Candidature']);
    
    if ($jobId) {
        $exportStmt = $db->prepare("SELECT a.id, u.nom, u.email, u.telephone, cp.experience_annees, a.statut, a.created_at FROM applications a JOIN users u ON a.candidate_id = u.id LEFT JOIN candidate_profiles cp ON u.id = cp.user_id WHERE a.job_id = ?");
        $exportStmt->execute([$jobId]);
    } else {
        $exportStmt = $db->prepare("SELECT a.id, u.nom, u.email, u.telephone, cp.experience_annees, a.statut, a.created_at FROM applications a JOIN users u ON a.candidate_id = u.id LEFT JOIN candidate_profiles cp ON u.id = cp.user_id JOIN jobs j ON a.job_id = j.id WHERE j.company_id = ?");
        $exportStmt->execute([$companyId]);
    }
    while ($row = $exportStmt->fetch()) {
        fputcsv($output, [$row['id'], $row['nom'], $row['email'], $row['telephone'], $row['experience_annees'] . ' ans', $row['statut'], $row['created_at']]);
    }
    fclose($output);
    exit;
}

// Offre active ou toutes
if ($jobId) {
    $jobStmt = $db->prepare("SELECT j.*, c.nom AS company_nom FROM jobs j JOIN companies c ON j.company_id = c.id WHERE j.id = ?");
    $jobStmt->execute([$jobId]);
    $job = $jobStmt->fetch() ?: ['titre' => 'Toutes vos offres', 'company_nom' => 'Mon Entreprise'];
} else {
    $job = ['titre' => 'Toutes vos offres', 'company_nom' => 'Mon Entreprise'];
}

// Filtre d'onglet statut
$filterStatus = $_GET['status'] ?? 'tous';
$whereStatus = ($filterStatus !== 'tous') ? "AND a.statut = " . $db->quote($filterStatus) : "";

// Liste des candidatures
if ($jobId) {
    $appsStmt = $db->prepare("SELECT a.*, u.nom AS candidate_nom, u.email AS candidate_email, u.telephone AS candidate_telephone, u.avatar AS candidate_avatar, cp.experience_annees, cp.titre_professionnel, j.titre AS job_titre FROM applications a JOIN users u ON a.candidate_id = u.id LEFT JOIN candidate_profiles cp ON u.id = cp.user_id JOIN jobs j ON a.job_id = j.id WHERE a.job_id = ? {$whereStatus} ORDER BY a.created_at DESC");
    $appsStmt->execute([$jobId]);
} else {
    $appsStmt = $db->prepare("SELECT a.*, u.nom AS candidate_nom, u.email AS candidate_email, u.telephone AS candidate_telephone, u.avatar AS candidate_avatar, cp.experience_annees, cp.titre_professionnel, j.titre AS job_titre FROM applications a JOIN users u ON a.candidate_id = u.id LEFT JOIN candidate_profiles cp ON u.id = cp.user_id JOIN jobs j ON a.job_id = j.id WHERE j.company_id = ? {$whereStatus} ORDER BY a.created_at DESC");
    $appsStmt->execute([$companyId]);
}
$applications = $appsStmt->fetchAll();

// KPIs
$kpis = [];
if ($jobId) {
    $kpis = [
      'tous' => (int)$db->query("SELECT COUNT(*) FROM applications WHERE job_id = {$jobId}")->fetchColumn(),
      'nouveau' => (int)$db->query("SELECT COUNT(*) FROM applications WHERE job_id = {$jobId} AND statut = 'nouveau'")->fetchColumn(),
      'evaluation' => (int)$db->query("SELECT COUNT(*) FROM applications WHERE job_id = {$jobId} AND statut = 'evaluation'")->fetchColumn(),
      'entretien' => (int)$db->query("SELECT COUNT(*) FROM applications WHERE job_id = {$jobId} AND statut = 'entretien'")->fetchColumn(),
      'embauche' => (int)$db->query("SELECT COUNT(*) FROM applications WHERE job_id = {$jobId} AND statut = 'embauche'")->fetchColumn()
    ];
} else {
    $kpis = [
      'tous' => (int)$db->query("SELECT COUNT(a.id) FROM applications a JOIN jobs j ON a.job_id = j.id WHERE j.company_id = {$companyId}")->fetchColumn(),
      'nouveau' => (int)$db->query("SELECT COUNT(a.id) FROM applications a JOIN jobs j ON a.job_id = j.id WHERE j.company_id = {$companyId} AND a.statut = 'nouveau'")->fetchColumn(),
      'evaluation' => (int)$db->query("SELECT COUNT(a.id) FROM applications a JOIN jobs j ON a.job_id = j.id WHERE j.company_id = {$companyId} AND a.statut = 'evaluation'")->fetchColumn(),
      'entretien' => (int)$db->query("SELECT COUNT(a.id) FROM applications a JOIN jobs j ON a.job_id = j.id WHERE j.company_id = {$companyId} AND a.statut = 'entretien'")->fetchColumn(),
      'embauche' => (int)$db->query("SELECT COUNT(a.id) FROM applications a JOIN jobs j ON a.job_id = j.id WHERE j.company_id = {$companyId} AND a.statut = 'embauche'")->fetchColumn()
    ];
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">

  <!-- ── Dark Sidebar ─────────────────────────────────────────────────────── -->
  <?php require __DIR__ . '/../includes/recruiter_sidebar.php'; ?>

  <!-- ── Main Content ──────────────────────────────────────────────────────── -->
  <main class="dashboard-content-main" style="overflow-y:auto;">

    <!-- Topbar -->
    <div class="dashboard-topbar">
      <div class="dashboard-header-left" style="display:flex; align-items:center; gap:1rem;">
        <button class="dashboard-mobile-btn" onclick="document.querySelector('.dashboard-sidebar-dark').classList.toggle('open'); event.stopPropagation();">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>
        <div>
        <h1 class="user-greeting">Mes candidatures</h1>
        <p style="font-size:.9rem;color:var(--text-muted);">Gérez les candidatures reçues pour vos offres</p>
      </div>
      </div>
      <div style="display:flex;align-items:center;gap:1.25rem;">
        <button onclick="startTinderMode()" class="btn-primary" style="background: linear-gradient(135deg, #EC4899, #F43F5E); border: none; box-shadow: 0 4px 10px rgba(236, 72, 153, 0.3); padding: 0.5rem 1rem; border-radius: 8px; color: white; cursor: pointer;">⚡ Mode Rapide</button>
        <div class="company-selector-dropdown">
          <span style="color:#2563EB;">●</span><span><?= htmlspecialchars($companyName) ?></span>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
        </div>
        <a href="../pages/notifications.php" style="position:relative;color:var(--text-muted);display:flex;" title="Notifications">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
          <span style="position:absolute;top:2px;right:2px;width:8px;height:8px;background:#3B82F6;border-radius:50%;border:2px solid #F8FAFC;"></span>
        </a>
        <a href="../recruteur/parametres.php" style="display:flex;">
          <?php if (!empty($companyLogo) && file_exists(__DIR__.'/'.$companyLogo)): ?>
            <img src="<?= htmlspecialchars($companyLogo) ?>" alt="Logo" style="width:42px;height:42px;border-radius:50%;object-fit:cover;border:2px solid #E2E8F0;">
          <?php else: ?>
            <div style="width:42px;height:42px;border-radius:50%;background:#081326;color:#FFB800;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.9rem;border:2px solid #E2E8F0;"><?= strtoupper(substr($user['nom'],0,2)) ?></div>
          <?php endif; ?>
        </a>
      </div>
    </div>

    <!-- Toolbar -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
      <?php if ($jobId): ?>
        <a href="../recruteur/mes-offres.php" style="font-size:.85rem;color:#2563EB;font-weight:600;display:inline-flex;align-items:center;gap:.35rem;text-decoration:none;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>
          Retour à mes offres
        </a>
      <?php else: ?>
        <div></div>
      <?php endif; ?>
      <a href="<?= $jobId ? "job_id={$jobId}&" : "" ?>export=csv" style="display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1rem;border:1.5px solid #E2E8F0;background:#FFF;border-radius:12px;font-size:.8rem;font-weight:700;color:#0F172A;text-decoration:none;transition:all .2s;" onmouseover="this.style.borderColor='#94A3B8'" onmouseout="this.style.borderColor='#E2E8F0'">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
        Exporter CSV
      </a>
    </div>

    <!-- KPIs Card -->
    <div style="background:#FFF;border:1.5px solid #E2E8F0;border-radius:20px;padding:1.5rem 2rem;margin-bottom:2rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:2rem;">
      <div style="display:flex;align-items:center;gap:1.25rem;">
        <?php if (!empty($companyLogo) && file_exists(__DIR__.'/'.$companyLogo)): ?>
          <img src="<?= htmlspecialchars($companyLogo) ?>" alt="Logo" style="width:52px;height:52px;border-radius:14px;object-fit:cover;border:2px solid #E2E8F0;">
        <?php else: ?>
          <div style="width:52px;height:52px;border-radius:14px;background:#F8FAFC;color:#081326;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1.1rem;border:2px solid #E2E8F0;"><?= strtoupper(substr($companyName,0,2)) ?></div>
        <?php endif; ?>
        <div>
          <h2 style="font-size:1.25rem;font-weight:800;color:#081326;margin:0;"><?= htmlspecialchars($job['titre']) ?></h2>
          <p style="font-size:.85rem;color:#64748B;margin:.15rem 0 0;"><?= htmlspecialchars($job['company_nom']) ?></p>
        </div>
      </div>
      
      <div style="display:flex;gap:2rem;text-align:center;">
        <div>
          <div style="font-size:1.5rem;font-weight:900;color:#081326;"><?= $kpis['tous'] ?></div>
          <div style="font-size:.72rem;color:#94A3B8;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;margin-top:.2rem;">Total</div>
        </div>
        <div>
          <div style="font-size:1.5rem;font-weight:900;color:#059669;"><?= $kpis['nouveau'] ?></div>
          <div style="font-size:.72rem;color:#94A3B8;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;margin-top:.2rem;">Nouveaux</div>
        </div>
        <div>
          <div style="font-size:1.5rem;font-weight:900;color:#D97706;"><?= $kpis['evaluation'] ?></div>
          <div style="font-size:.72rem;color:#94A3B8;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;margin-top:.2rem;">À évaluer</div>
        </div>
        <div>
          <div style="font-size:1.5rem;font-weight:900;color:#2563EB;"><?= $kpis['entretien'] ?></div>
          <div style="font-size:.72rem;color:#94A3B8;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;margin-top:.2rem;">Entretiens</div>
        </div>
        <div>
          <div style="font-size:1.5rem;font-weight:900;color:#7C3AED;"><?= $kpis['embauche'] ?></div>
          <div style="font-size:.72rem;color:#94A3B8;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;margin-top:.2rem;">Embauchés</div>
        </div>
      </div>
    </div>

    <!-- Candidate List -->
    <div style="background:#FFF;border:1.5px solid #E2E8F0;border-radius:20px;overflow:hidden;">
      
      <!-- Tabs -->
      <div style="display:flex;overflow-x:auto;border-bottom:1.5px solid #E2E8F0;background:#F8FAFC;">
        <?php
          $tabs = [
            'tous' => ['Toutes', $kpis['tous'], '#0F172A', '#F1F5F9'],
            'nouveau' => ['Nouveau', $kpis['nouveau'], '#059669', '#ECFDF5'],
            'evaluation' => ['À évaluer', $kpis['evaluation'], '#D97706', '#FFFBEB'],
            'entretien' => ['Entretien', $kpis['entretien'], '#2563EB', '#EFF6FF'],
            'embauche' => ['Embauché', $kpis['embauche'], '#7C3AED', '#F5F3FF']
          ];
          foreach ($tabs as $k => $t):
            $isActive = ($filterStatus === $k);
        ?>
        <a href="<?= $jobId?"job_id={$jobId}&":"" ?>status=<?= $k ?>" style="padding:1rem 1.5rem;display:inline-flex;align-items:center;gap:.5rem;font-size:.85rem;font-weight:700;color:<?= $isActive?'#0F172A':'#64748B' ?>;text-decoration:none;border-bottom:2px solid <?= $isActive?'#2563EB':'transparent' ?>;transition:all .2s;background:<?= $isActive?'#FFF':'transparent' ?>;">
          <?= $t[0] ?>
          <span style="background:<?= $isActive?$t[3]:'#E2E8F0' ?>;color:<?= $isActive?$t[2]:'#64748B' ?>;padding:.1rem .5rem;border-radius:99px;font-size:.7rem;"><?= $t[1] ?></span>
        </a>
        <?php endforeach; ?>
      </div>

      <!-- Table -->
      <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;min-width:700px;">
          <thead>
            <tr style="background:#F8FAFC;border-bottom:1.5px solid #E2E8F0;text-align:left;font-size:.75rem;color:#64748B;text-transform:uppercase;letter-spacing:0.05em;">
              <th style="padding:1rem 1.5rem;font-weight:700;">Candidat</th>
              <th style="padding:1rem 1.5rem;font-weight:700;">Expérience</th>
              <th style="padding:1rem 1.5rem;font-weight:700;">Date</th>
              <th style="padding:1rem 1.5rem;font-weight:700;">Statut</th>
              <th style="padding:1rem 1.5rem;font-weight:700;text-align:right;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($applications)): ?>
            <tr>
              <td colspan="5" style="padding:4rem 2rem;text-align:center;color:#94A3B8;">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity:.3;margin-bottom:1rem;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                <div style="font-size:1rem;font-weight:700;color:#64748B;">Aucune candidature</div>
                <div style="font-size:.85rem;">Rien à afficher pour ce filtre.</div>
              </td>
            </tr>
            <?php else: ?>
              <?php foreach ($applications as $app): 
                $bgColors = ['#081326','#1E40AF','#7C3AED','#059669','#D97706'];
                $bg = $bgColors[crc32($app['candidate_nom']) % count($bgColors)];
                $initials = strtoupper(substr($app['candidate_nom'],0,2));
                
                $statusColors = match($app['statut']) {
                  'nouveau' => ['#ECFDF5','#059669'],
                  'evaluation' => ['#FFFBEB','#D97706'],
                  'entretien' => ['#EFF6FF','#2563EB'],
                  'embauche' => ['#F5F3FF','#7C3AED'],
                  'refuse' => ['#FEF2F2','#DC2626'],
                  default => ['#F1F5F9','#64748B']
                };
              ?>
              <tr style="border-bottom:1px solid #E2E8F0;transition:background .2s;cursor:pointer;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'" onclick="window.location.href='user_id=<?= $app['candidate_id'] ?>'">
                <td style="padding:1rem 1.5rem;">
                  <div style="display:flex;align-items:center;gap:.75rem;">
                    <?php if (!empty($app['candidate_avatar']) && file_exists(__DIR__.'/'.$app['candidate_avatar'])): ?>
                      <img src="<?= htmlspecialchars($app['candidate_avatar']) ?>" alt="Avatar" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">
                    <?php else: ?>
                      <div style="width:40px;height:40px;border-radius:50%;background:<?= $bg ?>;color:#FFF;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.8rem;"><?= $initials ?></div>
                    <?php endif; ?>
                    <div>
                      <div style="font-weight:700;color:#0F172A;font-size:.9rem;"><?= htmlspecialchars($app['candidate_nom']) ?></div>
                      <div style="font-size:.75rem;color:#64748B;"><?= htmlspecialchars($app['candidate_email']) ?></div>
                    </div>
                  </div>
                </td>
                <td style="padding:1rem 1.5rem;font-size:.85rem;color:#334155;font-weight:500;">
                  <?= $app['experience_annees'] ?? 0 ?> ans
                </td>
                <td style="padding:1rem 1.5rem;font-size:.85rem;color:#64748B;">
                  <?= date('d/m/Y', strtotime($app['created_at'])) ?>
                </td>
                <td style="padding:1rem 1.5rem;" onclick="event.stopPropagation();">
                  <form action="candidatures.php?<?= $jobId?"job_id={$jobId}":"" ?>" method="POST" style="margin:0;">
                    <input type="hidden" name="action_update_status" value="1">
                    <input type="hidden" name="application_id" value="<?= $app['id'] ?>">
                    <select name="statut" onchange="this.form.submit()" style="background:<?= $statusColors[0] ?>;color:<?= $statusColors[1] ?>;border:none;padding:.3rem .75rem;border-radius:99px;font-size:.75rem;font-weight:700;cursor:pointer;outline:none;font-family:inherit;">
                      <option value="nouveau" <?= $app['statut'] === 'nouveau' ? 'selected' : '' ?>>Nouveau</option>
                      <option value="evaluation" <?= $app['statut'] === 'evaluation' ? 'selected' : '' ?>>À évaluer</option>
                      <option value="entretien" <?= $app['statut'] === 'entretien' ? 'selected' : '' ?>>Entretien</option>
                      <option value="embauche" <?= $app['statut'] === 'embauche' ? 'selected' : '' ?>>Embauché</option>
                      <option value="refuse" <?= $app['statut'] === 'refuse' ? 'selected' : '' ?>>Refusé</option>
                    </select>
                  </form>
                </td>
                <td style="padding:1rem 1.5rem;text-align:right;" onclick="event.stopPropagation();">
                  <div style="display:inline-flex;gap:.5rem;">
                    <a href="user_id=<?= $app['candidate_id'] ?><?= $jobId ? '&job_id='.$jobId : '' ?>" style="color:#64748B;padding:.4rem;background:#F1F5F9;border-radius:8px;transition:all .2s;" onmouseover="this.style.color='#2563EB';this.style.background='#EFF6FF'" onmouseout="this.style.color='#64748B';this.style.background='#F1F5F9'" title="Voir Profil">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    </a>
                    <a href="api-start-conversation.php?candidate_id=<?= $app['candidate_id'] ?>" style="color:#64748B;padding:.4rem;background:#F1F5F9;border-radius:8px;transition:all .2s;" onmouseover="this.style.color='#059669';this.style.background='#ECFDF5'" onmouseout="this.style.color='#64748B';this.style.background='#F1F5F9'" title="Contacter">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                    </a>
                    <button style="color:#64748B;padding:.4rem;background:#F1F5F9;border:none;border-radius:8px;cursor:pointer;transition:all .2s;" onmouseover="this.style.color='#0F172A';this.style.background='#E2E8F0'" onmouseout="this.style.color='#64748B';this.style.background='#F1F5F9'">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="1"></circle><circle cx="19" cy="12" r="1"></circle><circle cx="5" cy="12" r="1"></circle></svg>
                    </button>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </main>
</div>

  <!-- TINDER MODE MODAL -->
  <div id="tinder-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(15,23,42,0.95); z-index:9999; justify-content:center; align-items:center; flex-direction:column;">
    <button onclick="closeTinderMode()" style="position:absolute; top:2rem; right:2rem; background:none; border:none; color:white; font-size:2rem; cursor:pointer;">✕</button>
    <div id="tinder-card-container" style="position:relative; width:90%; max-width:500px; height:600px;">
      <!-- Cards will be injected here via JS -->
    </div>
    <div style="display:flex; gap:2rem; margin-top:2rem;">
      <button onclick="swipeAction('refuse')" style="width:70px; height:70px; border-radius:50%; background:white; color:#EF4444; border:none; font-size:2rem; cursor:pointer; box-shadow:0 10px 25px rgba(239,68,68,0.3); display:flex; align-items:center; justify-content:center; transition:transform 0.2s;">✕</button>
      <button onclick="swipeAction('retenu')" style="width:70px; height:70px; border-radius:50%; background:white; color:#10B981; border:none; font-size:2rem; cursor:pointer; box-shadow:0 10px 25px rgba(16,185,129,0.3); display:flex; align-items:center; justify-content:center; transition:transform 0.2s;">❤</button>
    </div>
  </div>

  <script>
    const apps = <?= json_encode($applications) ?>;
    let currentAppIndex = 0;

    function startTinderMode() {
      if(apps.length === 0) {
        alert("Aucune candidature à traiter.");
        return;
      }
      currentAppIndex = 0;
      document.getElementById('tinder-modal').style.display = 'flex';
      document.body.style.overflow = 'hidden';
      renderCurrentCard();
    }

    function closeTinderMode() {
      document.getElementById('tinder-modal').style.display = 'none';
      document.body.style.overflow = 'auto';
    }

    function renderCurrentCard() {
      const container = document.getElementById('tinder-card-container');
      if (currentAppIndex >= apps.length) {
        container.innerHTML = '<div style="color:white; text-align:center; font-size:1.5rem; margin-top:50%;">Toutes les candidatures ont été vues ! 🎉</div>';
        return;
      }
      
      const app = apps[currentAppIndex];
      const avatarSrc = app.candidate_avatar || 'img/default-avatar.png'; 
      const avatarLetter = app.candidate_nom.substring(0,2).toUpperCase();
      let avatarHtml = `<div style="width:120px; height:120px; border-radius:50%; background:#2563EB; color:white; font-size:2.5rem; font-weight:800; display:flex; align-items:center; justify-content:center; margin:0 auto 1.5rem; border:4px solid white; box-shadow:0 4px 10px rgba(0,0,0,0.1);">${avatarLetter}</div>`;
      if(app.candidate_avatar) {
          avatarHtml = `<img src="${app.candidate_avatar}" style="width:120px; height:120px; border-radius:50%; object-fit:cover; margin:0 auto 1.5rem; display:block; border:4px solid white; box-shadow:0 4px 10px rgba(0,0,0,0.1);">`;
      }

      let attachHtml = '';
      if(app.video_pitch) attachHtml += `<a href="${app.video_pitch}" target="_blank" style="display:inline-block; margin-right:1rem; color:#2563EB; font-weight:600;">🎥 Voir le Pitch Vidéo</a>`;
      if(app.lettre_motivation && app.lettre_motivation.endsWith('.pdf')) attachHtml += `<a href="${app.lettre_motivation}" target="_blank" style="display:inline-block; color:#2563EB; font-weight:600;">📄 Voir Lettre (PDF)</a>`;

      const cardHtml = `
        <div id="tinder-card" style="width:100%; height:100%; background:white; border-radius:24px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); padding:2rem; text-align:center; position:absolute; top:0; left:0; transition: transform 0.4s ease, opacity 0.4s ease;">
          <h4 style="font-size:0.8rem; color:#64748B; text-transform:uppercase; letter-spacing:0.1em; margin-bottom:1rem;">Postule pour : ${app.job_titre}</h4>
          ${avatarHtml}
          <h2 style="font-size:1.8rem; font-weight:800; color:#0F172A; margin-bottom:0.5rem;">${app.candidate_nom}</h2>
          <p style="font-size:1.1rem; color:#475569; margin-bottom:1.5rem;">${app.titre_professionnel || 'Profil Candidat'}</p>
          <div style="display:flex; justify-content:center; gap:1rem; margin-bottom:2rem;">
            <span style="background:#F1F5F9; color:#475569; padding:0.5rem 1rem; border-radius:99px; font-weight:600; font-size:0.9rem;">⭐ ${app.experience_annees || 'N/A'} ans</span>
          </div>
          <p style="font-size:0.95rem; color:#334155; line-height:1.6; text-align:left; background:#F8FAFC; padding:1rem; border-radius:12px; height:100px; overflow-y:auto; margin-bottom:1.5rem;">
            ${app.lettre_motivation && !app.lettre_motivation.endsWith('.pdf') ? app.lettre_motivation : (app.lettre_motivation ? 'Lettre en PDF jointe.' : 'Pas de lettre de motivation.')}
          </p>
          ${attachHtml}
        </div>
      `;
      container.innerHTML = cardHtml;
    }

    function swipeAction(status) {
      if (currentAppIndex >= apps.length) return;
      const card = document.getElementById('tinder-card');
      const app = apps[currentAppIndex];
      
      const transformValue = status === 'retenu' ? 'translate(100vw, -20deg)' : 'translate(-100vw, -20deg)';
      card.style.transform = transformValue;
      card.style.opacity = '0';

      const formData = new FormData();
      formData.append('application_id', app.id);
      formData.append('statut', status);
      formData.append('action_update_status', '1');

      fetch('candidatures.php', {
        method: 'POST',
        body: formData
      }).then(() => {
        setTimeout(() => {
            currentAppIndex++;
            renderCurrentCard();
        }, 300);
      });
    }
  </script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>




