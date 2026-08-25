<?php
$activePage = 'abonnements';
$pageTitle = 'Abonnements - TGTravail';
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

// Count total published jobs by this company
$totalJobs = (int)$db->query("SELECT COUNT(*) FROM jobs WHERE company_id = {$companyId}")->fetchColumn();
$freeQuota = 3;
$freeUsed = min($totalJobs, $freeQuota);
$freeRemaining = max(0, $freeQuota - $totalJobs);
$isOnFree = ($totalJobs < $freeQuota);

// Load active jobs for the boost/feature/push modals
$activeJobs = $db->query("
    SELECT id, titre, boosted_until, featured_until, push_sent_at
    FROM jobs
    WHERE company_id = {$companyId} AND statut = 'active'
    ORDER BY created_at DESC
")->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">

  <!-- Dark Sidebar Left -->
  <?php require __DIR__ . '/../includes/recruiter_sidebar.php'; ?>

  <!-- Main Content -->
  <main class="dashboard-content-main" style="overflow-y:auto;">

    <!-- Topbar -->
    <div class="dashboard-topbar">
      <div class="dashboard-header-left" style="display:flex; align-items:center; gap:1rem;">
        <button class="dashboard-mobile-btn" onclick="document.querySelector('.dashboard-sidebar-dark').classList.toggle('open'); event.stopPropagation();">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>
        <div>
        <h1 class="user-greeting">Abonnements & Crédits</h1>
        <p style="font-size:0.9rem; color:var(--text-muted);">Choisissez la formule adaptée à vos besoins de recrutement</p>
      </div>
      </div>
      <div style="display:flex; align-items:center; gap:1.25rem;">
        <div class="company-selector-dropdown">
          <span style="color:#2563EB;">●</span>
          <span><?= htmlspecialchars($companyName) ?></span>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
        </div>
        <a href="../pages/notifications.php" style="position:relative; color:var(--text-muted); display:flex;" title="Notifications">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
          <span style="position:absolute; top:2px; right:2px; width:8px; height:8px; background:#3B82F6; border-radius:50%; border:2px solid #F8FAFC;"></span>
        </a>
        <a href="../recruteur/parametres.php" style="display:flex;">
          <?php if (!empty($companyLogo) && file_exists(__DIR__ . '/' . $companyLogo)): ?>
            <img src="<?= htmlspecialchars($companyLogo) ?>" alt="Logo" style="width:42px; height:42px; border-radius:50%; object-fit:cover; border:2px solid #E2E8F0;">
          <?php else: ?>
            <div style="width:42px; height:42px; border-radius:50%; background:#081326; color:#FFB800; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.9rem; border:2px solid #E2E8F0;">
              <?= strtoupper(substr($user['nom'], 0, 2)) ?>
            </div>
          <?php endif; ?>
        </a>
      </div>
    </div>

    <!-- ===== BETA TESTING BANNER ===== -->
    <div style="margin-bottom:1.5rem; padding:1rem 1.5rem; background:linear-gradient(135deg,#FFF7ED,#FFFBEB); border:1.5px solid #FCD34D; border-radius:16px; display:flex; align-items:center; gap:1rem; flex-wrap:wrap;">
      <div style="width:36px; height:36px; border-radius:50%; background:#D97706; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#FFF" stroke-width="2.5"><path d="M9 3H5a2 2 0 0 0-2 2v4m6-6h10a2 2 0 0 1 2 2v4M9 3v18m0 0h10a2 2 0 0 0 2-2v-4M9 21H5a2 2 0 0 1-2-2v-4m0 0h18"/></svg>
      </div>
      <div style="flex:1; min-width:200px;">
        <p style="font-weight:800; font-size:0.95rem; color:#92400E; margin:0;">Phase de test — Tous les abonnements sont ouverts</p>
        <p style="font-size:0.82rem; color:#B45309; margin:0.2rem 0 0;">Pendant cette phase, les limites de chaque plan ne sont pas encore appliquees. Vous avez acces a toutes les fonctionnalites gratuitement. Les restrictions seront activees lors du lancement officiel.</p>
      </div>
      <span style="background:#D97706; color:#FFF; font-size:0.72rem; font-weight:800; padding:0.3rem 0.85rem; border-radius:99px; white-space:nowrap; flex-shrink:0;">BETA</span>
    </div>

    <!-- ===== FREE STATUS BANNER ===== -->
    <div style="margin-bottom:2rem; padding:1.25rem 1.75rem; border-radius:20px; background:<?= $isOnFree ? 'linear-gradient(135deg,#ECFDF5,#D1FAE5)' : 'linear-gradient(135deg,#FFF7ED,#FFEDD5)' ?>; border:1.5px solid <?= $isOnFree ? '#6EE7B7' : '#FED7AA' ?>; display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap;">
      <div style="display:flex; align-items:center; gap:1rem;">
        <div style="width:46px; height:46px; border-radius:50%; background:<?= $isOnFree ? '#059669' : '#EA580C' ?>; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#FFF" stroke-width="2.5">
            <?php if ($isOnFree): ?>
              <polyline points="20 6 9 17 4 12"></polyline>
            <?php else: ?>
              <circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line>
            <?php endif; ?>
          </svg>
        </div>
        <div>
          <p style="font-weight:800; font-size:1rem; color:<?= $isOnFree ? '#065F46' : '#9A3412' ?>; margin:0;">
            <?= $isOnFree ? "🎉 Vous bénéficiez encore de {$freeRemaining} offre(s) gratuite(s)" : "Vos 3 offres gratuites ont été utilisées" ?>
          </p>
          <p style="font-size:0.85rem; color:<?= $isOnFree ? '#047857' : '#C2410C' ?>; margin:0.2rem 0 0;">
            <?= $isOnFree
              ? "{$freeUsed}/{$freeQuota} offres publiées · Aucun paiement requis pour le moment"
              : "Passez à un plan payant ou achetez des crédits à l'unité pour continuer à recruter." ?>
          </p>
        </div>
      </div>
      <!-- Progress Bar -->
      <div style="flex:1; min-width:200px;">
        <div style="display:flex; justify-content:space-between; font-size:0.78rem; color:#64748B; margin-bottom:0.35rem;">
          <span>Offres gratuites utilisées</span><span><?= $freeUsed ?>/<?= $freeQuota ?></span>
        </div>
        <div style="background:#E2E8F0; border-radius:99px; height:8px; overflow:hidden;">
          <div style="height:100%; width:<?= round(($freeUsed/$freeQuota)*100) ?>%; background:<?= $isOnFree ? 'linear-gradient(90deg,#10B981,#059669)' : 'linear-gradient(90deg,#F97316,#EA580C)' ?>; border-radius:99px; transition:width 0.8s ease;"></div>
        </div>
      </div>
    </div>

    <!-- ===== SMART UPSELL NOTICE (shows when >2 jobs/month) ===== -->
    <?php if ($totalJobs >= 2): ?>
    <div style="margin-bottom:2rem; padding:1rem 1.5rem; background:linear-gradient(135deg,#EFF6FF,#DBEAFE); border:1.5px solid #93C5FD; border-radius:16px; display:flex; align-items:center; gap:1rem;">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2.5"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
      <p style="font-size:0.9rem; color:#1E40AF; font-weight:600; margin:0;">
        💡 Avec <?= $totalJobs ?> offres publiées, l'abonnement <strong>Pro (15 000 FCFA/mois)</strong> devient plus rentable que le paiement à l'unité (<?= $totalJobs * 5000 ?> FCFA). Économisez <?= number_format($totalJobs * 5000 - 15000, 0, ',', ' ') ?> FCFA ce mois-ci en passant au Pro !
      </p>
    </div>
    <?php endif; ?>

    <!-- ===== PLANS GRID ===== -->
    <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:1.5rem; margin-bottom:2.5rem;">

      <!-- FREE -->
      <div style="background:#FFF; border:1.5px solid #E2E8F0; border-radius:24px; padding:2rem; display:flex; flex-direction:column; gap:1.25rem; position:relative;">
        <div>
          <span style="font-size:0.75rem; font-weight:700; color:#64748B; text-transform:uppercase; letter-spacing:0.08em;">Démarrage</span>
          <h2 style="font-size:1.6rem; font-weight:800; color:#081326; margin:0.4rem 0 0.2rem;">Gratuit</h2>
          <p style="font-size:0.875rem; color:#64748B; margin:0;">Pour découvrir la plateforme</p>
        </div>
        <div style="display:flex; align-items:baseline; gap:0.35rem;">
          <span style="font-size:2.5rem; font-weight:900; color:#081326;">0</span>
          <span style="font-size:1rem; color:#64748B; font-weight:600;">FCFA</span>
        </div>
        <ul style="list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:0.75rem;">
          <?php $freeFeatures = [
            ['ok' => true,  'label' => '3 offres d\'emploi gratuites'],
            ['ok' => true,  'label' => 'Messagerie integree recruteur-candidat'],
            ['ok' => true,  'label' => 'Gestion des candidatures'],
            ['ok' => true,  'label' => 'Planification d\'entretiens (basique)'],
            ['ok' => true,  'label' => 'Notifications en temps reel'],
            ['ok' => false, 'label' => 'CVtheque avancee + filtres'],
            ['ok' => false, 'label' => 'Boost d\'offres'],
            ['ok' => false, 'label' => 'Credits reportables'],
          ]; foreach ($freeFeatures as $f): ?>
          <li style="display:flex; align-items:center; gap:0.6rem; font-size:0.875rem; color:<?= $f['ok'] ? '#334155' : '#94A3B8' ?>;">
            <?php if ($f['ok']): ?>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            <?php else: ?>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            <?php endif; ?>
            <?= $f['label'] ?>
          </li>
          <?php endforeach; ?>
        </ul>
        <button style="width:100%; padding:0.75rem; border-radius:14px; border:1.5px solid #E2E8F0; background:#F8FAFC; color:#64748B; font-weight:700; font-size:0.9rem; cursor:pointer;" disabled>
          <?= $isOnFree ? '✓ Plan actuel' : 'Plan épuisé' ?>
        </button>
      </div>

      <!-- PAY PER POST -->
      <div style="background:#FFF; border:1.5px solid #E2E8F0; border-radius:24px; padding:2rem; display:flex; flex-direction:column; gap:1.25rem;">
        <div>
          <span style="font-size:0.75rem; font-weight:700; color:#64748B; text-transform:uppercase; letter-spacing:0.08em;">Flexible</span>
          <h2 style="font-size:1.6rem; font-weight:800; color:#081326; margin:0.4rem 0 0.2rem;">À l'unité</h2>
          <p style="font-size:0.875rem; color:#64748B; margin:0;">Recrutez à votre rythme</p>
        </div>
        <div style="display:flex; align-items:baseline; gap:0.35rem;">
          <span style="font-size:2.5rem; font-weight:900; color:#081326;">5 000</span>
          <span style="font-size:1rem; color:#64748B; font-weight:600;">FCFA / offre</span>
        </div>

        <!-- Smart comparison -->
        <div style="padding:0.75rem 1rem; background:#FFF7ED; border:1px solid #FED7AA; border-radius:12px; font-size:0.8rem; color:#C2410C;">
          ⚡ <strong>Astuce :</strong> Au-delà de 3 offres/mois, l'abonnement Pro est plus rentable !
        </div>

        <ul style="list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:0.75rem;">
          <?php $unitFeatures = [
            'Offre visible 60 jours',
            'Candidatures illimitees',
            'Messagerie recruteur-candidat',
            'Planification d\'entretiens avancee',
            'Acces complet a la CVtheque',
            'Gestion des candidatures (pipeline)',
            'Credits achetes non expires',
            'Notifications en temps reel',
          ]; foreach ($unitFeatures as $f): ?>
          <li style="display:flex; align-items:center; gap:0.6rem; font-size:0.875rem; color:#334155;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            <?= $f ?>
          </li>
          <?php endforeach; ?>
          <li style="display:flex; align-items:center; gap:0.6rem; font-size:0.875rem; color:#94A3B8;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            Boost d'offres (payant en option)
          </li>
        </ul>

        <!-- Credit packs -->
        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:0.5rem; margin-top:0.25rem;">
          <button onclick="startFedaPayRecruteur('buy_pack', 5000, 'Pack 1 Offre d\'emploi', 1)" style="padding:0.6rem 0.4rem; border-radius:12px; border:1.5px solid #E2E8F0; background:#F8FAFC; font-size:0.78rem; font-weight:700; cursor:pointer; color:#081326; transition:all 0.2s;" onmouseover="this.style.borderColor='#2563EB';this.style.background='#EFF6FF'" onmouseout="this.style.borderColor='#E2E8F0';this.style.background='#F8FAFC'">
            1 offre<br><span style="color:#2563EB;">5 000</span>
          </button>
          <button onclick="startFedaPayRecruteur('buy_pack', 22000, 'Pack 5 Offres d\'emploi', 5)" style="padding:0.6rem 0.4rem; border-radius:12px; border:1.5px solid #2563EB; background:#EFF6FF; font-size:0.78rem; font-weight:700; cursor:pointer; color:#1E40AF; transition:all 0.2s; position:relative;" onmouseover="this.style.background='#DBEAFE'" onmouseout="this.style.background='#EFF6FF'">
            <span style="position:absolute; top:-8px; left:50%; transform:translateX(-50%); background:#2563EB; color:#FFF; font-size:0.62rem; padding:1px 6px; border-radius:99px; white-space:nowrap;">POPULAIRE</span>
            5 offres<br><span style="color:#2563EB;">22 000</span>
          </button>
          <button onclick="startFedaPayRecruteur('buy_pack', 40000, 'Pack 10 Offres d\'emploi', 10)" style="padding:0.6rem 0.4rem; border-radius:12px; border:1.5px solid #E2E8F0; background:#F8FAFC; font-size:0.78rem; font-weight:700; cursor:pointer; color:#081326; transition:all 0.2s;" onmouseover="this.style.borderColor='#2563EB';this.style.background='#EFF6FF'" onmouseout="this.style.borderColor='#E2E8F0';this.style.background='#F8FAFC'">
            10 offres<br><span style="color:#2563EB;">40 000</span>
          </button>
        </div>
        <button onclick="startFedaPayRecruteur('buy_pack', 5000, 'Pack 1 Offre d\'emploi', 1)" style="width:100%; padding:0.75rem; border-radius:14px; border:none; background:#081326; color:#FFB800; font-weight:700; font-size:0.9rem; cursor:pointer; transition:background 0.2s;" onmouseover="this.style.background='#0F2040'" onmouseout="this.style.background='#081326'">
          Acheter des crédits
        </button>
      </div>

      <!-- PRO (highlighted) -->
      <div style="background:linear-gradient(160deg,#081326 0%,#1E3A5F 100%); border:2px solid #FFB800; border-radius:24px; padding:2rem; display:flex; flex-direction:column; gap:1.25rem; position:relative; overflow:hidden;">
        <!-- Glow -->
        <div style="position:absolute; top:-40px; right:-40px; width:120px; height:120px; background:#FFB800; opacity:0.08; border-radius:50%;"></div>

        <div style="display:flex; justify-content:space-between; align-items:flex-start;">
          <div>
            <span style="font-size:0.75rem; font-weight:700; color:#FFB800; text-transform:uppercase; letter-spacing:0.08em;">Recommandé</span>
            <h2 style="font-size:1.6rem; font-weight:800; color:#FFF; margin:0.4rem 0 0.2rem;">Pro</h2>
            <p style="font-size:0.875rem; color:#94A3B8; margin:0;">Recrutement sans limite</p>
          </div>
          <span style="background:#FFB800; color:#081326; font-size:0.72rem; font-weight:800; padding:0.3rem 0.8rem; border-radius:99px; white-space:nowrap;">⭐ LE PLUS POPULAIRE</span>
        </div>

        <div style="display:flex; align-items:baseline; gap:0.35rem;">
          <span style="font-size:2.5rem; font-weight:900; color:#FFF;">15 000</span>
          <span style="font-size:1rem; color:#94A3B8; font-weight:600;">FCFA / mois</span>
        </div>

        <!-- Vs unit comparison -->
        <div style="padding:0.75rem 1rem; background:rgba(255,184,0,0.12); border:1px solid rgba(255,184,0,0.3); border-radius:12px; font-size:0.8rem; color:#FCD34D;">
          💡 Économisez par rapport à l'unité dès <strong>4 offres/mois</strong> (4 × 5 000 = 20 000 FCFA)
        </div>

        <ul style="list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:0.75rem;">
          <?php $proFeatures = [
            'Offres d\'emploi illimitees',
            'Credits mensuels reportables (5 credits/mois)',
            'CVtheque complete + filtres avances',
            'Messagerie prioritaire recruteur-candidat',
            'Planification d\'entretiens illimitee',
            'Pipeline de candidatures avance (Kanban)',
            'Diffusion de notifications ciblees',
            'Badge "Entreprise Verifiee"',
            '2 boosts offerts chaque mois',
            'Signalement de contenu protege',
            'Support prioritaire WhatsApp',
          ]; foreach ($proFeatures as $f): ?>
          <li style="display:flex; align-items:center; gap:0.6rem; font-size:0.875rem; color:#E2E8F0;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#FFB800" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            <?= $f ?>
          </li>
          <?php endforeach; ?>
        </ul>

        <button onclick="startFedaPayRecruteur('subscribe_recruteur_pro', 15000, 'Abonnement Recruteur Pro - 1 Mois')" style="width:100%; padding:0.85rem; border-radius:14px; border:none; background:#FFB800; color:#081326; font-weight:800; font-size:0.95rem; cursor:pointer; transition:opacity 0.2s;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
          Passer au Pro →
        </button>
        <p style="font-size:0.75rem; color:#64748B; text-align:center; margin:0;">Résiliation possible à tout moment</p>
      </div>

    </div>

    <!-- ===== ADD-ONS SECTION ===== -->
    <div style="margin-bottom:2.5rem;">
      <div style="margin-bottom:1.25rem;">
        <h2 style="font-size:1.2rem; font-weight:800; color:#081326; margin:0 0 0.35rem; display:flex; align-items:center; gap:0.5rem;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2.5"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
          Options supplementaires (Add-ons)
        </h2>
        <p style="font-size:0.875rem; color:#64748B; margin:0;">Disponibles a l'unite pour tous les plans, y compris les abonnes Pro.</p>
      </div>

      <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:1.25rem;">

        <!-- Boost -->
        <div style="background:#FFF; border:1.5px solid #E2E8F0; border-radius:20px; padding:1.5rem; display:flex; flex-direction:column; gap:1rem; transition:box-shadow 0.2s;" onmouseover="this.style.boxShadow='0 8px 24px rgba(37,99,235,0.1)';this.style.borderColor='#93C5FD'" onmouseout="this.style.boxShadow='none';this.style.borderColor='#E2E8F0'">
          <div style="width:48px; height:48px; border-radius:14px; background:#EFF6FF; display:flex; align-items:center; justify-content:center;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2.5"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
          </div>
          <div>
            <h3 style="font-size:1rem; font-weight:800; color:#081326; margin:0 0 0.25rem;">Boost d'offre</h3>
            <p style="font-size:0.82rem; color:#64748B; margin:0; line-height:1.5;">Remettez votre offre en tete des resultats de recherche pendant 7 jours.</p>
          </div>
          <div style="display:flex; align-items:center; justify-content:space-between; margin-top:auto;">
            <span style="font-size:1.1rem; font-weight:800; color:#2563EB;">2 500 FCFA</span>
            <button onclick="openModal('modal-boost')" style="padding:0.5rem 1rem; border-radius:10px; border:none; background:#2563EB; color:#FFF; font-size:0.8rem; font-weight:700; cursor:pointer; transition:background 0.2s;" onmouseover="this.style.background='#1D4ED8'" onmouseout="this.style.background='#2563EB'">Booster</button>
          </div>
        </div>

        <!-- Entretien video -->
        <div style="background:#FFF; border:1.5px solid #E2E8F0; border-radius:20px; padding:1.5rem; display:flex; flex-direction:column; gap:1rem; transition:box-shadow 0.2s;" onmouseover="this.style.boxShadow='0 8px 24px rgba(147,51,234,0.1)';this.style.borderColor='#D8B4FE'" onmouseout="this.style.boxShadow='none';this.style.borderColor='#E2E8F0'">
          <div style="width:48px; height:48px; border-radius:14px; background:#FAF5FF; display:flex; align-items:center; justify-content:center;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#9333EA" stroke-width="2.5"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M8 2v4M16 2v4M3 10h18"/></svg>
          </div>
          <div>
            <h3 style="font-size:1rem; font-weight:800; color:#081326; margin:0 0 0.25rem;">Planification d'entretien</h3>
            <p style="font-size:0.82rem; color:#64748B; margin:0; line-height:1.5;">Proposez des creneaux, recevez confirmation et envoyez un rappel automatique au candidat.</p>
          </div>
          <div style="display:flex; align-items:center; justify-content:space-between; margin-top:auto;">
            <span style="font-size:1.1rem; font-weight:800; color:#9333EA;">Inclus</span>
            <a href="interviews.php" style="padding:0.5rem 1rem; border-radius:10px; border:none; background:#9333EA; color:#FFF; font-size:0.8rem; font-weight:700; cursor:pointer; text-decoration:none; transition:background 0.2s;" onmouseover="this.style.background='#7E22CE'" onmouseout="this.style.background='#9333EA'">Planifier</a>
          </div>
        </div>

        <!-- Push candidats -->
        <div style="background:#FFF; border:1.5px solid #E2E8F0; border-radius:20px; padding:1.5rem; display:flex; flex-direction:column; gap:1rem; transition:box-shadow 0.2s;" onmouseover="this.style.boxShadow='0 8px 24px rgba(5,150,105,0.1)';this.style.borderColor='#6EE7B7'" onmouseout="this.style.boxShadow='none';this.style.borderColor='#E2E8F0'">
          <div style="width:48px; height:48px; border-radius:14px; background:#ECFDF5; display:flex; align-items:center; justify-content:center;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13.5 19.79 19.79 0 0 1 1.61 4.9 2 2 0 0 1 3.6 2.69h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 10a16 16 0 0 0 6.09 6.09l.91-.92a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 21.73 17z"/></svg>
          </div>
          <div>
            <h3 style="font-size:1rem; font-weight:800; color:#081326; margin:0 0 0.25rem;">Notification Push candidats</h3>
            <p style="font-size:0.82rem; color:#64748B; margin:0; line-height:1.5;">Envoi automatique a tous les candidats correspondant au profil recherche. Reponse rapide garantie.</p>
          </div>
          <div style="display:flex; align-items:center; justify-content:space-between; margin-top:auto;">
            <span style="font-size:1.1rem; font-weight:800; color:#059669;">3 500 FCFA</span>
            <button onclick="openModal('modal-push')" style="padding:0.5rem 1rem; border-radius:10px; border:none; background:#059669; color:#FFF; font-size:0.8rem; font-weight:700; cursor:pointer; transition:background 0.2s;" onmouseover="this.style.background='#047857'" onmouseout="this.style.background='#059669'">Envoyer</button>
          </div>
        </div>

        <!-- Mise en avant -->
        <div style="background:#FFF; border:1.5px solid #E2E8F0; border-radius:20px; padding:1.5rem; display:flex; flex-direction:column; gap:1rem; transition:box-shadow 0.2s;" onmouseover="this.style.boxShadow='0 8px 24px rgba(217,119,6,0.1)';this.style.borderColor='#FCD34D'" onmouseout="this.style.boxShadow='none';this.style.borderColor='#E2E8F0'">
          <div style="width:48px; height:48px; border-radius:14px; background:#FFFBEB; display:flex; align-items:center; justify-content:center;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          </div>
          <div>
            <h3 style="font-size:1rem; font-weight:800; color:#081326; margin:0 0 0.25rem;">Mise en avant (Home)</h3>
            <p style="font-size:0.82rem; color:#64748B; margin:0; line-height:1.5;">Votre offre affichee en banniere sur la page d'accueil pendant 3 jours. Exposition maximale.</p>
          </div>
          <div style="display:flex; align-items:center; justify-content:space-between; margin-top:auto;">
            <span style="font-size:1.1rem; font-weight:800; color:#D97706;">5 000 FCFA</span>
            <button onclick="openModal('modal-feature')" style="padding:0.5rem 1rem; border-radius:10px; border:none; background:#D97706; color:#FFF; font-size:0.8rem; font-weight:700; cursor:pointer; transition:background 0.2s;" onmouseover="this.style.background='#B45309'" onmouseout="this.style.background='#D97706'">Activer</button>
          </div>
        </div>

        <!-- Messagerie prioritaire -->
        <div style="background:#FFF; border:1.5px solid #E2E8F0; border-radius:20px; padding:1.5rem; display:flex; flex-direction:column; gap:1rem; transition:box-shadow 0.2s;" onmouseover="this.style.boxShadow='0 8px 24px rgba(37,99,235,0.1)';this.style.borderColor='#93C5FD'" onmouseout="this.style.boxShadow='none';this.style.borderColor='#E2E8F0'">
          <div style="width:48px; height:48px; border-radius:14px; background:#EFF6FF; display:flex; align-items:center; justify-content:center;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          </div>
          <div>
            <h3 style="font-size:1rem; font-weight:800; color:#081326; margin:0 0 0.25rem;">Messagerie integree</h3>
            <p style="font-size:0.82rem; color:#64748B; margin:0; line-height:1.5;">Echangez directement avec les candidats depuis votre tableau de bord. Inclus dans tous les plans.</p>
          </div>
          <div style="display:flex; align-items:center; justify-content:space-between; margin-top:auto;">
            <span style="font-size:1.1rem; font-weight:800; color:#2563EB;">Inclus</span>
            <a href="../recruteur/messages.php" style="padding:0.5rem 1rem; border-radius:10px; border:none; background:#2563EB; color:#FFF; font-size:0.8rem; font-weight:700; cursor:pointer; text-decoration:none;" onmouseover="this.style.background='#1D4ED8'" onmouseout="this.style.background='#2563EB'">Ouvrir</a>
          </div>
        </div>

        <!-- Verification dossier -->
        <div style="background:#FFF; border:1.5px solid #E2E8F0; border-radius:20px; padding:1.5rem; display:flex; flex-direction:column; gap:1rem; transition:box-shadow 0.2s;" onmouseover="this.style.boxShadow='0 8px 24px rgba(5,150,105,0.1)';this.style.borderColor='#6EE7B7'" onmouseout="this.style.boxShadow='none';this.style.borderColor='#E2E8F0'">
          <div style="width:48px; height:48px; border-radius:14px; background:#ECFDF5; display:flex; align-items:center; justify-content:center;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.5"><path d="M9 12l2 2 4-4"/><path d="M21 12c-1 5.33-4.5 9-9 9s-8-3.67-9-9 4.5-9 9-9 8 3.67 9 9z"/></svg>
          </div>
          <div>
            <h3 style="font-size:1rem; font-weight:800; color:#081326; margin:0 0 0.25rem;">Badge Entreprise Verifiee</h3>
            <p style="font-size:0.82rem; color:#64748B; margin:0; line-height:1.5;">Obtenez le badge de verification apres validation de votre dossier par l'equipe TGTravail.</p>
          </div>
          <div style="display:flex; align-items:center; justify-content:space-between; margin-top:auto;">
            <span style="font-size:1.1rem; font-weight:800; color:#059669;">Pro inclus</span>
            <span style="padding:0.5rem 1rem; border-radius:10px; background:#D1FAE5; color:#065F46; font-size:0.8rem; font-weight:700;">Pro</span>
          </div>
        </div>

      </div>
    </div>

    <!-- ===== REPORTABLE CREDITS INFO ===== -->
    <div style="background:linear-gradient(135deg,#F8FAFC,#EFF6FF); border:1.5px solid #DBEAFE; border-radius:20px; padding:1.75rem 2rem; display:flex; align-items:center; gap:2rem; flex-wrap:wrap; margin-bottom:2rem;">
      <div style="width:56px; height:56px; border-radius:50%; background:#2563EB; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#FFF" stroke-width="2.5"><rect x="2" y="3" width="20" height="14" rx="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
      </div>
      <div style="flex:1; min-width:250px;">
        <h3 style="font-size:1rem; font-weight:800; color:#081326; margin:0 0 0.35rem;">💾 Crédits reportables — Zéro frustration</h3>
        <p style="font-size:0.875rem; color:#334155; margin:0; line-height:1.6;">
          Avec l'abonnement Pro, vos <strong>5 crédits mensuels</strong> n'expirent jamais ! Si vous en utilisez 2 ce mois-ci, les 3 restants s'accumulent et s'ajoutent aux crédits du mois prochain. Pas de gaspillage, pas de pression.
        </p>
      </div>
      <div style="text-align:center; flex-shrink:0;">
        <div style="font-size:2rem; font-weight:900; color:#2563EB;">∞</div>
        <div style="font-size:0.75rem; color:#64748B; font-weight:600;">Sans expiration</div>
      </div>
    </div>

    <!-- Payment methods -->
    <div style="text-align:center; padding:1rem; color:#94A3B8; font-size:0.82rem;">
      <p style="margin:0;">💳 Paiements acceptés via <strong>T-Money</strong> · <strong>Flooz</strong> · Virement bancaire · <strong>Wave</strong></p>
      <p style="margin:0.25rem 0 0;">Toutes les transactions sont sécurisées. Support disponible sur WhatsApp : +228 90 00 00 00</p>
    </div>

  </main>
</div>

<!-- ===== MODALS BOOST / MISE EN AVANT / PUSH ===== -->
<?php
// Build jobs options HTML for reuse
$jobsOptionsHtml = '';
if (empty($activeJobs)) {
    $jobsOptionsHtml = '<option value="">Aucune offre active</option>';
} else {
    foreach ($activeJobs as $j) {
        $jobsOptionsHtml .= '<option value="' . $j['id'] . '">' . htmlspecialchars($j['titre']) . '</option>';
    }
}
?>

<!-- Modal Boost -->
<div id="modal-boost" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.65); backdrop-filter:blur(4px); z-index:9999; align-items:center; justify-content:center;">
  <div style="background:#FFF; border-radius:20px; padding:2rem; width:100%; max-width:460px; box-shadow:0 20px 60px rgba(0,0,0,0.15); margin:1rem;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
      <div>
        <h3 style="font-size:1.1rem; font-weight:800; color:#081326; margin:0;">Booster une offre</h3>
        <p style="font-size:0.82rem; color:#64748B; margin:0.25rem 0 0;">Offre remise en tete des resultats pendant 7 jours</p>
      </div>
      <button onclick="closeModal('modal-boost')" style="background:none; border:none; cursor:pointer; color:#94A3B8; font-size:1.5rem; line-height:1;">&times;</button>
    </div>
    <div style="background:#EFF6FF; border-radius:12px; padding:0.75rem 1rem; margin-bottom:1.25rem; font-size:0.82rem; color:#1E40AF; display:flex; align-items:center; gap:0.5rem;">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      Phase beta — Activation gratuite (valeur : 2 500 FCFA)
    </div>
    <label style="display:block; font-weight:600; font-size:0.9rem; color:#334155; margin-bottom:0.5rem;">Selectionner l'offre a booster</label>
    <select id="boost-job-select" style="width:100%; padding:0.75rem; border:1.5px solid #E2E8F0; border-radius:10px; font-family:inherit; font-size:0.9rem; color:#081326; margin-bottom:1.25rem;">
      <?= $jobsOptionsHtml ?>
    </select>
    <div id="boost-result" style="display:none; margin-bottom:1rem; padding:0.75rem 1rem; border-radius:10px; font-size:0.875rem; font-weight:600;"></div>
    <button onclick="submitBoost()" style="width:100%; padding:0.8rem; border-radius:12px; border:none; background:linear-gradient(135deg,#2563EB,#1D4ED8); color:#FFF; font-weight:700; font-size:0.95rem; cursor:pointer;">
      Activer le Boost
    </button>
  </div>
