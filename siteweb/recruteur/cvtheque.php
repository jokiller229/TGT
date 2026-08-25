<?php
$activePage = 'cvtheque';
$pageTitle = 'CVthèque - TGTravail';
require_once __DIR__ . '/../includes/auth.php';
requireRole('recruteur');

$db = getDB();
$companyId = getCurrentCompanyId();
if (!$companyId) { header("Location: ../index.php"); exit; }

$stmtComp = $db->prepare("SELECT subscription_plan, subscription_end FROM companies WHERE id = ?");
$stmtComp->execute([$companyId]);
$companyData = $stmtComp->fetch();
$isPro = (($companyData['subscription_plan'] ?? '') === 'pro' && strtotime($companyData['subscription_end'] ?? '0') > time());

// [BETA] Verification disabled during testing phase
// $stmtComp = getDB()->prepare("SELECT verifie FROM companies WHERE id = ?");
// $stmtComp->execute([$companyId]);
// $companyData = $stmtComp->fetch();
// if (!$companyData || !$companyData['verifie']) {
//     header('Location: ../recruteur/recruteur-dashboard.php');
//     exit;
// }


$user = getCurrentUser();
$stmtComp = $db->prepare("SELECT nom, logo FROM companies WHERE id = ?");
$stmtComp->execute([$companyId]);
$company = $stmtComp->fetch();
$companyName = $company['nom'] ?? 'Mon Entreprise';
$companyLogo = $company['logo'] ?? null;

// ── Filters from GET ──────────────────────────────────────────────────────────
$search       = trim($_GET['q'] ?? '');
$filVille     = trim($_GET['ville'] ?? '');
$filDispo     = trim($_GET['dispo'] ?? '');
$filContrat   = trim($_GET['contrat'] ?? '');
$filExpMin    = (int)($_GET['exp_min'] ?? 0);
$filCompetence = trim($_GET['comp'] ?? '');

// ── Build query ───────────────────────────────────────────────────────────────
$where = ["u.role = 'candidat'", "u.statut_compte = 'actif'"];
$params = [];

if ($search) {
    $where[] = "(u.nom LIKE ? OR cp.titre_professionnel LIKE ? OR cp.competences LIKE ? OR cp.bio LIKE ?)";
    $like = "%{$search}%";
    $params = array_merge($params, [$like, $like, $like, $like]);
}
if ($filVille) {
    $where[] = "cp.ville LIKE ?";
    $params[] = "%{$filVille}%";
}
if ($filDispo) {
    $where[] = "cp.disponibilite = ?";
    $params[] = $filDispo;
}
if ($filContrat) {
    $where[] = "cp.type_contrat_souhaite = ?";
    $params[] = $filContrat;
}
if ($filExpMin) {
    $where[] = "cp.experience_annees >= ?";
    $params[] = $filExpMin;
}
if ($filCompetence) {
    $where[] = "cp.competences LIKE ?";
    $params[] = "%{$filCompetence}%";
}

$sql = "
  SELECT u.id, u.nom, u.email, u.telephone, u.avatar,
         cp.titre_professionnel, cp.ville, cp.experience_annees,
         cp.disponibilite, cp.type_contrat_souhaite, cp.pretention_salariale,
         cp.competences, cp.completion_pct, cp.cv_file
  FROM users u
  LEFT JOIN candidate_profiles cp ON cp.user_id = u.id
  WHERE " . implode(' AND ', $where) . "
  ORDER BY cp.completion_pct DESC, u.created_at DESC
  LIMIT 60
";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$candidates = $stmt->fetchAll();

