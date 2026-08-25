<?php
$activePage = 'cvtheque';
$pageTitle = 'Profil Candidat - TGTravail';
require_once __DIR__ . '/../includes/auth.php';

requireRole('recruteur');

$db = getDB();
$candidateId = (int)($_GET['user_id'] ?? 0);
$jobId = (int)($_GET['job_id'] ?? 0); // Optionnel, pour retour

if (!$candidateId) {
    header("Location: ../recruteur/cvtheque.php");
    exit;
}

$stmt = $db->prepare("
  SELECT u.nom, u.email, u.telephone, u.avatar,
         p.*
  FROM users u
  LEFT JOIN candidate_profiles p ON u.id = p.user_id
  WHERE u.id = ? AND u.role = 'candidat'
");
$stmt->execute([$candidateId]);
$candidat = $stmt->fetch();

if (!$candidat) {
    header("Location: ../recruteur/cvtheque.php");
    exit;
}

$initials = strtoupper(substr($candidat['nom'], 0, 2));
$bgColors = ['#081326','#1E40AF','#7C3AED','#059669','#DC2626','#D97706','#0369A1'];
$bg = $bgColors[crc32($candidat['nom']) % count($bgColors)];
$skills = array_filter(array_map('trim', explode(',', $candidat['competences'] ?? '')));

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
  <?php require __DIR__ . '/../includes/recruiter_sidebar.php'; ?>

  <div style="flex:1; display:flex; flex-direction:column; overflow-y:auto; background:#F8FAFC;">
    <div class="dashboard-topbar">
      <div class="dashboard-header-left" style="display:flex; align-items:center; gap:1rem;">
        <button class="dashboard-mobile-btn" onclick="document.querySelector('.dashboard-sidebar-dark').classList.toggle('open'); event.stopPropagation();">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>
        <div>
        <h1 class="user-greeting">Profil de <?= htmlspecialchars($candidat['nom']) ?></h1>
        <p style="font-size:0.9rem; color:var(--text-muted);">Consultation du CV détaillé</p>
      </div>
      </div>
      <div style="display:flex; align-items:center; gap:1rem;">
        <?php if ($jobId): ?>
          <a href="job_id=<?= $jobId ?>" class="btn-link" style="color:#64748B;">← Retour aux candidatures</a>
        <?php else: ?>
          <a href="../recruteur/cvtheque.php" class="btn-link" style="color:#64748B;">← Retour à la CVthèque</a>
        <?php endif; ?>
      </div>
    </div>

    <div style="padding:2rem; max-width:900px; margin:0 auto; width:100%;">
      
      <!-- En-tête du profil -->
      <div style="background:#FFF; border:1px solid #E2E8F0; border-radius:24px; padding:2rem; margin-bottom:2rem; display:flex; gap:2rem; align-items:center; box-shadow:0 10px 25px rgba(0,0,0,0.02);">
        <?php if (!empty($candidat['avatar']) && file_exists(__DIR__.'/'.$candidat['avatar'])): ?>
          <img src="<?= htmlspecialchars($candidat['avatar']) ?>" alt="Avatar" style="width:120px; height:120px; border-radius:50%; object-fit:cover; border:4px solid #F1F5F9;">
        <?php else: ?>
          <div style="width:120px; height:120px; border-radius:50%; background:<?= $bg ?>; color:#FFF; display:flex; align-items:center; justify-content:center; font-size:2.5rem; font-weight:800; border:4px solid #F1F5F9;"><?= $initials ?></div>
        <?php endif; ?>
        
        <div style="flex:1;">
          <h2 style="font-size:1.75rem; font-weight:800; color:#0F172A; margin:0 0 0.5rem 0;"><?= htmlspecialchars($candidat['nom']) ?></h2>
          <p style="font-size:1.1rem; color:#2563EB; font-weight:600; margin:0 0 1rem 0;"><?= htmlspecialchars($candidat['titre_professionnel'] ?? 'En recherche d\'opportunités') ?></p>
          
          <div style="display:flex; flex-wrap:wrap; gap:1.25rem; font-size:0.9rem; color:#64748B;">
            <span style="display:flex; align-items:center; gap:0.4rem;">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
              <?= htmlspecialchars($candidat['ville'] ?? 'Non spécifié') ?>
            </span>
            <span style="display:flex; align-items:center; gap:0.4rem;">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
              <?= $candidat['experience_annees'] ?? 0 ?> ans d'expérience
            </span>
            <span style="display:flex; align-items:center; gap:0.4rem;">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
              <?= htmlspecialchars($candidat['telephone'] ?? 'Non renseigné') ?>
            </span>
            <span style="display:flex; align-items:center; gap:0.4rem;">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
              <?= htmlspecialchars($candidat['email']) ?>
            </span>
          </div>
        </div>
        
        <div style="display:flex; flex-direction:column; gap:0.75rem;">
          <a href="api-start-conversation.php?candidate_id=<?= $candidateId ?>" style="background:#2563EB; color:#FFF; padding:0.875rem 1.5rem; border-radius:12px; font-weight:700; text-decoration:none; display:flex; align-items:center; gap:0.5rem; justify-content:center; transition:background 0.2s;" onmouseover="this.style.background='#1D4ED8'" onmouseout="this.style.background='#2563EB'">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
            Envoyer un message
          </a>
          <?php if (!empty($candidat['cv_file'])): ?>
            <a href="<?= htmlspecialchars($candidat['cv_file']) ?>" download style="background:#F1F5F9; color:#0F172A; padding:0.875rem 1.5rem; border-radius:12px; font-weight:700; text-decoration:none; display:flex; align-items:center; gap:0.5rem; justify-content:center; border:1px solid #E2E8F0; transition:all 0.2s;" onmouseover="this.style.background='#E2E8F0';this.style.borderColor='#CBD5E1'" onmouseout="this.style.background='#F1F5F9';this.style.borderColor='#E2E8F0'">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
              Télécharger le CV (PDF)
            </a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Section détails -->
      <div style="display:grid; grid-template-columns:2fr 1fr; gap:2rem;">
        
        <!-- Colonne Gauche -->
        <div style="display:flex; flex-direction:column; gap:2rem;">
          <!-- Bio -->
          <div style="background:#FFF; border:1px solid #E2E8F0; border-radius:24px; padding:2rem; box-shadow:0 10px 25px rgba(0,0,0,0.02);">
            <h3 style="font-size:1.25rem; font-weight:800; color:#0F172A; margin:0 0 1rem 0; display:flex; align-items:center; gap:0.5rem;">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
              À propos
            </h3>
            <p style="color:#475569; line-height:1.7; margin:0; font-size:0.95rem;">
              <?= !empty($candidat['bio']) ? nl2br(htmlspecialchars($candidat['bio'])) : '<i>Le candidat n\'a pas encore rédigé de biographie.</i>' ?>
            </p>
          </div>

          <!-- Compétences -->
          <div style="background:#FFF; border:1px solid #E2E8F0; border-radius:24px; padding:2rem; box-shadow:0 10px 25px rgba(0,0,0,0.02);">
            <h3 style="font-size:1.25rem; font-weight:800; color:#0F172A; margin:0 0 1.25rem 0; display:flex; align-items:center; gap:0.5rem;">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
              Compétences
            </h3>
            <?php if (!empty($skills)): ?>
              <div style="display:flex; flex-wrap:wrap; gap:0.75rem;">
                <?php foreach ($skills as $s): ?>
                  <span style="background:#F8FAFC; border:1px solid #E2E8F0; color:#334155; padding:0.5rem 1rem; border-radius:99px; font-weight:600; font-size:0.875rem;">
                    <?= htmlspecialchars($s) ?>
                  </span>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <p style="color:#94A3B8; font-size:0.95rem; margin:0;"><i>Aucune compétence renseignée.</i></p>
            <?php endif; ?>
          </div>
        </div>

        <!-- Colonne Droite -->
        <div>
          <div style="background:#FFF; border:1px solid #E2E8F0; border-radius:24px; padding:2rem; box-shadow:0 10px 25px rgba(0,0,0,0.02); position:sticky; top:2rem;">
            <h3 style="font-size:1.1rem; font-weight:800; color:#0F172A; margin:0 0 1.5rem 0;">Souhaits & Critères</h3>
            
            <div style="display:flex; flex-direction:column; gap:1.25rem;">
              <div>
                <span style="display:block; font-size:0.75rem; font-weight:700; color:#64748B; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.25rem;">Disponibilité</span>
                <div style="display:flex; align-items:center; gap:0.5rem;">
                  <span style="width:8px; height:8px; background:#10B981; border-radius:50%;"></span>
                  <span style="font-weight:600; color:#0F172A;"><?= htmlspecialchars($candidat['disponibilite'] ?? 'Non spécifié') ?></span>
                </div>
              </div>

              <div>
                <span style="display:block; font-size:0.75rem; font-weight:700; color:#64748B; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.25rem;">Type de contrat visé</span>
                <span style="font-weight:600; color:#0F172A;"><?= htmlspecialchars($candidat['type_contrat_souhaite'] ?? 'Non spécifié') ?></span>
              </div>

              <div>
                <span style="display:block; font-size:0.75rem; font-weight:700; color:#64748B; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.25rem;">Prétention salariale</span>
                <span style="font-weight:800; color:#D97706; font-size:1.1rem;">
                  <?= !empty($candidat['pretention_salariale']) ? number_format((float)$candidat['pretention_salariale'], 0, ',', ' ') . ' FCFA / mois' : 'À négocier' ?>
                </span>
              </div>
            </div>
            
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>




