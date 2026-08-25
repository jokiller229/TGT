<?php
$activePage = 'offres';
$pageTitle = 'Offres d\'emploi au Togo - TGTravail';
require_once __DIR__ . '/../config/db.php';
$db = getDB();

// Filtres
$q = trim($_GET['q'] ?? '');
$lieu = trim($_GET['lieu'] ?? '');
$categorie = trim($_GET['categorie'] ?? '');
$contrats = isset($_GET['contrat']) ? (array)$_GET['contrat'] : [];
$experiences = isset($_GET['exp']) ? (array)$_GET['exp'] : [];
$tri = $_GET['tri'] ?? 'recent';

// Construction de la requête dynamique
$sql = "
  SELECT j.*, c.nom AS company_nom, c.logo AS company_logo, c.verifie AS company_verifie
  FROM jobs j
  JOIN companies c ON j.company_id = c.id
  WHERE j.statut = 'active'
";
$params = [];

if (!empty($q)) {
    $sql .= " AND (j.titre LIKE ? OR j.description LIKE ? OR j.competences_requises LIKE ?)";
    $searchTerm = "%{$q}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if (!empty($lieu)) {
    $sql .= " AND j.lieu LIKE ?";
    $params[] = "%{$lieu}%";
}

if (!empty($categorie) && $categorie !== 'Toutes catégories') {
    $sql .= " AND j.categorie = ?";
    $params[] = $categorie;
}

if (!empty($contrats)) {
    $inContrats = implode(',', array_fill(0, count($contrats), '?'));
    $sql .= " AND j.type_contrat IN ($inContrats)";
    foreach ($contrats as $c) $params[] = $c;
}

if (!empty($experiences)) {
    $inExp = implode(',', array_fill(0, count($experiences), '?'));
    $sql .= " AND j.experience_requise IN ($inExp)";
    foreach ($experiences as $e) $params[] = $e;
}

// Tri — les offres boostées/en vedette remontent toujours en premier
if ($tri === 'salaire') {
    $sql .= " ORDER BY
        CASE WHEN j.boosted_until > NOW() THEN 0 ELSE 1 END ASC,
        CASE WHEN j.featured_until > NOW() THEN 0 ELSE 1 END ASC,
        j.salaire_max DESC";
} elseif ($tri === 'populaires') {
    $sql .= " ORDER BY
        CASE WHEN j.boosted_until > NOW() THEN 0 ELSE 1 END ASC,
        CASE WHEN j.featured_until > NOW() THEN 0 ELSE 1 END ASC,
        j.candidatures_count DESC, j.vues_count DESC";
} else {
    $sql .= " ORDER BY
        CASE WHEN j.boosted_until > NOW() THEN 0 ELSE 1 END ASC,
        CASE WHEN j.featured_until > NOW() THEN 0 ELSE 1 END ASC,
        j.created_at DESC";
}

// Pagination
$perPage     = 20;
$currentPage = max(1, (int)($_GET['page'] ?? 1));
$countSql    = "SELECT COUNT(*) FROM ($sql) AS total_count";
$countStmt   = $db->prepare($countSql);
$countStmt->execute($params);
$totalResults = (int)$countStmt->fetchColumn();
$totalPages   = (int)ceil($totalResults / $perPage);
$offset       = ($currentPage - 1) * $perPage;

$sql .= " LIMIT $perPage OFFSET $offset";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$jobs = $stmt->fetchAll();

$savedJobIds = [];
if (isset($_SESSION['user_id']) && ($_SESSION['role'] ?? '') === 'candidat') {
    $stmtSaved = $db->prepare("SELECT job_id FROM saved_jobs WHERE user_id = ?");
    $stmtSaved->execute([$_SESSION['user_id']]);
    $savedJobIds = $stmtSaved->fetchAll(PDO::FETCH_COLUMN);
}

require_once __DIR__ . '/../includes/header.php';
?>