</div>

<!-- Modal Mise en avant -->
<div id="modal-feature" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.65); backdrop-filter:blur(4px); z-index:9999; align-items:center; justify-content:center;">
  <div style="background:#FFF; border-radius:20px; padding:2rem; width:100%; max-width:460px; box-shadow:0 20px 60px rgba(0,0,0,0.15); margin:1rem;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
      <div>
        <h3 style="font-size:1.1rem; font-weight:800; color:#081326; margin:0;">Mettre une offre en avant</h3>
        <p style="font-size:0.82rem; color:#64748B; margin:0.25rem 0 0;">Votre offre en banniere sur la page d'accueil pendant 3 jours</p>
      </div>
      <button onclick="closeModal('modal-feature')" style="background:none; border:none; cursor:pointer; color:#94A3B8; font-size:1.5rem; line-height:1;">&times;</button>
    </div>
    <div style="background:#FFFBEB; border-radius:12px; padding:0.75rem 1rem; margin-bottom:1.25rem; font-size:0.82rem; color:#92400E; display:flex; align-items:center; gap:0.5rem;">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      Phase beta — Activation gratuite (valeur : 5 000 FCFA)
    </div>
    <label style="display:block; font-weight:600; font-size:0.9rem; color:#334155; margin-bottom:0.5rem;">Selectionner l'offre a mettre en avant</label>
    <select id="feature-job-select" style="width:100%; padding:0.75rem; border:1.5px solid #E2E8F0; border-radius:10px; font-family:inherit; font-size:0.9rem; color:#081326; margin-bottom:1.25rem;">
      <?= $jobsOptionsHtml ?>
    </select>
    <div id="feature-result" style="display:none; margin-bottom:1rem; padding:0.75rem 1rem; border-radius:10px; font-size:0.875rem; font-weight:600;"></div>
    <button onclick="submitFeature()" style="width:100%; padding:0.8rem; border-radius:12px; border:none; background:linear-gradient(135deg,#D97706,#B45309); color:#FFF; font-weight:700; font-size:0.95rem; cursor:pointer;">
      Activer la Mise en avant
    </button>
  </div>
