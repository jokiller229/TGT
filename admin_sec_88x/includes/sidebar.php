<?php
$current_page = basename($_SERVER['PHP_SELF']);
// Badge : messages non résolus
$_sdb = getDB();
$_nouveauxCount = (int)$_sdb->query("SELECT COUNT(*) FROM support_messages WHERE statut = 'nouveau'")->fetchColumn();
$_signalementsCount = (int)$_sdb->query("SELECT COUNT(*) FROM signalements WHERE statut = 'en attente'")->fetchColumn();
?>
<aside class="dashboard-sidebar-dark">
  <div class="sidebar-header-section">
    <a href="dashboard.php" class="logo-brand" style="margin-bottom: 2rem; display: flex;">
      <img src="../siteweb/img/tgtravail-logo.png" alt="TGTravail" class="logo-img" style="width: 38px; height: auto; border-radius: 8px;">
      <span><span class="tg">TG</span><span style="color:#FFF;">Travail</span></span>
    </a>

    <div>
      <span class="sidebar-role-badge">ADMIN</span>
      <ul class="sidebar-menu-list" style="margin-top: 0.85rem;">
        <li class="sidebar-menu-item <?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
          <a href="dashboard.php">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
            <span>Vue d'ensemble</span>
          </a>
        </li>
        <li class="sidebar-menu-item <?= $current_page === 'recruteurs.php' ? 'active' : '' ?>">
          <a href="recruteurs.php">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path></svg>
            <span>Modération Recruteurs</span>
          </a>
        </li>
        <li class="sidebar-menu-item <?= $current_page === 'offres.php' ? 'active' : '' ?>">
          <a href="offres.php">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
            <span>Modération Offres</span>
          </a>
        </li>
        <li class="sidebar-menu-item <?= $current_page === 'utilisateurs.php' ? 'active' : '' ?>">
          <a href="utilisateurs.php">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            <span>Utilisateurs</span>
          </a>
        </li>
        <li class="sidebar-menu-item <?= $current_page === 'signalements.php' ? 'active' : '' ?>">
          <a href="signalements.php" style="<?= $_signalementsCount > 0 ? 'color: #F59E0B;' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path><line x1="4" y1="22" x2="4" y2="15"></line></svg>
            <span>Signalements</span>
            <?php if ($_signalementsCount > 0): ?>
              <span style="background: #F59E0B; color: #FFF; font-size: 0.65rem; padding: 2px 6px; border-radius: 99px; font-weight: 800; margin-left: auto;"><?= $_signalementsCount ?></span>
            <?php endif; ?>
          </a>
        </li>
        <li class="sidebar-menu-item <?= $current_page === 'diffusions.php' ? 'active' : '' ?>">
          <a href="diffusions.php">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 11l19-9-9 19-2-8-8-2z"/></svg>
            <span>Diffusions</span>
          </a>
        </li>
        <li class="sidebar-menu-item <?= $current_page === 'abonnements.php' ? 'active' : '' ?>">
          <a href="abonnements.php">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
            <span>Abonnements</span>
          </a>
        </li>
        <li class="sidebar-menu-item <?= $current_page === 'support.php' ? 'active' : '' ?>">
          <a href="support.php" style="<?= $_nouveauxCount > 0 ? 'color: #F87171;' : '' ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
            <span>Service Client<?= $_nouveauxCount > 0 ? ' <span style="background:#EF4444;color:white;font-size:0.65rem;border-radius:99px;padding:0.1rem 0.4rem;margin-left:0.25rem;font-weight:800;">' . $_nouveauxCount . '</span>' : '' ?></span>
          </a>
        </li>
        <li class="sidebar-menu-item <?= $current_page === 'conseils.php' ? 'active' : '' ?>">
          <a href="conseils.php">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            <span>Conseils / Blog</span>
          </a>
        </li>
        <li class="sidebar-menu-item <?= $current_page === 'cv-templates.php' ? 'active' : '' ?>">
          <a href="cv-templates.php">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
            <span>Modèles de CV</span>
          </a>
        </li>
        <li class="sidebar-menu-item <?= $current_page === 'equipe.php' ? 'active' : '' ?>">
          <a href="equipe.php">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><polyline points="16 11 18 13 22 9"></polyline></svg>
            <span>Équipe (Admins)</span>
          </a>
        </li>
        <li class="sidebar-menu-item" style="margin-top: 1.5rem;">
          <a href="logout.php" style="color: #F87171;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
            <span>Déconnexion</span>
          </a>
        </li>
      </ul>
    </div>
  </div>
</aside>