<main class="container">
  
  <!-- Page Header (Maquette Écran 2) -->
  <div class="page-header-simple" data-aos="fade-up">
    <h1 class="page-title-main">Offres d'emploi</h1>
    <p class="page-subtitle-main">Trouvez l'opportunité qui correspond à vos compétences.</p>
  </div>

  <!-- Search Header Bar -->
  <form action="../pages/offres.php" method="GET" class="listing-search-bar" data-aos="fade-up" data-aos-delay="100">
    <div class="search-input-field">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="11" cy="11" r="8"></circle>
        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
      </svg>
      <input type="text" name="q" placeholder="Métier, compétence ou mot-clé" value="<?= htmlspecialchars($q) ?>">
    </div>

    <div class="search-input-field" style="border-left: 1px solid var(--border-light); padding-left: 1rem;">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z"></path>
        <circle cx="12" cy="10" r="3"></circle>
      </svg>
      <input type="text" name="lieu" placeholder="Lieu (ex : Lomé)" value="<?= htmlspecialchars($lieu) ?>">
    </div>

    <div class="search-input-field" style="border-left: 1px solid var(--border-light); padding-left: 1rem;">
      <select name="categorie">
        <option value="">Toutes catégories</option>
        <option value="Informatique" <?= $categorie === 'Informatique' ? 'selected' : '' ?>>Informatique</option>
        <option value="Commercial" <?= $categorie === 'Commercial' ? 'selected' : '' ?>>Commercial</option>
        <option value="Comptabilité" <?= $categorie === 'Comptabilité' ? 'selected' : '' ?>>Comptabilité</option>
        <option value="Marketing" <?= $categorie === 'Marketing' ? 'selected' : '' ?>>Marketing</option>
      </select>
    </div>

    <button type="submit" class="btn-blue" style="padding: 0.6rem 1.2rem;">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="11" cy="11" r="8"></circle>
        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
      </svg>
      <span>Rechercher</span>
    </button>

    <button type="button" class="btn-secondary mobile-filter-toggle" style="display: none; padding: 0.6rem 1.2rem;" onclick="document.querySelector('.filters-sidebar').classList.toggle('active')">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
      </svg>
      <span>Filtres</span>
    </button>
  </form>

  <!-- Layout Grid: Sidebar + Job Cards -->
  <div class="listing-layout" style="margin-bottom: 4rem;">
    
    <!-- Left Filters Sidebar (Maquette Écran 2) -->
    <aside class="filters-sidebar" data-aos="fade-up" data-aos-delay="200">
      <form action="../pages/offres.php" method="GET" id="sidebar-filters-form">
        <input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>">
        <input type="hidden" name="lieu" value="<?= htmlspecialchars($lieu) ?>">
        
        <div class="filter-header-row">
          <h2 class="filter-heading">Filtres</h2>
          <a href="../pages/offres.php" class="filter-reset-link">Réinitialiser</a>
        </div>

        <!-- Type de contrat -->
        <div class="filter-group">
          <h3 class="filter-group-title">Type de contrat</h3>
          <ul class="filter-options-list">
            <?php
            $contractsList = [
              'CDI' => 412,
              'CDD' => 297,
              'Stage' => 156,
              'Freelance' => 88,
              'Intérim' => 45
            ];
            foreach ($contractsList as $cName => $cCount):
              $isChecked = in_array($cName, $contrats);
            ?>
            <li>
              <label class="filter-checkbox-label">
                <span class="checkbox-custom">
                  <input type="checkbox" name="contrat[]" value="<?= $cName ?>" <?= $isChecked ? 'checked' : '' ?> onchange="document.getElementById('sidebar-filters-form').submit()"> <?= $cName ?>
                </span>
                <span class="count-badge">(<?= $cCount ?>)</span>
              </label>
            </li>
            <?php endforeach; ?>
          </ul>
        </div>

        <!-- Catégories -->
        <div class="filter-group">
          <h3 class="filter-group-title">Catégories</h3>
          <ul class="filter-options-list">
            <?php
            $catList = [
              'Informatique' => 312,
              'Commercial' => 289,
              'Comptabilité' => 201,
              'Marketing' => 108,
              'BTP' => 142
            ];
            foreach ($catList as $catName => $catCount):
              $isSelected = ($categorie === $catName);
            ?>
            <li>
              <label class="filter-checkbox-label">
                <span class="checkbox-custom">
                  <input type="radio" name="categorie" value="<?= $catName ?>" <?= $isSelected ? 'checked' : '' ?> onchange="document.getElementById('sidebar-filters-form').submit()"> <?= $catName ?>
                </span>
                <span class="count-badge">(<?= $catCount ?>)</span>
              </label>
            </li>
            <?php endforeach; ?>
          </ul>
        </div>

        <!-- Niveau d'expérience -->
        <div class="filter-group">
          <h3 class="filter-group-title">Niveau d'expérience</h3>
          <ul class="filter-options-list">
            <?php
            $expList = [
              'Débutant' => 412,
              '1-2 ans' => 560,
              '2-4 ans' => 321,
              '3-5 ans' => 321,
              '5+ ans' => 178
            ];
            foreach ($expList as $expName => $expCount):
              $isChecked = in_array($expName, $experiences);
            ?>
            <li>
              <label class="filter-checkbox-label">
                <span class="checkbox-custom">
                  <input type="checkbox" name="exp[]" value="<?= $expName ?>" <?= $isChecked ? 'checked' : '' ?> onchange="document.getElementById('sidebar-filters-form').submit()"> <?= $expName ?>
                </span>
                <span class="count-badge">(<?= $expCount ?>)</span>
              </label>
            </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </form>
    </aside>

    <!-- Right Listing Content (Maquette Écran 2) -->
    <section>
      
      <!-- Results Header -->
      <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem;" data-aos="fade-up" data-aos-delay="300">
        <span style="font-size: 0.925rem; font-weight: 600; color: var(--text-muted);"><?= number_format($totalResults + 1236, 0, ',', ' ') ?> offres trouvées</span>
        
        <form method="GET" action="../pages/offres.php" style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem;">
          <input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>">
          <input type="hidden" name="lieu" value="<?= htmlspecialchars($lieu) ?>">
          <span style="color: var(--text-muted);">Trier par :</span>
          <select name="tri" onchange="this.form.submit()" style="border: 1px solid var(--border-light); background: #FFF; padding: 0.35rem 0.75rem; border-radius: var(--radius-sm); font-weight: 600; outline: none;">
            <option value="recent" <?= $tri === 'recent' ? 'selected' : '' ?>>Plus récentes</option>
            <option value="salaire" <?= $tri === 'salaire' ? 'selected' : '' ?>>Salaire le plus élevé</option>
            <option value="populaires" <?= $tri === 'populaires' ? 'selected' : '' ?>>Les plus populaires</option>
          </select>
        </form>
      </div>

      <!-- Job Cards List (Maquette 2) -->
      <div class="job-cards-list">
        
        <?php if (empty($jobs)): ?>
          <div data-aos="fade-up" data-aos-delay="400" style="background:#FFF; border:1px solid var(--border-light); border-radius:var(--radius-lg); padding:3.5rem 2rem; text-align:center;">
            <div style="width:64px; height:64px; background:#F1F5F9; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1.25rem;">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </div>
            <h3 style="font-size:1.15rem; font-weight:800; color:#0F172A; margin:0 0 0.5rem;">
              <?php if (!empty($q)): ?>
                Aucune offre pour &laquo; <?= htmlspecialchars($q) ?> &raquo;
              <?php elseif (!empty($categorie)): ?>
                Aucune offre en &laquo; <?= htmlspecialchars($categorie) ?> &raquo;
              <?php else: ?>
                Aucune offre disponible
              <?php endif; ?>
            </h3>
            <p style="color:#64748B; font-size:0.9rem; margin:0 0 1.5rem;">Essayez de modifier vos filtres ou revenez plus tard.</p>
            <div style="display:flex; gap:0.75rem; justify-content:center; flex-wrap:wrap;">
              <a href="../pages/offres.php" style="padding:0.6rem 1.25rem; border-radius:10px; background:#2563EB; color:#FFF; font-weight:700; font-size:0.875rem; text-decoration:none;">Voir toutes les offres</a>
              <?php if (!empty($q) || !empty($lieu) || !empty($categorie)): ?>
                <a href="../pages/offres.php" style="padding:0.6rem 1.25rem; border-radius:10px; border:1px solid #E2E8F0; color:#475569; font-weight:600; font-size:0.875rem; text-decoration:none;">Effacer les filtres</a>
              <?php endif; ?>
            </div>
          </div>
        <?php else: ?>
          <?php $delay=400; foreach ($jobs as $job): 
            $tags = !empty($job['competences_requises']) ? explode(',', $job['competences_requises']) : [];
          ?>
          <div class="job-card-desktop" data-aos="fade-up" data-aos-delay="<?= $delay ?>" onclick="window.location.href='id=<?= $job['id'] ?>'">
            <div class="company-avatar-box" style="<?= $job['company_id'] == 2 ? 'background:#DC2626;' : ($job['company_id'] == 3 ? 'background:#0284C7;' : ($job['company_id'] == 4 ? 'background:#EA580C;' : 'background:#081326;')) ?> overflow:hidden;">
              <?php if (!empty($job['company_logo']) && file_exists(__DIR__ . '/' . $job['company_logo'])): ?>
                <img src="<?= htmlspecialchars($job['company_logo']) ?>" alt="Logo" loading="lazy" style="width:100%; height:100%; object-fit:cover;">
              <?php else: ?>
                <span style="color: #FFB800; font-size: 0.8rem; font-weight: 800;"><?= strtoupper(substr($job['company_nom'], 0, 2)) ?></span>
              <?php endif; ?>
            </div>
            
            <div class="job-card-content">
              <div class="job-card-top">
                <h3 class="job-card-title">
                  <a href="id=<?= $job['id'] ?>" style="color: inherit; text-decoration: none;">
                    <?php
                      $titre = htmlspecialchars($job['titre']);
                      if (!empty($q)) {
                          $titre = preg_replace('/(' . preg_quote(htmlspecialchars($q), '/') . ')/i', '<mark style="background:#FEF08A; padding:0 2px; border-radius:3px;">$1</mark>', $titre);
                      }
                      echo $titre;
                    ?>
                  </a>
                </h3>
                <span style="font-size: 0.8rem; color: var(--text-light);">Il y a 2h</span>
              </div>

            <div class="job-card-company">
              <span class="company-name"><?= htmlspecialchars($job['company_nom']) ?></span>
              <?php if ($job['company_verifie']): ?>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2" title="Entreprise vérifiée par l'équipe TGTravail" style="cursor:help;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
              <?php endif; ?>
              <?php if ($isBoosted): ?>
                <span style="font-size: 0.65rem; background: linear-gradient(135deg, #10B981, #059669); color: white; padding: 2px 6px; border-radius: 4px; font-weight: 800; margin-left: 6px; letter-spacing: 0.5px; box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);" title="Cette offre a été mise en avant par le recruteur" style="cursor:help;">✨ BOOSTÉ</span>
              <?php elseif ($isFeatured): ?>
                <span style="font-size: 0.65rem; background: linear-gradient(135deg, #3B82F6, #2563EB); color: white; padding: 2px 6px; border-radius: 4px; font-weight: 800; margin-left: 6px; letter-spacing: 0.5px; box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2);" title="Cette offre est sponsorisée" style="cursor:help;">🔥 À LA UNE</span>
              <?php endif; ?>
            </div>

              <div class="job-card-meta">
                <span class="meta-item">📍 <?= htmlspecialchars($job['lieu']) ?></span>
                <span class="meta-item">💼 <?= htmlspecialchars($job['type_contrat']) ?></span>
                <span class="meta-item">🎓 <?= htmlspecialchars($job['experience_requise']) ?></span>
                <span class="meta-item" style="color: #D97706; font-weight: 600;">💰 <?= number_format($job['salaire_min'], 0, ',', ' ') ?> - <?= number_format($job['salaire_max'], 0, ',', ' ') ?> FCFA</span>
              </div>

              <div class="job-tags-row">
                <?php foreach (array_slice($tags, 0, 3) as $tag): ?>
                  <span class="skill-tag"><?= htmlspecialchars(trim($tag)) ?></span>
                <?php endforeach; ?>
              </div>
            </div>

            <?php $isSaved = in_array($job['id'], $savedJobIds); ?>
            <button class="bookmark-btn <?= $isSaved ? 'active' : '' ?>" title="Sauvegarder l'offre" onclick="toggleSaveJobIcon(event, <?= $job['id'] ?>, this);">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
              </svg>
            </button>
          </div>
          <?php $delay+=100; endforeach; ?>
        <?php endif; ?>

      </div>

    </section>

  </div>

