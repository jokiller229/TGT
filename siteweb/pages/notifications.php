<?php
$activePage = 'notifications';
$pageTitle = 'Notifications - TGTravail';
require_once __DIR__ . '/../includes/auth.php';
requireRole('recruteur');

$db = getDB();
$companyId = getCurrentCompanyId();
if (!$companyId) { header("Location: ../index.php"); exit; }

$user = getCurrentUser();
$stmtComp = $db->prepare("SELECT nom, logo FROM companies WHERE id = ?");
$stmtComp->execute([$companyId]);
$company = $stmtComp->fetch();
$companyName = $company['nom'] ?? 'Mon Entreprise';
$companyLogo = $company['logo'] ?? null;

// Build notifications from real data: new applications
$stmtNotifs = $db->prepare("
  SELECT 'application' as type, a.id, a.statut, a.created_at,
         u.nom AS candidate_nom, u.avatar AS candidate_avatar,
         j.titre AS job_titre, '' AS message
  FROM applications a
  JOIN users u ON a.candidate_id = u.id
  JOIN jobs j ON a.job_id = j.id
  WHERE j.company_id = ?
");
$stmtNotifs->execute([$companyId]);
$apps = $stmtNotifs->fetchAll(PDO::FETCH_ASSOC);

// Build admin broadcasts
$stmtAdmin = $db->prepare("
  SELECT 'admin' as type, id, '' as statut, created_at,
         'Admin TGTravail' as candidate_nom, '' as candidate_avatar,
         '' as job_titre, message
  FROM notifications
  WHERE user_id = ?
");
$stmtAdmin->execute([$user['id']]);
$adminBroadcasts = $stmtAdmin->fetchAll(PDO::FETCH_ASSOC);

$notifications = array_merge($apps, $adminBroadcasts);
usort($notifications, function($a, $b) {
    return strtotime($b['created_at']) <=> strtotime($a['created_at']);
});
$notifications = array_slice($notifications, 0, 30);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">

  <!-- Sidebar -->
  <?php require __DIR__ . '/../includes/recruiter_sidebar.php'; ?>

  <!-- Main Content -->
  <main class="dashboard-content-main">

    <!-- Topbar -->
    <div class="dashboard-topbar">
      <div>
        <h1 class="user-greeting">Notifications</h1>
        <p style="font-size:0.9rem; color:var(--text-muted);">Toutes vos alertes et activités récentes</p>
      </div>

      <div style="display:flex; align-items:center; gap:1.25rem;">
        <div class="company-selector-dropdown">
          <span style="color:#2563EB;">●</span>
          <span><?= htmlspecialchars($companyName) ?></span>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
        </div>
        <a href="../pages/notifications.php" style="position:relative; color:var(--text-muted); display:flex;" title="Notifications">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2.5"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
        </a>
        <a href="../recruteur/parametres.php" title="Modifier le logo" style="display:flex;">
          <?php if ($companyLogo && file_exists(__DIR__ . '/' . $companyLogo)): ?>
            <img src="<?= htmlspecialchars($companyLogo) ?>" alt="Logo" style="width:42px; height:42px; border-radius:50%; object-fit:cover; border:2px solid #E2E8F0;">
          <?php else: ?>
            <div style="width:42px; height:42px; border-radius:50%; background:#081326; color:#FFB800; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.9rem; text-transform:uppercase; border:2px solid #E2E8F0;">
              <?= htmlspecialchars(strtoupper(substr($user['nom'] ?? 'US', 0, 2))) ?>
            </div>
          <?php endif; ?>
        </a>
      </div>
    </div>

    <!-- Notifications list -->
    <div style="background:#FFF; border-radius:24px; padding:2rem; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05); max-width:800px;">

      <?php if (empty($notifications)): ?>
        <div style="text-align:center; padding:4rem 0; color:#94A3B8;">
          <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity:0.4; margin-bottom:1rem;"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
          <p style="font-weight:600; color:#64748B;">Aucune notification pour le moment.</p>
          <p style="font-size:0.85rem;">Vous serez alerté dès qu'une candidature ou une annonce sera reçue.</p>
        </div>
      <?php else: ?>
        <div style="display:flex; flex-direction:column;">
          <?php foreach ($notifications as $i => $n): ?>
            <?php
              $timeAgo = '';
              $diff = time() - strtotime($n['created_at']);
              if ($diff < 3600) $timeAgo = round($diff/60) . ' min';
              elseif ($diff < 86400) $timeAgo = round($diff/3600) . 'h';
              else $timeAgo = round($diff/86400) . 'j';
            ?>

            <?php if ($n['type'] === 'admin'): ?>
              <!-- Admin Broadcast -->
              <div style="display:flex; align-items:flex-start; gap:1rem; padding:1.25rem 0; border-bottom:<?= $i < count($notifications)-1 ? '1px solid #F1F5F9' : 'none' ?>;">
                <div style="position:relative; flex-shrink:0;">
                  <div style="width:46px; height:46px; border-radius:50%; background:var(--color-primary-navy); color:#FFB800; display:flex; align-items:center; justify-content:center;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3zm-8.27 4a2 2 0 0 1-3.46 0"></path></svg>
                  </div>
                </div>
                <div style="flex:1; min-width:0;">
                  <p style="font-weight:700; color:#081326; margin:0; font-size:0.95rem;">Administration TGTravail</p>
                  <p style="margin:0.3rem 0 0; font-size:0.9rem; color:#1E293B; line-height:1.4;">
                    <?= htmlspecialchars(preg_replace('/^\[ADMIN\] /', '', $n['message'])) ?>
                  </p>
                </div>
                <div style="flex-shrink:0; text-align:right;">
                  <span style="display:inline-block; padding:0.2rem 0.65rem; border-radius:99px; background:#FEE2E2; color:#DC2626; font-size:0.78rem; font-weight:700;">Important</span>
                  <p style="margin:0.3rem 0 0; font-size:0.78rem; color:#94A3B8;">il y a <?= $timeAgo ?></p>
                </div>
              </div>

            <?php else: ?>
              <!-- Application -->
              <?php
                $statusColors = ['nouveau' => '#059669', 'evaluation' => '#D97706', 'entretien' => '#7C3AED', 'embauche' => '#2563EB', 'refuse' => '#DC2626'];
                $statusLabels = ['nouveau' => 'Nouvelle candidature', 'evaluation' => 'En cours d\'évaluation', 'entretien' => 'Entretien programmé', 'embauche' => 'Candidat embauché', 'refuse' => 'Candidature refusée'];
                $color = $statusColors[$n['statut']] ?? '#64748B';
                $label = $statusLabels[$n['statut']] ?? ucfirst($n['statut']);
              ?>
              <a href="user_id=<?= $n['id'] ?>" style="display:flex; align-items:center; gap:1rem; padding:1.25rem 0; border-bottom:<?= $i < count($notifications)-1 ? '1px solid #F1F5F9' : 'none' ?>; text-decoration:none; transition:background 0.15s;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                <div style="position:relative; flex-shrink:0;">
                  <img src="<?= htmlspecialchars($n['candidate_avatar'] ?: 'https://ui-avatars.com/api/?name='.urlencode($n['candidate_nom']).'&background=random') ?>"
                       style="width:46px; height:46px; border-radius:50%; object-fit:cover;" alt="<?= htmlspecialchars($n['candidate_nom']) ?>">
                  <span style="position:absolute; bottom:0; right:0; width:14px; height:14px; background:<?= $color ?>; border-radius:50%; border:2px solid #FFF;"></span>
                </div>
                <div style="flex:1; min-width:0;">
                  <p style="font-weight:700; color:#081326; margin:0; font-size:0.95rem;">
                    <?= htmlspecialchars($n['candidate_nom']) ?>
                    <span style="font-weight:500; color:#64748B;"> — <?= htmlspecialchars($label) ?></span>
                  </p>
                  <p style="margin:0.2rem 0 0; font-size:0.85rem; color:#94A3B8; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                    Offre : <strong><?= htmlspecialchars($n['job_titre']) ?></strong>
                  </p>
                </div>
                <div style="flex-shrink:0; text-align:right;">
                  <span style="display:inline-block; padding:0.2rem 0.65rem; border-radius:99px; background:<?= $color ?>20; color:<?= $color ?>; font-size:0.78rem; font-weight:700;">
                    <?= htmlspecialchars($label) ?>
                  </span>
                  <p style="margin:0.3rem 0 0; font-size:0.78rem; color:#94A3B8;">il y a <?= $timeAgo ?></p>
                </div>
              </a>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

  </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>




