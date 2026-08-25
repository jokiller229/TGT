<?php
$activePage = 'profil';
$pageTitle = 'Mon Profil - TGTravail';
require_once __DIR__ . '/../includes/auth.php';

// Rediriger vers l'accueil si non connecté en tant que candidat
if (getCurrentRole() !== 'candidat') {
    header("Location: ../index.php");
    exit;
}

require_once __DIR__ . '/../includes/header.php';
$user = getCurrentUser();
?>

<div class="dashboard-wrapper">
  <!-- Sidebar -->
  <aside class="dashboard-sidebar-dark hide-on-mobile">
    <div class="sidebar-header-section">
      <a href="../index.php" class="logo-brand">
        <img src="../img/tgtravail-logo.png" alt="TGTravail" class="logo-img" style="width: 38px; height: auto; border-radius: 8px;">
        <span><span class="tg">TG</span><span style="color:#FFF;">Travail</span></span>
      </a>

      <div>
        <span class="sidebar-role-badge" style="background: rgba(37, 99, 235, 0.2); color: #60A5FA;">CANDIDAT</span>
        <ul class="sidebar-menu-list" style="margin-top: 0.85rem;">
          <li class="sidebar-menu-item">
            <a href="../candidat/candidat-dashboard.php">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
              <span>Tableau de bord</span>
            </a>
          </li>
          <li class="sidebar-menu-item active">
            <a href="../candidat/profil-candidat.php">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
              <span>Mon profil</span>
            </a>
          </li>
          <li class="sidebar-menu-item">
            <a href="../candidat/mes-candidatures.php">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
              <span>Candidatures</span>
            </a>
          </li>
          <li class="sidebar-menu-item" style="margin-top: 1.5rem;">
            <a href="?logout=1" style="color: #F87171;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
              <span>Déconnexion</span>
            </a>
          </li>
        </ul>
      </div>
    </div>
  </aside>

  <!-- Main Content -->
  <main class="dashboard-content-main">
    <div class="dashboard-topbar">
      <div class="dashboard-header-left" style="display:flex; align-items:center; gap:1rem;">
        <button class="dashboard-mobile-btn" onclick="document.querySelector('.dashboard-sidebar-dark').classList.toggle('open'); event.stopPropagation();">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>
        <div>
        <h1 class="user-greeting">Mon Profil</h1>
        <p class="greeting-subtext">Gérez vos informations personnelles et votre CV</p>
      </div>
      </div>
    </div>
    
    <div style="background: var(--bg-surface); padding: 2rem; border-radius: var(--radius-xl); border: 1px solid var(--border-light); box-shadow: var(--shadow-sm); max-width: 800px;">
      <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--color-primary-navy); margin-bottom: 1.5rem;">Informations Personnelles</h2>
      
      <form style="display: grid; gap: 1.5rem;">
        <div>
          <label class="form-label">Nom complet</label>
          <input type="text" class="form-input" value="<?= htmlspecialchars($user['name'] ?? '') ?>">
        </div>
        <div>
          <label class="form-label">Adresse Email</label>
          <input type="email" class="form-input" value="<?= htmlspecialchars($user['email'] ?? '') ?>" readonly style="background: var(--bg-surface-secondary); opacity: 0.8;">
        </div>
        <div>
          <label class="form-label">Numéro de téléphone</label>
          <input type="text" class="form-input" placeholder="+228 XX XX XX XX">
        </div>
        <div>
          <label class="form-label">Titre professionnel</label>
          <input type="text" class="form-input" placeholder="Ex: Développeur Web, Comptable...">
        </div>
        <div>
          <label class="form-label">Bio / Présentation</label>
          <textarea class="form-input" rows="4" placeholder="Décrivez brièvement votre parcours..."></textarea>
        </div>
        <div style="text-align: right; margin-top: 1rem;">
          <button type="button" class="btn-primary" style="border-radius: var(--radius-pill); padding: 0.75rem 2rem;">Enregistrer les modifications</button>
        </div>
      </form>
    </div>
  </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>




