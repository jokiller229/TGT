<?php
$activePage = 'entretiens';
$pageTitle = 'Mes Entretiens Visio - TGTravail';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('candidat');

$db = getDB();
$userId = $_SESSION['user_id'];

// Get all interviews for this candidate
$interviewsStmt = $db->prepare("
  SELECT i.*, c.nom AS company_nom, c.logo AS company_logo, c.verifie AS company_verifie
  FROM interviews i
  JOIN users u ON i.recruiter_id = u.id
  LEFT JOIN companies c ON c.user_id = u.id
  WHERE i.candidate_id = ?
  ORDER BY i.date_time ASC
");
$interviewsStmt->execute([$userId]);
$interviews = $interviewsStmt->fetchAll();

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
        <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--color-primary-dark);">Mes Entretiens Visio</h1>
        <p style="color: var(--text-muted); margin-top: 0.25rem;">Consultez et rejoignez vos entretiens vidéo avec les recruteurs.</p>
      </div>
      </div>
    </div>

    <?php if (empty($interviews)): ?>
      <div style="background: #FFF; border: 1px solid var(--border-light); border-radius: 16px; padding: 4rem 2rem; text-align: center;">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity: 0.4; margin-bottom: 1rem; color: var(--color-primary-dark);"><path d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14v-4z"></path><rect x="3" y="6" width="12" height="12" rx="2" ry="2"></rect></svg>
        <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--color-primary-dark); margin-bottom: 0.5rem;">Aucun entretien planifié</h3>
        <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Vous n'avez pas d'entretien vidéo programmé pour le moment.</p>
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
                <div style="width: 50px; height: 50px; border-radius: 12px; background: #F1F5F9; display: flex; align-items: center; justify-content: center; overflow: hidden; font-weight: 700; color: #64748B;">
                  <?php if (!empty($i['company_logo']) && file_exists(__DIR__ . '/' . $i['company_logo'])): ?>
                    <img src="<?= htmlspecialchars($i['company_logo']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                  <?php else: ?>
                    <?= strtoupper(substr($i['company_nom'] ?? 'EN', 0, 2)) ?>
                  <?php endif; ?>
                </div>
                <div>
                  <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--color-primary-dark);"><?= htmlspecialchars($i['company_nom'] ?? 'Entreprise') ?></h3>
                  <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.2rem;">Recruteur</div>
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
                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.1rem;">Date & Heure de l'entretien</div>
              </div>
            </div>

            <div style="display: flex; gap: 1rem; align-items: center; border-top: 1px solid var(--border-light); padding-top: 1.25rem;">
              <?php if ($i['status'] === 'planned'): ?>
                <a href="<?= htmlspecialchars($i['meet_link']) ?>" target="_blank" class="btn-primary" style="flex: 1; text-align: center; justify-content: center; display: inline-flex;">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 0.5rem;"><path d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14v-4z"></path><rect x="3" y="6" width="12" height="12" rx="2" ry="2"></rect></svg>
                  Rejoindre la visio
                </a>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>




