<?php
$pageTitle = 'Inscription - TGTravail';
$hideHeader = true;
require_once __DIR__ . '/../includes/header.php';

// Si l'utilisateur est déjà connecté, on le redirige
if ($currentRole !== 'visiteur' && $currentRole !== '') {
    header("Location: ../index.php");
    exit;
}
?>

<style>
  .register-container {
    min-height: 100vh;
    display: flex;
    background: #FFFFFF;
    overflow: hidden;
  }
  .register-left {
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
    .register-left { display: flex; }
  }
  .register-right {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    position: relative;
    overflow-y: auto;
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
    width: 280px;
    height: 160px;
    transform: rotateX(15deg) rotateY(20deg) translateZ(100px);
    top: 10%;
    left: 15%;
    animation-delay: 0s;
    background: linear-gradient(135deg, rgba(37, 99, 235, 0.3) 0%, rgba(255, 255, 255, 0.02) 100%);
  }
  .card-2 {
    width: 320px;
    height: 250px;
    transform: rotateX(-15deg) rotateY(-15deg) translateZ(80px);
    bottom: 15%;
    right: 10%;
    animation-delay: -2s;
  }
  .card-3 {
    width: 120px;
    height: 120px;
    transform: rotateX(30deg) rotateY(45deg) translateZ(150px);
    top: 50%;
    left: 10%;
    animation-delay: -4s;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.3) 0%, rgba(255, 255, 255, 0.02) 100%);
  }

  @keyframes float {
    0% { margin-top: 0px; }
    50% { margin-top: -25px; }
    100% { margin-top: 0px; }
  }

  /* Form Styling */
  .register-form-box {
    padding: 2rem;
    width: 100%;
    max-width: 480px;
    position: relative;
    z-index: 10;
    margin: auto;
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

  /* Role Switcher */
  .role-switcher {
    display: grid; 
    grid-template-columns: 1fr 1fr; 
    gap: 0.5rem; 
    background: #F1F5F9; 
    padding: 0.35rem; 
    border-radius: 12px;
  }
  .role-tab {
    padding: 0.75rem; 
    border-radius: 10px; 
    font-size: 0.95rem; 
    font-weight: 700; 
    cursor: pointer; 
    border: none; 
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: transparent;
    color: #64748B;
  }
  .role-tab.active {
    background: white; 
    color: #2563EB; 
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
  }
  .radio-group label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    font-weight: 500;
    color: #475569;
  }
</style>

