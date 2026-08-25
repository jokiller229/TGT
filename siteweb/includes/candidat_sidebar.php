<?php
// Active page should be defined in the parent file
$activePage = $activePage ?? 'dashboard';
?>
<!-- Dark Sidebar Left -->
<aside class="dashboard-sidebar-dark">
  <div class="sidebar-header-section">
    <a href="<?= $baseUrl ?>/candidat/candidat-dashboard.php" class="logo-brand">
      <img src="<?= $baseUrl ?>/img/tgtravail-logo.png" alt="TGTravail" class="logo-img" style="width: 38px; height: auto; border-radius: 8px;">
      <span><span class="tg">TG</span><span style="color:#FFF;">Travail</span></span>
    </a>

    <div>
      <span class="sidebar-role-badge" style="background: rgba(2, 132, 199, 0.2); color: #38BDF8;">CANDIDAT</span>
      <ul class="sidebar-menu-list" style="margin-top: 0.85rem;">
        <li class="sidebar-menu-item <?= $activePage === 'dashboard' ? 'active' : '' ?>">
          <a href="../candidat/candidat-dashboard.php">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
            <span>Tableau de bord</span>
          </a>
        </li>
        <li class="sidebar-menu-item <?= $activePage === 'profil' ? 'active' : '' ?>">
          <a href="../candidat/candidat-profil.php">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            <span>Mon Profil & CV</span>
          </a>
        </li>
        <li class="sidebar-menu-item <?= $activePage === 'generateur-cv' ? 'active' : '' ?>">
          <a href="../candidat/candidat-generateur-cv.php">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            <span>Générateur de CV</span>
          </a>
        </li>
        <li class="sidebar-menu-item <?= $activePage === 'mes-candidatures' ? 'active' : '' ?>">
          <a href="../candidat/mes-candidatures.php">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
            <span>Mes Candidatures</span>
          </a>
        </li>
        <li class="sidebar-menu-item <?= $activePage === 'favoris' ? 'active' : '' ?>">
          <a href="../candidat/offres-sauvegardees.php">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path></svg>
            <span>Offres sauvegardées</span>
          </a>
        </li>
        <li class="sidebar-menu-item <?= $activePage === 'messages' ? 'active' : '' ?>">
          <a href="../candidat/candidat-messages.php">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
            <span>Messages</span>
          </a>
        </li>
        <li class="sidebar-menu-item <?= $activePage === 'entretiens' ? 'active' : '' ?>">
          <a href="../candidat/candidat-entretiens.php">
            <svg width="18" height="18" viewBox="0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14v-4z"></path><rect x="3" y="6" width="12" height="12" rx="2" ry="2"></rect></svg>
            <span>Mes Entretiens</span>
          </a>
        </li>
        <li class="sidebar-menu-item <?= $activePage === 'alertes' ? 'active' : '' ?>">
          <a href="../candidat/candidat-alertes.php">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
            <span>Alertes Emploi</span>
          </a>
        </li>
        <li class="sidebar-menu-item <?= $activePage === 'parametres' ? 'active' : '' ?>">
          <a href="../candidat/candidat-parametres.php">
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

  <a href="../pages/offres.php" class="btn-primary" style="width: 100%; border-radius: var(--radius-pill); background: var(--bg-surface); color: var(--color-primary-dark); border: none;">
    🔍 Trouver un emploi
  </a>
</aside>

<!-- MOBILE APP SHELL: Bottom Navigation Bar -->
<nav class="mobile-bottom-nav">
  <a href="../candidat/candidat-dashboard.php" class="mobile-bottom-nav-item <?= $activePage === 'dashboard' ? 'active' : '' ?>">
    <svg width="24" height="24" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
    <span>Accueil</span>
  </a>
  <a href="../pages/offres.php" class="mobile-bottom-nav-item <?= $activePage === 'offres' ? 'active' : '' ?>">
    <svg width="24" height="24" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
    <span>Rechercher</span>
  </a>
  <a href="../candidat/mes-candidatures.php" class="mobile-bottom-nav-item <?= $activePage === 'mes-candidatures' ? 'active' : '' ?>">
    <svg width="24" height="24" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
    <span>Candidatures</span>
  </a>
  <a href="../candidat/candidat-messages.php" class="mobile-bottom-nav-item <?= $activePage === 'messages' ? 'active' : '' ?>">
    <svg width="24" height="24" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
    <span>Messages</span>
  </a>
  <a href="../candidat/candidat-profil.php" class="mobile-bottom-nav-item <?= $activePage === 'profil' ? 'active' : '' ?>">
    <svg width="24" height="24" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
    <span>Profil</span>
  </a>
</nav>
