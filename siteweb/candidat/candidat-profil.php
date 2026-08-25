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

    <!-- WEB VIEW (Restauration Complète) -->
    <div class="web-only-view" style="padding: 2rem;">
      <div class="dashboard-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem; flex-wrap:wrap; gap:1rem;">
        <div class="dashboard-header-left">
          <button class="dashboard-mobile-btn" onclick="document.querySelector('.dashboard-sidebar-dark').classList.toggle('open'); event.stopPropagation();">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
          </button>
          <div>
            <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--color-primary-dark);">Mon Profil Candidat</h1>
            <p style="color:var(--text-muted); margin-top:0.25rem;">Mettez en valeur vos compétences et votre CV pour attirer les recruteurs</p>
          </div>
        </div>
        <div>
          <button class="btn-primary" style="border-radius: var(--radius-pill); padding: 0.75rem 1.5rem; display: flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 15px rgba(2, 132, 199, 0.3);">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
            Enregistrer le profil
          </button>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 350px; gap: 2rem; align-items: start;">
        
        <!-- SECTION GAUCHE : Formulaire de Profil -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
          
          <!-- Bloc 1: Informations de Base -->
          <div style="background:var(--bg-surface); border:1px solid var(--border-light); border-radius:var(--radius-xl); padding:1.5rem; box-shadow:var(--shadow-sm);">
            <h2 style="font-size: 1.15rem; font-weight: 700; color: var(--color-primary-navy); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--color-primary)" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
              Informations Principales
            </h2>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
              <div style="grid-column: span 2;">
                <label class="form-label" style="font-weight: 600; color: #475569;">Titre professionnel</label>
                <input type="text" class="form-input" placeholder="Ex: Développeur Web Fullstack, Comptable..." style="background: #F8FAFC; border-color: #E2E8F0;">
              </div>
              
              <div>
                <label class="form-label" style="font-weight: 600; color: #475569;">Années d'expérience</label>
                <select class="form-input" style="background: #F8FAFC; border-color: #E2E8F0;">
                  <option>Débutant (0-1 an)</option>
                  <option>Junior (1-3 ans)</option>
                  <option>Confirmé (3-5 ans)</option>
                  <option>Senior (5+ ans)</option>
                </select>
              </div>
              
              <div>
                <label class="form-label" style="font-weight: 600; color: #475569;">Lieu de résidence</label>
                <input type="text" class="form-input" placeholder="Ex: Lomé, Togo" style="background: #F8FAFC; border-color: #E2E8F0;">
              </div>
              
              <div style="grid-column: span 2;">
                <label class="form-label" style="font-weight: 600; color: #475569;">Bio / Présentation</label>
                <textarea class="form-input" rows="4" placeholder="Décrivez votre parcours, vos objectifs et ce qui vous passionne..." style="background: #F8FAFC; border-color: #E2E8F0; resize: vertical;"></textarea>
              </div>
            </div>
          </div>

          <!-- Bloc 2: Compétences & Dispo -->
          <div style="background:var(--bg-surface); border:1px solid var(--border-light); border-radius:var(--radius-xl); padding:1.5rem; box-shadow:var(--shadow-sm);">
            <h2 style="font-size: 1.15rem; font-weight: 700; color: var(--color-primary-navy); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--color-accent)" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
              Compétences et Préférences
            </h2>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
              <div style="grid-column: span 2;">
                <label class="form-label" style="font-weight: 600; color: #475569;">Vos Compétences (séparées par des virgules)</label>
                <input type="text" class="form-input" placeholder="Ex: PHP, Marketing Digital, Gestion de projet..." style="background: #F8FAFC; border-color: #E2E8F0;">
              </div>
              
              <div>
                <label class="form-label" style="font-weight: 600; color: #475569;">Disponibilité</label>
                <select class="form-input" style="background: #F8FAFC; border-color: #E2E8F0;">
                  <option>Immédiate</option>
                  <option>Dans 1 mois</option>
                  <option>Dans 3 mois</option>
                  <option>À l'écoute du marché</option>
                </select>
              </div>
              
              <div>
                <label class="form-label" style="font-weight: 600; color: #475569;">Type de contrat souhaité</label>
                <select class="form-input" style="background: #F8FAFC; border-color: #E2E8F0;">
                  <option>CDI</option>
                  <option>CDD</option>
                  <option>Freelance / Consultant</option>
                  <option>Stage</option>
                </select>
              </div>
              
              <div style="grid-column: span 2;">
                <label class="form-label" style="font-weight: 600; color: #475569;">Prétention salariale mensuelle (FCFA)</label>
                <input type="number" class="form-input" placeholder="Ex: 250000" style="background: #F8FAFC; border-color: #E2E8F0;">
              </div>
            </div>
          </div>
          
        </div>

        <!-- SECTION DROITE : CV Upload & Actions -->
        <div style="display: flex; flex-direction: column; gap: 1.5rem;">
          
          <!-- Bloc Upload CV -->
          <div style="background:var(--bg-surface); border:1px solid var(--border-light); border-radius:var(--radius-xl); padding:1.5rem; box-shadow:var(--shadow-sm); text-align: center;">
            <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(2, 132, 199, 0.1); color: var(--color-primary); display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="12" y1="18" x2="12" y2="12"></line><line x1="9" y1="15" x2="15" y2="15"></line></svg>
            </div>
            <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--color-primary-navy); margin-bottom: 0.5rem;">Votre CV (PDF)</h3>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1.5rem;">Téléversez votre CV au format PDF pour que les recruteurs puissent le télécharger.</p>
            
            <div style="border: 2px dashed #CBD5E1; border-radius: 12px; padding: 2rem 1rem; background: #F8FAFC; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='var(--color-primary)'; this.style.background='rgba(2,132,199,0.05)'" onmouseout="this.style.borderColor='#CBD5E1'; this.style.background='#F8FAFC'">
              <input type="file" id="cv_upload" style="display: none;" accept=".pdf">
              <label for="cv_upload" style="cursor: pointer; display: flex; flex-direction: column; align-items: center;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="2" style="margin-bottom: 0.5rem;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                <span style="font-weight: 600; color: var(--color-primary-navy);">Cliquez pour ajouter un fichier</span>
                <span style="font-size: 0.8rem; color: #94A3B8; margin-top: 0.25rem;">Max 5 Mo (.pdf uniquement)</span>
              </label>
            </div>
          </div>
          
          <!-- Bloc Complétion du profil -->
          <div style="background: linear-gradient(135deg, var(--color-primary-dark), var(--color-primary)); border-radius: var(--radius-xl); padding: 1.5rem; color: white; box-shadow: 0 10px 25px rgba(2, 132, 199, 0.3);">
            <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
              Complétion du profil
            </h3>
            
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 0.5rem;">
              <span style="font-size: 2rem; font-weight: 800; line-height: 1;">80%</span>
              <span style="font-size: 0.85rem; opacity: 0.9;">Presque parfait !</span>
            </div>
            
            <div style="width: 100%; background: rgba(255,255,255,0.2); border-radius: 99px; height: 8px; margin-bottom: 1.25rem; overflow: hidden;">
              <div style="height: 100%; background: var(--color-accent); width: 80%; border-radius: 99px;"></div>
            </div>
            
            <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.9rem; opacity: 0.9; display: flex; flex-direction: column; gap: 0.5rem;">
              <li style="display: flex; align-items: center; gap: 0.5rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                Photo de profil
              </li>
              <li style="display: flex; align-items: center; gap: 0.5rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                Titre et Bio
              </li>
              <li style="display: flex; align-items: center; gap: 0.5rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                Compétences
              </li>
              <li style="display: flex; align-items: center; gap: 0.5rem; color: #FEF08A; font-weight: 500;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                CV manquant
              </li>
            </ul>
          </div>
          
        </div>
        
      </div>
    </div>
  </main>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