<div class="register-container">
  <!-- Left Side: 3D Decorative -->
  <div class="register-left">
    
    <!-- Floating Decor Elements -->
    <div class="floating-card card-1">
      <div style="display: flex; height: 100%; align-items: center; justify-content: center; font-size: 4rem;">
        🚀
      </div>
    </div>
    
    <div class="floating-card card-2">
      <div style="padding: 2.5rem; color: white;">
        <h3 style="font-size: 1.5rem; margin-bottom: 1rem; font-weight: 800; display: flex; align-items: center; gap: 0.5rem;">
          <svg width="24" height="24" fill="none" stroke="#F59E0B" stroke-width="2.5"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
          Croissance
        </h3>
        <p style="color: rgba(255,255,255,0.8); font-size: 0.95rem; line-height: 1.5;">Que vous soyez un jeune diplômé, un profil senior ou une entreprise en pleine expansion, c'est ici que tout commence.</p>
        <div style="margin-top: 2rem; height: 10px; width: 75%; background: rgba(255,255,255,0.15); border-radius: 6px;"></div>
        <div style="margin-top: 1rem; height: 10px; width: 50%; background: rgba(255,255,255,0.15); border-radius: 6px;"></div>
      </div>
    </div>

    <div class="floating-card card-3"></div>
    
    <!-- Center Hero Text -->
    <div style="position: relative; z-index: 10; text-align: center; color: white; max-width: 480px; padding: 2rem; transform: translateZ(120px);">
      <h2 style="font-size: 3.25rem; font-weight: 900; margin-bottom: 1rem; text-shadow: 0 10px 30px rgba(0,0,0,0.8); letter-spacing: -0.02em;">Rejoignez-nous</h2>
      <p style="font-size: 1.25rem; color: rgba(255,255,255,0.9); line-height: 1.6; text-shadow: 0 5px 15px rgba(0,0,0,0.5);">Prenez votre avenir en main en quelques clics.</p>
    </div>
  </div>

  <!-- Right Side: Form -->
  <div class="register-right">
    <div style="position: absolute; width: 600px; height: 600px; background: radial-gradient(circle, rgba(37,99,235,0.06) 0%, rgba(248,250,252,0) 70%); bottom: -100px; left: -100px; z-index: 0; pointer-events: none;"></div>

    <div class="register-form-box">
      <div style="margin-bottom: 2rem; text-align: center;">
        <a href="../index.php" style="display: inline-block; transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);" onmouseover="this.style.transform='scale(1.1) rotate(3deg)'" onmouseout="this.style.transform='scale(1)'">
          <img src="../img/tgtravail-logo.png" alt="TGTravail" style="width: 64px; height: auto; border-radius: 16px; box-shadow: 0 8px 20px rgba(0,0,0,0.12);">
        </a>
        <h1 style="font-size: 1.85rem; font-weight: 800; color: #0F172A; margin: 1.25rem 0 0.25rem; letter-spacing: -0.02em;">Créer un compte</h1>
        <p style="color: #64748B; font-size: 1rem;">L'inscription est gratuite et rapide</p>
      </div>

      <?php if (isset($_SESSION['auth_error'])): ?>
        <div style="background: #FEF2F2; color: #991B1B; border: 1px solid #FECACA; border-radius: 12px; padding: 1rem; margin-bottom: 1.5rem; text-align: left; font-size: 0.95rem; display: flex; gap: 0.75rem; align-items: flex-start; box-shadow: 0 4px 6px rgba(220, 38, 38, 0.05);">
          <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0; margin-top: 1px;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
          <div style="font-weight: 500; line-height: 1.5;"><?= htmlspecialchars($_SESSION['auth_error']) ?></div>
        </div>
        <?php unset($_SESSION['auth_error']); ?>
      <?php endif; ?>

      <form action="../includes/auth.php" method="POST" style="display: flex; flex-direction: column; gap: 1.25rem;">
        <input type="hidden" name="action" value="register">

        <!-- Choix du rôle -->
        <div class="role-switcher">
          <button type="button" id="tab-candidat" class="role-tab active" onclick="selectRole('candidat')">
            👤 Candidat
          </button>
          <button type="button" id="tab-recruteur" class="role-tab" onclick="selectRole('recruteur')">
            🏢 Recruteur
          </button>
        </div>
        <input type="hidden" name="role" id="register-role" value="candidat">

        <!-- Type entité (pour recruteur) -->
        <div id="group-type-entite" style="display:none;">
          <label style="display: block; font-weight: 700; color: #1E293B; margin-bottom: 0.6rem; font-size: 0.95rem;">Vous êtes :</label>
          <div class="radio-group" style="display: flex; gap: 1.5rem;">
            <label><input type="radio" name="type_entite" value="entreprise" checked onclick="updateLabels()"> Une Entreprise</label>
            <label><input type="radio" name="type_entite" value="particulier" onclick="updateLabels()"> Un Particulier</label>
          </div>
        </div>

        <div>
          <label id="label-nom" style="display: block; font-weight: 700; color: #1E293B; margin-bottom: 0.6rem; font-size: 0.95rem;" for="register-nom">Nom complet</label>
          <input type="text" id="register-nom" name="nom" class="form-input" required placeholder="John Doe">
        </div>

        <div>
          <label style="display: block; font-weight: 700; color: #1E293B; margin-bottom: 0.6rem; font-size: 0.95rem;" for="register-email">Adresse e-mail</label>
          <input type="email" id="register-email" name="email" class="form-input" required placeholder="nom@exemple.com">
        </div>

        <div id="group-telephone" style="display:none;">
          <label style="display: flex; justify-content: space-between; font-weight: 700; color: #1E293B; margin-bottom: 0.6rem; font-size: 0.95rem;" for="register-telephone">
            Numéro de téléphone
            <span style="font-size:0.8rem; color:#2563EB; font-weight: 600;">(Obligatoire)</span>
          </label>
          <input type="tel" id="register-telephone" name="telephone" class="form-input" placeholder="+228 XX XX XX XX">
        </div>

        <div>
          <label style="display: block; font-weight: 700; color: #1E293B; margin-bottom: 0.6rem; font-size: 0.95rem;" for="register-password">Mot de passe</label>
          <input type="password" id="register-password" name="password" class="form-input" required minlength="6" placeholder="Au moins 6 caractères">
        </div>

        <button type="submit" class="submit-btn" style="margin-top: 0.5rem;">
          Créer mon compte
        </button>
      </form>

      <div style="margin-top: 2.5rem; text-align: center; border-top: 1.5px solid #F1F5F9; padding-top: 1.5rem;">
        <p style="font-size: 0.95rem; color: #64748B; margin-bottom: 1rem;">
          Déjà un compte ? <a href="../auth/connexion.php" style="color: #2563EB; font-weight: 800; text-decoration: none; transition: color 0.2s; margin-left: 0.25rem;" onmouseover="this.style.color='#1D4ED8'" onmouseout="this.style.color='#2563EB'">Se connecter</a>
        </p>
        <a href="../index.php" style="font-size: 0.85rem; color: #94A3B8; text-decoration: none; display: inline-flex; align-items: center; gap: 0.25rem; transition: color 0.2s;" onmouseover="this.style.color='#475569'" onmouseout="this.style.color='#94A3B8'">
          <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
          Retour à l'accueil
        </a>
      </div>

    </div>
  </div>
