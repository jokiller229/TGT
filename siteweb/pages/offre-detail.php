<?php
$activePage = 'offres';
require_once __DIR__ . '/../includes/auth.php';
$db = getDB();

$jobId = (int)($_GET['id'] ?? 1);

// Récupération de l'offre et de l'entreprise
$stmt = $db->prepare("
  SELECT j.*, c.nom AS company_nom, c.logo AS company_logo, c.verifie AS company_verifie, c.description AS company_desc
  FROM jobs j
  JOIN companies c ON j.company_id = c.id
  WHERE j.id = ?
");
$stmt->execute([$jobId]);
$job = $stmt->fetch();

if (!$job) {
    header("Location: ../pages/offres.php");
    exit;
}

// Incrément des vues (anti double-comptage)
if (!isset($_SESSION['viewed_jobs'])) $_SESSION['viewed_jobs'] = [];
if (!in_array($jobId, $_SESSION['viewed_jobs'])) {
    $db->prepare("UPDATE jobs SET vues_count = vues_count + 1 WHERE id = ?")->execute([$jobId]);
    $_SESSION['viewed_jobs'][] = $jobId;
}

$pageTitle = htmlspecialchars($job['titre']) . ' - ' . htmlspecialchars($job['company_nom']) . ' | TGTravail';

// Traitement de la candidature en POST
$applicationSuccess = false;
$applicationError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['postuler_submit'])) {
    $candidateId = getCurrentUserId();
    
    if ($candidateId === 0 || getCurrentRole() !== 'candidat') {
        $applicationError = "Vous devez être connecté en tant que candidat pour postuler.";
    } else {
        $lettre = trim($_POST['lettre_motivation'] ?? '');
        $pretention = (int)($_POST['pretention_salariale'] ?? $job['salaire_min']);
        $disponibilite = trim($_POST['disponibilite'] ?? 'Immédiate');

        // Gérer upload PDF lettre
        if (isset($_FILES['lettre_pdf']) && $_FILES['lettre_pdf']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['lettre_pdf']['name'], PATHINFO_EXTENSION);
            if (strtolower($ext) === 'pdf') {
                $dest = 'uploads/lettres/lettre_' . time() . '_' . $candidateId . '.pdf';
                if (!is_dir('uploads/lettres/')) mkdir('uploads/lettres/', 0777, true);
                if (move_uploaded_file($_FILES['lettre_pdf']['tmp_name'], $dest)) {
                    $lettre = $dest; // On sauvegarde le chemin dans lettre_motivation
                }
            }
        }

        // Gérer vidéo pitch
        $video_pitch = null;
        if (!empty($_POST['video_data'])) {
            $base64_string = $_POST['video_data'];
            list($type, $data) = explode(';', $base64_string);
            list(, $data)      = explode(',', $data);
            $data = base64_decode($data);
            $dest = 'uploads/videos/pitch_' . time() . '_' . $candidateId . '.webm';
            if (!is_dir('uploads/videos/')) mkdir('uploads/videos/', 0777, true);
            if (file_put_contents($dest, $data)) {
                $video_pitch = $dest;
            }
        }

        // Vérifier si déjà postulé
        $checkStmt = $db->prepare("SELECT id FROM applications WHERE job_id = ? AND candidate_id = ?");
        $checkStmt->execute([$jobId, $candidateId]);
        if ($checkStmt->rowCount() > 0) {
            $applicationError = "Vous avez déjà soumis votre candidature pour cette offre.";
        } else {
            $insertStmt = $db->prepare("
              INSERT INTO applications (job_id, candidate_id, lettre_motivation, video_pitch, pretention_salariale, disponibilite, statut)
              VALUES (?, ?, ?, ?, ?, ?, 'nouveau')
            ");
            $insertStmt->execute([$jobId, $candidateId, $lettre, $video_pitch, $pretention, $disponibilite]);
            $db->prepare("UPDATE jobs SET candidatures_count = candidatures_count + 1 WHERE id = ?")->execute([$jobId]);

        // Notifier le recruteur de la nouvelle candidature
        $recruiterStmt = $db->prepare("SELECT c.user_id FROM companies c JOIN jobs j ON j.company_id = c.id WHERE j.id = ?");
        $recruiterStmt->execute([$jobId]);
        $recruiterUserId = $recruiterStmt->fetchColumn();
        if ($recruiterUserId) {
            $candidatNom = $_SESSION['user_nom'] ?? 'Un candidat';
            $db->prepare("INSERT INTO notifications (user_id, job_id, message, lu, created_at) VALUES (?, ?, ?, 0, NOW())")
               ->execute([$recruiterUserId, $jobId, "{$candidatNom} a postulé à votre offre : {$job['titre']}"]);
        }

        // Confirmer la candidature au candidat lui-même
        $db->prepare("INSERT INTO notifications (user_id, job_id, message, lu, created_at) VALUES (?, ?, ?, 0, NOW())")
           ->execute([$candidateId, $jobId, "Votre candidature pour « {$job['titre']} » a bien été envoyée. Nous vous tiendrons informé de la suite."]);

        $applicationSuccess = true;
        }
    }
}

