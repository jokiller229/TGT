<?php
$activePage = 'dashboard';
$pageTitle = 'Espace Particulier - TGTravail';
require_once __DIR__ . '/../includes/auth.php';
requireRole('recruteur');

$db = getDB();
$companyId = getCurrentCompanyId();
if (!$companyId) {
    header("Location: ../index.php");
    exit;
}

$user = getCurrentUser();
$stmtComp = $db->prepare("SELECT nom, logo, type_entite, verifie, rccm, site_web, description, adresse, cni_document FROM companies WHERE id = ?");
$stmtComp->execute([$companyId]);
$company = $stmtComp->fetch();
$companyName = $company ? $company['nom'] : 'Mon Entreprise';


// Calcul des KPIs réels depuis MySQL
$activeJobsCount = (int)$db->query("SELECT COUNT(*) FROM jobs WHERE company_id = {$companyId} AND statut = 'active'")->fetchColumn();

$totalApplications = (int)$db->query("
  SELECT COUNT(a.id) 
  FROM applications a 
  JOIN jobs j ON a.job_id = j.id 
  WHERE j.company_id = {$companyId}
")->fetchColumn();

$interviewsCount = (int)$db->query("
  SELECT COUNT(a.id) 
  FROM applications a 
  JOIN jobs j ON a.job_id = j.id 
  WHERE j.company_id = {$companyId} AND a.statut = 'entretien'
")->fetchColumn();

$hiresCount = (int)$db->query("
  SELECT COUNT(a.id) 
  FROM applications a 
  JOIN jobs j ON a.job_id = j.id 
  WHERE j.company_id = {$companyId} AND a.statut = 'embauche'
")->fetchColumn();

// Candidatures récentes réelles depuis MySQL
$recentAppsStmt = $db->query("
  SELECT a.*, u.nom AS candidate_nom, u.avatar AS candidate_avatar, j.titre AS job_titre, cp.experience_annees
  FROM applications a
  JOIN users u ON a.candidate_id = u.id
  JOIN jobs j ON a.job_id = j.id
  LEFT JOIN candidate_profiles cp ON u.id = cp.user_id
  WHERE j.company_id = {$companyId}
  ORDER BY a.created_at DESC
  LIMIT 5
");
$recentApplications = $recentAppsStmt->fetchAll();

// Top offres les plus performantes
$topJobsStmt = $db->query("
  SELECT j.id, j.titre, j.candidatures_count, j.vues_count
  FROM jobs j
  WHERE j.company_id = {$companyId}
  ORDER BY j.candidatures_count DESC
  LIMIT 3
");
$topJobs = $topJobsStmt->fetchAll();

// Handle Onboarding Submission
$onboardingSuccess = false;
$uploadError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_onboarding'])) {
    $rccm = $_POST['rccm'] ?? '';
    $site_web = $_POST['site_web'] ?? '';
    $description = $_POST['description'] ?? '';
    $adresse = $_POST['adresse'] ?? '';
    
    // Upload CNI
    $cni_path = $company['cni_document'] ?? '';
    if (isset($_FILES['cni_upload']) && $_FILES['cni_upload']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
        if (in_array($_FILES['cni_upload']['type'], $allowedTypes)) {
            $uploadDir = __DIR__ . '/../uploads/cni/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $filename = uniqid('cni_') . '_' . basename($_FILES['cni_upload']['name']);
            $targetPath = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['cni_upload']['tmp_name'], $targetPath)) {
                $cni_path = 'uploads/cni/' . $filename;
            } else {
                $uploadError = 'Erreur lors de la sauvegarde du fichier.';
            }
        } else {
            $uploadError = 'Format de fichier non autorisé (JPEG, PNG, PDF uniquement).';
        }
    }
    
    if (!$uploadError) {
        $upd = $db->prepare("UPDATE companies SET rccm = ?, site_web = ?, description = ?, adresse = ?, cni_document = ? WHERE id = ?");
        $upd->execute([$rccm, $site_web, $description, $adresse, $cni_path, $companyId]);
        
        $company['rccm'] = $rccm;
        $company['site_web'] = $site_web;
        $company['description'] = $description;
        $company['adresse'] = $adresse;
        $company['cni_document'] = $cni_path;
        $onboardingSuccess = true;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
  
  <!-- Dark Sidebar Left (Maquette Écran 4) -->
  <?php require __DIR__ . '/../includes/recruiter_sidebar.php'; ?>

  <!-- Main Content Area Right -->
  <main class="dashboard-content-main">
    
    <!-- Topbar -->
    <div class="dashboard-topbar">
      <div class="dashboard-header-left" style="display:flex; align-items:center; gap:1rem;">
        <button class="dashboard-mobile-btn" onclick="document.querySelector('.dashboard-sidebar-dark').classList.toggle('open'); event.stopPropagation();">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>
        <div>
        <h1 class="user-greeting">Espace Particulier</h1>
        <p style="font-size: 0.9rem; color: var(--text-muted);">Bienvenue, <?= htmlspecialchars($user['nom'] ?: 'Utilisateur') ?> 👋</p>
      </div>
      </div>

      <div style="display: flex; align-items: center; gap: 1.25rem;">
        <div class="company-selector-dropdown" style="position: relative; cursor: pointer;" onclick="this.querySelector('.dropdown-menu').classList.toggle('show')">
          <span style="color: #2563EB;">●</span>
          <span><?= htmlspecialchars($companyName ?? 'Mon Profil') ?></span>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
          
          <div class="dropdown-menu" style="position: absolute; top: 100%; right: 0; margin-top: 0.5rem; background: white; border: 1px solid #E2E8F0; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); min-width: 200px; display: none; z-index: 100; flex-direction: column;">
            <a href="../index.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem 1.25rem; color: #1e293b; text-decoration: none; font-size: 0.9rem; font-weight: 500; transition: background 0.2s;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
              Accueil du site
            </a>
            <a href="../recruteur/parametres.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem 1.25rem; color: #1e293b; text-decoration: none; font-size: 0.9rem; font-weight: 500; border-top: 1px solid #F1F5F9; transition: background 0.2s;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
              Mon profil
            </a>
          </div>
          <style>
            .company-selector-dropdown .dropdown-menu.show { display: flex !important; }
          </style>
          <script>
            document.addEventListener('click', function(e) {
              if (!e.target.closest('.company-selector-dropdown')) {
                document.querySelectorAll('.company-selector-dropdown .dropdown-menu').forEach(function(menu) {
                  menu.classList.remove('show');
                });
              }
            });
          </script>
        </div>

        <div id="smart-notif-container" style="position:relative; display:flex; align-items:center;">
          <a href="../pages/notifications.php" style="position:relative; color:var(--text-muted); display:flex;" title="Notifications">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
          </a>
        </div>

        <a href="../recruteur/parametres.php" title="Paramètres / Logo entreprise" style="display:flex;">
          <?php if (!empty($company['logo']) && file_exists(__DIR__ . '/' . $company['logo'])): ?>
            <img src="<?= htmlspecialchars($company['logo']) ?>" alt="Logo" style="width:42px; height:42px; border-radius:50%; object-fit:cover; border:2px solid #E2E8F0;">
          <?php else: ?>
            <div style="width:42px; height:42px; border-radius:50%; background:#081326; color:#FFB800; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.9rem; text-transform:uppercase; border:2px solid #E2E8F0;">
              <?= htmlspecialchars(strtoupper(substr($user['nom'], 0, 2))) ?>
            </div>
          <?php endif; ?>
        </a>
      </div>
    </div>

    <?php if (!$company['verifie']): ?>
      <!-- Onboarding Section -->
      <div class="dashboard-card-white" style="margin-bottom: 2rem; border-left: 4px solid #FFB800;">
        <h2 style="font-size: 1.25rem; font-weight: 700; color: #1e293b; margin-bottom: 0.5rem;">Vérification de votre compte entreprise</h2>
        
        <?php if (($company['type_entite'] === 'entreprise' && !empty($company['rccm']) && !empty($company['description'])) || ($company['type_entite'] === 'particulier' && !empty($company['cni_document']))): ?>
            <p style="color: #64748b; margin-bottom: 1.5rem;">Votre dossier est actuellement en cours d'examen par nos administrateurs. Vous serez notifié dès qu'il sera validé et que vous aurez accès à toutes les fonctionnalités.</p>
            <?php if ($onboardingSuccess): ?>
                <div style="background-color: #d1fae5; color: #065f46; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                    Vos informations ont bien été mises à jour.
                </div>
            <?php endif; ?>
        <?php else: ?>
            <p style="color: #ef4444; margin-bottom: 1.5rem;">Pour accéder à l'ensemble des fonctionnalités (Offres, Candidatures, Messages, etc.), vous devez compléter votre profil.</p>
            
            <?php if (!empty($uploadError)): ?>
                <div style="background-color: #fee2e2; color: #b91c1c; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                    <?= htmlspecialchars($uploadError) ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data" style="max-width: 600px;">
                <?php if ($company['type_entite'] === 'entreprise'): ?>
                    <div style="margin-bottom: 1rem;">
                        <label style="display:block; font-weight: 500; margin-bottom: 0.25rem;">Numéro RCCM / SIRET *</label>
                        <input type="text" name="rccm" required class="form-input" placeholder="Ex: TG-LOM-2026-B-1234" value="<?= htmlspecialchars($company['rccm'] ?? '') ?>" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label style="display:block; font-weight: 500; margin-bottom: 0.25rem;">Site Web</label>
                        <input type="url" name="site_web" class="form-input" placeholder="https://www.monentreprise.com" value="<?= htmlspecialchars($company['site_web'] ?? '') ?>" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label style="display:block; font-weight: 500; margin-bottom: 0.25rem;">Adresse</label>
                        <input type="text" name="adresse" class="form-input" placeholder="Adresse complète" value="<?= htmlspecialchars($company['adresse'] ?? '') ?>" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label style="display:block; font-weight: 500; margin-bottom: 0.25rem;">Description de l'entreprise *</label>
                        <textarea name="description" required class="form-input" rows="4" placeholder="Que fait votre entreprise ?" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;"><?= htmlspecialchars($company['description'] ?? '') ?></textarea>
                    </div>
                <?php else: ?>
                    <div style="margin-bottom: 1rem;">
                        <label style="display:block; font-weight: 500; margin-bottom: 0.25rem;">Pièce d'identité (CNI, Passeport) *</label>
                        <input type="file" name="cni_upload" required class="form-input" accept=".jpg,.jpeg,.png,.pdf" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                        <span style="font-size:0.8rem; color:#64748b;">Formats acceptés : JPG, PNG, PDF. Max 5Mo.</span>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label style="display:block; font-weight: 500; margin-bottom: 0.25rem;">Zone de résidence (Ville/Quartier)</label>
                        <input type="text" name="adresse" class="form-input" placeholder="Ex: Lomé, Agoè" value="<?= htmlspecialchars($company['adresse'] ?? '') ?>" style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;">
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <label style="display:block; font-weight: 500; margin-bottom: 0.25rem;">Nature de vos besoins (Optionnel)</label>
                        <textarea name="description" class="form-input" rows="3" placeholder="Ex: Ménage, Garde d'enfants..." style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;"><?= htmlspecialchars($company['description'] ?? '') ?></textarea>
                    </div>
                <?php endif; ?>
                
                <button type="submit" name="submit_onboarding" style="background: #2563EB; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; font-weight: 600; cursor: pointer;">Soumettre pour vérification</button>
            </form>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <!-- 4 Metrics Cards (Données directes MySQL - Maquette Écran 4) -->
    <div class="dashboard-metrics-grid" <?= (!$company['verifie']) ? 'style="opacity: 0.3; pointer-events: none;"' : '' ?>>
      <div class="metric-box">
        <div class="metric-val"><?= $activeJobsCount ?></div>
        <div class="metric-label">Offres actives</div>
      </div>
      <div class="metric-box">
        <div class="metric-val"><?= $totalApplications ?></div>
        <div class="metric-label">Candidatures</div>
      </div>
      <div class="metric-box">
        <div class="metric-val"><?= $interviewsCount ?></div>
        <div class="metric-label">Entretiens</div>
      </div>
      <div class="metric-box">
        <div class="metric-val"><?= $hiresCount ?></div>
        <div class="metric-label">Embauches</div>
      </div>
    </div>

    <!-- 2-Columns Grid: Candidatures récentes + Performance -->
    <div class="dashboard-split-grid" <?= (!$company['verifie']) ? 'style="opacity: 0.3; pointer-events: none;"' : '' ?>>
      
      <!-- Left: Candidatures récentes (Maquette 4) -->
      <div class="dashboard-card-white">
        <div class="card-header-row">
          <h2 class="card-header-title">Candidatures récentes</h2>
          <a href="../recruteur/candidatures.php" style="font-size: 0.85rem; font-weight: 600; color: var(--color-primary-blue);">Voir tout</a>
        </div>

        <div style="display: flex; flex-direction: column; gap: 1rem;">
          <?php foreach ($recentApplications as $app): 
            $statusClass = 'badge-nouveau';
            $statusLabel = 'Nouveau';
            if ($app['statut'] === 'evaluation') { $statusClass = 'badge-evaluation'; $statusLabel = 'À évaluer'; }
            elseif ($app['statut'] === 'entretien') { $statusClass = 'badge-entretien'; $statusLabel = 'Entretien'; }
            elseif ($app['statut'] === 'embauche') { $statusClass = 'badge-embauche'; $statusLabel = 'Embauché'; }
            elseif ($app['statut'] === 'refuse') { $statusClass = 'badge-refuse'; $statusLabel = 'Refusé'; }
          ?>
          <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem 0; border-bottom: 1px solid var(--border-subtle);">
            <div style="display: flex; align-items: center; gap: 0.85rem;">
              <img src="<?= htmlspecialchars($app['candidate_avatar'] ?: 'https://ui-avatars.com/api/?name='.urlencode($app['candidate_nom']).'&background=random') ?>" style="width: 44px; height: 44px; border-radius: var(--radius-pill); object-fit: cover;" alt="<?= htmlspecialchars($app['candidate_nom']) ?>">
              <div>
                <h3 style="font-size: 0.925rem; font-weight: 700; color: var(--color-primary-dark);"><?= htmlspecialchars($app['candidate_nom']) ?></h3>
                <p style="font-size: 0.8rem; color: var(--text-muted);"><?= htmlspecialchars($app['job_titre']) ?></p>
              </div>
            </div>
            <span class="badge-status <?= $statusClass ?>"><?= $statusLabel ?></span>
          </div>
          <?php endforeach; ?>
        </div>

        <a href="../recruteur/candidatures.php" style="display: block; text-align: center; margin-top: 1.25rem; font-size: 0.875rem; font-weight: 600; color: var(--color-primary-blue);">
          Voir toutes les candidatures →
        </a>
      </div>

      <!-- Right: Performance & Top Offres (Maquette 4) -->
      <div class="dashboard-card-white">
        <div class="card-header-row">
          <h2 class="card-header-title">Performance de vos offres</h2>
          <span style="font-size: 0.8rem; color: var(--text-muted);">30 derniers jours</span>
        </div>

        <!-- Performance Chart (SVG Line Chart) -->
        <div style="margin: 1rem 0 1.5rem;">
          <svg viewBox="0 0 320 100" style="width: 100%; height: 90px; overflow: visible;">
            <polyline fill="none" stroke="#2563EB" stroke-width="3" points="0,70 60,60 120,40 180,50 240,25 320,35" />
            <polyline fill="none" stroke="#FFB800" stroke-width="2" stroke-dasharray="4" points="0,85 60,75 120,65 180,60 240,45 320,50" />
          </svg>
          <div style="display: flex; gap: 1.5rem; font-size: 0.75rem; color: var(--text-muted); justify-content: center; margin-top: 0.5rem;">
            <span style="display: flex; align-items: center; gap: 0.35rem;"><span style="width: 8px; height: 8px; background: #2563EB; border-radius: 50%;"></span> Vues (<?= isset($topJobs[0]) ? $topJobs[0]['vues_count'] : 0 ?>)</span>
            <span style="display: flex; align-items: center; gap: 0.35rem;"><span style="width: 8px; height: 8px; background: #FFB800; border-radius: 50%;"></span> Candidatures (<?= $totalApplications ?>)</span>
          </div>
        </div>

        <h3 style="font-size: 0.9rem; font-weight: 700; color: var(--color-primary-dark); margin-bottom: 0.75rem;">Offres les plus performantes</h3>
        
        <div style="display: flex; flex-direction: column; gap: 0.6rem; font-size: 0.875rem;">
          <?php foreach ($topJobs as $tj): ?>
          <div style="display: flex; justify-content: space-between;">
            <span style="color: var(--text-main); font-weight: 500;"><?= htmlspecialchars($tj['titre']) ?></span>
            <strong style="color: #2563EB;"><?= $tj['candidatures_count'] ?> candidatures</strong>
          </div>
          <?php endforeach; ?>
        </div>

      </div>

    </div>

  </main>
</div>

<script src="../js/app.js?v=<?= filemtime(__DIR__ . '/js/app.js') ?>"></script>
</body>
</html>