</div>

<!-- Modal Push Candidats -->
<div id="modal-push" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.65); backdrop-filter:blur(4px); z-index:9999; align-items:center; justify-content:center;">
  <div style="background:#FFF; border-radius:20px; padding:2rem; width:100%; max-width:460px; box-shadow:0 20px 60px rgba(0,0,0,0.15); margin:1rem;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
      <div>
        <h3 style="font-size:1.1rem; font-weight:800; color:#081326; margin:0;">Notification Push candidats</h3>
        <p style="font-size:0.82rem; color:#64748B; margin:0.25rem 0 0;">Envoie une notification a tous les candidats correspondants</p>
      </div>
      <button onclick="closeModal('modal-push')" style="background:none; border:none; cursor:pointer; color:#94A3B8; font-size:1.5rem; line-height:1;">&times;</button>
    </div>
    <div style="background:#ECFDF5; border-radius:12px; padding:0.75rem 1rem; margin-bottom:1.25rem; font-size:0.82rem; color:#065F46; display:flex; align-items:center; gap:0.5rem;">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      Phase beta — Activation gratuite (valeur : 3 500 FCFA) · Delai de 24h entre chaque envoi
    </div>
    <label style="display:block; font-weight:600; font-size:0.9rem; color:#334155; margin-bottom:0.5rem;">Selectionner l'offre pour le push</label>
    <select id="push-job-select" style="width:100%; padding:0.75rem; border:1.5px solid #E2E8F0; border-radius:10px; font-family:inherit; font-size:0.9rem; color:#081326; margin-bottom:1.25rem;">
      <?= $jobsOptionsHtml ?>
    </select>
    <div id="push-result" style="display:none; margin-bottom:1rem; padding:0.75rem 1rem; border-radius:10px; font-size:0.875rem; font-weight:600;"></div>
    <button onclick="submitPush()" style="width:100%; padding:0.8rem; border-radius:12px; border:none; background:linear-gradient(135deg,#059669,#047857); color:#FFF; font-weight:700; font-size:0.95rem; cursor:pointer;">
      Envoyer la notification Push
    </button>
  </div>
