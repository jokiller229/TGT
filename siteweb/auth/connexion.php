<?php
$pageTitle = 'Connexion - TGTravail';
$hideHeader = true; // Optionnel : pour avoir une page plus clean
require_once __DIR__ . '/../includes/header.php';

// Si l'utilisateur est déjà connecté, on le redirige
if ($currentRole !== 'visiteur' && $currentRole !== '') {
    if ($currentRole === 'candidat') header("Location: ../candidat/candidat-dashboard.php");
    elseif ($currentRole === 'recruteur') header("Location: ../recruteur/recruteur-dashboard.php");
    elseif ($currentRole === 'admin') header("Location: ../admin/admin-dashboard.php");
    else header("Location: ../index.php");
    exit;
}
?>

<style>
  .login-container {
    min-height: 100vh;
    display: flex;
    background: #F8FAFC;
    overflow: hidden;
  }
  .login-left {
    display: none;
    flex: 1;
    background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
    position: relative;
    overflow: hidden;
    align-items: center;
    justify-content: center;
    perspective: 1200px;
  }
  @media (min-width: 1024px) {
    .login-left { display: flex; }
  }
  .login-right {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    position: relative;
  }
  
  /* 3D Floating Elements */
  .floating-card {
    position: absolute;
    border-radius: 24px;
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.5);
    animation: float 6s ease-in-out infinite;
    transform-style: preserve-3d;
  }
  .card-1 {
    width: 340px;
    height: 220px;
    transform: rotateX(15deg) rotateY(-20deg) translateZ(80px);
    top: 15%;
    left: 10%;
    animation-delay: 0s;
  }
  .card-2 {
    width: 260px;
    height: 320px;
    transform: rotateX(-10deg) rotateY(15deg) translateZ(120px);
    bottom: 10%;
    right: 8%;
    animation-delay: -2s;
    background: linear-gradient(135deg, rgba(37, 99, 235, 0.2) 0%, rgba(255, 255, 255, 0.02) 100%);
  }
  .card-3 {
    width: 180px;
    height: 180px;
    transform: rotateX(25deg) rotateY(35deg) translateZ(200px);
    top: 35%;
    right: 15%;
    animation-delay: -4s;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(217, 119, 6, 0.3) 0%, rgba(255, 255, 255, 0.02) 100%);
  }

  @keyframes float {
    0% { margin-top: 0px; }
    50% { margin-top: -25px; }
    100% { margin-top: 0px; }
  }

  /* Form Styling */
  .login-form-box {
    background: white;
    border-radius: 24px;
    padding: 3rem;
    width: 100%;
    max-width: 440px;
    box-shadow: 0 20px 40px -10px rgba(0,0,0,0.1), 0 0 0 1px rgba(0,0,0,0.02);
    position: relative;
    z-index: 10;
  }
  .form-input {
    width: 100%;
    padding: 0.9rem 1.2rem;
    border: 2px solid #E2E8F0;
    border-radius: 12px;
    outline: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-size: 1rem;
    background: #F8FAFC;
    color: #0F172A;
  }
  .form-input:focus {
    border-color: #2563EB;
    background: white;
    box-shadow: 0 4px 15px rgba(37, 99, 235, 0.15);
    transform: translateY(-2px);
  }
  .submit-btn {
    width: 100%;
    background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%);
    color: white;
    border: none;
    padding: 1.1rem;
    border-radius: 12px;
    font-weight: 700;
    font-size: 1.05rem;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
  }
  .submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(37, 99, 235, 0.4);
  }
</style>

