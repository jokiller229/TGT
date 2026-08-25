<?php
$pageTitle = 'Publier une offre - TGTravail';
require_once __DIR__ . '/../includes/auth.php';
requireRole('recruteur');
$db = getDB();
$companyId = getCurrentCompanyId();
if (!$companyId) { header("Location: ../index.php"); exit; }

$user = getCurrentUser();
$stmtComp = $db->prepare("SELECT nom, logo, type_entite, subscription_plan, subscription_end, job_credits FROM companies WHERE id = ?");
$stmtComp->execute([$companyId]);
$company = $stmtComp->fetch();
$companyName = $company['nom'] ?? 'Mon Entreprise';
$type_entite = $company['type_entite'] ?? 'entreprise';

$isPro = (($company['subscription_plan'] ?? '') === 'pro' && strtotime($company['subscription_end'] ?? '0') > time());
$jobCredits = (int)($company['job_credits'] ?? 0);
$totalJobs = (int)$db->query("SELECT COUNT(*) FROM jobs WHERE company_id = {$companyId}")->fetchColumn();
$canPost = ($isPro || $totalJobs < 3 || $jobCredits > 0);

$postError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_publish_job'])) {
    if (!$canPost) {
        $postError = "Vous avez atteint votre limite d'offres. Veuillez acheter un pack ou passer au plan Pro.";
    } else {
        $titre       = trim($_POST['titre'] ?? '');
    $categorie   = trim($_POST['categorie'] ?? '');
    $type_contrat= trim($_POST['type_contrat'] ?? '');
    $lieu        = trim($_POST['lieu'] ?? '');
    $mode        = trim($_POST['mode'] ?? '');
    $salaire_min = (int)($_POST['salaire_min'] ?? 0);
    $salaire_max = (int)($_POST['salaire_max'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $competences = trim($_POST['competences'] ?? '');
    $experience  = trim($_POST['experience'] ?? '');
    $formation   = trim($_POST['formation'] ?? '');
    $pack        = trim($_POST['pack'] ?? 'simple');

        if (empty($titre)) {
            $postError = "Veuillez renseigner l'intitulé du poste.";
        } else {
            $stmt = $db->prepare("INSERT INTO jobs (company_id, titre, categorie, type_contrat, lieu, mode_travail, salaire_min, salaire_max, description, pack, statut, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())");
            $stmt->execute([$companyId, $titre, $categorie, $type_contrat, $lieu, $mode, $salaire_min, $salaire_max, $description, $pack]);
            $jobId = $db->lastInsertId();

            if (!$isPro && $totalJobs >= 3) {
                $db->query("UPDATE companies SET job_credits = job_credits - 1 WHERE id = {$companyId}");
            }

            // MATCHING ET NOTIFICATIONS POUR LES ALERTES EMPLOI
        $stmtAlerts = $db->prepare("
            SELECT a.user_id, a.mots_cles, u.email 
            FROM job_alerts a
            JOIN users u ON a.user_id = u.id
            WHERE (a.categorie = ? OR a.categorie = '' OR a.categorie IS NULL)
              AND (a.type_contrat = ? OR a.type_contrat = '' OR a.type_contrat IS NULL)
        ");
        $stmtAlerts->execute([$categorie, $type_contrat]);
        $alerts = $stmtAlerts->fetchAll(PDO::FETCH_ASSOC);

        $matchedUsers = [];
        $notifStmt = $db->prepare("INSERT INTO notifications (user_id, job_id, message) VALUES (?, ?, ?)");

        foreach ($alerts as $al) {
            $match = false;
            if (empty(trim($al['mots_cles'] ?? ''))) {
                $match = true;
            } else {
                $keywords = array_filter(array_map('trim', explode(',', $al['mots_cles'])));
                foreach ($keywords as $kw) {
                    if (stripos($titre, $kw) !== false || stripos($description, $kw) !== false) {
                        $match = true;
                        break;
                    }
                }
            }

            if ($match && !in_array($al['user_id'], $matchedUsers)) {
                $msg = "Nouvelle offre correspondante : " . mb_substr($titre, 0, 50) . (mb_strlen($titre)>50?'...':'');
                $notifStmt->execute([$al['user_id'], $jobId, $msg]);
                $matchedUsers[] = $al['user_id'];
                
                // TODO: Intégration SMTP
                // En production, utiliser PHPMailer ou mail() ici pour notifier $al['email']
                // mail($al['email'], "Nouvelle offre : $titre", "Découvrez cette offre sur TGTravail !");
            }
        }

        header("Location: ../recruteur/mes-offres.php?published=1");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $pageTitle ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html, body { height: 100%; font-family: 'Inter', sans-serif; background: #F8FAFC; color: #0F172A; }

    /* ---- FULL PAGE LAYOUT ---- */
    .wizard-page { min-height: 100vh; display: flex; flex-direction: column; }

    /* ---- TOP BAR ---- */
    .wizard-topbar {
      background: #FFF;
      border-bottom: 1px solid #E2E8F0;
      padding: 0 2.5rem;
      height: 64px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-shrink: 0;
    }
    .wizard-topbar .logo { display: flex; align-items: center; gap: 0.6rem; text-decoration: none; }
    .wizard-topbar .logo .icon { width: 38px; height: 38px; background: #FFB800; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
    .wizard-topbar .logo span { font-size: 1.15rem; font-weight: 800; }
    .wizard-topbar .logo .tg { color: #2563EB; }
    .wizard-topbar .logo .tr { color: #081326; }

    /* ---- STEPPER ---- */
    .wizard-stepper {
      display: flex;
      align-items: center;
      gap: 0;
    }
    .step-item {
      display: flex;
      align-items: center;
      gap: 0.6rem;
      font-size: 0.82rem;
      color: #94A3B8;
      font-weight: 600;
      white-space: nowrap;
    }
    .step-item.active { color: #2563EB; }
    .step-item.done { color: #059669; }
    .step-circle {
      width: 28px; height: 28px; border-radius: 50%;
      border: 2px solid #CBD5E1;
      display: flex; align-items: center; justify-content: center;
      font-size: 0.75rem; font-weight: 800;
      color: #94A3B8;
      flex-shrink: 0;
      background: #FFF;
    }
    .step-item.active .step-circle { border-color: #2563EB; color: #2563EB; background: #EFF6FF; }
    .step-item.done .step-circle { border-color: #059669; color: #FFF; background: #059669; }
    .step-line { width: 40px; height: 2px; background: #E2E8F0; margin: 0 0.5rem; }
    .step-line.done { background: #059669; }

    /* ---- BODY ---- */
    .wizard-body {
      flex: 1;
      display: grid;
      grid-template-columns: 1fr 340px;
      gap: 0;
      overflow: hidden;
    }

    /* ---- LEFT FORM AREA ---- */
    .wizard-form-area {
      padding: 2.5rem 3rem;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
    }
    .step-panel { display: none; flex-direction: column; gap: 1.5rem; flex: 1; }
    .step-panel.active { display: flex; }

    /* ---- FORM ELEMENTS ---- */
    .form-title { font-size: 1.4rem; font-weight: 800; color: #081326; margin-bottom: 0.25rem; }
    .form-subtitle { font-size: 0.875rem; color: #64748B; margin-bottom: 0.5rem; }

    .field-label { display: block; font-size: 0.8rem; font-weight: 700; color: #374151; margin-bottom: 0.4rem; letter-spacing: 0.01em; }
    .field-label span { color: #EF4444; }
    .field-input {
      width: 100%; padding: 0.75rem 1rem;
      border: 1.5px solid #E2E8F0; border-radius: 12px;
      font-size: 0.9rem; font-family: inherit; color: #0F172A;
      outline: none; transition: border-color 0.2s, box-shadow 0.2s;
      background: #FFF;
    }
    .field-input:focus { border-color: #2563EB; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
    .field-input::placeholder { color: #CBD5E1; }
    textarea.field-input { min-height: 140px; resize: vertical; line-height: 1.6; }

    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.75rem; }

    /* Radio pill mode */
    .radio-pill-group { display: flex; gap: 0.75rem; }
    .radio-pill input { display: none; }
    .radio-pill label {
      padding: 0.55rem 1.2rem; border-radius: 99px;
      border: 1.5px solid #E2E8F0; font-size: 0.85rem; font-weight: 600;
      cursor: pointer; color: #64748B; transition: all 0.2s; background: #FFF; user-select: none;
    }
    .radio-pill input:checked + label { border-color: #2563EB; background: #EFF6FF; color: #2563EB; }

    /* Plan cards */
    .plan-card { border: 1.5px solid #E2E8F0; border-radius: 16px; padding: 1.25rem; cursor: pointer; transition: all 0.2s; }
    .plan-card:hover { border-color: #93C5FD; background: #F8FAFC; }
    .plan-card.selected { border-color: #2563EB; background: #EFF6FF; }
    .plan-card-pro { border-color: #FFB800; background: linear-gradient(135deg,#FFFBEB,#FEF3C7); }
    .plan-card-pro.selected { border-color: #D97706; background: linear-gradient(135deg,#FEF3C7,#FDE68A); }

    /* ---- BOTTOM ACTION BAR ---- */
    .wizard-actions {
      display: flex; align-items: center; justify-content: space-between;
      padding: 1.25rem 3rem;
      border-top: 1px solid #E2E8F0;
      background: #FFF;
      flex-shrink: 0;
    }

    /* ---- RIGHT PANEL ---- */
    .wizard-right {
      background: #F8FAFC;
      border-left: 1px solid #E2E8F0;
      padding: 2rem 1.5rem;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
      gap: 1.5rem;
    }

    .tip-box {
      background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 16px; padding: 1.25rem;
    }
    .tip-box h4 { font-size: 0.85rem; font-weight: 700; color: #92400E; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.4rem; }
    .tip-box p { font-size: 0.8rem; color: #78350F; line-height: 1.6; }

    .preview-box { background: #FFF; border: 1px solid #E2E8F0; border-radius: 16px; padding: 1.25rem; }
    .preview-box h4 { font-size: 0.85rem; font-weight: 700; color: #0F172A; margin-bottom: 1rem; }
    .preview-row {
      display: flex; justify-content: space-between; align-items: flex-start;
      padding: 0.5rem 0; border-bottom: 1px solid #F1F5F9;
      font-size: 0.8rem; gap: 0.5rem;
    }
    .preview-row:last-child { border-bottom: none; }
    .preview-row .lbl { color: #94A3B8; font-weight: 500; flex-shrink: 0; }
    .preview-row .val { font-weight: 700; color: #0F172A; text-align: right; word-break: break-word; }
    .preview-row .val.empty { color: #CBD5E1; font-style: italic; font-weight: 400; }

    /* Buttons */
    .btn-back { background: none; border: 1.5px solid #E2E8F0; border-radius: 12px; padding: 0.7rem 1.5rem; font-size: 0.9rem; font-weight: 700; color: #64748B; cursor: pointer; transition: all 0.2s; font-family: inherit; }
    .btn-back:hover { background: #F8FAFC; }
    .btn-next { background: #2563EB; color: #FFF; border: none; border-radius: 12px; padding: 0.7rem 2rem; font-size: 0.9rem; font-weight: 700; cursor: pointer; transition: background 0.2s; font-family: inherit; display: inline-flex; align-items: center; gap: 0.5rem; }
    .btn-next:hover { background: #1D4ED8; }
    .btn-publish { background: #081326; color: #FFB800; border: none; border-radius: 12px; padding: 0.7rem 2rem; font-size: 0.9rem; font-weight: 800; cursor: pointer; font-family: inherit; display: inline-flex; align-items: center; gap: 0.5rem; transition: background 0.2s; }
    .btn-publish:hover { background: #0F2040; }
    .btn-cancel { color: #94A3B8; font-size: 0.875rem; font-weight: 600; text-decoration: none; }
    .btn-cancel:hover { color: #64748B; }

    /* Progress bar top */
    .progress-bar { height: 3px; background: #E2E8F0; }
    .progress-bar-fill { height: 100%; background: linear-gradient(90deg, #2563EB, #60A5FA); transition: width 0.4s ease; }

    /* Skill tags input */
    .tag-input-wrapper { display: flex; flex-wrap: wrap; gap: 0.5rem; padding: 0.6rem 0.75rem; border: 1.5px solid #E2E8F0; border-radius: 12px; background: #FFF; min-height: 48px; cursor: text; transition: border-color 0.2s; }
    .tag-input-wrapper:focus-within { border-color: #2563EB; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
    .skill-tag { background: #EFF6FF; color: #2563EB; border-radius: 99px; padding: 0.2rem 0.7rem; font-size: 0.78rem; font-weight: 700; display: flex; align-items: center; gap: 0.3rem; }
    .skill-tag button { background: none; border: none; cursor: pointer; color: #93C5FD; font-size: 0.75rem; padding: 0; line-height: 1; }
    .tag-input-field { border: none; outline: none; font-size: 0.85rem; font-family: inherit; color: #0F172A; min-width: 120px; background: transparent; flex: 1; }
  </style>
</head>
<body>
<?php if (!$canPost): ?>
<div style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #F8FAFC; padding: 2rem;">
  <div style="background: #FFF; padding: 3rem 2rem; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); text-align: center; max-width: 450px;">
    <div style="width: 64px; height: 64px; background: #FEF2F2; color: #DC2626; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
      <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
    </div>
    <h2 style="font-size: 1.4rem; font-weight: 800; color: #0F172A; margin-bottom: 0.75rem;">Limite d'offres atteinte</h2>
    <p style="font-size: 0.95rem; color: #64748B; margin-bottom: 2rem; line-height: 1.5;">Vous avez utilisé vos 3 offres gratuites. Pour continuer à recruter les meilleurs talents, passez au plan Pro ou achetez un pack d'offres.</p>
    <a href="../recruteur/abonnements.php" style="display: inline-block; width: 100%; padding: 0.85rem; background: #2563EB; color: #FFF; border-radius: 12px; font-weight: 700; text-decoration: none; transition: background 0.2s;" onmouseover="this.style.background='#1D4ED8'" onmouseout="this.style.background='#2563EB'">Voir les offres et abonnements</a>
    <a href="../recruteur/mes-offres.php" style="display: inline-block; width: 100%; margin-top: 1rem; color: #94A3B8; font-size: 0.85rem; font-weight: 600; text-decoration: none;">Retour à mes offres</a>
  </div>
</div>
<?php exit; endif; ?>

<form action="../recruteur/publier-offre.php" method="POST" id="publish-form" class="wizard-page">
<input type="hidden" name="submit_publish_job" value="1">

<!-- Hidden field storage (synced by JS) -->
<input type="hidden" name="titre" id="h-titre">
<input type="hidden" name="categorie" id="h-categorie">
<input type="hidden" name="type_contrat" id="h-contrat">
<input type="hidden" name="lieu" id="h-lieu">
<input type="hidden" name="mode" id="h-mode" value="Sur site">
<input type="hidden" name="salaire_min" id="h-salmin">
<input type="hidden" name="salaire_max" id="h-salmax">
<input type="hidden" name="description" id="h-description">
<input type="hidden" name="competences" id="h-competences">
<input type="hidden" name="experience" id="h-experience">
<input type="hidden" name="formation" id="h-formation">
<input type="hidden" name="pack" id="h-pack" value="simple">

<?php if (!empty($postError)): ?>
<div style="background:#FEF2F2; border:1px solid #FECACA; color:#991B1B; padding:0.75rem 2rem; font-size:0.875rem; font-weight:600;">
  ⚠️ <?= htmlspecialchars($postError) ?>
</div>
<?php endif; ?>

<!-- TOP BAR -->
<div class="wizard-topbar">
  <a href="../recruteur/mes-offres.php" class="logo">
    <img src="../img/tgtravail-logo.png" alt="TGTravail" class="logo-img" style="width: 38px; height: auto; border-radius: 8px;">
    <span><span class="tg">TG</span><span class="tr">Travail</span></span>
  </a>

  <!-- Stepper -->
  <div class="wizard-stepper">
    <div class="step-item active" id="si-1">
      <div class="step-circle">1</div>
      <span>Informations</span>
    </div>
    <div class="step-line" id="sl-1"></div>
    <div class="step-item" id="si-2">
      <div class="step-circle">2</div>
      <span>Détails du poste</span>
    </div>
    <div class="step-line" id="sl-2"></div>
    <div class="step-item" id="si-3">
      <div class="step-circle">3</div>
      <span>Profil recherché</span>
    </div>
    <div class="step-line" id="sl-3"></div>
    <div class="step-item" id="si-4">
      <div class="step-circle">4</div>
      <span>Publication</span>
    </div>
  </div>

  <div style="font-size:0.8rem; color:#94A3B8; font-weight:600;"><?= htmlspecialchars($companyName) ?></div>
</div>

<!-- Progress bar -->
<div class="progress-bar"><div class="progress-bar-fill" id="progress-fill" style="width:25%"></div></div>

<!-- MAIN BODY -->
<div class="wizard-body">

  <!-- LEFT: Form Steps -->
  <div class="wizard-form-area">

    <!-- ===== STEP 1: Informations ===== -->
    <div class="step-panel active" id="step-1">
      <div>
        <div class="form-title">Informations générales</div>
        <div class="form-subtitle">Les données de base de votre offre d'emploi</div>
      </div>

      <div>
        <label class="field-label" for="f-titre">Intitulé du poste <span>*</span></label>
        <input id="f-titre" type="text" class="field-input" placeholder="Ex : Développeur Web Fullstack" oninput="syncPreview()">
      </div>

      <div class="grid-2">
        <div>
          <label class="field-label" for="f-categorie">Catégorie <span>*</span></label>
          <select id="f-categorie" class="field-input" onchange="syncPreview()">
            <option value="">Sélectionner une catégorie</option>
            <?php if ($type_entite === 'entreprise'): ?>
                <option value="Informatique">Informatique & Tech</option>
                <option value="Commercial">Commercial & Vente</option>
                <option value="Comptabilité">Comptabilité & Finance</option>
                <option value="Marketing">Marketing & Communication</option>
                <option value="Ressources Humaines">Ressources Humaines</option>
                <option value="Juridique">Juridique</option>
                <option value="Santé">Santé & Médical</option>
                <option value="Ingénierie">Ingénierie & BTP</option>
                <option value="Logistique">Logistique & Transport</option>
                <option value="Éducation">Éducation & Formation</option>
                <option value="Agriculture">Agriculture</option>
            <?php else: ?>
                <option value="Ménage">Ménage & Entretien</option>
                <option value="Garde d'enfants">Garde d'enfants</option>
                <option value="Jardinage">Jardinage</option>
                <option value="Petit chantier">Petit chantier / BTP</option>
                <option value="Livraison">Livraison & Courses</option>
                <option value="Aide à domicile">Aide à domicile</option>
            <?php endif; ?>
            <option value="Autre">Autre</option>
          </select>
        </div>
        <div>
          <label class="field-label" for="f-contrat">Type de contrat <span>*</span></label>
          <select id="f-contrat" class="field-input" onchange="syncPreview()">
            <option value="">Sélectionner</option>
            <?php if ($type_entite === 'entreprise'): ?>
                <option value="CDI">CDI</option>
                <option value="CDD">CDD</option>
                <option value="Stage">Stage</option>
                <option value="Freelance">Freelance / Mission</option>
                <option value="Temps partiel">Temps partiel</option>
                <option value="Alternance">Alternance</option>
                <option value="Bénévolat">Bénévolat</option>
            <?php else: ?>
                <option value="Mission courte">Mission courte (1 à quelques jours)</option>
                <option value="Ponctuel">Ponctuel (Quelques heures)</option>
                <option value="Régulier">Régulier (Temps partiel)</option>
                <option value="Temps plein">Temps plein</option>
            <?php endif; ?>
          </select>
        </div>
      </div>

      <div>
        <label class="field-label" for="f-lieu">Lieu <span>*</span></label>
        <input id="f-lieu" type="text" class="field-input" placeholder="Ex : Lomé" oninput="syncPreview()">
      </div>

      <div>
        <label class="field-label"><?= $type_entite === 'entreprise' ? 'Salaire annuel (FCFA)' : 'Montant proposé (FCFA)' ?></label>
        <div class="grid-2">
          <input id="f-salmin" type="number" class="field-input" placeholder="Min" oninput="syncPreview()">
          <input id="f-salmax" type="number" class="field-input" placeholder="Max" oninput="syncPreview()">
        </div>
      </div>

      <div>
        <label class="field-label">Mode de travail</label>
        <div class="radio-pill-group">
          <div class="radio-pill">
            <input type="radio" name="mode_vis" id="m-onsite" value="Sur site" checked onchange="setMode(this.value)">
            <label for="m-onsite">Sur site</label>
          </div>
          <div class="radio-pill">
            <input type="radio" name="mode_vis" id="m-hybrid" value="Hybride" onchange="setMode(this.value)">
            <label for="m-hybrid">Hybride</label>
          </div>
          <div class="radio-pill">
            <input type="radio" name="mode_vis" id="m-remote" value="À distance" onchange="setMode(this.value)">
            <label for="m-remote">À distance</label>
          </div>
        </div>
      </div>
    </div>

    <!-- ===== STEP 2: Détails du poste ===== -->
    <div class="step-panel" id="step-2">
      <div>
        <div class="form-title">Détails du poste</div>
        <div class="form-subtitle">Décrivez le rôle, les missions et l'environnement de travail</div>
      </div>

      <div>
        <label class="field-label" for="f-description">Description du poste <span>*</span></label>
        <textarea id="f-description" class="field-input" placeholder="Décrivez les missions, l'environnement de travail, les avantages... (markdown supporté)" style="min-height:200px;" oninput="syncPreview()"></textarea>
      </div>

      <div>
        <label class="field-label" for="f-avantages">Avantages proposés</label>
        <textarea id="f-avantages" class="field-input" placeholder="Ex : Assurance maladie, téléphone de service, tickets repas, formation continue..." style="min-height:100px;"></textarea>
      </div>

      <div class="grid-2">
        <div>
          <label class="field-label" for="f-debut">Date de début souhaitée</label>
          <input id="f-debut" type="date" class="field-input">
        </div>
        <div>
          <label class="field-label" for="f-duree">Durée (si CDD/Stage)</label>
          <input id="f-duree" type="text" class="field-input" placeholder="Ex : 6 mois">
        </div>
      </div>
    </div>

    <!-- ===== STEP 3: Profil recherché ===== -->
    <div class="step-panel" id="step-3">
      <div>
        <div class="form-title">Profil recherché</div>
        <div class="form-subtitle">Précisez ce que vous attendez du candidat idéal</div>
      </div>

      <div>
        <label class="field-label">Compétences requises</label>
        <div class="tag-input-wrapper" id="tag-wrapper" onclick="document.getElementById('tag-field').focus()">
          <input id="tag-field" class="tag-input-field" placeholder="Tapez une compétence et appuyez sur Entrée...">
        </div>
        <p style="font-size:0.75rem; color:#94A3B8; margin-top:0.35rem;">Ex : JavaScript, React, Gestion de projet, Excel...</p>
      </div>

      <div class="grid-2">
        <div>
          <label class="field-label" for="f-experience">Expérience requise</label>
          <select id="f-experience" class="field-input">
            <option value="">Peu importe</option>
            <option value="0-1 an">0–1 an (débutant)</option>
            <option value="1-3 ans">1–3 ans</option>
            <option value="3-5 ans">3–5 ans</option>
            <option value="5-10 ans">5–10 ans</option>
            <option value="10+ ans">10+ ans (expert)</option>
          </select>
        </div>
        <div>
          <label class="field-label" for="f-formation">Niveau de formation</label>
          <select id="f-formation" class="field-input">
            <option value="">Peu importe</option>
            <option value="BAC">BAC</option>
            <option value="BAC+2">BAC+2 (BTS/DUT)</option>
            <option value="BAC+3">BAC+3 (Licence)</option>
            <option value="BAC+5">BAC+5 (Master)</option>
            <option value="BAC+8">BAC+8 (Doctorat)</option>
          </select>
        </div>
      </div>

      <div>
        <label class="field-label" for="f-langues">Langues requises</label>
        <input id="f-langues" type="text" class="field-input" placeholder="Ex : Français (courant), Anglais (notions)">
      </div>
    </div>

    <!-- ===== STEP 4: Publication ===== -->
    <div class="step-panel" id="step-4">
      <div>
        <div class="form-title">Options de publication</div>
        <div class="form-subtitle">Choisissez comment mettre en avant votre offre</div>
      </div>

      <div style="display:flex; flex-direction:column; gap:1rem;">
        <label onclick="selectPlan('simple')" id="plan-simple" class="plan-card selected" style="cursor:pointer; display:block;">
          <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.5rem;">
            <div>
              <div style="font-size:0.9rem; font-weight:800; color:#0F172A;">Publication Standard</div>
              <div style="font-size:0.78rem; color:#64748B; margin-top:0.15rem;">Offre visible pendant 60 jours dans les résultats</div>
            </div>
            <div style="text-align:right;">
              <div style="font-size:1.1rem; font-weight:900; color:#2563EB;">5 000 FCFA</div>
              <div style="font-size:0.7rem; color:#94A3B8;">ou 1 crédit</div>
            </div>
          </div>
          <ul style="list-style:none; display:flex; flex-direction:column; gap:0.35rem;">
            <li style="font-size:0.78rem; color:#64748B; display:flex; align-items:center; gap:0.4rem;"><span style="color:#059669;">✓</span> Visible dans tous les résultats de recherche</li>
            <li style="font-size:0.78rem; color:#64748B; display:flex; align-items:center; gap:0.4rem;"><span style="color:#059669;">✓</span> Candidatures illimitées</li>
            <li style="font-size:0.78rem; color:#64748B; display:flex; align-items:center; gap:0.4rem;"><span style="color:#059669;">✓</span> Notifications par email</li>
          </ul>
        </label>

        <label onclick="selectPlan('alaune')" id="plan-alaune" class="plan-card plan-card-pro" style="cursor:pointer; display:block; position:relative;">
          <div style="position:absolute; top:-10px; right:1rem; background:#FFB800; color:#081326; font-size:0.68rem; font-weight:800; padding:0.2rem 0.75rem; border-radius:99px;">⭐ RECOMMANDÉ</div>
          <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:0.5rem;">
            <div>
              <div style="font-size:0.9rem; font-weight:800; color:#92400E;">Publication À la Une</div>
              <div style="font-size:0.78rem; color:#78350F; margin-top:0.15rem;">Position prioritaire + badge doré</div>
            </div>
            <div style="text-align:right;">
              <div style="font-size:1.1rem; font-weight:900; color:#D97706;">12 000 FCFA</div>
              <div style="font-size:0.7rem; color:#92400E;">ou 2 crédits Pro</div>
            </div>
          </div>
          <ul style="list-style:none; display:flex; flex-direction:column; gap:0.35rem;">
            <li style="font-size:0.78rem; color:#78350F; display:flex; align-items:center; gap:0.4rem;"><span>✓</span> Position prioritaire en tête des résultats</li>
            <li style="font-size:0.78rem; color:#78350F; display:flex; align-items:center; gap:0.4rem;"><span>✓</span> Badge "À la Une" doré sur votre offre</li>
            <li style="font-size:0.78rem; color:#78350F; display:flex; align-items:center; gap:0.4rem;"><span>✓</span> Notification push aux candidats correspondants</li>
            <li style="font-size:0.78rem; color:#78350F; display:flex; align-items:center; gap:0.4rem;"><span>✓</span> Mise en avant sur la page d'accueil 3 jours</li>
          </ul>
        </label>
      </div>

      <!-- Summary -->
      <div style="background:#F8FAFC; border:1.5px solid #E2E8F0; border-radius:16px; padding:1.25rem; margin-top:0.5rem;">
        <div style="font-size:0.85rem; font-weight:700; color:#0F172A; margin-bottom:0.75rem;">Récapitulatif de l'offre</div>
        <div style="display:flex; flex-direction:column; gap:0.5rem; font-size:0.82rem;">
          <div style="display:flex; justify-content:space-between;"><span style="color:#64748B;">Poste</span><strong id="sum-titre" style="color:#0F172A;">—</strong></div>
          <div style="display:flex; justify-content:space-between;"><span style="color:#64748B;">Catégorie</span><strong id="sum-cat" style="color:#0F172A;">—</strong></div>
          <div style="display:flex; justify-content:space-between;"><span style="color:#64748B;">Contrat</span><strong id="sum-contrat" style="color:#0F172A;">—</strong></div>
          <div style="display:flex; justify-content:space-between;"><span style="color:#64748B;">Lieu</span><strong id="sum-lieu" style="color:#0F172A;">—</strong></div>
          <div style="display:flex; justify-content:space-between;"><span style="color:#64748B;">Salaire</span><strong id="sum-sal" style="color:#D97706;">—</strong></div>
        </div>
      </div>

      <!-- Payment note -->
      <div style="background:#EFF6FF; border:1px solid #BFDBFE; border-radius:12px; padding:1rem; font-size:0.8rem; color:#1E40AF; display:flex; gap:0.6rem; align-items:flex-start;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2.5" style="flex-shrink:0; margin-top:1px;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
        <span>Paiement par <strong>T-Money</strong> ou <strong>Flooz</strong> au moment de la validation. Un SMS de confirmation vous sera envoyé.</span>
      </div>
    </div>

  </div><!-- /wizard-form-area -->

  <!-- RIGHT: Tips & Preview -->
  <div class="wizard-right">

    <div class="tip-box" id="tip-box">
      <h4>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#92400E" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
        Conseils
      </h4>
      <p id="tip-text">Soyez précis dans l'intitulé du poste pour attirer les bons candidats. Les offres complètes reçoivent <strong>3x plus de candidatures</strong>.</p>
    </div>

    <div class="preview-box">
      <h4>Aperçu de votre offre</h4>
      <div class="preview-row"><span class="lbl">Intitulé</span><span class="val empty" id="prev-titre">—</span></div>
      <div class="preview-row"><span class="lbl">Catégorie</span><span class="val empty" id="prev-cat">—</span></div>
      <div class="preview-row"><span class="lbl">Type de contrat</span><span class="val empty" id="prev-contrat">—</span></div>
      <div class="preview-row"><span class="lbl">Lieu</span><span class="val empty" id="prev-lieu">—</span></div>
      <div class="preview-row"><span class="lbl">Salaire</span><span class="val empty" id="prev-sal">—</span></div>
      <div class="preview-row"><span class="lbl">Mode de travail</span><span class="val" id="prev-mode">Sur site</span></div>
    </div>

    <!-- Completion meter -->
    <div style="background:#FFF; border:1px solid #E2E8F0; border-radius:16px; padding:1.25rem;">
      <div style="display:flex; justify-content:space-between; margin-bottom:0.5rem;">
        <span style="font-size:0.8rem; font-weight:700; color:#0F172A;">Complétion de l'offre</span>
        <span style="font-size:0.8rem; font-weight:700; color:#2563EB;" id="compl-pct">0%</span>
      </div>
      <div style="background:#E2E8F0; border-radius:99px; height:6px; overflow:hidden;">
        <div id="compl-bar" style="height:100%; width:0%; background:linear-gradient(90deg,#2563EB,#60A5FA); border-radius:99px; transition:width 0.4s;"></div>
      </div>
      <p style="font-size:0.75rem; color:#94A3B8; margin-top:0.5rem;">Une offre complète reçoit 3× plus de candidatures.</p>
    </div>

  </div>
</div><!-- /wizard-body -->

<!-- ACTION BAR -->
<div class="wizard-actions">
  <a href="../recruteur/mes-offres.php" class="btn-cancel">Annuler</a>

  <div style="display:flex; align-items:center; gap:0.75rem;">
    <button type="button" class="btn-back" id="btn-back" style="display:none;" onclick="prevStep()">
      ← Précédent
    </button>
    <button type="button" class="btn-next" id="btn-next" onclick="nextStep()">
      Suivant
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
    </button>
    <button type="submit" class="btn-publish" id="btn-publish" style="display:none;">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
      Publier l'offre
    </button>
  </div>
</div>

</form>

<script>
let currentStep = 1;
const TOTAL_STEPS = 4;
const tags = [];

const tips = {
  1: 'Soyez précis dans l\'intitulé du poste pour attirer les bons candidats. Les offres complètes reçoivent <strong>3× plus de candidatures</strong>.',
  2: 'Une description détaillée avec les missions concrètes, l\'environnement et les avantages augmente significativement le nombre de candidatures qualifiées.',
  3: 'N\'exigez que les compétences vraiment indispensables. Des critères trop restrictifs réduisent inutilement votre vivier de candidats.',
  4: 'L\'option "À la Une" génère en moyenne <strong>5× plus de vues</strong> et des candidatures plus rapides. Idéale pour les postes urgents.',
};

function updateStep() {
  for (let i = 1; i <= TOTAL_STEPS; i++) {
    const panel = document.getElementById('step-' + i);
    const si = document.getElementById('si-' + i);
    panel.classList.toggle('active', i === currentStep);
    si.classList.remove('active', 'done');
    if (i === currentStep) si.classList.add('active');
    if (i < currentStep) si.classList.add('done');
    // Step circle content
    si.querySelector('.step-circle').textContent = i < currentStep ? '✓' : i;
    if (i < TOTAL_STEPS) {
      document.getElementById('sl-' + i).classList.toggle('done', i < currentStep);
    }
  }
  document.getElementById('progress-fill').style.width = (currentStep / TOTAL_STEPS * 100) + '%';
  document.getElementById('btn-back').style.display = currentStep > 1 ? '' : 'none';
  document.getElementById('btn-next').style.display = currentStep < TOTAL_STEPS ? '' : 'none';
  document.getElementById('btn-publish').style.display = currentStep === TOTAL_STEPS ? '' : 'none';
  document.getElementById('tip-text').innerHTML = tips[currentStep];
  if (currentStep === TOTAL_STEPS) updateSummary();
}

function nextStep() {
  if (currentStep === 1) {
    const titre = document.getElementById('f-titre').value.trim();
    const cat = document.getElementById('f-categorie').value;
    const contrat = document.getElementById('f-contrat').value;
    const lieu = document.getElementById('f-lieu').value.trim();
    if (!titre || !cat || !contrat || !lieu) {
      alert('Veuillez remplir tous les champs obligatoires (Intitulé, Catégorie, Type de contrat, Lieu).');
      return;
    }
  }
  if (currentStep === 2) {
    const desc = document.getElementById('f-description').value.trim();
    if (!desc) {
      alert('Veuillez rédiger une description du poste.');
      return;
    }
  }
  if (currentStep < TOTAL_STEPS) { currentStep++; updateStep(); syncPreview(); window.scrollTo(0,0); }
}

function prevStep() {
  if (currentStep > 1) { currentStep--; updateStep(); window.scrollTo(0,0); }
}

function setMode(val) {
  document.getElementById('h-mode').value = val;
  document.getElementById('prev-mode').textContent = val;
  document.getElementById('prev-mode').classList.remove('empty');
  updateCompletion();
}

function syncPreview() {
  const titre = document.getElementById('f-titre').value.trim();
  const cat = document.getElementById('f-categorie').value;
  const contrat = document.getElementById('f-contrat').value;
  const lieu = document.getElementById('f-lieu').value.trim();
  const salMin = document.getElementById('f-salmin').value;
  const salMax = document.getElementById('f-salmax').value;

  // Sync hidden
  document.getElementById('h-titre').value = titre;
  document.getElementById('h-categorie').value = cat;
  document.getElementById('h-contrat').value = contrat;
  document.getElementById('h-lieu').value = lieu;
  document.getElementById('h-salmin').value = salMin;
  document.getElementById('h-salmax').value = salMax;
  document.getElementById('h-description').value = document.getElementById('f-description').value;

  // Sync preview
  setPrev('prev-titre', titre);
  setPrev('prev-cat', cat);
  setPrev('prev-contrat', contrat);
  setPrev('prev-lieu', lieu);

  if (salMin || salMax) {
    const t = [salMin ? fmtN(salMin) : '', salMax ? fmtN(salMax) : ''].filter(Boolean).join(' – ') + ' FCFA';
    document.getElementById('prev-sal').textContent = t;
    document.getElementById('prev-sal').classList.remove('empty');
    document.getElementById('prev-sal').style.color = '#D97706';
  } else {
    document.getElementById('prev-sal').textContent = '—';
    document.getElementById('prev-sal').classList.add('empty');
    document.getElementById('prev-sal').style.color = '';
  }
  updateCompletion();
}

function setPrev(id, val) {
  const el = document.getElementById(id);
  if (val) { el.textContent = val; el.classList.remove('empty'); }
  else { el.textContent = '—'; el.classList.add('empty'); }
}

function fmtN(n) { return Number(n).toLocaleString('fr-FR'); }

function updateCompletion() {
  const fields = [
    document.getElementById('f-titre').value.trim(),
    document.getElementById('f-categorie').value,
    document.getElementById('f-contrat').value,
    document.getElementById('f-lieu').value.trim(),
    document.getElementById('h-mode').value !== 'Sur site' ? 'x' : '',
    document.getElementById('f-salmin').value,
    document.getElementById('f-salmax').value,
    document.getElementById('f-description').value.trim(),
    tags.length > 0 ? 'x' : '',
  ];
  const filled = fields.filter(Boolean).length;
  const pct = Math.round((filled / fields.length) * 100);
  document.getElementById('compl-pct').textContent = pct + '%';
  document.getElementById('compl-bar').style.width = pct + '%';
}

function updateSummary() {
  document.getElementById('sum-titre').textContent = document.getElementById('f-titre').value || '—';
  document.getElementById('sum-cat').textContent = document.getElementById('f-categorie').value || '—';
  document.getElementById('sum-contrat').textContent = document.getElementById('f-contrat').value || '—';
  document.getElementById('sum-lieu').textContent = document.getElementById('f-lieu').value || '—';
  const smin = document.getElementById('f-salmin').value;
  const smax = document.getElementById('f-salmax').value;
  document.getElementById('sum-sal').textContent = (smin || smax) ? ([smin ? fmtN(smin) : '', smax ? fmtN(smax) : ''].filter(Boolean).join(' – ') + ' FCFA') : '—';
}

function selectPlan(p) {
  document.getElementById('h-pack').value = p;
  document.getElementById('plan-simple').classList.toggle('selected', p === 'simple');
  document.getElementById('plan-alaune').classList.toggle('selected', p === 'alaune');
}

// Tag input for skills
const tagField = document.getElementById('tag-field');
const tagWrapper = document.getElementById('tag-wrapper');

tagField.addEventListener('keydown', function(e) {
  if ((e.key === 'Enter' || e.key === ',') && this.value.trim()) {
    e.preventDefault();
    addTag(this.value.trim().replace(/,$/, ''));
    this.value = '';
  }
  if (e.key === 'Backspace' && !this.value && tags.length) {
    removeTag(tags.length - 1);
  }
});

function addTag(val) {
  if (!val || tags.includes(val)) return;
  tags.push(val);
  const tag = document.createElement('span');
  tag.className = 'skill-tag';
  tag.innerHTML = val + '<button type="button" onclick="removeTag(' + (tags.length - 1) + ')">×</button>';
  tag.dataset.idx = tags.length - 1;
  tagWrapper.insertBefore(tag, tagField);
  syncTags();
  updateCompletion();
}

function removeTag(idx) {
  tags.splice(idx, 1);
  // Rebuild tags
  tagWrapper.querySelectorAll('.skill-tag').forEach(el => el.remove());
  const copy = [...tags];
  tags.length = 0;
  copy.forEach(t => addTag(t));
}

function syncTags() {
  document.getElementById('h-competences').value = tags.join(', ');
}

updateStep();

// Auto-save form data to prevent loss
const draftKey = 'tgt_job_draft';
const formElements = document.querySelectorAll('input:not([type="hidden"]), textarea, select');

function loadDraft() {
  const saved = localStorage.getItem(draftKey);
  if (saved) {
    const data = JSON.parse(saved);
    formElements.forEach(el => {
      if (data[el.name] !== undefined) {
        if (el.type === 'checkbox' || el.type === 'radio') {
          el.checked = data[el.name];
        } else {
          el.value = data[el.name];
        }
      }
    });
  }
}

function saveDraft() {
  const data = {};
  formElements.forEach(el => {
    if (el.name) {
      if (el.type === 'checkbox' || el.type === 'radio') {
        data[el.name] = el.checked;
      } else {
        data[el.name] = el.value;
      }
    }
  });
  localStorage.setItem(draftKey, JSON.stringify(data));
}

formElements.forEach(el => el.addEventListener('input', saveDraft));
formElements.forEach(el => el.addEventListener('change', saveDraft));

// Clear draft on submit
document.getElementById('wizard-form').addEventListener('submit', () => {
  localStorage.removeItem(draftKey);
});

document.addEventListener('DOMContentLoaded', loadDraft);

</script>
</body>
</html>