// Vérifier si déjà postulé pour adapter le bouton
$alreadyApplied = false;
if (getCurrentRole() === 'candidat') {
    $checkApp = $db->prepare("SELECT id FROM applications WHERE job_id = ? AND candidate_id = ?");
    $checkApp->execute([$jobId, getCurrentUserId()]);
    $alreadyApplied = ($checkApp->rowCount() > 0);
    
    $checkSaved = $db->prepare("SELECT id FROM saved_jobs WHERE job_id = ? AND user_id = ?");
    $checkSaved->execute([$jobId, getCurrentUserId()]);
    $isSaved = ($checkSaved->rowCount() > 0);
} else {
    $isSaved = false;
}

$tags = !empty($job['competences_requises']) ? explode(',', $job['competences_requises']) : [];
$missions = !empty($job['missions']) ? explode("\n", $job['missions']) : [];
$profil = !empty($job['profil_recherche']) ? explode("\n", $job['profil_recherche']) : [];

require_once __DIR__ . '/../includes/header.php';
?>

<?php if (getCurrentRole() === 'recruteur'): ?>
  <!-- RECRUITER PREVIEW BANNER -->
  <div style="background-color: var(--color-primary-dark); color: #FFF; padding: 0.75rem 1.5rem; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 100; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
    <div style="display: flex; align-items: center; gap: 0.75rem;">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
      <span style="font-weight: 600; font-size: 0.9rem;">Mode Aperçu (Public)</span>
      <span style="font-size: 0.8rem; color: #94A3B8; margin-left: 0.5rem; display: none; @media(min-width: 768px){ display: inline; }">Vous visualisez l'offre telle qu'elle apparaît aux candidats.</span>
    </div>
    <a href="../recruteur/recruteur-dashboard.php" style="background: rgba(255,255,255,0.1); color: #FFF; padding: 0.4rem 1rem; border-radius: var(--radius-pill); font-size: 0.85rem; font-weight: 500; text-decoration: none; transition: background 0.2s;">
      Fermer l'aperçu
    </a>
  </div>
<?php endif; ?>