</div>

<script src="https://cdn.fedapay.com/checkout.js?v=1.1.7"></script>
<script>
function startFedaPayRecruteur(type, amount, description, jobs = 0) {
    let widget = FedaPay.init({
        public_key: 'pk_live_aAUfRsADSFFOgUQFEWoH9sG0',
        transaction: {
            amount: amount,
            description: description,
        },
        customer: {
            email: '<?= addslashes($user['email'] ?? '') ?>',
            lastname: '<?= addslashes($user['nom'] ?? '') ?>'
        },
        onComplete: function(resp) {
            const reason = resp.reason || resp.status;
            if (reason === 'CHECKOUT COMPLETE' || reason === 'approved') {
                const txId = resp.transaction ? resp.transaction.id : 'unknown';
                fetch('../api/api-fedapay-success.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        transaction_id: txId,
                        action: type,
                        jobs: jobs
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.ok) {
                        alert(data.msg);
                        window.location.reload();
                    } else {
                        alert("Erreur: " + data.msg);
                    }
                });
            }
        }
    });
    
    widget.open();
}

function openModal(id) {
    const el = document.getElementById(id);
    el.style.display = 'flex';
    // Reset result
    const res = document.getElementById(id.replace('modal-', '') + '-result');
    if (res) { res.style.display = 'none'; res.textContent = ''; }
}
function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}
// Close on backdrop click
['modal-boost','modal-feature','modal-push'].forEach(id => {
    document.getElementById(id).addEventListener('click', function(e) {
        if (e.target === this) closeModal(id);
    });
});

