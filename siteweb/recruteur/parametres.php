<?php
$activePage = 'parametres';
$pageTitle = 'Paramètres - TGTravail';
$hideHeader = true;
require_once __DIR__ . '/../includes/auth.php';
requireRole('recruteur');

$db = getDB();
$companyId = getCurrentCompanyId();
if (!$companyId) {
    header("Location: ../index.php");
    exit;
}

$user = getCurrentUser();

$successMsg = '';
$errorMsg = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
        $nom = trim($_POST['nom']);
        $email = trim($_POST['email']);
        $telephone = trim($_POST['telephone']);
        $password = $_POST['password'];

        if (empty($nom) || empty($email)) {
            $errorMsg = "Le nom et l'email sont obligatoires.";
        } else {
            try {
                if (!empty($password)) {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("UPDATE users SET nom = ?, email = ?, telephone = ?, password = ? WHERE id = ?");
                    $stmt->execute([$nom, $email, $telephone, $hashed, $user['id']]);
                } else {
                    $stmt = $db->prepare("UPDATE users SET nom = ?, email = ?, telephone = ? WHERE id = ?");
                    $stmt->execute([$nom, $email, $telephone, $user['id']]);
                }
                $successMsg = "Votre profil a été mis à jour avec succès.";
                // Refresh user data
                $user['nom'] = $nom;
                $user['email'] = $email;
                $user['telephone'] = $telephone;
                $_SESSION['user_name'] = $nom;
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $errorMsg = "Cet email est déjà utilisé par un autre compte.";
                } else {
                    $errorMsg = "Une erreur est survenue lors de la mise à jour.";
                }
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'update_company') {
        $compNom = trim($_POST['company_nom']);
        $compSecteur = trim($_POST['company_secteur']);
        $compVille = trim($_POST['company_ville']);
        $compAdresse = trim($_POST['company_adresse']);
        $compEmail = trim($_POST['company_email']);
        $compTelephone = trim($_POST['company_telephone']);
        $compDescription = trim($_POST['company_description']);

        if (empty($compNom) || empty($compSecteur) || empty($compVille)) {
            $errorMsg = "Le nom, le secteur et la ville de l'entreprise sont obligatoires.";
        } else {
            try {
                // Handle logo upload
                $logoPath = null;
                if (!empty($_FILES['company_logo']['name'])) {
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    $ftype = mime_content_type($_FILES['company_logo']['tmp_name']);
                    if (!in_array($ftype, $allowedTypes)) {
                        $errorMsg = "Format d'image invalide. Utilisez JPG, PNG ou WebP.";
                    } elseif ($_FILES['company_logo']['size'] > 2 * 1024 * 1024) {
                        $errorMsg = "L'image ne doit pas dépasser 2 Mo.";
                    } else {
                        $ext = pathinfo($_FILES['company_logo']['name'], PATHINFO_EXTENSION);
                        $logoPath = 'uploads/logos/company_' . $companyId . '_' . time() . '.' . $ext;
                        move_uploaded_file($_FILES['company_logo']['tmp_name'], __DIR__ . '/' . $logoPath);
                    }
                }

                if (empty($errorMsg)) {
                    if ($logoPath) {
                        $stmt = $db->prepare("UPDATE companies SET nom = ?, secteur = ?, ville = ?, adresse = ?, email = ?, telephone = ?, description = ?, logo = ? WHERE id = ? AND user_id = ?");
                        $stmt->execute([$compNom, $compSecteur, $compVille, $compAdresse, $compEmail, $compTelephone, $compDescription, $logoPath, $companyId, $user['id']]);
                    } else {
                        $stmt = $db->prepare("UPDATE companies SET nom = ?, secteur = ?, ville = ?, adresse = ?, email = ?, telephone = ?, description = ? WHERE id = ? AND user_id = ?");
                        $stmt->execute([$compNom, $compSecteur, $compVille, $compAdresse, $compEmail, $compTelephone, $compDescription, $companyId, $user['id']]);
                    }
                    $successMsg = "Les informations de l'entreprise ont été mises à jour avec succès.";
                    // Refresh company data
                    $stmtComp2 = $db->prepare("SELECT * FROM companies WHERE id = ?");
                    $stmtComp2->execute([$companyId]);
                    $company = $stmtComp2->fetch();
                }
            } catch (Exception $e) {
                $errorMsg = "Une erreur est survenue lors de la mise à jour de l'entreprise.";
            }
        }
    }
}

