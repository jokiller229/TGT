<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('candidat');

$activePage = 'dashboard';
$userId = $_SESSION['user_id'];
$pdo = getDB();
$user = getCurrentUser();

// Fetch candidate stats
$stmt = $pdo->prepare("SELECT COUNT(*) FROM applications WHERE candidate_id = ?");
$stmt->execute([$userId]);
$candidaturesCount = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM saved_jobs WHERE user_id = ?");
$stmt->execute([$userId]);
$savedCount = (int)$stmt->fetchColumn();

// Fetch full profile for completion checklist
$stmt = $pdo->prepare("SELECT * FROM candidate_profiles WHERE user_id = ?");
$stmt->execute([$userId]);
$profileData = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

// Build checklist
$checklistItems = [
    'titre_professionnel'  => ['label' => 'Titre professionnel',   'done' => !empty($profileData['titre_professionnel'])],
    'bio'                  => ['label' => 'Biographie / Resume',    'done' => !empty($profileData['bio'])],
    'competences'          => ['label' => 'Competences cles',       'done' => !empty($profileData['competences'])],
    'ville'                => ['label' => 'Ville de residence',     'done' => !empty($profileData['ville'])],
    'disponibilite'        => ['label' => 'Disponibilite',          'done' => !empty($profileData['disponibilite'])],
    'experience_annees'    => ['label' => "Annees d'experience",    'done' => !empty($profileData['experience_annees']) && (int)$profileData['experience_annees'] > 0],
    'cv_file'              => ['label' => 'CV telecharge (PDF)',     'done' => !empty($profileData['cv_file'])],
    'pretention_salariale' => ['label' => 'Pretention salariale',   'done' => !empty($profileData['pretention_salariale']) && (int)$profileData['pretention_salariale'] > 0],
];
$doneCount   = count(array_filter($checklistItems, fn($i) => $i['done']));
$totalItems  = count($checklistItems);
$completionPct = $totalItems > 0 ? (int)round($doneCount / $totalItems * 100) : 0;

// Fetch recent notifications
$notifStmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$notifStmt->execute([$userId]);
$notifications = $notifStmt->fetchAll();
$unreadCount = count(array_filter($notifications, fn($n) => !$n['lu']));

