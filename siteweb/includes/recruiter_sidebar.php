<?php
// Active page should be defined in the parent file
$activePage = $activePage ?? 'dashboard';

// Check verifie status
$db = getDB();
$sidebarCompanyId = getCurrentCompanyId();
$sidebarCompany = $db->query("SELECT verifie FROM companies WHERE id = " . (int)$sidebarCompanyId)->fetch();
$isVerified = $sidebarCompany ? (bool)$sidebarCompany['verifie'] : false;
?>
<!-- Dark Sidebar Left -->
<aside class="dashboard-sidebar-dark">
  <div class="sidebar-header-section">
    <a href="<?= $baseUrl ?>/recruteur/recruteur-dashboard.php" class="logo-brand">
      <img src="<?= $baseUrl ?>/img/tgtravail-logo.png" alt="TGTravail" class="logo-img" style="width: 38px; height: auto; border-radius: 8px;">
      <span><span class="tg">TG</span><span style="color:#FFF;">Travail</span></span>
    </a>

    <div>
      <span class="sidebar-role-badge">RECRUTEUR</span>
      <ul class="sidebar-menu-list" style="margin-top: 0.85rem;">
        <li class="sidebar-menu-item <?= $activePage === 'dashboard' ? 'active' : '' ?>">
          <a href="../recruteur/recruteur-dashboard.php">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
            <span>Tableau de bord</span>
          </a>
        </li>
        <li class="sidebar-menu-item <?= $activePage === 'mes-offres' ? 'active' : '' ?>">
          <a href="<?= $isVerified ? 'mes-offres.php' : '#' ?>" <?= !$isVerified ? 'style="opacity: 0.5; cursor: not-allowed;" title="Vérification requise"' : '' ?>>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
            <span>Mes offres</span>
          </a>
        </li>
        <li class="sidebar-menu-item <?= $activePage === 'candidatures' ? 'active' : '' ?>">
          <a href="<?= $isVerified ? 'candidatures.php' : '#' ?>" <?= !$isVerified ? 'style="opacity: 0.5; cursor: not-allowed;" title="Vérification requise"' : '' ?>>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            <span>Mes candidatures reçues</span>
          </a>
        </li>
        <li class="sidebar-menu-item <?= $activePage === 'cvtheque' ? 'active' : '' ?>">
          <a href="<?= $isVerified ? 'cvtheque.php' : '#' ?>" <?= !$isVerified ? 'style="opacity: 0.5; cursor: not-allowed;" title="Vérification requise"' : '' ?>>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
            <span>CVthèque</span>
          </a>
        </li>
        <li class="sidebar-menu-item <?= $activePage === 'messages' ? 'active' : '' ?>">
          <a href="<?= $isVerified ? 'messages.php' : '#' ?>" <?= !$isVerified ? 'style="opacity: 0.5; cursor: not-allowed;" title="Vérification requise"' : '' ?>>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
            <span>Messages</span>
          </a>
        </li>
        <li class="sidebar-menu-item <?= $activePage === 'entretiens' ? 'active' : '' ?>">
          <a href="<?= $isVerified ? 'recruteur-entretiens.php' : '#' ?>" <?= !$isVerified ? 'style="opacity: 0.5; cursor: not-allowed;" title="Vérification requise"' : '' ?>>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14v-4z"></path><rect x="3" y="6" width="12" height="12" rx="2" ry="2"></rect></svg>
            <span>Entretiens Visio</span>
          </a>
        </li>
        <li class="sidebar-menu-item <?= $activePage === 'abonnements' ? 'active' : '' ?>">
          <a href="../recruteur/abonnements.php">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            <span>Abonnements</span>
          </a>
        </li>
        <li class="sidebar-menu-item <?= $activePage === 'parametres' ? 'active' : '' ?>">
          <a href="../recruteur/parametres.php">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
            <span>Paramètres</span>
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

  <a href="../recruteur/publier-offre.php" class="btn-primary" style="width: 100%; border-radius: var(--radius-pill);">
    + Publier une offre
  </a>
</aside>



