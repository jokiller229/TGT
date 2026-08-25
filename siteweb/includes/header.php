<?php
require_once __DIR__ . '/auth.php';
$user = getCurrentUser();
$currentRole = getCurrentRole();
$activePage = $activePage ?? 'accueil';

// Récupérer les messages flash de session
$authError = $_SESSION['auth_error'] ?? null;
$authSuccess = $_SESSION['auth_success'] ?? null;
unset($_SESSION['auth_error'], $_SESSION['auth_success']);

// Variables auth enlevées car traitées dans connexion.php et inscription.php
$authError = $_SESSION['auth_error'] ?? null;
$authSuccess = $_SESSION['auth_success'] ?? null;
unset($_SESSION['auth_error'], $_SESSION['auth_success']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle ?? 'TGTravail - Trouvez le bon emploi. Trouvez le bon talent au Togo') ?></title>
  <meta name="description" content="Plateforme togolaise de recherche d'emploi et de mise en relation entre candidats et recruteurs.">
  <!-- PWA Manifest & Theme -->
  <link rel="manifest" href="<?= $baseUrl ?>/manifest.json">
  <meta name="theme-color" content="#0b192c">
  <link rel="apple-touch-icon" href="<?= $baseUrl ?>/img/tgtravail-logo.png">
  
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  
  <!-- AOS Animation CSS -->
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

  <!-- Styles -->
  <link rel="stylesheet" href="<?= $baseUrl ?>/css/style.css?v=<?= filemtime(__DIR__ . '/../css/style.css') ?>">
  <link rel="stylesheet" href="<?= $baseUrl ?>/css/pwa-mobile.css?v=<?= filemtime(__DIR__ . '/../css/pwa-mobile.css') ?>">
  
  <!-- Critical styles (garantis sans cache) -->
  <style>
    /* Masquer le mobile drawer par défaut */
    .mobile-nav-drawer { display: none !important; flex-direction: column; gap: .25rem; padding: .75rem 0 1rem; border-top: 1px solid #E2E8F0; }
    .mobile-nav-drawer.open { display: flex !important; }



    /* Tabs rôle */
    .role-choice-tabs { display:grid; grid-template-columns:1fr 1fr; gap:.5rem; background:#F1F5F9; padding:.35rem; border-radius:12px; }
    .role-tab { padding:.65rem; border-radius:10px; font-size:.875rem; font-weight:600; color:#64748B; cursor:pointer; border:none; background:none; transition:all .2s; }
    .role-tab.active { background:#fff; color:#2563EB; box-shadow:0 1px 2px rgba(0,0,0,.05); }

    /* Formulaire */
    .form-group { display:flex; flex-direction:column; gap:.4rem; }
    .form-label { font-size:.875rem; font-weight:600; color:#0F172A; }
    .form-input { width:100%; padding:.75rem 1rem; border:1.5px solid #E2E8F0; border-radius:12px; font-size:.9rem; color:#0F172A; background:#fff; outline:none; font-family:inherit; transition:border-color .2s; }
    .form-input:focus { border-color:#2563EB; box-shadow:0 0 0 3px rgba(37,99,235,.1); }
    .form-error-banner { background:#FEE2E2; color:#991B1B; border:1px solid #FECACA; border-radius:12px; padding:.75rem 1rem; font-size:.875rem; font-weight:600; }

    /* Comptes démo */
    .demo-accounts-box { margin-top:1.25rem; padding:1rem; background:#F1F5F9; border-radius:12px; border:1px dashed #E2E8F0; }
    .demo-acc-btn { display:block; width:100%; text-align:left; padding:.4rem .6rem; font-size:.8rem; color:#2563EB; font-weight:600; border-radius:8px; cursor:pointer; border:none; background:none; }
    .demo-acc-btn:hover { background:#EFF6FF; }

    /* Flash banners */
    .flash-banner { display:flex; align-items:center; justify-content:space-between; padding:.85rem 1.5rem; font-size:.9rem; font-weight:600; }
    .flash-banner button { cursor:pointer; opacity:.7; font-size:1rem; padding:0 .25rem; background:none; border:none; }
    .flash-success { background:#DCFCE7; color:#166534; border-bottom:1px solid #BBF7D0; }
    .flash-error { background:#FEE2E2; color:#991B1B; border-bottom:1px solid #FECACA; }

  </style>
</head>
<?php
$bodyClass = '';
if ($currentRole === 'recruteur') {
    $bodyClass = 'role-recruteur';
} elseif ($currentRole === 'candidat') {
    $bodyClass = 'pwa-candidate-mode';
}
?>
<body class="<?= $bodyClass ?>">

  <?php if ($authSuccess): ?>
  <div class="flash-banner flash-success"><?= htmlspecialchars($authSuccess) ?> <button onclick="this.parentElement.remove()">✕</button></div>
  <?php endif; ?>

  <?php if ($authError): ?>
  <div class="flash-banner flash-error"><?= htmlspecialchars($authError) ?> <button onclick="this.parentElement.remove()">✕</button></div>
  <?php endif; ?>

  <?php 
  $isPublicPage = in_array($activePage ?? '', ['accueil', 'offres', 'offre-detail', 'apropos', 'conseils', 'contact', 'entreprises']);
  $shouldHideHeaderOnDesktop = !(($isPublicPage || $currentRole !== 'recruteur') && empty($hideHeader));
  if (!empty($hideHeader) || in_array($activePage ?? '', ['dashboard', 'profil', 'generateur-cv', 'mes-candidatures', 'favoris', 'messages', 'entretiens', 'alertes', 'parametres', 'mes-offres', 'candidatures', 'cvtheque', 'abonnements', 'notifications'])) {
      $shouldHideHeaderOnDesktop = true;
  }
  ?>
  <!-- Site Header (fidèle maquette Écran 1) -->
  <header class="site-header" <?php if($shouldHideHeaderOnDesktop) echo 'style="display:none !important;"'; ?>>
    <div class="container" style="max-width: 1536px;">
      <nav class="header-nav">
        <!-- Logo Brand -->
        <a href="<?= $baseUrl ?>/index.php" class="logo-brand">
          <img src="<?= $baseUrl ?>/img/tgtravail-logo.png" alt="TGTravail Logo" class="logo-img" style="width: 38px; height: auto; border-radius: 8px;">
          <span>TGTravail</span>
        </a>

        <!-- Desktop Navigation (Hidden on mobile) -->
        <div class="nav-links">
          <a href="<?= $baseUrl ?>/index.php" class="nav-link <?= ($activePage ?? '') === 'accueil' ? 'active' : '' ?>">Accueil</a>
          <a href="<?= $baseUrl ?>/pages/offres.php" class="nav-link <?= ($activePage ?? '') === 'offres' ? 'active' : '' ?>">Offres d'emploi</a>
          <a href="<?= $baseUrl ?>/pages/conseils.php" class="nav-link <?= ($activePage ?? '') === 'conseils' ? 'active' : '' ?>">Conseils</a>
          <a href="<?= $baseUrl ?>/pages/a-propos.php" class="nav-link <?= ($activePage ?? '') === 'apropos' ? 'active' : '' ?>">À propos</a>
          <a href="<?= $baseUrl ?>/pages/contact.php" class="nav-link <?= ($activePage ?? '') === 'contact' ? 'active' : '' ?>">Contact</a>
        </div>

        <!-- Desktop Actions -->
        <div class="nav-actions">
          <?php if ($currentRole === 'visiteur' || !$currentRole): ?>
            <a href="<?= $baseUrl ?>/auth/connexion.php" class="btn-outline">Connexion</a>
            <a href="<?= $baseUrl ?>/auth/inscription.php" class="btn-primary">S'inscrire</a>
          <?php else: ?>
            <?php if ($currentRole === 'candidat'): ?>
              <div class="profile-dropdown-container" style="position: relative;">
                <button class="btn-profile-toggle" onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'none' ? 'flex' : 'none';" style="background: none; border: none; display: flex; align-items: center; gap: 0.5rem; cursor: pointer; padding: 0;">
                  <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--bg-surface-secondary); display: flex; align-items: center; justify-content: center; border: 2px solid var(--color-primary-blue);">
                    <svg width="20" height="20" fill="none" stroke="var(--color-primary-blue)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                  </div>
                  <span style="font-weight: 600; color: var(--text-main);">Mon Espace</span>
                  <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>
                <div class="profile-dropdown-menu" style="display: none; position: absolute; top: 100%; right: 0; margin-top: 0.5rem; background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-md); box-shadow: var(--shadow-md); min-width: 200px; z-index: 100; flex-direction: column; overflow: hidden;">
                  <a href="<?= $baseUrl ?>/candidat/candidat-dashboard.php" style="padding: 0.75rem 1rem; color: var(--text-main); font-weight: 600; border-bottom: 1px solid var(--border-light); text-decoration: none; display: block;">Tableau de bord</a>
                  <a href="<?= $baseUrl ?>/candidat/profil-candidat.php" style="padding: 0.75rem 1rem; color: var(--text-main); font-weight: 500; text-decoration: none; display: block;">Mon Profil</a>
                  <a href="?logout=1" style="padding: 0.75rem 1rem; color: #ef4444; font-weight: 600; text-decoration: none; display: block; border-top: 1px solid var(--border-light);">Déconnexion</a>
                </div>
              </div>
            <?php elseif ($currentRole === 'recruteur'): ?>
              <div class="profile-dropdown-container" style="position: relative;">
                <button class="btn-profile-toggle" onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'none' ? 'flex' : 'none';" style="background: none; border: none; display: flex; align-items: center; gap: 0.5rem; cursor: pointer; padding: 0;">
                  <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--bg-surface-secondary); display: flex; align-items: center; justify-content: center; border: 2px solid var(--color-primary-blue);">
                    <svg width="20" height="20" fill="none" stroke="var(--color-primary-blue)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                  </div>
                  <span style="font-weight: 600; color: var(--text-main);">Espace Recruteur</span>
                  <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>
                <div class="profile-dropdown-menu" style="display: none; position: absolute; top: 100%; right: 0; margin-top: 0.5rem; background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-md); box-shadow: var(--shadow-md); min-width: 200px; z-index: 100; flex-direction: column; overflow: hidden;">
                  <a href="<?= $baseUrl ?>/recruteur/recruteur-dashboard.php" style="padding: 0.75rem 1rem; color: var(--text-main); font-weight: 600; border-bottom: 1px solid var(--border-light); text-decoration: none; display: block;">Tableau de bord</a>
                  <a href="<?= $baseUrl ?>/recruteur/mes-offres.php" style="padding: 0.75rem 1rem; color: var(--text-main); font-weight: 500; text-decoration: none; display: block;">Mes offres</a>
                  <a href="?logout=1" style="padding: 0.75rem 1rem; color: #ef4444; font-weight: 600; text-decoration: none; display: block; border-top: 1px solid var(--border-light);">Déconnexion</a>
                </div>
              </div>
            <?php elseif ($currentRole === 'admin'): ?>
              <div class="profile-dropdown-container" style="position: relative;">
                <button class="btn-profile-toggle" onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'none' ? 'flex' : 'none';" style="background: none; border: none; display: flex; align-items: center; gap: 0.5rem; cursor: pointer; padding: 0;">
                  <div style="width: 40px; height: 40px; border-radius: 50%; background: #F3E8FF; display: flex; align-items: center; justify-content: center; border: 2px solid #7C3AED;">
                    <span style="font-size: 1.2rem;">👑</span>
                  </div>
                  <span style="font-weight: 700; color: #7C3AED;">Admin</span>
                  <svg width="16" height="16" fill="none" stroke="#7C3AED" stroke-width="2" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>
                <div class="profile-dropdown-menu" style="display: none; position: absolute; top: 100%; right: 0; margin-top: 0.5rem; background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-md); box-shadow: var(--shadow-md); min-width: 200px; z-index: 100; flex-direction: column; overflow: hidden;">
                  <a href="<?= $baseUrl ?>/admin/admin-dashboard.php" style="padding: 0.75rem 1rem; color: var(--text-main); font-weight: 600; border-bottom: 1px solid var(--border-light); text-decoration: none; display: block;">Tableau de bord</a>
                  <a href="?logout=1" style="padding: 0.75rem 1rem; color: #ef4444; font-weight: 600; text-decoration: none; display: block; border-top: 1px solid var(--border-light);">Déconnexion</a>
                </div>
              </div>
            <?php else: ?>
              <span style="color: red; font-weight: bold;">Rôle inconnu : <?= htmlspecialchars(var_export($currentRole, true)) ?></span>
              <a href="?logout=1" class="btn-link" style="color: #94A3B8; font-size: 0.85rem;">Déconnexion</a>
            <?php endif; ?>
          <?php endif; ?>
        </div>

        <!-- Mobile menu toggle -->
        <button class="mobile-menu-btn" id="mobile-menu-toggle" aria-label="Menu">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="3" y1="12" x2="21" y2="12"></line>
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <line x1="3" y1="18" x2="21" y2="18"></line>
          </svg>
        </button>
      </nav>

      <!-- Mobile Drawer Navigation -->
      <div class="mobile-nav-drawer" id="mobile-nav">
        <a href="<?= $baseUrl ?>/index.php" class="nav-link <?= ($activePage ?? '') === 'accueil' ? 'active' : '' ?>">Accueil</a>
        <a href="<?= $baseUrl ?>/pages/offres.php" class="nav-link <?= ($activePage ?? '') === 'offres' ? 'active' : '' ?>">Offres d'emploi</a>
        <a href="<?= $baseUrl ?>/pages/conseils.php" class="nav-link <?= ($activePage ?? '') === 'conseils' ? 'active' : '' ?>">Conseils</a>
        <a href="<?= $baseUrl ?>/pages/a-propos.php" class="nav-link <?= ($activePage ?? '') === 'apropos' ? 'active' : '' ?>">À propos</a>
        <a href="<?= $baseUrl ?>/pages/contact.php" class="nav-link <?= ($activePage ?? '') === 'contact' ? 'active' : '' ?>">Contact</a>
        
        <hr style="border: 0; border-top: 1px solid #E2E8F0; margin: 0.5rem 0;">
        
        <?php if ($currentRole === 'visiteur' || !$currentRole): ?>
          <a href="<?= $baseUrl ?>/auth/connexion.php" class="nav-link" style="color: #475569;">Connexion</a>
          <a href="<?= $baseUrl ?>/auth/inscription.php" class="nav-link" style="color: var(--color-primary-blue); font-weight: 700;">S'inscrire</a>
        <?php else: ?>
          <?php if ($currentRole === 'candidat'): ?>
            <div style="padding: 0.5rem; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #94A3B8; font-weight: 700;">Mon Espace</div>
            <a href="<?= $baseUrl ?>/candidat/candidat-dashboard.php" class="nav-link <?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>" style="font-weight: 600;">Tableau de bord</a>
          <?php elseif ($currentRole === 'recruteur'): ?>
            <div style="padding: 0.5rem; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #94A3B8; font-weight: 700;">Mon Entreprise</div>
            <a href="<?= $baseUrl ?>/recruteur/recruteur-dashboard.php" class="nav-link <?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>" style="font-weight: 600;">Tableau de bord</a>
          <?php elseif ($currentRole === 'admin'): ?>
            <div style="padding: 0.5rem; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #94A3B8; font-weight: 700;">Administration</div>
            <a href="<?= $baseUrl ?>/admin/admin-dashboard.php" class="nav-link" style="font-weight: 600; color: #7C3AED;">Tableau de bord</a>
          <?php endif; ?>
          <hr style="border: 0; border-top: 1px solid #E2E8F0; margin: 0.5rem 0;">
          <a href="?logout=1" class="nav-link" style="color: #DC2626;">Déconnexion</a>
        <?php endif; ?>
      </div>
    </div>
  </header>

<!-- Enregistrement du Service Worker pour PWA -->
<script>
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('<?= $baseUrl ?>/sw.js')
      .then(reg => console.log('Service Worker enregistré', reg.scope))
      .catch(err => console.log('Échec Service Worker', err));
  });
}
</script>



<script>
  // ── Modaux ───────────────────────────────────────────────
  function openModal(id) { document.getElementById(id).style.display = 'flex'; document.body.style.overflow = 'hidden'; }
  function closeModal(id) { document.getElementById(id).style.display = 'none'; document.body.style.overflow = ''; }

  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => { if (e.target === overlay) closeModal(overlay.id); });
  });
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.querySelectorAll('.modal-overlay').forEach(m => closeModal(m.id));
  });

  const btnLogin = document.getElementById('btn-open-login');
  const btnRegister = document.getElementById('btn-open-register');
  const mobileLogin = document.getElementById('mobile-login-btn');
  const mobileRegister = document.getElementById('mobile-register-btn');
  if (btnLogin) btnLogin.addEventListener('click', e => { e.preventDefault(); openModal('modal-login'); });
  if (btnRegister) btnRegister.addEventListener('click', e => { e.preventDefault(); openModal('modal-register'); });
  if (mobileLogin) mobileLogin.addEventListener('click', e => { e.preventDefault(); openModal('modal-login'); });
  if (mobileRegister) mobileRegister.addEventListener('click', e => { e.preventDefault(); openModal('modal-register'); });

  // ── Choix de rôle inscription ────────────────────────────
  function selectRole(role, btn) {
    document.getElementById('register-role').value = role;
    document.querySelectorAll('.role-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    
    // Afficher/Masquer les champs spécifiques
    const typeEntiteGroup = document.getElementById('group-type-entite');
    const telephoneGroup = document.getElementById('group-telephone');
    
    if (role === 'recruteur') {
      typeEntiteGroup.style.display = 'block';
      toggleTelephone(); // Check radio button state
    } else {
      typeEntiteGroup.style.display = 'none';
      telephoneGroup.style.display = 'none';
      document.getElementById('register-telephone').removeAttribute('required');
    }
  }

  // Écouter les changements sur le type d'entité
  document.querySelectorAll('input[name="type_entite"]').forEach(radio => {
    radio.addEventListener('change', toggleTelephone);
  });

  function toggleTelephone() {
    const isParticulier = document.querySelector('input[name="type_entite"]:checked').value === 'particulier';
    const telephoneGroup = document.getElementById('group-telephone');
    const telephoneInput = document.getElementById('register-telephone');
    const role = document.getElementById('register-role').value;
    
    if (role === 'recruteur' && isParticulier) {
      telephoneGroup.style.display = 'block';
      telephoneInput.setAttribute('required', 'required');
    } else {
      telephoneGroup.style.display = 'none';
      telephoneInput.removeAttribute('required');
    }
  }

  // ── Remplir le login démo ────────────────────────────────
  function fillLogin(email, pass) {
    document.getElementById('login-email').value = email;
    document.getElementById('login-password').value = pass;
  }


  // Password Strength Indicator
  function checkPasswordStrength(val) {
    const bar = document.getElementById('pwd-str-bar');
    const txt = document.getElementById('pwd-str-text');
    let strength = 0;
    if (val.length > 5) strength += 1;
    if (val.length > 7) strength += 1;
    if (/[A-Z]/.test(val)) strength += 1;
    if (/[0-9]/.test(val)) strength += 1;
    if (/[^A-Za-z0-9]/.test(val)) strength += 1;
    
    if (val.length === 0) {
      bar.style.width = '0%';
      txt.textContent = '';
    } else if (strength <= 2) {
      bar.style.width = '33%';
      bar.style.backgroundColor = '#EF4444'; // Red
      txt.textContent = 'Faible';
      txt.style.color = '#EF4444';
    } else if (strength === 3 || strength === 4) {
      bar.style.width = '66%';
      bar.style.backgroundColor = '#F59E0B'; // Orange
      txt.textContent = 'Moyen';
      txt.style.color = '#F59E0B';
    } else {
      bar.style.width = '100%';
      bar.style.backgroundColor = '#10B981'; // Green
      txt.textContent = 'Fort';
      txt.style.color = '#10B981';
    }
  }

  // Global Keyboard Shortcuts (Ctrl+K)
    document.addEventListener('keydown', (e) => {
      if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const searchInput = document.querySelector('input[name="q"]');
        if (searchInput) {
          searchInput.focus();
          window.scrollTo({top: 0, behavior: 'smooth'});
        } else {
          window.location.href = 'offres.php';
        }
      }
    });

    // Toggle Password Visibility
    function togglePassword(inputId, btn) {
      const input = document.getElementById(inputId);
      const icon = btn.querySelector('svg');
      if (input.type === 'password') {
        input.type = 'text';
        // Crossed-eye SVG
        icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
      } else {
        input.type = 'password';
        // Eye SVG
        icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
      }
    }
  </script>