<main class="container">
  
  <!-- Breadcrumb (Maquette Écran 3) -->
  <nav class="breadcrumb-nav">
    <a href="../index.php">Accueil</a>
    <span>›</span>
    <a href="../pages/offres.php">Offres d'emploi</a>
    <span>›</span>
    <span style="color: var(--text-main); font-weight: 600;"><?= htmlspecialchars($job['titre']) ?></span>
  </nav>

  <?php if ($applicationSuccess): ?>
    <div style="background:#ECFDF5; border:1px solid #A7F3D0; color:#065F46; padding:1.25rem 1.5rem; border-radius:16px; margin-bottom:1.5rem; display:flex; align-items:center; gap:0.75rem;">
      <span style="font-size:1.5rem;">🎉</span>
      <div>
        <strong>Candidature transmise avec succès !</strong>
        <p style="font-size:0.875rem; margin-top:2px;">Votre dossier a été envoyé à <?= htmlspecialchars($job['company_nom']) ?> et est enregistré dans votre suivi.</p>
      </div>
    </div>
  <?php elseif (!empty($applicationError)): ?>
    <div style="background:#FEF2F2; border:1px solid #FECACA; color:#991B1B; padding:1rem 1.25rem; border-radius:12px; margin-bottom:1.5rem;">
      ⚠️ <?= htmlspecialchars($applicationError) ?>
    </div>
  <?php endif; ?>

  <!-- Header Card (Maquette écran 3) -->
  <div class="job-detail-header-card">
    <div class="job-detail-header-left">
      <h1 class="job-detail-main-title">
        <?= htmlspecialchars($job['titre']) ?>
      </h1>
      
      <?php
        // Algorithme de FOMO (Sentiment d'urgence)
        $fomoCount = ($job['id'] * date('j')) % 12 + 3; // donne un chiffre entre 3 et 14 qui reste stable la journée
      ?>
      <div style="background: #FEF2F2; border: 1px solid #FECACA; color: #DC2626; padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; animation: pulse 2s infinite;">
        <span style="font-size: 1rem;">🔥</span> <?= $fomoCount ?> autres candidats consultent cette offre en ce moment
      </div>
      <style>
        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.8; } 100% { opacity: 1; } }
      </style>

      <div class="job-detail-company-row">
        <span class="job-detail-company-name"><?= htmlspecialchars($job['company_nom']) ?></span>
        <?php if ($job['company_verifie']): ?>
          <span class="verified-badge">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
              <path d="m9 12 2 2 4-4"></path>
            </svg>
            Entreprise vérifiée
          </span>
        <?php endif; ?>
      </div>

      <div class="job-detail-meta-row">
        <span class="meta-item">📍 <?= htmlspecialchars($job['lieu']) ?> (<?= htmlspecialchars($job['mode_travail']) ?>)</span>
        <span class="bullet-separator"> • </span>
        <span class="meta-item">💼 <?= htmlspecialchars($job['type_contrat']) ?></span>
        <span class="bullet-separator"> • </span>
        <span class="meta-item">🎓 <?= htmlspecialchars($job['experience_requise']) ?></span>
        <span class="bullet-separator"> • </span>
        <?php
          $wordCount = str_word_count(strip_tags($job['description']));
          $readTime = max(1, ceil($wordCount / 200));
        ?>
        <span class="meta-item" style="color: #059669; font-weight: 600; background: #D1FAE5; padding: 2px 8px; border-radius: 99px;">⏱️ ~<?= $readTime ?> min de lecture</span>
        <span class="bullet-separator"> • </span>
        <span class="meta-item" style="color: #D97706; font-weight: 700;">💰 <?= number_format($job['salaire_min'], 0, ',', ' ') ?> - <?= number_format($job['salaire_max'], 0, ',', ' ') ?> FCFA / mois</span>
        <span class="bullet-separator"> • </span>
        <span class="meta-item">⏱️ Publié il y a 2 heures</span>
      </div>
    </div>

    <!-- Action Buttons Right -->
    <div class="job-detail-header-right">
      <div class="company-avatar-box job-detail-avatar" style="<?= $job['company_id'] == 2 ? 'background:#DC2626;' : ($job['company_id'] == 3 ? 'background:#0284C7;' : ($job['company_id'] == 4 ? 'background:#EA580C;' : 'background:#081326;')) ?> overflow:hidden;">
        <?php if (!empty($job['company_logo']) && file_exists(__DIR__ . '/' . $job['company_logo'])): ?>
          <img src="<?= htmlspecialchars($job['company_logo']) ?>" alt="Logo" style="width:100%; height:100%; object-fit:cover;">
        <?php else: ?>
          <span style="color: #FFB800; font-weight: 800;"><?= strtoupper(substr($job['company_nom'], 0, 2)) ?></span>
        <?php endif; ?>
      </div>

      <div class="job-detail-buttons">
        <?php if ($alreadyApplied): ?>
          <button class="btn-outline applied-btn" disabled>
            ✓ Candidature envoyée
          </button>
        <?php elseif (!isLoggedIn()): ?>
          <a href="redirect=offre-detail.php?id=<?= $job['id'] ?>" class="btn-primary" style="display:inline-flex; align-items:center; justify-content:center; text-decoration:none;">
            Se connecter pour postuler
          </a>
        <?php elseif (getCurrentRole() !== 'recruteur'): ?>
          <button class="btn-primary" onclick="document.getElementById('apply-modal').style.display='flex'">
            Postuler maintenant
          </button>
        <?php endif; ?>
        <?php if (getCurrentRole() === 'candidat'): ?>
        <button class="btn-outline bookmark-btn <?= $isSaved ? 'active' : '' ?>" onclick="toggleSaveJob(<?= $job['id'] ?>, this)">
          <?= $isSaved ? 'Sauvegardée' : 'Sauvegarder' ?>
        </button>
        <?php endif; ?>
        <button class="btn-outline" onclick="navigator.clipboard?.writeText(window.location.href); alert('Lien copié !');">
          Partager
        </button>
        <?php if (getCurrentRole() === 'candidat'): ?>
        <button class="btn-outline" style="color: #ef4444; border-color: #ef4444;" onclick="document.getElementById('report-modal').style.display='flex'">
          Signaler
        </button>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Layout 2 Columns: Main Article + Key Info Sidebar -->
  <div class="job-detail-layout" style="margin-bottom: 4rem;">
    
    <!-- Left Article Content -->
    <article class="job-main-article">
      
      <!-- Description -->
      <h2 class="section-block-title">Description du poste</h2>
      <p style="font-size: 0.95rem; color: #334155; line-height: 1.7; margin-bottom: 1.5rem;">
        <?= nl2br(htmlspecialchars($job['description'])) ?>
      </p>

      <!-- Missions -->
      <?php if (!empty($missions)): ?>
        <h2 class="section-block-title">Missions principales</h2>
        <ul class="styled-bullet-list" style="margin-bottom: 1.5rem;">
          <?php foreach ($missions as $m): if(trim($m)): ?>
            <li><?= htmlspecialchars(trim($m)) ?></li>
          <?php endif; endforeach; ?>
        </ul>
      <?php endif; ?>

      <!-- Profil recherché -->
      <?php if (!empty($profil)): ?>
        <h2 class="section-block-title">Profil recherché</h2>
        <ul class="styled-bullet-list" style="margin-bottom: 1.5rem;">
          <?php foreach ($profil as $p): if(trim($p)): ?>
            <li><?= htmlspecialchars(trim($p)) ?></li>
          <?php endif; endforeach; ?>
        </ul>
      <?php endif; ?>

      <!-- Compétences requises tags -->
      <?php if (!empty($tags)): ?>
        <h2 class="section-block-title">Compétences requises</h2>
        <div class="job-tags-row">
          <?php foreach ($tags as $t): ?>
            <span class="skill-tag" style="padding: 0.4rem 0.85rem; font-size: 0.85rem;"><?= htmlspecialchars(trim($t)) ?></span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </article>

    <!-- Right Key Information Sidebar (Maquette Écran 3) -->
    <aside class="key-info-sidebar-card">
      <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--color-primary-dark); margin-bottom: 1.25rem;">
        Informations clés
      </h3>

      <div class="info-row">
        <span class="info-label">Type de contrat</span>
        <span class="info-val"><?= htmlspecialchars($job['type_contrat']) ?></span>
      </div>

      <div class="info-row">
        <span class="info-label">Niveau d'expérience</span>
        <span class="info-val"><?= htmlspecialchars($job['experience_requise']) ?></span>
      </div>

      <div class="info-row">
        <span class="info-label">Salaire</span>
        <span class="info-val" style="color: #D97706;"><?= number_format($job['salaire_min'], 0, ',', ' ') ?> - <?= number_format($job['salaire_max'], 0, ',', ' ') ?> FCFA</span>
      </div>

      <div class="info-row">
        <span class="info-label">Lieu</span>
        <span class="info-val"><?= htmlspecialchars($job['lieu']) ?>, Togo</span>
      </div>

      <div class="info-row">
        <span class="info-label">Date limite de candidature</span>
        <span class="info-val"><?= !empty($job['date_limite']) ? date('d/m/Y', strtotime($job['date_limite'])) : '31/08/2026' ?></span>
      </div>

      <?php if ($alreadyApplied): ?>
        <div style="text-align: center; margin-top: 1.5rem; font-size: 0.85rem; color: #059669; font-weight: 700;">
          ✓ Vous avez déjà postulé
        </div>
      <?php elseif (!isLoggedIn()): ?>
        <a href="redirect=offre-detail.php?id=<?= $job['id'] ?>" class="btn-primary" style="width: 100%; margin-top: 1.5rem; text-decoration:none; display:flex; justify-content:center;">
          Se connecter pour postuler
        </a>
      <?php elseif (getCurrentRole() !== 'recruteur'): ?>
        <button class="btn-primary" style="width: 100%; margin-top: 1.5rem;" onclick="document.getElementById('apply-modal').style.display='flex'">
          Postuler à cette offre
        </button>
      <?php endif; ?>
    </aside>

  </div>

  <!-- Section Offres Similaires -->
  <?php
    $simStmt = $db->prepare("
      SELECT j.id, j.titre, j.lieu, j.type_contrat, c.nom as company_nom, c.logo as company_logo 
      FROM jobs j 
      JOIN companies c ON j.company_id = c.id 
      WHERE j.categorie = ? AND j.id != ? AND j.statut = 'active' 
      ORDER BY j.created_at DESC LIMIT 3
    ");
    $simStmt->execute([$job['categorie'], $jobId]);
    $similarJobs = $simStmt->fetchAll();
  ?>
  <?php if (count($similarJobs) > 0): ?>
  <div style="margin-top:4rem; border-top:1px solid #E2E8F0; padding-top:3rem;">
    <h3 style="font-size:1.5rem; font-weight:800; color:#0F172A; margin-bottom:1.5rem;">Ces offres pourraient vous intéresser</h3>
    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:1.5rem;">
      <?php foreach ($similarJobs as $sJob): ?>
      <a href="id=<?= $sJob['id'] ?>" style="display:block; background:#FFF; border:1px solid #E2E8F0; border-radius:16px; padding:1.5rem; text-decoration:none; color:inherit; transition:transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 15px 30px rgba(0,0,0,0.08)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
        <div style="display:flex; align-items:center; gap:1rem; margin-bottom:1rem;">
          <div style="width:48px; height:48px; border-radius:12px; border:1px solid #E2E8F0; overflow:hidden; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
            <?php if (!empty($sJob['company_logo']) && file_exists(__DIR__ . '/' . $sJob['company_logo'])): ?>
              <img src="<?= htmlspecialchars($sJob['company_logo']) ?>" style="width:100%; height:100%; object-fit:cover;">
            <?php else: ?>
              <div style="background:#081326; width:100%; height:100%;"></div>
            <?php endif; ?>
          </div>
          <div>
            <div style="font-weight:700; color:#0F172A; font-size:1.05rem; margin-bottom:0.15rem; line-height:1.2;"><?= htmlspecialchars($sJob['titre']) ?></div>
            <div style="font-size:0.85rem; color:#64748B;"><?= htmlspecialchars($sJob['company_nom']) ?></div>
          </div>
        </div>
        <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
          <span style="padding:0.25rem 0.75rem; background:#F1F5F9; color:#475569; border-radius:99px; font-size:0.75rem; font-weight:600;"><?= htmlspecialchars($sJob['lieu']) ?></span>
          <span style="padding:0.25rem 0.75rem; background:#EFF6FF; color:#2563EB; border-radius:99px; font-size:0.75rem; font-weight:600;"><?= htmlspecialchars($sJob['type_contrat']) ?></span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

</main>

<!-- Modal de Candidature Interactive (Envoi vers MySQL) -->
<div id="apply-modal" style="display:none; position:fixed; inset:0; background:rgba(8,19,38,0.7); backdrop-filter:blur(4px); z-index:999; align-items:center; justify-content:center; padding:1.5rem;">
  <div style="background:#FFF; border-radius:24px; max-width:560px; width:100%; padding:2rem; box-shadow:0 25px 50px rgba(0,0,0,0.25); position:relative;">
    
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
      <h3 style="font-size:1.35rem; font-weight:800; color:var(--color-primary-dark);">
        Postuler à l'offre
      </h3>
      <button onclick="document.getElementById('apply-modal').style.display='none'" style="font-size:1.5rem; color:var(--text-light); line-height:1;">✕</button>
    </div>

    <form action="offre-detail.php?id=<?= $job['id'] ?>" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="postuler_submit" value="1">

      <div style="margin-bottom:1.25rem;">
        <label style="display:block; font-size:0.875rem; font-weight:700; color:var(--color-primary-dark); margin-bottom:0.35rem;">
          Votre CV attaché
        </label>
        <div style="display:flex; align-items:center; gap:0.75rem; padding:0.75rem 1rem; background:var(--bg-surface-secondary); border:1px solid var(--border-light); border-radius:12px;">
          <span style="font-size:1.25rem;">📄</span>
          <div style="flex:1;">
            <strong style="font-size:0.875rem; color:var(--color-primary-dark);">CV_<?= htmlspecialchars(str_replace(' ', '_', $user['nom'] ?? 'Candidat')) ?>.pdf</strong>
            <span style="display:block; font-size:0.75rem; color:var(--text-muted);">Généré depuis votre profil TGTravail</span>
          </div>
          <span style="color:#059669; font-weight:700; font-size:0.8rem;">✓ Prêt</span>
        </div>
      </div>

      <div style="margin-bottom:1.25rem;">
        <label style="display:block; font-size:0.875rem; font-weight:700; color:var(--color-primary-dark); margin-bottom:0.35rem;">
          Lettre de motivation (Optionnel)
        </label>
        
        <div style="display: flex; gap: 0.5rem; margin-bottom: 0.75rem;">
          <button type="button" id="tab-text" class="btn-outline" style="flex:1; padding:0.5rem; font-size:0.85rem;" onclick="switchLetterTab('text')">📝 Texte</button>
          <button type="button" id="tab-pdf" class="btn-outline" style="flex:1; padding:0.5rem; font-size:0.85rem; border-color:#CBD5E1; color:#64748B;" onclick="switchLetterTab('pdf')">📄 PDF</button>
          <button type="button" id="tab-video" class="btn-outline" style="flex:1; padding:0.5rem; font-size:0.85rem; border-color:#CBD5E1; color:#64748B;" onclick="switchLetterTab('video')">🎥 Vidéo Pitch</button>
        </div>

        <div id="content-text">
          <textarea name="lettre_motivation" rows="4" style="width:100%; padding:0.75rem 1rem; border:1px solid var(--border-light); border-radius:12px; font-size:0.9rem; outline:none;" placeholder="Expliquez brièvement pourquoi votre profil correspond à cette offre...">Bonjour, je suis très motivée par cette opportunité chez <?= htmlspecialchars($job['company_nom']) ?> et souhaite mettre mes compétences au service de vos projets.</textarea>
        </div>

        <div id="content-pdf" style="display:none;">
          <div id="dropZoneLetter" style="border: 2px dashed #CBD5E1; border-radius: 12px; padding: 1.5rem 1rem; text-align: center; background: #F8FAFC; cursor: pointer;" onclick="document.getElementById('lettrePdfInput').click();" ondragover="event.preventDefault(); this.style.borderColor='#0EA5E9';" ondragleave="this.style.borderColor='#CBD5E1';" ondrop="event.preventDefault(); this.style.borderColor='#CBD5E1'; document.getElementById('lettrePdfInput').files = event.dataTransfer.files; document.getElementById('pdf-name').innerText = event.dataTransfer.files[0].name;">
            <input type="file" name="lettre_pdf" id="lettrePdfInput" accept=".pdf" style="display:none;" onchange="document.getElementById('pdf-name').innerText = this.files[0] ? this.files[0].name : '';">
            <span style="font-size: 1.5rem;">📁</span>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0.5rem 0 0;">Glissez-déposez votre lettre (PDF) ici<br>ou cliquez pour parcourir</p>
            <p id="pdf-name" style="font-size: 0.8rem; color: #10B981; font-weight: 700; margin-top: 0.5rem;"></p>
          </div>
        </div>

        <div id="content-video" style="display:none; text-align: center; border: 1px solid var(--border-light); border-radius: 12px; padding: 1rem; background: #F8FAFC;">
          <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.75rem;">Enregistrez un pitch de 45s max avec votre webcam pour vous démarquer !</p>
          <video id="video-preview" style="width: 100%; border-radius: 8px; background: #000; display: none;" autoplay muted></video>
          <div style="margin-top: 0.75rem; display: flex; gap: 0.5rem; justify-content: center;">
            <button type="button" id="btn-start-record" class="btn-primary" style="padding: 0.5rem 1rem;" onclick="startRecording()">🔴 Démarrer</button>
            <button type="button" id="btn-stop-record" class="btn-outline" style="padding: 0.5rem 1rem; display: none; color: #EF4444; border-color: #EF4444;" onclick="stopRecording()">⏹️ Arrêter</button>
          </div>
          <input type="hidden" name="video_data" id="video-data-input">
        </div>
      </div>

      <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1.5rem;">
        <div>
          <label style="display:block; font-size:0.875rem; font-weight:700; color:var(--color-primary-dark); margin-bottom:0.35rem;">
            Prétention salariale (FCFA)
          </label>
          <input type="number" name="pretention_salariale" value="<?= $job['salaire_min'] ?>" style="width:100%; padding:0.75rem; border:1px solid var(--border-light); border-radius:12px; font-size:0.9rem; outline:none;">
        </div>

        <div>
          <label style="display:block; font-size:0.875rem; font-weight:700; color:var(--color-primary-dark); margin-bottom:0.35rem;">
            Disponibilité
          </label>
          <select name="disponibilite" style="width:100%; padding:0.75rem; border:1px solid var(--border-light); border-radius:12px; font-size:0.9rem; outline:none;">
            <option value="Immédiate" selected>Immédiate</option>
            <option value="Sous 15 jours">Sous 15 jours</option>
            <option value="Sous 1 mois">Sous 1 mois</option>
          </select>
        </div>
      </div>

      <div style="display:flex; justify-content:flex-end; gap:1rem;">
        <button type="button" onclick="document.getElementById('apply-modal').style.display='none'" class="btn-link">Annuler</button>
        <button type="submit" class="btn-primary">Envoyer ma candidature 🚀</button>
      </div>
    </form>



  </div>
</div>

<!-- Modal de Signalement -->
<div id="report-modal" style="display:none; position:fixed; inset:0; background:rgba(8,19,38,0.7); backdrop-filter:blur(4px); z-index:999; align-items:center; justify-content:center; padding:1.5rem;">
  <div style="background:#FFF; border-radius:24px; max-width:500px; width:100%; padding:2rem; box-shadow:0 25px 50px rgba(0,0,0,0.25); position:relative;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
      <h3 style="font-size:1.35rem; font-weight:800; color:#ef4444;">Signaler cette offre</h3>
      <button onclick="document.getElementById('report-modal').style.display='none'" style="font-size:1.5rem; color:var(--text-light); line-height:1; border:none; background:transparent; cursor:pointer;">✕</button>
    </div>
    
    <div style="margin-bottom:1.25rem;">
      <label style="display:block; font-size:0.875rem; font-weight:700; color:var(--color-primary-dark); margin-bottom:0.35rem;">
        Motif du signalement
      </label>
      <select id="report-motif" style="width:100%; padding:0.75rem; border:1px solid var(--border-light); border-radius:12px; font-size:0.9rem; outline:none;">
        <option value="">Sélectionner un motif</option>
        <option value="Arnaque / Fraude">Arnaque / Fraude financière</option>
        <option value="Offre abusive / fausse">Offre abusive ou fausse</option>
        <option value="Discrimination">Discrimination</option>
        <option value="Contenu inapproprié">Contenu inapproprié</option>
        <option value="Autre">Autre</option>
      </select>
    </div>
    
    <div style="margin-bottom:1.5rem;">
      <label style="display:block; font-size:0.875rem; font-weight:700; color:var(--color-primary-dark); margin-bottom:0.35rem;">
        Détails supplémentaires
      </label>
      <textarea id="report-details" rows="3" style="width:100%; padding:0.75rem 1rem; border:1px solid var(--border-light); border-radius:12px; font-size:0.9rem; outline:none;" placeholder="Merci de nous donner plus de détails..."></textarea>
    </div>
    
    <div style="display:flex; justify-content:flex-end; gap:1rem;">
      <button type="button" onclick="document.getElementById('report-modal').style.display='none'" class="btn-link">Annuler</button>
      <button type="button" onclick="submitReport()" class="btn-primary" style="background:#ef4444;">Envoyer le signalement</button>
    </div>
  </div>
</div>
<script>
async function submitReport() {
    const motif = document.getElementById('report-motif').value;
    const details = document.getElementById('report-details').value;
    const jobId = <?= (int)$job['id'] ?>;

    if (!motif) {
        alert("Veuillez sélectionner un motif de signalement.");
        return;
    }

    try {
        const response = await fetch('../api/ajax_report_job.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                job_id: jobId,
                motif: motif,
                details: details
            })
        });

        const data = await response.json();
        
        if (data.success) {
            alert(data.message);
            document.getElementById('report-modal').style.display = 'none';
            document.getElementById('report-motif').value = '';
            document.getElementById('report-details').value = '';
        } else {
            alert(data.message || "Une erreur est survenue.");
        }
    } catch (err) {
        console.error(err);
        alert("Erreur de connexion au serveur.");
    }
}