</div>

<script>
function selectRole(role) {
    document.getElementById('register-role').value = role;
    
    // Reset tabs
    document.getElementById('tab-candidat').style.background = 'none';
    document.getElementById('tab-candidat').style.color = '#64748B';
    document.getElementById('tab-candidat').style.boxShadow = 'none';
    
    document.getElementById('tab-recruteur').style.background = 'none';
    document.getElementById('tab-recruteur').style.color = '#64748B';
    document.getElementById('tab-recruteur').style.boxShadow = 'none';

    // Active tab
    const activeTab = document.getElementById('tab-' + role);
    activeTab.style.background = '#fff';
    activeTab.style.color = '#2563EB';
    activeTab.style.boxShadow = '0 1px 2px rgba(0,0,0,.05)';

    const typeEntiteGrp = document.getElementById('group-type-entite');
    const telGrp = document.getElementById('group-telephone');
    
    if (role === 'recruteur') {
        typeEntiteGrp.style.display = 'block';
    } else {
        typeEntiteGrp.style.display = 'none';
        telGrp.style.display = 'none';
    }
    updateLabels();
}

function updateLabels() {
    const role = document.getElementById('register-role').value;
    const labelNom = document.getElementById('label-nom');
    const telGrp = document.getElementById('group-telephone');
    const typeEntite = document.querySelector('input[name="type_entite"]:checked').value;

    if (role === 'candidat') {
        labelNom.innerHTML = "Nom complet";
        telGrp.style.display = 'none';
    } else if (role === 'recruteur') {
        if (typeEntite === 'particulier') {
            labelNom.innerHTML = "Votre nom complet <span style='font-size:0.8em; color:#64748B;'>(Recruteur Particulier)</span>";
            telGrp.style.display = 'block';
        } else {
            labelNom.innerHTML = "Nom de l'entreprise";
            telGrp.style.display = 'none';
        }
    }
}
</script>

</body>
</html>