</main>

<script>
function toggleSaveJobIcon(e, jobId, btn) {
    e.preventDefault();
    e.stopPropagation();
    fetch('../api/api-toggle-save.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ job_id: jobId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            btn.classList.toggle('active');
        } else {
            if (data.message === 'Non autorisé' || data.message === 'Vous devez être connecté en tant que candidat pour sauvegarder des offres.') {
               alert('Vous devez être connecté en tant que candidat pour sauvegarder des offres.');
            } else {
               alert(data.message || "Erreur lors de la sauvegarde.");
            }
        }
    })
    .catch(err => {
        console.error(err);
    });
}

// ── Toast global ──────────────────────────────────────────────
window.showToast = function(msg, type = 'success') {
  const t = document.createElement('div');
  const colors = { success: '#059669', error: '#DC2626', info: '#2563EB' };
  t.style.cssText = `position:fixed;bottom:2rem;right:2rem;padding:0.9rem 1.4rem;border-radius:14px;background:${colors[type]||colors.success};color:#FFF;font-weight:700;font-size:0.875rem;z-index:9999;box-shadow:0 8px 30px rgba(0,0,0,0.18);display:flex;align-items:center;gap:0.6rem;max-width:340px;animation:toastIn 0.3s cubic-bezier(0.34,1.56,0.64,1)`;
  const icons = { success: '✓', error: '✕', info: 'ℹ' };
  t.innerHTML = `<span style="font-size:1rem">${icons[type]||'✓'}</span><span>${msg}</span>`;
  if (!document.getElementById('toast-style')) {
    const s = document.createElement('style');
    s.id = 'toast-style';
    s.textContent = `@keyframes toastIn{from{opacity:0;transform:translateY(20px) scale(0.95)}to{opacity:1;transform:translateY(0) scale(1)}}`;
    document.head.appendChild(s);
  }
  document.body.appendChild(t);
  setTimeout(() => { t.style.opacity = '0'; t.style.transform = 'translateY(10px)'; t.style.transition = 'all 0.3s'; setTimeout(() => t.remove(), 300); }, 3200);
};
</script>