<div class="login-container">
  <!-- Left Side: 3D Decorative -->
  <div class="login-left">
    
    <!-- Floating Decor Elements -->
    <div class="floating-card card-1">
      <div style="padding: 2.5rem; color: white;">
        <h3 style="font-size: 1.5rem; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem; font-weight: 800;">
          <svg width="24" height="24" fill="none" stroke="#60A5FA" stroke-width="2.5"><path d="M21 13V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h8m6-7-2-2m2 2 2-2m-2 2v6"/></svg>
          Opportunités
        </h3>
        <p style="color: rgba(255,255,255,0.8); font-size: 1rem; line-height: 1.5;">Des milliers d'offres d'emploi au Togo n'attendent que vous pour décoller.</p>
      </div>
    </div>
    
    <div class="floating-card card-2">
      <div style="padding: 2.5rem; color: white;">
        <h3 style="font-size: 1.5rem; margin-bottom: 1rem; font-weight: 800;">Recrutement VIP</h3>
        <p style="color: rgba(255,255,255,0.8); font-size: 0.95rem; line-height: 1.5;">Accédez aux meilleurs talents pour votre entreprise grâce à notre CVthèque premium.</p>
        <div style="margin-top: 2rem; height: 10px; width: 65%; background: rgba(255,255,255,0.15); border-radius: 6px;"></div>
        <div style="margin-top: 1rem; height: 10px; width: 45%; background: rgba(255,255,255,0.15); border-radius: 6px;"></div>
        <div style="margin-top: 1rem; height: 10px; width: 85%; background: rgba(255,255,255,0.15); border-radius: 6px;"></div>
      </div>
    </div>

    <div class="floating-card card-3">
      <div style="display: flex; height: 100%; align-items: center; justify-content: center;">
        <svg width="64" height="64" fill="none" stroke="rgba(255,255,255,0.8)" stroke-width="1.5"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
      </div>
    </div>
    
    <!-- Center Hero Text -->
    <div style="position: relative; z-index: 10; text-align: center; color: white; max-width: 480px; padding: 2rem; transform: translateZ(100px);">
      <h2 style="font-size: 3.5rem; font-weight: 900; margin-bottom: 1rem; text-shadow: 0 10px 30px rgba(0,0,0,0.8); letter-spacing: -0.02em;">TGTravail</h2>
      <p style="font-size: 1.25rem; color: rgba(255,255,255,0.9); line-height: 1.6; text-shadow: 0 5px 15px rgba(0,0,0,0.5);">L'écosystème nouvelle génération pour propulser votre carrière ou développer vos équipes.</p>
    </div>
  </div>

  <!-- Right Side: Form -->
  <div class="login-right">
    <!-- Subtle background blob -->
    <div style="position: absolute; width: 600px; height: 600px; background: radial-gradient(circle, rgba(37,99,235,0.06) 0%, rgba(248,250,252,0) 70%); top: -100px; right: -100px; z-index: 0; pointer-events: none;"></div>

    <div class="login-form-box">
      <div style="margin-bottom: 2.5rem; text-align: center;">
        <a href="../index.php" style="display: inline-block; transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);" onmouseover="this.style.transform='scale(1.1) rotate(-3deg)'" onmouseout="this.style.transform='scale(1)'">
          <img src="../img/tgtravail-logo.png" alt="TGTravail" style="width: 64px; height: auto; border-radius: 16px; box-shadow: 0 8px 20px rgba(0,0,0,0.12);">
        </a>
        <h1 style="font-size: 2rem; font-weight: 800; color: #0F172A; margin: 1.5rem 0 0.5rem; letter-spacing: -0.02em;">Bon retour !</h1>
        <p style="color: #64748B; font-size: 1.05rem;">Connectez-vous pour continuer</p>
      </div>

      <?php if (isset($_SESSION['auth_error'])): ?>
        <div style="background: #FEF2F2; color: #991B1B; border: 1px solid #FECACA; border-radius: 12px; padding: 1.25rem; margin-bottom: 2rem; font-size: 0.95rem; display: flex; gap: 0.75rem; align-items: flex-start; box-shadow: 0 4px 6px rgba(220, 38, 38, 0.05);">
          <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0; margin-top: 1px;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
          <div style="font-weight: 500; line-height: 1.5;"><?= htmlspecialchars($_SESSION['auth_error']) ?></div>
        </div>
        <?php unset($_SESSION['auth_error']); ?>
      <?php endif; ?>

      <form action="../includes/auth.php" method="POST" style="display: flex; flex-direction: column; gap: 1.5rem;">
        <input type="hidden" name="action" value="login">
        
        <div>
          <label style="display: block; font-weight: 700; color: #1E293B; margin-bottom: 0.6rem; font-size: 0.95rem;" for="login-email">Adresse e-mail</label>
          <input type="email" id="login-email" name="email" class="form-input" required placeholder="nom@exemple.com">
        </div>

        <div>
          <label style="display: flex; justify-content: space-between; align-items: center; font-weight: 700; color: #1E293B; margin-bottom: 0.6rem; font-size: 0.95rem;" for="login-password">
            Mot de passe
            <a href="#" style="font-size: 0.85rem; color: #2563EB; text-decoration: none; font-weight: 600;">Oublié ?</a>
          </label>
          <input type="password" id="login-password" name="password" class="form-input" required placeholder="••••••••">
        </div>

        <button type="submit" class="submit-btn" style="margin-top: 1rem;">
          Se connecter
        </button>
      </form>

      <div style="margin-top: 3rem; text-align: center; border-top: 1.5px solid #F1F5F9; padding-top: 2rem;">
        <p style="font-size: 1rem; color: #64748B; margin-bottom: 1.25rem;">
          Nouveau sur TGTravail ? <br class="mobile-break" style="display:none;"><a href="../auth/inscription.php" style="color: #2563EB; font-weight: 800; text-decoration: none; transition: color 0.2s; margin-left: 0.25rem;" onmouseover="this.style.color='#1D4ED8'" onmouseout="this.style.color='#2563EB'">Créer un compte</a>
        </p>
        <a href="../index.php" style="font-size: 0.9rem; color: #94A3B8; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem; transition: color 0.2s;" onmouseover="this.style.color='#475569'" onmouseout="this.style.color='#94A3B8'">
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
          Retour à l'accueil
        </a>
      </div>
    </div>
  </div>
</div>

</body>
</html>





