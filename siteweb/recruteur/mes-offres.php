<?php
$activePage = 'mes-offres';
$pageTitle = 'Mes offres - TGTravail';
$hideHeader = true;
require_once __DIR__ . '/../includes/auth.php';
requireRole('recruteur');

$db = getDB();
$companyId = getCurrentCompanyId();
if (!$companyId) {
    header("Location: ../index.php");
    exit;
}

// Require Verified Company
$stmtComp = getDB()->prepare("SELECT verifie FROM companies WHERE id = ?");
$stmtComp->execute([$companyId]);
$companyData = $stmtComp->fetch();
if (!$companyData || !$companyData['verifie']) {
    header('Location: ../recruteur/recruteur-dashboard.php');
    exit;
}


$user = getCurrentUser();

$stmtComp = $db->prepare("SELECT nom, logo FROM companies WHERE id = ?");;
$stmtComp->execute([$companyId]);
$company = $stmtComp->fetch();
$companyName = $company['nom'] ?? 'Mon Entreprise';
$companyLogo = $company['logo'] ?? null;

// Récupérer toutes les offres de l'entreprise avec champs boost
$stmtJobs = $db->prepare("
    SELECT j.*,
           (SELECT COUNT(id) FROM applications WHERE job_id = j.id) as candidatures_real,
           j.boosted_until,
           j.featured_until,
           j.date_limite
    FROM jobs j
    WHERE j.company_id = ?
    ORDER BY j.created_at DESC
");
$stmtJobs->execute([$companyId]);
$jobs = $stmtJobs->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
  
  <?php require __DIR__ . '/../includes/recruiter_sidebar.php'; ?>

  <main class="dashboard-content-main">
    <div class="dashboard-top-nav" style="margin-bottom: 2.5rem;">
      <div class="dashboard-header-left" style="display:flex; align-items:center; gap:1rem;">
        <button class="dashboard-mobile-btn" onclick="document.querySelector('.dashboard-sidebar-dark').classList.toggle('open'); event.stopPropagation();">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>
        <div>
        <h1 class="dashboard-title">Mes Offres Publiées</h1>
        <p class="dashboard-subtitle" style="color: #64748B;">Gérez vos annonces et consultez les candidatures associées</p>
      </div>
      </div>

      <div style="display: flex; align-items: center; gap: 1.25rem;">
        <div class="company-selector-dropdown">
          <span style="color:#2563EB;">●</span>
          <span><?= htmlspecialchars($companyName) ?></span>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
        </div>
        <a href="../pages/notifications.php" style="position:relative; color:var(--text-muted); display:flex;" title="Notifications">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
          <span style="position:absolute; top:2px; right:2px; width:8px; height:8px; background:#3B82F6; border-radius:50%; border:2px solid #F8FAFC;"></span>
        </a>
        <a href="../recruteur/parametres.php" title="Paramètres" style="display:flex;">
          <?php if (!empty($companyLogo) && file_exists(__DIR__ . '/' . $companyLogo)): ?>
            <img src="<?= htmlspecialchars($companyLogo) ?>" alt="Logo" style="width:42px; height:42px; border-radius:50%; object-fit:cover; border:2px solid #E2E8F0;">
          <?php else: ?>
            <div style="width:42px; height:42px; border-radius:50%; background:#081326; color:#FFB800; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.9rem; text-transform:uppercase; border:2px solid #E2E8F0;">
              <?= htmlspecialchars(strtoupper(substr($user['nom'], 0, 2))) ?>
            </div>
          <?php endif; ?>
        </a>
      </div>
    </div>

    <div class="card" style="background: #FFF; border-radius: 24px; padding: 2rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
      <?php if (count($jobs) === 0): ?>
        <div style="text-align: center; padding: 3rem 0;">
          <p style="color: #64748B; margin-bottom: 1rem;">Vous n'avez publié aucune offre pour le moment.</p>
          <a href="../recruteur/publier-offre.php" class="btn-primary">+ Publier ma première offre</a>
        </div>
      <?php else: ?>

        <div style="overflow-x: auto; -webkit-overflow-scrolling: touch; padding-bottom: 1rem;">
          <table style="width: 100%; min-width: 800px; border-collapse: collapse; text-align: left;">
            <thead>
              <tr style="border-bottom: 2px solid #F1F5F9;">
                <th style="padding: 1rem 0; color: #64748B; font-weight: 600;">Titre de l'offre</th>
                <th style="padding: 1rem 0; color: #64748B; font-weight: 600;">Publication</th>
                <th style="padding: 1rem 0; color: #64748B; font-weight: 600;">Statut</th>
                <th style="padding: 1rem 0; color: #64748B; font-weight: 600;">Vues</th>
                <th style="padding: 1rem 0; color: #64748B; font-weight: 600;">Candidatures</th>
                <th style="padding: 1rem 0; color: #64748B; font-weight: 600;">Taux</th>
                <th style="padding: 1rem 0; color: #64748B; font-weight: 600; text-align: right;">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($jobs as $j):
                $isBoosted  = !empty($j['boosted_until'])  && strtotime($j['boosted_until'])  > time();
                $isFeatured = !empty($j['featured_until']) && strtotime($j['featured_until']) > time();
                $daysLeft   = !empty($j['date_limite']) ? (int)((strtotime($j['date_limite']) - time()) / 86400) : null;
                $vues       = (int)($j['vues_count'] ?? 0);
                $apps       = (int)($j['candidatures_real'] ?? $j['candidatures_count'] ?? 0);
                $taux       = $vues > 0 ? round($apps / $vues * 100, 1) : 0;
              ?>
                <tr style="border-bottom: 1px solid #F1F5F9; transition:background 0.15s;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background=''">
                  <td style="padding: 1rem 0;">
                    <div style="font-weight: 700; color: #081326; margin-bottom: 0.35rem;"><?= htmlspecialchars($j['titre']) ?></div>
                    <div style="display:flex; gap:0.4rem; flex-wrap:wrap;">
                      <?php if ($isBoosted): ?>
                        <span style="background:#EFF6FF; color:#2563EB; font-size:0.68rem; font-weight:700; padding:0.15rem 0.55rem; border-radius:99px; border:1px solid #BFDBFE;">BOOST ACTIF</span>
                      <?php endif; ?>
                      <?php if ($isFeatured): ?>
                        <span style="background:#FFFBEB; color:#B45309; font-size:0.68rem; font-weight:700; padding:0.15rem 0.55rem; border-radius:99px; border:1px solid #FDE68A;">A LA UNE</span>
                      <?php endif; ?>
                      <?php if ($daysLeft !== null && $daysLeft <= 7 && $daysLeft >= 0): ?>
                        <span style="background:#FEF2F2; color:#DC2626; font-size:0.68rem; font-weight:700; padding:0.15rem 0.55rem; border-radius:99px;">Expire dans <?= $daysLeft ?>j</span>
                      <?php endif; ?>
                    </div>
                  </td>
                  <td style="padding: 1rem 0; color: #64748B;">
                    <?= date('d/m/Y', strtotime($j['created_at'])) ?>
                  </td>
                  <td style="padding: 1rem 0;">
                    <?php if ($j['statut'] === 'active'): ?>
                      <span style="background: #DCFCE7; color: #166534; padding: 0.25rem 0.75rem; border-radius: 99px; font-size: 0.85rem; font-weight: 600;">Active</span>
                    <?php elseif ($j['statut'] === 'en_attente'): ?>
                      <span style="background: #FEF3C7; color: #B45309; padding: 0.25rem 0.75rem; border-radius: 99px; font-size: 0.85rem; font-weight: 600;">En attente</span>
                    <?php elseif ($j['statut'] === 'refusee'): ?>
                      <span style="background: #FEF2F2; color: #DC2626; padding: 0.25rem 0.75rem; border-radius: 99px; font-size: 0.85rem; font-weight: 600;">Refusée</span>
                    <?php else: ?>
                      <span style="background: #F1F5F9; color: #475569; padding: 0.25rem 0.75rem; border-radius: 99px; font-size: 0.85rem; font-weight: 600;"><?= ucfirst($j['statut']) ?></span>
                    <?php endif; ?>
                  </td>
                  <td style="padding: 1rem 0; color: #64748B; font-size:0.9rem;"><?= number_format($vues) ?></td>
                  <td style="padding: 1rem 0; font-weight: 700; color: #2563EB;"><?= $apps ?></td>
                  <td style="padding: 1rem 0;">
                    <?php
                      $tauxColor = $taux >= 5 ? '#059669' : ($taux >= 1 ? '#D97706' : '#94A3B8');
                    ?>
                    <span style="font-size:0.82rem; font-weight:700; color:<?= $tauxColor ?>"><?= $taux ?>%</span>
                  </td>
                  <td style="padding: 1rem 0; text-align: right; display:flex; gap:0.5rem; justify-content:flex-end;">
                    <a href="job_id=<?= $j['id'] ?>" class="btn-outline" style="padding: 0.5rem 1rem; font-size: 0.85rem;">Candidatures</a>
                    <a href="id=<?= $j['id'] ?>" target="_blank" style="display:inline-flex; align-items:center; gap:0.35rem; padding:0.5rem 0.75rem; border-radius:8px; border:1px solid #E2E8F0; color:#475569; font-size:0.8rem; text-decoration:none;">
                      Voir <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>




