<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('candidat');

$activePage = 'profil';
$userId = $_SESSION['user_id'];
$pdo = getDB();

// Fetch candidate profile
$stmt = $pdo->prepare("
    SELECT u.nom, u.email, u.telephone, c.* 
    FROM users u 
    LEFT JOIN candidate_profiles c ON u.id = c.user_id 
    WHERE u.id = ?
");
$stmt->execute([$userId]);
$userProfile = $stmt->fetch(PDO::FETCH_ASSOC);

$hideHeader = true; // Use the App Shell instead
include __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper" style="background: #F8FAFC;">
  <?php include __DIR__ . '/../includes/candidat_sidebar.php'; ?>

  <main class="dashboard-content-main" style="padding: 0; min-height: 100vh;">
    
    <!-- PWA VIEW -->
    <div class="pwa-only-view">
      <!-- Profil Header (Dark Blue like Mockup) -->
      <div style="background: var(--color-primary-navy); color: white; padding: 3rem 1.5rem 2rem; display: flex; flex-direction: column; align-items: center; text-align: center;">
        <!-- Avatar -->
        <div style="width: 80px; height: 80px; border-radius: 50%; background: #CBD5E1; border: 3px solid #FFF; overflow: hidden; margin-bottom: 1rem; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 800; color: #475569;">
          <?php
          if (!empty($userProfile['avatar_html'])) {
              echo $userProfile['avatar_html'];
          } else {
              $initials = strtoupper(substr($userProfile['nom'], 0, 2));
              echo $initials;
          }
          ?>
        </div>
        <h1 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 0.25rem;">
          <?= htmlspecialchars($userProfile['nom']) ?>
        </h1>
        <p style="color: #94A3B8; font-size: 0.95rem; font-weight: 500;">Candidat</p>
      </div>

      <!-- Links List (Mockup Style) -->
      <div style="background: white; border-radius: 24px 24px 0 0; margin-top: -1.5rem; padding: 2rem 1.5rem; min-height: 60vh;">
        
        <a href="profil-candidat.php" style="display: flex; align-items: center; justify-content: space-between; padding: 1.25rem 0; border-bottom: 1px solid #F1F5F9; text-decoration: none; color: #0F172A;">
          <div style="display: flex; align-items: center; gap: 1rem;">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: #F8FAFC; display: flex; align-items: center; justify-content: center; color: #64748B;">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            </div>
            <div>
              <div style="font-weight: 600; font-size: 1rem;">Mon profil public</div>
              <div style="font-size: 0.8rem; color: #64748B;">Voir mon profil tel que les recruteurs</div>
            </div>
          </div>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </a>

        <a href="candidat-generateur-cv.php" style="display: flex; align-items: center; justify-content: space-between; padding: 1.25rem 0; border-bottom: 1px solid #F1F5F9; text-decoration: none; color: #0F172A;">
          <div style="display: flex; align-items: center; gap: 1rem;">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: #F8FAFC; display: flex; align-items: center; justify-content: center; color: #64748B;">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
            </div>
            <div>
              <div style="font-weight: 600; font-size: 1rem;">Mon CV</div>
              <div style="font-size: 0.8rem; color: #64748B;">Télécharger ou modifier mon CV</div>
            </div>
          </div>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </a>

        <a href="candidat-parametres.php" style="display: flex; align-items: center; justify-content: space-between; padding: 1.25rem 0; border-bottom: 1px solid #F1F5F9; text-decoration: none; color: #0F172A;">
          <div style="display: flex; align-items: center; gap: 1rem;">
            <div style="width: 40px; height: 40px; border-radius: 10px; background: #F8FAFC; display: flex; align-items: center; justify-content: center; color: #64748B;">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
            </div>
            <div>
              <div style="font-weight: 600; font-size: 1rem;">Paramètres du compte</div>
              <div style="font-size: 0.8rem; color: #64748B;">Notifications, confidentialité...</div>
            </div>
          </div>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </a>

        <!-- Logout -->
        <a href="?logout=1" style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; margin-top: 2rem; color: #EF4444; font-weight: 600; font-size: 1rem; text-decoration: none;">
          Se déconnecter
        </a>
      </div>
    </div>

    <!-- WEB VIEW (Restauration) -->
    <div class="web-only-view" style="padding: 2rem;">
      <div class="dashboard-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem; flex-wrap:wrap; gap:1rem;">
        <div class="dashboard-header-left">
          <button class="dashboard-mobile-btn" onclick="document.querySelector('.dashboard-sidebar-dark').classList.toggle('open'); event.stopPropagation();">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
          </button>
          <div>
            <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--color-primary-dark);">Mon Profil & CV</h1>
            <p style="color:var(--text-muted); margin-top:0.25rem;">Gérez vos informations personnelles et votre CV</p>
          </div>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
        <!-- Card 1: Profil -->
        <div style="background:var(--bg-surface); border:1px solid var(--border-light); border-radius:var(--radius-xl); padding:1.5rem; box-shadow:var(--shadow-sm);">
          <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--color-primary-navy); margin-bottom: 1rem;">Informations Personnelles</h2>
          <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Consultez et modifiez vos informations de contact et votre biographie.</p>
          <a href="profil-candidat.php" class="btn-primary" style="display: inline-block; padding: 0.5rem 1rem; border-radius: var(--radius-pill); text-decoration: none;">Modifier mon profil</a>
        </div>

        <!-- Card 2: CV -->
        <div style="background:var(--bg-surface); border:1px solid var(--border-light); border-radius:var(--radius-xl); padding:1.5rem; box-shadow:var(--shadow-sm);">
          <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--color-primary-navy); margin-bottom: 1rem;">Générateur de CV</h2>
          <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Créez un CV professionnel en quelques minutes grâce à nos modèles.</p>
          <a href="candidat-generateur-cv.php" class="btn-primary" style="display: inline-block; padding: 0.5rem 1rem; border-radius: var(--radius-pill); text-decoration: none;">Créer mon CV</a>
        </div>
      </div>
    </div>
  </main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