function postApi(url, data, resultEl, successBg, errorBg) {
    const form = new FormData();
    for (const k in data) form.append(k, data[k]);
    resultEl.style.display = 'block';
    resultEl.style.background = '#F1F5F9';
    resultEl.style.color = '#475569';
    resultEl.textContent = 'Traitement en cours...';
    fetch(url, { method: 'POST', body: form })
        .then(r => r.json())
        .then(res => {
            resultEl.style.background = res.ok ? successBg : errorBg;
            resultEl.style.color = res.ok ? '#065F46' : '#991B1B';
            resultEl.textContent = res.msg;
        })
        .catch(() => {
            resultEl.style.background = errorBg;
            resultEl.style.color = '#991B1B';
            resultEl.textContent = 'Erreur de connexion. Verifiez que le serveur est actif.';
        });
}

function submitBoost() {
    const jobId = document.getElementById('boost-job-select').value;
    if (!jobId) return alert('Selectionnez une offre.');
    postApi('api-boost-job.php', { job_id: jobId },
        document.getElementById('boost-result'), '#D1FAE5', '#FEE2E2');
}
function submitFeature() {
    const jobId = document.getElementById('feature-job-select').value;
    if (!jobId) return alert('Selectionnez une offre.');
    postApi('api-feature-job.php', { job_id: jobId },
        document.getElementById('feature-result'), '#D1FAE5', '#FEE2E2');
}
function submitPush() {
    const jobId = document.getElementById('push-job-select').value;
    if (!jobId) return alert('Selectionnez une offre.');
    postApi('api-push-candidates.php', { job_id: jobId },
        document.getElementById('push-result'), '#D1FAE5', '#FEE2E2');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>