// Fetch recent applications
$recentAppsStmt = $pdo->prepare("
    SELECT a.statut, a.created_at, j.titre, c.nom as company_nom
    FROM applications a
    JOIN jobs j ON a.job_id = j.id
    JOIN companies c ON j.company_id = c.id
    WHERE a.candidate_id = ?
    ORDER BY a.created_at DESC
    LIMIT 3
");
$recentAppsStmt->execute([$userId]);
$recentApps = $recentAppsStmt->fetchAll();

$hideHeader = true;
include __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/candidat_sidebar.php'; ?>

  <main class="dashboard-content-main">

    <!-- PWA App Top Bar (Visible only on installed PWA) -->
    <div class="pwa-only-view">
      <div class="app-top-bar">
        <div class="app-top-bar-logo">
          <img src="../img/tgtravail-logo.png" alt="TGTravail" style="width: 24px; height: 24px; border-radius: 4px;">
          <span><span class="tg">TG</span>Travail</span>
        </div>
        <div class="app-top-bar-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
          </svg>
          <?php if ($unreadCount > 0): ?>
            <span class="app-top-bar-badge"><?= $unreadCount ?></span>
          <?php endif; ?>
        </div>
      </div>

      <!-- Header Content (Mockup Style) -->
      <div class="dashboard-header" style="margin-bottom: 2rem;">
        <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--color-primary-dark);">
          Bonjour <?= htmlspecialchars(explode(' ', $_SESSION['user_nom'] ?? '')[0]) ?> 👋
        </h1>
        <p style="color: var(--text-muted); margin-top: 0.25rem; font-size: 0.95rem;">Que recherchez-vous aujourd'hui ?</p>
        
        <!-- Fake Search Bar leading to offres.php -->
        <a href="../pages/offres.php" style="display: flex; align-items: center; gap: 0.75rem; background: #F1F5F9; border-radius: 12px; padding: 0.85rem 1rem; margin-top: 1rem; color: #64748B; text-decoration: none;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
          <span style="font-size: 0.9rem;">Métier, compétence ou entreprise</span>
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-left: auto;"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
        </a>
      </div>

      <!-- Catégories populaires (Mockup Style) -->
      <div style="margin-bottom: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
          <h2 style="font-size: 1.1rem; font-weight: 700; color: #0F172A;">Catégories populaires</h2>
          <a href="../pages/offres.php" style="font-size: 0.8rem; color: #2563EB; font-weight: 600;">Voir tout</a>
        </div>
        <div style="display: flex; gap: 1rem; overflow-x: auto; padding-bottom: 0.5rem; scrollbar-width: none;">
          <!-- Commerce -->
          <a href="../pages/offres.php?q=commerce" style="min-width: 80px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;">
            <div style="width: 50px; height: 50px; background: #EFF6FF; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #3B82F6;">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <span style="font-size: 0.75rem; font-weight: 600; color: #334155;">Commerce</span>
          </a>
          <!-- Informatique -->
          <a href="../pages/offres.php?q=informatique" style="min-width: 80px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;">
            <div style="width: 50px; height: 50px; background: #FAF5FF; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #9333EA;">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
            </div>
            <span style="font-size: 0.75rem; font-weight: 600; color: #334155;">Informatique</span>
          </a>
          <!-- Comptabilité -->
          <a href="../pages/offres.php?q=comptabilité" style="min-width: 80px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;">
            <div style="width: 50px; height: 50px; background: #FFFBEB; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #D97706;">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2" ry="2"></rect><rect x="9" y="9" width="6" height="6"></rect><line x1="9" y1="1" x2="9" y2="4"></line><line x1="15" y1="1" x2="15" y2="4"></line><line x1="9" y1="20" x2="9" y2="23"></line><line x1="15" y1="20" x2="15" y2="23"></line><line x1="20" y1="9" x2="23" y2="9"></line><line x1="20" y1="14" x2="23" y2="14"></line><line x1="1" y1="9" x2="4" y2="9"></line><line x1="1" y1="14" x2="4" y2="14"></line></svg>
            </div>
            <span style="font-size: 0.75rem; font-weight: 600; color: #334155;">Compta</span>
          </a>
          <!-- Marketing -->
          <a href="../pages/offres.php?q=marketing" style="min-width: 80px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 0.5rem;">
            <div style="width: 50px; height: 50px; background: #FEF2F2; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #DC2626;">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20v-6M6 20V10M18 20V4"></path></svg>
            </div>
            <span style="font-size: 0.75rem; font-weight: 600; color: #334155;">Marketing</span>
          </a>
        </div>
      </div>
    </div>

    <!-- Original Web Header -->
    <div class="dashboard-header web-only-view" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem; flex-wrap:wrap; gap:1rem;">
      <div class="dashboard-header-left">
        <button class="dashboard-mobile-btn" onclick="document.querySelector('.dashboard-sidebar-dark').classList.toggle('open'); event.stopPropagation();">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>
        <div>
          <h1 style="font-size:1.75rem; font-weight:800; color:var(--color-primary-dark);">
            Bonjour, <?= htmlspecialchars($_SESSION['user_nom'] ?? '') ?> !
          </h1>
          <p style="color:var(--text-muted); margin-top:0.25rem;">Voici un recap de votre recherche d'emploi.</p>
        </div>
      </div>
      <div style="display:flex; align-items:center; gap:1.25rem;">
        <div class="candidat-selector-dropdown" style="position: relative; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; padding: 0.4rem 1rem; border-radius: 99px; background: white; border: 1px solid #E2E8F0; font-weight: 600; font-size: 0.9rem;" onclick="this.querySelector('.dropdown-menu').classList.toggle('show')">
          <span style="color: #2563EB;">●</span>
          <span><?= htmlspecialchars($_SESSION['user_nom'] ?? 'Mon Profil') ?></span>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-left: 0.5rem;"><polyline points="6 9 12 15 18 9"></polyline></svg>
          
          <div class="dropdown-menu" style="position: absolute; top: 100%; right: 0; margin-top: 0.5rem; background: white; border: 1px solid #E2E8F0; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); min-width: 200px; display: none; z-index: 100; flex-direction: column;">
            <a href="../index.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem 1.25rem; color: #1e293b; text-decoration: none; font-size: 0.9rem; font-weight: 500; transition: background 0.2s;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
              Accueil du site
            </a>
            <a href="../candidat/candidat-profil.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem 1.25rem; color: #1e293b; text-decoration: none; font-size: 0.9rem; font-weight: 500; border-top: 1px solid #F1F5F9; transition: background 0.2s;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
              Mon profil / CV
            </a>
          </div>
          <style>
            .candidat-selector-dropdown .dropdown-menu.show { display: flex !important; }
          </style>
          <script>
            document.addEventListener('click', function(e) {
              if (!e.target.closest('.candidat-selector-dropdown')) {
                document.querySelectorAll('.candidat-selector-dropdown .dropdown-menu').forEach(function(menu) {
                  menu.classList.remove('show');
                });
              }
            });
          </script>
        </div>

        <!-- Notifications dropdown -->
        <div id="smart-notif-container" style="position:relative; display:flex; align-items:center;">
          <a href="#" style="position:relative; color:var(--text-muted); display:flex;" title="Notifications">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
              <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            </svg>
          </a>
        </div>
      </div>
    </div>

    <!-- Quick Stats -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1.5rem; margin-bottom:2rem;">

      <!-- Profil Completion -->
      <div style="background:var(--bg-surface); border:1px solid var(--border-light); border-radius:var(--radius-xl); padding:1.5rem; box-shadow:var(--shadow-sm);">
        <div style="font-size:0.8rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.5rem;">Profil</div>
        <div style="font-size:2rem; font-weight:900; color:<?= $completionPct >= 80 ? '#059669' : ($completionPct >= 50 ? '#D97706' : '#DC2626') ?>;"><?= $completionPct ?>%</div>
        <div style="height:6px; background:#F1F5F9; border-radius:99px; margin-top:0.75rem; overflow:hidden;">
          <div style="height:100%; width:<?= $completionPct ?>%; background:<?= $completionPct >= 80 ? '#059669' : ($completionPct >= 50 ? '#D97706' : '#DC2626') ?>; border-radius:99px; transition:width 0.6s;"></div>
        </div>
        <div style="font-size:0.78rem; color:var(--text-muted); margin-top:0.5rem;"><?= $doneCount ?>/<?= $totalItems ?> elements completes</div>
      </div>

      <!-- Candidatures -->
      <div style="background:var(--bg-surface); border:1px solid var(--border-light); border-radius:var(--radius-xl); padding:1.5rem; box-shadow:var(--shadow-sm);">
        <div style="font-size:0.8rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.5rem;">Candidatures</div>
        <div style="font-size:2rem; font-weight:900; color:var(--color-primary-dark);"><?= $candidaturesCount ?></div>
        <a href="../candidat/mes-candidatures.php" style="font-size:0.8rem; color:#2563EB; text-decoration:none; display:inline-block; margin-top:0.75rem; font-weight:600;">Voir toutes &rarr;</a>
      </div>

      <!-- Offres sauvegardees -->
      <div style="background:var(--bg-surface); border:1px solid var(--border-light); border-radius:var(--radius-xl); padding:1.5rem; box-shadow:var(--shadow-sm);">
        <div style="font-size:0.8rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.5rem;">Offres sauvegardees</div>
        <div style="font-size:2rem; font-weight:900; color:var(--color-primary-dark);"><?= $savedCount ?></div>
        <a href="../candidat/offres-sauvegardees.php" style="font-size:0.8rem; color:#2563EB; text-decoration:none; display:inline-block; margin-top:0.75rem; font-weight:600;">Voir toutes &rarr;</a>
      </div>

    </div>

    <!-- Grid: Checklist + Candidatures recentes -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; margin-bottom:2rem;">

      <!-- Checklist profil -->
      <div style="background:var(--bg-surface); border:1px solid var(--border-light); border-radius:var(--radius-xl); padding:1.5rem; box-shadow:var(--shadow-sm);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem;">
          <h2 style="font-size:1rem; font-weight:800; color:var(--color-primary-dark); margin:0;">Completez votre profil</h2>
          <a href="../candidat/candidat-profil.php" style="font-size:0.8rem; color:#2563EB; font-weight:600; text-decoration:none;">Modifier &rarr;</a>
        </div>
        <?php if ($completionPct >= 100): ?>
          <div style="text-align:center; padding:1.5rem 0;">
            <div style="width:48px; height:48px; background:#ECFDF5; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 0.75rem;">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <p style="font-weight:700; color:#059669; margin:0;">Profil completement rempli !</p>
          </div>
        <?php else: ?>
          <div style="display:flex; flex-direction:column; gap:0.6rem;">
            <?php foreach ($checklistItems as $key => $item): ?>
              <div style="display:flex; align-items:center; gap:0.75rem; padding:0.5rem 0.75rem; border-radius:10px; background:<?= $item['done'] ? '#F0FDF4' : '#FFF7ED' ?>;">
                <div style="width:20px; height:20px; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0; background:<?= $item['done'] ? '#059669' : '#E2E8F0' ?>;">
                  <?php if ($item['done']): ?>
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#FFF" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                  <?php else: ?>
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                  <?php endif; ?>
                </div>
                <span style="font-size:0.85rem; font-weight:<?= $item['done'] ? '500' : '700' ?>; color:<?= $item['done'] ? '#475569' : '#B45309' ?>; text-decoration:<?= $item['done'] ? 'line-through' : 'none' ?>;">
                  <?= $item['label'] ?>
                </span>
                <?php if (!$item['done']): ?>
                  <a href="../candidat/candidat-profil.php" style="margin-left:auto; font-size:0.72rem; color:#2563EB; font-weight:700; text-decoration:none; white-space:nowrap;">Ajouter &rarr;</a>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Candidatures recentes -->
      <div style="background:var(--bg-surface); border:1px solid var(--border-light); border-radius:var(--radius-xl); padding:1.5rem; box-shadow:var(--shadow-sm);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem;">
          <h2 style="font-size:1rem; font-weight:800; color:var(--color-primary-dark); margin:0;">Candidatures recentes</h2>
          <a href="../candidat/mes-candidatures.php" style="font-size:0.8rem; color:#2563EB; font-weight:600; text-decoration:none;">Toutes &rarr;</a>
        </div>
        <?php if (empty($recentApps)): ?>
          <div style="text-align:center; padding:2rem 1rem;">
            <div style="width:48px; height:48px; background:#EFF6FF; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 0.75rem;">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
            <p style="font-size:0.9rem; color:#64748B; margin:0 0 1rem;">Vous n'avez pas encore postule.</p>
            <a href="../pages/offres.php" style="display:inline-block; padding:0.6rem 1.25rem; border-radius:10px; background:#2563EB; color:#FFF; font-size:0.85rem; font-weight:700; text-decoration:none;">
              Decouvrir les offres
            </a>
          </div>
        <?php else: ?>
          <div style="display:flex; flex-direction:column; gap:0.75rem;">
            <?php
              $statusConfig = [
                'nouveau'    => ['Envoyee',       '#64748B', '#F1F5F9'],
                'en_attente' => ['En attente',    '#D97706', '#FFFBEB'],
                'evaluation' => ['En evaluation', '#7C3AED', '#F5F3FF'],
                'vue'        => ['Vue',            '#2563EB', '#EFF6FF'],
                'retenu'     => ['Retenu(e)',      '#059669', '#ECFDF5'],
                'entretien'  => ['Entretien',      '#059669', '#ECFDF5'],
                'embauche'   => ['Recrute(e)',     '#065F46', '#D1FAE5'],
                'refuse'     => ['Non retenu(e)', '#DC2626', '#FEF2F2'],
              ];
              foreach ($recentApps as $app):
                [$bt, $bc, $bb] = $statusConfig[$app['statut']] ?? [ucfirst($app['statut']), '#64748B', '#F1F5F9'];
            ?>
            <div style="display:flex; justify-content:space-between; align-items:center; padding:0.75rem 1rem; background:#F8FAFC; border-radius:12px; border:1px solid #E2E8F0;">
              <div>
                <div style="font-size:0.875rem; font-weight:700; color:#081326;"><?= htmlspecialchars($app['titre']) ?></div>
                <div style="font-size:0.75rem; color:#94A3B8; margin-top:0.15rem;"><?= htmlspecialchars($app['company_nom']) ?> · <?= date('d/m', strtotime($app['created_at'])) ?></div>
              </div>
              <span style="padding:0.25rem 0.7rem; border-radius:99px; font-size:0.72rem; font-weight:700; background:<?= $bb ?>; color:<?= $bc ?>; white-space:nowrap; flex-shrink:0;"><?= $bt ?></span>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

    </div>

    <!-- CTA section -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem;">
      <a href="../pages/offres.php" style="display:block; padding:1.5rem; background:linear-gradient(135deg,#2563EB,#1D4ED8); border-radius:16px; text-decoration:none; color:#FFF; transition:transform 0.2s; box-shadow:0 4px 15px rgba(37,99,235,0.25);" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''">
        <div style="font-size:0.8rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; opacity:0.75; margin-bottom:0.35rem;">Opportunites</div>
        <div style="font-size:1.1rem; font-weight:800; margin-bottom:0.25rem;">Parcourir les offres</div>
        <div style="font-size:0.85rem; opacity:0.8;">Trouvez le poste qui correspond a votre profil</div>
      </a>
      <a href="canditdat-alertes.php" style="display:block; padding:1.5rem; background:var(--bg-surface); border:1px solid var(--border-light); border-radius:16px; text-decoration:none; color:var(--color-primary-dark); transition:all 0.2s; box-shadow:var(--shadow-sm);" onmouseover="this.style.borderColor='#2563EB';this.style.transform='translateY(-2px)'" onmouseout="this.style.borderColor='var(--border-light)';this.style.transform=''">
        <div style="font-size:0.8rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-muted); margin-bottom:0.35rem;">Mes alertes</div>
        <div style="font-size:1.1rem; font-weight:800; margin-bottom:0.25rem;">Configurer mes alertes</div>
        <div style="font-size:0.85rem; color:var(--text-muted);">Soyez notifie des nouvelles offres correspondantes</div>
      </a>
    </div>

  </main>
</div>

<!-- Clic à l'extérieur pour fermer la sidebar mobile -->
<script>
  document.addEventListener('click', function(e) {
    const sidebar = document.querySelector('.dashboard-sidebar-dark');
    if (sidebar && sidebar.classList.contains('open')) {
      if (!e.target.closest('.dashboard-sidebar-dark') && !e.target.closest('.dashboard-mobile-btn')) {
        sidebar.classList.remove('open');
      }
    }
  });
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>