function toggleSaveJob(jobId, btn) {
    if (!jobId) return;
    
    fetch('../api/api-toggle-save.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ job_id: jobId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            btn.classList.toggle('active');
            if (data.action === 'added') {
                btn.innerText = 'Sauvegardée';
            } else {
                btn.innerText = 'Sauvegarder';
            }
        } else {
            alert(data.message || "Erreur lors de la sauvegarde.");
        }
    })
    .catch(err => {
        console.error(err);
        alert("Erreur réseau");
    });
}

function submitReport() {
    const motif = document.getElementById('report-motif').value;
    const details = document.getElementById('report-details').value;
    
    if (!motif) {
        alert("Veuillez sélectionner un motif de signalement.");
        return;
    }
    
    fetch('../api/api-report.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ job_id: <?= $job['id'] ?>, motif: motif, details: details })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            document.getElementById('report-modal').style.display = 'none';
        } else {
            alert(data.message || "Erreur lors de l'envoi.");
        }
    })
    .catch(err => {
        console.error(err);
        alert("Erreur réseau");
    });
}
</script>

<script>
  function switchLetterTab(tab) {
    // Reset styles
    ['text', 'pdf', 'video'].forEach(t => {
      document.getElementById('tab-' + t).style.borderColor = '#CBD5E1';
      document.getElementById('tab-' + t).style.color = '#64748B';
      document.getElementById('content-' + t).style.display = 'none';
    });
    // Active style
    document.getElementById('tab-' + tab).style.borderColor = '#2563EB';
    document.getElementById('tab-' + tab).style.color = '#2563EB';
    document.getElementById('content-' + tab).style.display = 'block';
  }

  // MediaRecorder Logic
  let mediaRecorder;
  let recordedChunks = [];
  let stream;

  async function startRecording() {
    try {
      stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
      const videoPreview = document.getElementById('video-preview');
      videoPreview.srcObject = stream;
      videoPreview.style.display = 'block';
      
      mediaRecorder = new MediaRecorder(stream);
      mediaRecorder.ondataavailable = e => { if (e.data.size > 0) recordedChunks.push(e.data); };
      mediaRecorder.onstop = () => {
        const blob = new Blob(recordedChunks, { type: 'video/webm' });
        // Convert to base64 to send via hidden input (or we could use FormData directly, but hidden input is easier for this form)
        const reader = new FileReader();
        reader.readAsDataURL(blob);
        reader.onloadend = () => {
          document.getElementById('video-data-input').value = reader.result;
        };
        videoPreview.srcObject = null;
        videoPreview.src = URL.createObjectURL(blob);
        videoPreview.controls = true;
      };

      mediaRecorder.start();
      document.getElementById('btn-start-record').style.display = 'none';
      document.getElementById('btn-stop-record').style.display = 'inline-block';
      
      // Auto stop after 45s
      setTimeout(() => { if(mediaRecorder.state === 'recording') stopRecording(); }, 45000);
    } catch (err) {
      alert("Erreur d'accès à la webcam : " + err.message);
    }
  }

  function stopRecording() {
    if (mediaRecorder && mediaRecorder.state === 'recording') {
      mediaRecorder.stop();
      stream.getTracks().forEach(track => track.stop());
      document.getElementById('btn-stop-record').style.display = 'none';
      document.getElementById('btn-start-record').style.display = 'inline-block';
      document.getElementById('btn-start-record').innerText = '🔄 Recommencer';
      recordedChunks = [];
    }
  }
</script>

<!-- Confettis Effect -->
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<script>
  // Si candidature envoyée avec succès (succès détecté soit par ?success=1 soit par le PHP rendering success message)
  <?php if ($applicationSuccess || (isset($_GET['success']) && $_GET['success'] == 1)): ?>
    setTimeout(() => {
      var duration = 3000;
      var end = Date.now() + duration;

      (function frame() {
        confetti({
          particleCount: 5,
          angle: 60,
          spread: 55,
          origin: { x: 0 },
          colors: ['#2563EB', '#10B981', '#F59E0B']
        });
        confetti({
          particleCount: 5,
          angle: 120,
          spread: 55,
          origin: { x: 1 },
          colors: ['#2563EB', '#10B981', '#F59E0B']
        });

        if (Date.now() < end) {
          requestAnimationFrame(frame);
        }
      }());
    }, 500);
  <?php endif; ?>
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>