$totalCount = count($candidates);

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">

  <!-- ── Dark Sidebar ─────────────────────────────────────────────────────── -->
  <?php require __DIR__ . '/../includes/recruiter_sidebar.php'; ?>

  <!-- ── Main Content ──────────────────────────────────────────────────────── -->
  <main class="dashboard-content-main" style="overflow-y:auto; display:flex; flex-direction:column;">

    <!-- Topbar -->
    <div class="dashboard-topbar" style="flex-shrink:0;">
      <div class="dashboard-header-left" style="display:flex; align-items:center; gap:1rem;">
        <button class="dashboard-mobile-btn" onclick="document.querySelector('.dashboard-sidebar-dark').classList.toggle('open'); event.stopPropagation();">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>
        <div>
        <h1 class="user-greeting">CVthèque</h1>
        <p style="font-size:.9rem;color:var(--text-muted);"><?= $totalCount ?> candidat<?= $totalCount > 1 ? 's' : '' ?> trouvé<?= $totalCount > 1 ? 's' : '' ?> · Parcourez les profils et contactez les talents</p>
      </div>
      </div>
      <div style="display:flex;align-items:center;gap:1.25rem;">
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

    <!-- ── Search + Filters bar ─────────────────────────────────────────── -->
    <div style="padding:1.25rem 2rem;background:#FFF;border-bottom:1px solid #E2E8F0;flex-shrink:0;">
      <form method="GET" action="../recruteur/cvtheque.php" id="filter-form">
        <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr auto;gap:.75rem;align-items:end;">

          <!-- Search -->
          <div>
            <div style="display:flex;align-items:center;gap:.5rem;background:#F8FAFC;border:1.5px solid #E2E8F0;border-radius:12px;padding:.65rem 1rem;transition:border-color .2s;">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
              <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Rechercher par nom, titre, compétence..." style="border:none;outline:none;background:transparent;font-size:.875rem;color:#0F172A;width:100%;font-family:inherit;" oninput="autoSubmit()">
            </div>
          </div>

          <!-- Ville -->
          <div>
            <select name="ville" onchange="this.form.submit()" style="width:100%;padding:.65rem 1rem;border:1.5px solid #E2E8F0;border-radius:12px;font-size:.875rem;font-family:inherit;color:#0F172A;outline:none;background:#F8FAFC;cursor:pointer;">
              <option value="">Toutes les villes</option>
              <?php foreach (['Lomé','Kpalimé','Sokodé','Atakpamé','Kara','Tsévié','Aného','Notsé'] as $v): ?>
                <option value="<?= $v ?>" <?= $filVille === $v ? 'selected' : '' ?>><?= $v ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Disponibilité -->
          <div>
            <select name="dispo" onchange="this.form.submit()" style="width:100%;padding:.65rem 1rem;border:1.5px solid #E2E8F0;border-radius:12px;font-size:.875rem;font-family:inherit;color:#0F172A;outline:none;background:#F8FAFC;cursor:pointer;">
              <option value="">Disponibilité</option>
              <?php foreach (['Immédiate','Sous 1 mois','Sous 3 mois','En poste'] as $d): ?>
                <option value="<?= $d ?>" <?= $filDispo === $d ? 'selected' : '' ?>><?= $d ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Contrat souhaité -->
          <div>
            <select name="contrat" onchange="this.form.submit()" style="width:100%;padding:.65rem 1rem;border:1.5px solid #E2E8F0;border-radius:12px;font-size:.875rem;font-family:inherit;color:#0F172A;outline:none;background:#F8FAFC;cursor:pointer;">
              <option value="">Type de contrat</option>
              <?php foreach (['CDI','CDD','Stage','Freelance','Temps partiel','Alternance'] as $c): ?>
                <option value="<?= $c ?>" <?= $filContrat === $c ? 'selected' : '' ?>><?= $c ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Expérience min -->
          <div>
            <select name="exp_min" onchange="this.form.submit()" style="width:100%;padding:.65rem 1rem;border:1.5px solid #E2E8F0;border-radius:12px;font-size:.875rem;font-family:inherit;color:#0F172A;outline:none;background:#F8FAFC;cursor:pointer;">
              <option value="0">Expérience</option>
              <option value="1" <?= $filExpMin==1?'selected':'' ?>>1+ an</option>
              <option value="3" <?= $filExpMin==3?'selected':'' ?>>3+ ans</option>
              <option value="5" <?= $filExpMin==5?'selected':'' ?>>5+ ans</option>
              <option value="10" <?= $filExpMin==10?'selected':'' ?>>10+ ans</option>
            </select>
          </div>

          <!-- Reset -->
          <div>
            <?php if ($search || $filVille || $filDispo || $filContrat || $filExpMin): ?>
              <a href="../recruteur/cvtheque.php" style="display:inline-flex;align-items:center;gap:.4rem;padding:.65rem 1rem;border:1.5px solid #E2E8F0;border-radius:12px;font-size:.8rem;font-weight:700;color:#64748B;text-decoration:none;white-space:nowrap;background:#FFF;transition:all .2s;" onmouseover="this.style.borderColor='#94A3B8'" onmouseout="this.style.borderColor='#E2E8F0'">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                Réinitialiser
              </a>
            <?php else: ?>
              <button type="submit" style="padding:.65rem 1.2rem;border:none;background:#2563EB;color:#FFF;border-radius:12px;font-size:.875rem;font-weight:700;cursor:pointer;font-family:inherit;">Rechercher</button>
            <?php endif; ?>
          </div>
        </div>

        <!-- Active filters chips -->
        <?php if ($search || $filVille || $filDispo || $filContrat || $filExpMin): ?>
        <div style="display:flex;flex-wrap:wrap;gap:.5rem;margin-top:.75rem;">
          <?php if ($search): ?><span style="background:#EFF6FF;color:#2563EB;border-radius:99px;padding:.25rem .85rem;font-size:.75rem;font-weight:700;">🔍 <?= htmlspecialchars($search) ?></span><?php endif; ?>
          <?php if ($filVille): ?><span style="background:#EFF6FF;color:#2563EB;border-radius:99px;padding:.25rem .85rem;font-size:.75rem;font-weight:700;">📍 <?= htmlspecialchars($filVille) ?></span><?php endif; ?>
          <?php if ($filDispo): ?><span style="background:#ECFDF5;color:#059669;border-radius:99px;padding:.25rem .85rem;font-size:.75rem;font-weight:700;">⏱ <?= htmlspecialchars($filDispo) ?></span><?php endif; ?>
          <?php if ($filContrat): ?><span style="background:#FFFBEB;color:#D97706;border-radius:99px;padding:.25rem .85rem;font-size:.75rem;font-weight:700;">📄 <?= htmlspecialchars($filContrat) ?></span><?php endif; ?>
          <?php if ($filExpMin): ?><span style="background:#F5F3FF;color:#7C3AED;border-radius:99px;padding:.25rem .85rem;font-size:.75rem;font-weight:700;">💼 <?= $filExpMin ?>+ ans</span><?php endif; ?>
        </div>
        <?php endif; ?>
      </form>
    </div>

    <!-- ── Candidates Grid ───────────────────────────────────────────────── -->
    <div style="flex:1;padding:1.75rem 2rem;overflow-y:auto;">

      <?php if (empty($candidates)): ?>
        <div style="text-align:center;padding:5rem 2rem;color:#94A3B8;">
          <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity:.3;margin-bottom:1rem;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
          <p style="font-size:1.1rem;font-weight:700;color:#64748B;margin-bottom:.5rem;">Aucun candidat trouvé</p>
          <p style="font-size:.875rem;">Essayez d'ajuster vos filtres de recherche.</p>
          <a href="../recruteur/cvtheque.php" style="display:inline-block;margin-top:1rem;padding:.65rem 1.5rem;background:#2563EB;color:#FFF;border-radius:12px;font-weight:700;text-decoration:none;font-size:.875rem;">Voir tous les candidats</a>
        </div>

      <?php else: ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:1.25rem;">
          <?php foreach ($candidates as $c):
            $skills = array_filter(array_map('trim', explode(',', $c['competences'] ?? '')));
            $skills = array_slice($skills, 0, 5);
            $initials = strtoupper(substr($c['nom'], 0, 2));
            $bgColors = ['#081326','#1E40AF','#7C3AED','#059669','#DC2626','#D97706','#0369A1'];
            $bg = $bgColors[crc32($c['nom']) % count($bgColors)];
            $dispoColor = match($c['disponibilite']) {
              'Immédiate' => ['bg'=>'#ECFDF5','text'=>'#059669'],
              'Sous 1 mois' => ['bg'=>'#EFF6FF','text'=>'#2563EB'],
              'Sous 3 mois' => ['bg'=>'#FFFBEB','text'=>'#D97706'],
              default => ['bg'=>'#F1F5F9','text'=>'#64748B'],
            };
            $completion = (int)($c['completion_pct'] ?? 60);
          ?>
          <div style="background:#FFF;border:1.5px solid #E2E8F0;border-radius:20px;padding:1.5rem;display:flex;flex-direction:column;gap:1.1rem;transition:all .2s;cursor:pointer;" onmouseover="this.style.boxShadow='0 8px 24px rgba(0,0,0,0.08)';this.style.borderColor='#93C5FD';this.style.transform='translateY(-2px)'" onmouseout="this.style.boxShadow='none';this.style.borderColor='#E2E8F0';this.style.transform='none'">

            <!-- Header: Avatar + Name + Dispo -->
            <div style="display:flex;align-items:flex-start;gap:.875rem;">
              <?php if (!empty($c['avatar']) && file_exists(__DIR__.'/'.$c['avatar'])): ?>
                <img src="<?= htmlspecialchars($c['avatar']) ?>" alt="<?= htmlspecialchars($c['nom']) ?>" style="width:56px;height:56px;border-radius:50%;object-fit:cover;border:2px solid #E2E8F0;flex-shrink:0;">
              <?php else: ?>
                <div style="width:56px;height:56px;border-radius:50%;background:<?= $bg ?>;color:#FFB800;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.9rem;flex-shrink:0;border:2px solid #E2E8F0;"><?= $initials ?></div>
              <?php endif; ?>
              <div style="flex:1;min-width:0;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:.5rem;">
                  <h3 style="font-size:1rem;font-weight:800;color:#081326;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($c['nom']) ?></h3>
                  <span style="background:<?= $dispoColor['bg'] ?>;color:<?= $dispoColor['text'] ?>;font-size:.68rem;font-weight:700;padding:.2rem .65rem;border-radius:99px;white-space:nowrap;flex-shrink:0;"><?= htmlspecialchars($c['disponibilite'] ?? 'N/A') ?></span>
                </div>
                <p style="font-size:.82rem;color:#64748B;font-weight:600;margin:.15rem 0 .4rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($c['titre_professionnel'] ?? 'Candidat') ?></p>
                <!-- Meta row -->
                <div style="display:flex;align-items:center;gap:.75rem;font-size:.75rem;color:#94A3B8;flex-wrap:wrap;">
                  <span style="display:inline-flex;align-items:center;gap:.25rem;">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#E11D48" stroke-width="2.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                    <?= htmlspecialchars($c['ville'] ?? 'Lomé') ?>
                  </span>
                  <span style="display:inline-flex;align-items:center;gap:.25rem;">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#64748B" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    <?= $c['experience_annees'] ?? 0 ?> ans
                  </span>
                  <span style="display:inline-flex;align-items:center;gap:.25rem;">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#64748B" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path></svg>
                    <?= htmlspecialchars($c['type_contrat_souhaite'] ?? 'CDI') ?>
                  </span>
                </div>
              </div>
            </div>

            <!-- Compétences -->
            <?php if (!empty($skills)): ?>
            <div style="display:flex;flex-wrap:wrap;gap:.4rem;">
              <?php foreach ($skills as $sk): ?>
                <span style="background:#F8FAFC;border:1px solid #E2E8F0;color:#334155;border-radius:8px;padding:.2rem .65rem;font-size:.72rem;font-weight:600;"><?= htmlspecialchars($sk) ?></span>
              <?php endforeach; ?>
              <?php
                $allSkills = array_filter(array_map('trim', explode(',', $c['competences'] ?? '')));
                $extra = count($allSkills) - 5;
              ?>
              <?php if ($extra > 0): ?>
                <span style="background:#EFF6FF;color:#2563EB;border-radius:8px;padding:.2rem .65rem;font-size:.72rem;font-weight:700;">+<?= $extra ?></span>
              <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Salary -->
            <?php if (!empty($c['pretention_salariale'])): ?>
            <div style="display:flex;align-items:center;justify-content:space-between;padding:.75rem;background:#FFFBEB;border-radius:12px;">
              <span style="font-size:.78rem;color:#92400E;font-weight:600;">Prétention salariale</span>
              <span style="font-size:.9rem;font-weight:800;color:#D97706;"><?= number_format((float)$c['pretention_salariale'],0,',',' ') ?> FCFA/mois</span>
            </div>
            <?php endif; ?>

            <!-- Profile completion bar -->
            <div>
              <div style="display:flex;justify-content:space-between;font-size:.72rem;color:#94A3B8;margin-bottom:.3rem;">
                <span>Profil complété</span><span style="font-weight:700;color:<?= $completion>=80?'#059669':($completion>=50?'#D97706':'#64748B') ?>"><?= $completion ?>%</span>
              </div>
              <div style="background:#E2E8F0;border-radius:99px;height:4px;overflow:hidden;">
                <div style="height:100%;width:<?= $completion ?>%;background:<?= $completion>=80?'linear-gradient(90deg,#059669,#34D399)':($completion>=50?'linear-gradient(90deg,#D97706,#FBBF24)':'#CBD5E1') ?>;border-radius:99px;"></div>
              </div>
            </div>

            <!-- Action buttons -->
            <div style="display:flex;gap:.6rem;margin-top:.25rem;">
              <a href="user_id=<?= $c['id'] ?>" style="flex:1;display:inline-flex;align-items:center;justify-content:center;gap:.4rem;padding:.6rem;border:1.5px solid #E2E8F0;border-radius:12px;font-size:.8rem;font-weight:700;color:#0F172A;text-decoration:none;transition:all .2s;background:#F8FAFC;" onmouseover="this.style.borderColor='#2563EB';this.style.background='#EFF6FF';this.style.color='#2563EB'" onmouseout="this.style.borderColor='#E2E8F0';this.style.background='#F8FAFC';this.style.color='#0F172A'">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="3"></circle><path d="M1 12S5 4 12 4s11 8 11 8-4 8-11 8S1 12 1 12z"></path></svg>
                Voir le profil
              </a>
              <a href="<?= $isPro ? 'api-start-conversation.php?candidate_id=' . $c['id'] : '#' ?>" <?= !$isPro ? 'onclick="alert(\'⭐ Passez au plan Pro pour contacter les candidats en direct.\'); return false;"' : '' ?> style="flex:1;display:inline-flex;align-items:center;justify-content:center;gap:.4rem;padding:.6rem;border:none;border-radius:12px;font-size:.8rem;font-weight:700;color:#FFF;text-decoration:none;background:#2563EB;transition:background .2s;" onmouseover="this.style.background='#1D4ED8'" onmouseout="this.style.background='#2563EB'">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                Contacter <?= $isPro ? '' : '⭐' ?>
              </a>
              <?php if (!empty($c['cv_file'])): ?>
              <a href="<?= $isPro ? htmlspecialchars($c['cv_file']) : '#' ?>" <?= $isPro ? 'download' : '' ?> <?= !$isPro ? 'onclick="alert(\'⭐ Passez au plan Pro pour télécharger les CV.\'); return false;"' : '' ?> title="Télécharger le CV" style="display:inline-flex;align-items:center;justify-content:center;padding:.6rem;border:1.5px solid #E2E8F0;border-radius:12px;color:#64748B;text-decoration:none;transition:all .2s;background:#F8FAFC;" onmouseover="this.style.borderColor='#059669';this.style.color='#059669'" onmouseout="this.style.borderColor='#E2E8F0';this.style.color='#64748B'">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
              </a>
              <?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Result count footer -->
        <div style="text-align:center;padding:2rem 0 1rem;color:#94A3B8;font-size:.825rem;">
          <?= $totalCount ?> candidat<?= $totalCount > 1 ? 's' : '' ?> affiché<?= $totalCount > 1 ? 's' : '' ?>
          <?= $totalCount >= 60 ? ' · <a href="limit=200" style="color:#2563EB;">Voir plus</a>' : '' ?>
        </div>
      <?php endif; ?>
    </div>

  </main>
</div>

<script>
// Debounced search
let searchTimer;
function autoSubmit() {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => document.getElementById('filter-form').submit(), 500);
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>




