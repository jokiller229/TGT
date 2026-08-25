<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('candidat');

$activePage = 'favoris';
$userId = $_SESSION['user_id'];
$pdo = getDB();

// Fetch saved jobs
$stmt = $pdo->prepare("
  SELECT j.*, comp.nom as raison_sociale, comp.logo as logo_url, comp.verifie as company_verifie
  FROM saved_jobs s
  JOIN jobs j ON s.job_id = j.id
  JOIN companies comp ON j.company_id = comp.id
  WHERE s.user_id = ?
  ORDER BY s.created_at DESC
");
$stmt->execute([$userId]);
$savedJobs = $stmt->fetchAll();

$hideHeader = true;
include __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/candidat_sidebar.php'; ?>

  <main class="dashboard-content-main">
    <div class="dashboard-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
      <div class="dashboard-header-left" style="display:flex; align-items:center; gap:1rem;">
        <button class="dashboard-mobile-btn" onclick="document.querySelector('.dashboard-sidebar-dark').classList.toggle('open'); event.stopPropagation();">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>
        <div>
        <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--color-primary-dark);">Offres sauvegardées</h1>
        <p style="color: var(--text-muted); margin-top: 0.25rem;">Les annonces que vous avez mises de côté.</p>
      </div>
      </div>
    </div>

    <?php if (empty($savedJobs)): ?>
      <div style="text-align: center; padding: 4rem 1rem; background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-xl); box-shadow: var(--shadow-sm);">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 1.5rem; opacity: 0.3; color: var(--color-primary-dark);"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path></svg>
        <p style="font-size: 1.25rem; font-weight: 700; color: var(--color-primary-dark); margin-bottom: 0.5rem;">Aucune offre sauvegardée</p>
        <p style="color: var(--text-muted); margin-bottom: 2rem;">Cliquez sur l'icône de sauvegarde sur une offre pour la retrouver ici plus tard.</p>
        <a href="../pages/offres.php" class="btn-primary" style="display: inline-block;">Découvrir les offres</a>
      </div>
    <?php else: ?>
      <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem;">
        <?php foreach ($savedJobs as $job): ?>
          <a href="id=<?= $job['id'] ?>" class="job-card-desktop" style="text-decoration: none; color: inherit; display: block;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
              <div style="display: flex; gap: 1rem; align-items: center;">
                <div class="company-avatar-box" style="width: 48px; height: 48px; border-radius: 12px; background: #E2E8F0; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #64748B; overflow: hidden;">
                  <?php if (!empty($job['logo_url']) && file_exists(__DIR__ . '/' . $job['logo_url'])): ?>
                    <img src="<?= htmlspecialchars($job['logo_url']) ?>" alt="Logo" style="width: 100%; height: 100%; object-fit: cover;">
                  <?php else: ?>
                    <?= strtoupper(substr($job['raison_sociale'], 0, 2)) ?>
                  <?php endif; ?>
                </div>
                <div>
                  <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--color-primary-dark); margin-bottom: 0.2rem;"><?= htmlspecialchars($job['titre']) ?></h3>
                  <div style="font-size: 0.85rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.5rem;">
                    <?= htmlspecialchars($job['raison_sociale']) ?>
                    <?php if ($job['company_verifie'] === 'verifie'): ?>
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0EA5E9" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><path d="m9 12 2 2 4-4"></path></svg>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
            
            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1.25rem;">
              <span class="badge" style="background: #F1F5F9; color: #475569;">📍 <?= htmlspecialchars($job['ville']) ?></span>
              <span class="badge" style="background: #F1F5F9; color: #475569;">💼 <?= htmlspecialchars($job['type_contrat']) ?></span>
              <?php if ($job['salaire_visible'] && $job['salaire_min']): ?>
                <span class="badge" style="background: #FFFBEB; color: #D97706; font-weight: 700;">💰 <?= number_format($job['salaire_min'], 0, ',', ' ') ?> FCFA+</span>
              <?php endif; ?>
            </div>
            
            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 1rem; border-top: 1px solid var(--border-light); font-size: 0.85rem; color: var(--text-muted);">
              <span>⏳ Expire le <?= date('d/m/Y', strtotime($job['date_limite'])) ?></span>
              <span style="color: var(--color-brand); font-weight: 700;">Voir l'offre ➔</span>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main></div>

<?php include __DIR__ . '/../includes/footer.php'; ?>