// Fetch current company data
$stmtComp = $db->prepare("SELECT * FROM companies WHERE id = ?");
$stmtComp->execute([$companyId]);
$company = $stmtComp->fetch();
$companyName = $company ? $company['nom'] : 'Mon Entreprise';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
  
  <!-- Dark Sidebar Left -->
  <?php require __DIR__ . '/../includes/recruiter_sidebar.php'; ?>

  <!-- Main Content Area Right -->
  <main class="dashboard-content-main">
    
    <!-- Top Nav / User Header -->
    <div class="dashboard-top-nav" style="margin-bottom: 2.5rem;">
      <div class="dashboard-header-left" style="display:flex; align-items:center; gap:1rem;">
        <button class="dashboard-mobile-btn" onclick="document.querySelector('.dashboard-sidebar-dark').classList.toggle('open'); event.stopPropagation();">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>
        <div>
        <h1 class="dashboard-title">Paramètres</h1>
        <p class="dashboard-subtitle" style="color: #64748B;">Gérez les informations de votre compte et de votre entreprise</p>
      </div>
      </div>

      <div style="display: flex; align-items: center; gap: 1.25rem;">
        <div class="company-selector-dropdown" style="position: relative; cursor: pointer;" onclick="this.querySelector('.dropdown-menu').classList.toggle('show')">
          <span style="color: #2563EB;">●</span>
          <span><?= htmlspecialchars($companyName ?? 'Mon Profil') ?></span>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
          
          <div class="dropdown-menu" style="position: absolute; top: 100%; right: 0; margin-top: 0.5rem; background: white; border: 1px solid #E2E8F0; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); min-width: 200px; display: none; z-index: 100; flex-direction: column;">
            <a href="../index.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem 1.25rem; color: #1e293b; text-decoration: none; font-size: 0.9rem; font-weight: 500; transition: background 0.2s;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
              Accueil du site
            </a>
            <a href="../recruteur/parametres.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem 1.25rem; color: #1e293b; text-decoration: none; font-size: 0.9rem; font-weight: 500; border-top: 1px solid #F1F5F9; transition: background 0.2s;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
              Mon profil
            </a>
          </div>
          <style>
            .company-selector-dropdown .dropdown-menu.show { display: flex !important; }
          </style>
          <script>
            document.addEventListener('click', function(e) {
              if (!e.target.closest('.company-selector-dropdown')) {
                document.querySelectorAll('.company-selector-dropdown .dropdown-menu').forEach(function(menu) {
                  menu.classList.remove('show');
                });
              }
            });
          </script>
        </div>

        <a href="../pages/notifications.php" style="position:relative; color:var(--text-muted); display:flex;" title="Notifications">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
          <span style="position:absolute; top:2px; right:2px; width:8px; height:8px; background:#3B82F6; border-radius:50%; border:2px solid #F8FAFC;"></span>
        </a>

        <div title="Logo de l'entreprise" style="display:flex;">
          <?php if (!empty($company['logo']) && file_exists(__DIR__ . '/' . $company['logo'])): ?>
            <img src="<?= htmlspecialchars($company['logo']) ?>" alt="Logo" style="width:42px; height:42px; border-radius:50%; object-fit:cover; border:2px solid #FFB800;">
          <?php else: ?>
            <div style="width:42px; height:42px; border-radius:50%; background:#081326; color:#FFB800; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.9rem; text-transform:uppercase; border:2px solid #FFB800;">
              <?= htmlspecialchars(strtoupper(substr($user['nom'], 0, 2))) ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <?php if ($successMsg): ?>
      <div class="flash-banner flash-success" style="margin-bottom: 1.5rem; padding: 1rem; border-radius: 12px; background: #DCFCE7; color: #166534; font-weight: 600;">
        <?= htmlspecialchars($successMsg) ?>
      </div>
    <?php endif; ?>

    <?php if ($errorMsg): ?>
      <div class="flash-banner flash-error" style="margin-bottom: 1.5rem; padding: 1rem; border-radius: 12px; background: #FEE2E2; color: #991B1B; font-weight: 600;">
        <?= htmlspecialchars($errorMsg) ?>
      </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
      
      <!-- User Profile Settings -->
      <div class="card" style="background: #FFF; border-radius: 24px; padding: 2rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <h3 style="font-size: 1.25rem; font-weight: 700; color: #081326; margin-bottom: 1.5rem;">Informations Personnelles</h3>
        <form method="POST" action="../recruteur/parametres.php">
          <input type="hidden" name="action" value="update_profile">
          
          <div class="form-group" style="margin-bottom: 1rem;">
            <label class="form-label" style="display:block; margin-bottom:0.5rem; font-weight:600; color:#0F172A;">Nom complet</label>
            <input type="text" name="nom" class="form-input" style="width:100%; padding:0.75rem 1rem; border:1.5px solid #E2E8F0; border-radius:12px;" value="<?= htmlspecialchars($user['nom']) ?>" required>
          </div>

          <div class="form-group" style="margin-bottom: 1rem;">
            <label class="form-label" style="display:block; margin-bottom:0.5rem; font-weight:600; color:#0F172A;">Adresse Email</label>
            <input type="email" name="email" class="form-input" style="width:100%; padding:0.75rem 1rem; border:1.5px solid #E2E8F0; border-radius:12px;" value="<?= htmlspecialchars($user['email']) ?>" required>
          </div>

          <div class="form-group" style="margin-bottom: 1rem;">
            <label class="form-label" style="display:block; margin-bottom:0.5rem; font-weight:600; color:#0F172A;">Numéro de téléphone</label>
            <input type="text" name="telephone" class="form-input" style="width:100%; padding:0.75rem 1rem; border:1.5px solid #E2E8F0; border-radius:12px;" value="<?= htmlspecialchars($user['telephone']) ?>">
          </div>

          <div class="form-group" style="margin-bottom: 1.5rem;">
            <label class="form-label" style="display:block; margin-bottom:0.5rem; font-weight:600; color:#0F172A;">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
            <input type="password" name="password" class="form-input" style="width:100%; padding:0.75rem 1rem; border:1.5px solid #E2E8F0; border-radius:12px;">
          </div>

          <button type="submit" class="btn-primary" style="width: 100%;">Mettre à jour mon profil</button>
        </form>
      </div>

      <!-- Company Profile Settings -->
      <?php if ($company['type_entite'] === 'entreprise'): ?>
      <div class="card" style="background: #FFF; border-radius: 24px; padding: 2rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <h3 style="font-size: 1.25rem; font-weight: 700; color: #081326; margin-bottom: 1.5rem;">Profil de l'Entreprise</h3>
        <form method="POST" action="../recruteur/parametres.php" enctype="multipart/form-data">
          <input type="hidden" name="action" value="update_company">

          <!-- Logo Upload -->
          <div style="margin-bottom:1.5rem; text-align:center;">
            <?php if (!empty($company['logo']) && file_exists(__DIR__ . '/' . $company['logo'])): ?>
              <img src="<?= htmlspecialchars($company['logo']) ?>" alt="Logo" style="width:80px; height:80px; border-radius:50%; object-fit:cover; border:3px solid #FFB800; margin-bottom:0.75rem; display:block; margin-left:auto; margin-right:auto;">
            <?php else: ?>
              <div style="width:80px; height:80px; border-radius:50%; background:#081326; color:#FFB800; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:1.5rem; border:3px solid #FFB800; margin:0 auto 0.75rem;">
                <?= strtoupper(substr($company['nom'] ?? 'E', 0, 2)) ?>
              </div>
            <?php endif; ?>
            <label style="cursor:pointer; font-size:0.85rem; color:#2563EB; font-weight:600;">
              📷 Changer le logo de l'entreprise
              <input type="file" name="company_logo" accept="image/*" style="display:none;" onchange="previewLogo(this)">
            </label>
            <p style="font-size:0.75rem; color:#94A3B8; margin-top:0.25rem;">JPG, PNG ou WebP · Max 2 Mo</p>
            <img id="logo-preview" src="#" alt="Aperçu" style="display:none; width:80px; height:80px; border-radius:50%; object-fit:cover; border:3px solid #FFB800; margin:0.5rem auto 0;">
          </div>
          
          <div class="form-group" style="margin-bottom: 1rem;">
            <label class="form-label" style="display:block; margin-bottom:0.5rem; font-weight:600; color:#0F172A;">Nom de l'entreprise</label>
            <input type="text" name="company_nom" class="form-input" style="width:100%; padding:0.75rem 1rem; border:1.5px solid #E2E8F0; border-radius:12px;" value="<?= htmlspecialchars($company['nom'] ?? '') ?>" required>
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <div class="form-group">
              <label class="form-label" style="display:block; margin-bottom:0.5rem; font-weight:600; color:#0F172A;">Secteur d'activité</label>
              <input type="text" name="company_secteur" class="form-input" style="width:100%; padding:0.75rem 1rem; border:1.5px solid #E2E8F0; border-radius:12px;" value="<?= htmlspecialchars($company['secteur'] ?? '') ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label" style="display:block; margin-bottom:0.5rem; font-weight:600; color:#0F172A;">Ville</label>
              <input type="text" name="company_ville" class="form-input" style="width:100%; padding:0.75rem 1rem; border:1.5px solid #E2E8F0; border-radius:12px;" value="<?= htmlspecialchars($company['ville'] ?? '') ?>" required>
            </div>
          </div>

          <div class="form-group" style="margin-bottom: 1rem;">
            <label class="form-label" style="display:block; margin-bottom:0.5rem; font-weight:600; color:#0F172A;">Adresse physique</label>
            <input type="text" name="company_adresse" class="form-input" style="width:100%; padding:0.75rem 1rem; border:1.5px solid #E2E8F0; border-radius:12px;" value="<?= htmlspecialchars($company['adresse'] ?? '') ?>">
          </div>

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <div class="form-group">
              <label class="form-label" style="display:block; margin-bottom:0.5rem; font-weight:600; color:#0F172A;">Email de contact</label>
              <input type="email" name="company_email" class="form-input" style="width:100%; padding:0.75rem 1rem; border:1.5px solid #E2E8F0; border-radius:12px;" value="<?= htmlspecialchars($company['email'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label class="form-label" style="display:block; margin-bottom:0.5rem; font-weight:600; color:#0F172A;">Téléphone entreprise</label>
              <input type="text" name="company_telephone" class="form-input" style="width:100%; padding:0.75rem 1rem; border:1.5px solid #E2E8F0; border-radius:12px;" value="<?= htmlspecialchars($company['telephone'] ?? '') ?>">
            </div>
          </div>

          <div class="form-group" style="margin-bottom: 1.5rem;">
            <label class="form-label" style="display:block; margin-bottom:0.5rem; font-weight:600; color:#0F172A;">Description de l'entreprise</label>
            <textarea name="company_description" class="form-input" style="width:100%; padding:0.75rem 1rem; border:1.5px solid #E2E8F0; border-radius:12px; min-height: 100px;"><?= htmlspecialchars($company['description'] ?? '') ?></textarea>
          </div>

          <button type="submit" class="btn-outline" style="width: 100%; border: 1.5px solid #E2E8F0; color: #0F172A; font-weight: 600;">Enregistrer l'entreprise</button>
        </form>
      </div>
      <?php else: ?>
      <div class="card" style="background: #FFF; border-radius: 24px; padding: 2rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
        <h3 style="font-size: 1.25rem; font-weight: 700; color: #081326; margin-bottom: 1.5rem;">Détails complémentaires</h3>
        <form method="POST" action="../recruteur/parametres.php">
          <input type="hidden" name="action" value="update_company">
          <input type="hidden" name="company_nom" value="<?= htmlspecialchars($company['nom'] ?? '') ?>">
          <input type="hidden" name="company_secteur" value="Autre">
          <input type="hidden" name="company_ville" value="<?= htmlspecialchars($company['ville'] ?? '') ?>">
          
          <div class="form-group" style="margin-bottom: 1rem;">
            <label class="form-label" style="display:block; margin-bottom:0.5rem; font-weight:600; color:#0F172A;">Zone de résidence</label>
            <input type="text" name="company_adresse" class="form-input" style="width:100%; padding:0.75rem 1rem; border:1.5px solid #E2E8F0; border-radius:12px;" value="<?= htmlspecialchars($company['adresse'] ?? '') ?>">
          </div>

          <div class="form-group" style="margin-bottom: 1rem;">
            <label class="form-label" style="display:block; margin-bottom:0.5rem; font-weight:600; color:#0F172A;">Téléphone</label>
            <input type="text" name="company_telephone" class="form-input" style="width:100%; padding:0.75rem 1rem; border:1.5px solid #E2E8F0; border-radius:12px;" value="<?= htmlspecialchars($company['telephone'] ?? '') ?>">
          </div>

          <div class="form-group" style="margin-bottom: 1.5rem;">
            <label class="form-label" style="display:block; margin-bottom:0.5rem; font-weight:600; color:#0F172A;">Nature de vos besoins</label>
            <textarea name="company_description" class="form-input" style="width:100%; padding:0.75rem 1rem; border:1.5px solid #E2E8F0; border-radius:12px; min-height: 100px;"><?= htmlspecialchars($company['description'] ?? '') ?></textarea>
          </div>

          <button type="submit" class="btn-outline" style="width: 100%; border: 1.5px solid #E2E8F0; color: #0F172A; font-weight: 600;">Enregistrer</button>
        </form>
      </div>
      <?php endif; ?>

    </div>

  </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
<script>
function previewLogo(input) {
  const preview = document.getElementById('logo-preview');
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      preview.src = e.target.result;
      preview.style.display = 'block';
    };
    reader.readAsDataURL(input.files[0]);
  }
}
</script>