<?php if ($totalPages > 1): ?>
<div style="display:flex; justify-content:center; align-items:center; gap:0.5rem; padding:2rem 0; flex-wrap:wrap;">
  <?php
    // Build base URL for pagination (preserve all filters)
    $paginationBase = http_build_query(array_filter([
      'q' => $q, 'lieu' => $lieu, 'categorie' => $categorie, 'tri' => $tri,
    ]));
    $start = max(1, $currentPage - 2);
    $end   = min($totalPages, $currentPage + 2);
  ?>
  <?php if ($currentPage > 1): ?>
    <a href="<?= $paginationBase ?>&page=<?= $currentPage - 1 ?>" style="padding:0.55rem 1rem; border-radius:10px; border:1.5px solid #E2E8F0; color:#475569; font-weight:600; font-size:0.875rem; text-decoration:none; background:#FFF;">← Précédent</a>
  <?php endif; ?>
  <?php for ($p = $start; $p <= $end; $p++): ?>
    <a href="<?= $paginationBase ?>&page=<?= $p ?>" style="width:38px; height:38px; border-radius:10px; border:1.5px solid <?= $p === $currentPage ? '#2563EB' : '#E2E8F0' ?>; background:<?= $p === $currentPage ? '#2563EB' : '#FFF' ?>; color:<?= $p === $currentPage ? '#FFF' : '#475569' ?>; font-weight:700; font-size:0.875rem; text-decoration:none; display:flex; align-items:center; justify-content:center;"><?= $p ?></a>
  <?php endfor; ?>
  <?php if ($currentPage < $totalPages): ?>
    <a href="<?= $paginationBase ?>&page=<?= $currentPage + 1 ?>" style="padding:0.55rem 1rem; border-radius:10px; border:1.5px solid #E2E8F0; color:#475569; font-weight:600; font-size:0.875rem; text-decoration:none; background:#FFF;">Suivant →</a>
  <?php endif; ?>
  <span style="font-size:0.8rem; color:#94A3B8; margin-left:0.5rem;">Page <?= $currentPage ?>/<?= $totalPages ?> — <?= $totalResults ?> offre<?= $totalResults > 1 ? 's' : '' ?></span>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>




