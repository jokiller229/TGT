<?php
$activePage = 'parametres';
$pageTitle = 'Paramètres Candidat - TGTravail';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('candidat');

$db = getDB();
$userId = $_SESSION['user_id'];
$user = getCurrentUser(); // Assumes this function fetches from users table

$successMsg = '';
$errorMsg = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
        $nom = trim($_POST['nom']);
        $email = trim($_POST['email']);
        $telephone = trim($_POST['telephone']);
        $password = $_POST['password'];

        // Gestion de l'avatar
        $avatarPath = $user['avatar'];
        if (!empty($_FILES['avatar_file']['name'])) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $ftype = mime_content_type($_FILES['avatar_file']['tmp_name']);
            if (in_array($ftype, $allowedTypes) && $_FILES['avatar_file']['size'] <= 2 * 1024 * 1024) {
                $uploadDir = __DIR__ . '/../uploads/avatars/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $ext = pathinfo($_FILES['avatar_file']['name'], PATHINFO_EXTENSION);
                $newAvatarPath = 'uploads/avatars/avatar_' . $userId . '_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['avatar_file']['tmp_name'], __DIR__ . '/' . $newAvatarPath)) {
                    $avatarPath = $newAvatarPath;
                }
            } else {
                $errorMsg = "L'image doit être au format JPG/PNG/GIF et ne pas dépasser 2 Mo.";
            }
        }

        if (empty($nom) || empty($email)) {
            $errorMsg = "Le nom et l'email sont obligatoires.";
        } else {
            try {
                if (!empty($password)) {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("UPDATE users SET nom = ?, email = ?, telephone = ?, password = ?, avatar = ? WHERE id = ?");
                    $stmt->execute([$nom, $email, $telephone, $hashed, $avatarPath, $userId]);
                } else {
                    $stmt = $db->prepare("UPDATE users SET nom = ?, email = ?, telephone = ?, avatar = ? WHERE id = ?");
                    $stmt->execute([$nom, $email, $telephone, $avatarPath, $userId]);
                }
                $successMsg = "Vos paramètres de compte ont été mis à jour avec succès.";
                // Refresh user data
                $user['nom'] = $nom;
                $user['email'] = $email;
                $user['telephone'] = $telephone;
                $_SESSION['user_nom'] = $nom;
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $errorMsg = "Cet email est déjà utilisé par un autre compte.";
                } else {
                    $errorMsg = "Une erreur est survenue lors de la mise à jour.";
                }
            }
        }
    }
}

$hideHeader = true;
include __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/candidat_sidebar.php'; ?>

  <main class="dashboard-content-main">
    <div class="dashboard-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
      <div class="dashboard-header-left" style="display:flex; align-items:center; gap:1rem;">
        <button class="dashboard-mobile-btn" onclick="document.querySelector('.dashboard-sidebar-dark').classList.toggle('open'); event.stopPropagation();">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>
        <div>
        <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--color-primary-dark);">Paramètres du compte</h1>
        <p style="color: var(--text-muted); margin-top: 0.25rem;">Gérez vos identifiants et informations de connexion.</p>
      </div>
      </div>
    </div>

    <?php if ($successMsg): ?>
      <div style="background: rgba(16, 185, 129, 0.1); border-left: 4px solid #10B981; color: #047857; padding: 1rem; margin-bottom: 1.5rem; border-radius: 4px;">
        <?= htmlspecialchars($successMsg) ?>
      </div>
    <?php endif; ?>
    <?php if ($errorMsg): ?>
      <div style="background: rgba(239, 68, 68, 0.1); border-left: 4px solid #EF4444; color: #B91C1C; padding: 1rem; margin-bottom: 1.5rem; border-radius: 4px;">
        <?= htmlspecialchars($errorMsg) ?>
      </div>
    <?php endif; ?>

<?php
        // Subscription handling is now done via API (api-fedapay-success.php)
        
        $stmtProf = $db->prepare("SELECT subscription_plan, subscription_end FROM candidate_profiles WHERE user_id = ?");
        $stmtProf->execute([$userId]);
        $candidatProfile = $stmtProf->fetch();
        $subPlan = $candidatProfile['subscription_plan'] ?? 'gratuit';
        $subEnd = $candidatProfile['subscription_end'] ?? null;
        
        $isPremium = ($subPlan === 'premium' && (!$subEnd || strtotime($subEnd) > time()));
        ?>

    <!-- Tabs navigation -->
    <div style="display: flex; gap: 1rem; margin-bottom: 2rem; border-bottom: 1px solid var(--border-light);">
        <button onclick="switchTab('profil')" id="tab-profil" style="background:none; border:none; padding:0.75rem 1rem; font-weight:700; color:var(--color-primary); border-bottom:3px solid var(--color-primary); cursor:pointer;">Mon Profil</button>
        <button onclick="switchTab('abonnement')" id="tab-abonnement" style="background:none; border:none; padding:0.75rem 1rem; font-weight:600; color:var(--text-muted); border-bottom:3px solid transparent; cursor:pointer;">Abonnement Premium <span style="background:#FEF3C7; color:#D97706; font-size:0.7rem; padding:0.15rem 0.4rem; border-radius:4px; margin-left:0.5rem;">Nouveau</span></button>
    </div>

    <!-- TAB 1: Profil -->
    <div id="content-profil" class="dashboard-section" style="background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-xl); padding: 2rem; box-shadow: var(--shadow-sm); max-width: 800px;">
      <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--color-primary-dark); margin-bottom: 1.5rem;">Informations de connexion</h2>
      
      <form method="POST" action="../candidat/candidat-parametres.php" enctype="multipart/form-data">
        <input type="hidden" name="action" value="update_profile">
        
        <div style="margin-bottom: 2rem; display: flex; align-items: center; gap: 1.5rem;">
          <?php if (!empty($user['avatar']) && file_exists(__DIR__ . '/' . $user['avatar'])): ?>
            <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 2px solid var(--border-light);">
          <?php else: ?>
            <div style="width: 80px; height: 80px; border-radius: 50%; background: #0EA5E9; color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 700; border: 2px solid var(--border-light);">
              <?= htmlspecialchars(strtoupper(substr($user['nom'] ?? 'C', 0, 2))) ?>
            </div>
          <?php endif; ?>
          <div>
            <label style="display: block; font-weight: 600; color: var(--color-primary-dark); margin-bottom: 0.5rem;">Photo de profil</label>
            <input type="file" name="avatar_file" accept="image/png, image/jpeg, image/gif" style="font-size: 0.9rem;">
            <p style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem;">Format JPG, PNG ou GIF. Max 2 Mo.</p>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
          <div class="form-group">
            <label style="display: block; font-weight: 600; color: var(--color-primary-dark); margin-bottom: 0.5rem;">Nom complet</label>
            <input type="text" name="nom" class="form-control" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-light); border-radius: var(--radius-md);" value="<?= htmlspecialchars($user['nom'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label style="display: block; font-weight: 600; color: var(--color-primary-dark); margin-bottom: 0.5rem;">Adresse E-mail</label>
            <input type="email" name="email" class="form-control" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-light); border-radius: var(--radius-md);" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
          </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
          <div class="form-group">
            <label style="display: block; font-weight: 600; color: var(--color-primary-dark); margin-bottom: 0.5rem;">Numéro de téléphone (optionnel)</label>
            <input type="text" name="telephone" class="form-control" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-light); border-radius: var(--radius-md);" value="<?= htmlspecialchars($user['telephone'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label style="display: block; font-weight: 600; color: var(--color-primary-dark); margin-bottom: 0.5rem;">Nouveau mot de passe</label>
            <input type="password" name="password" class="form-control" style="width: 100%; padding: 0.75rem; border: 1px solid var(--border-light); border-radius: var(--radius-md);" placeholder="Laisser vide pour ne pas modifier">
            <span style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem; display: block;">Remplissez uniquement si vous souhaitez changer.</span>
          </div>
        </div>

        <div style="text-align: right;">
          <button type="submit" class="btn-primary" style="padding: 0.75rem 2rem; border-radius: var(--radius-md);">Enregistrer les modifications</button>
        </div>
      </form>
    </div>

    <!-- TAB 2: Abonnement -->
    <div id="content-abonnement" style="display: none; max-width: 900px;">
        <div style="background: linear-gradient(135deg, #1E3A8A 0%, #3B82F6 100%); border-radius: 20px; padding: 2.5rem; color: white; display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem; box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3);">
            <div>
                <h2 style="font-size: 1.75rem; font-weight: 800; margin: 0 0 0.5rem 0;">Passez à la vitesse supérieure 🚀</h2>
                <p style="margin: 0; opacity: 0.9; font-size: 1.05rem;">Démarquez-vous des autres candidats et décrochez le poste de vos rêves plus rapidement.</p>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; opacity: 0.8;">Votre statut</div>
                <?php if ($isPremium): ?>
                    <div style="font-size: 1.5rem; font-weight: 800; color: #FCD34D;">Premium</div>
                    <div style="font-size: 0.85rem; opacity: 0.9;">Valable jusqu'au <?= date('d/m/Y', strtotime($subEnd)) ?></div>
                <?php else: ?>
                    <div style="font-size: 1.5rem; font-weight: 800;">Gratuit</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Plans -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
            <!-- Plan Gratuit -->
            <div style="background: white; border: 2px solid #E2E8F0; border-radius: 16px; padding: 2rem;">
                <h3 style="font-size: 1.25rem; font-weight: 700; color: #0F172A; margin: 0 0 1rem 0;">Candidat Standard</h3>
                <div style="font-size: 2.5rem; font-weight: 800; color: #0F172A; margin-bottom: 1.5rem;">0 F<span style="font-size: 1rem; color: #64748B; font-weight: 500;">/mois</span></div>
                <ul style="list-style: none; padding: 0; margin: 0 0 2rem 0; color: #475569;">
                    <li style="margin-bottom: 0.75rem; display:flex; align-items:center; gap:0.5rem;">✅ Création de profil</li>
                    <li style="margin-bottom: 0.75rem; display:flex; align-items:center; gap:0.5rem;">✅ Postuler aux offres</li>
                    <li style="margin-bottom: 0.75rem; display:flex; align-items:center; gap:0.5rem;">❌ Profil Top CVthèque</li>
                    <li style="margin-bottom: 0.75rem; display:flex; align-items:center; gap:0.5rem;">❌ Badge Profil Vérifié</li>
                    <li style="margin-bottom: 0.75rem; display:flex; align-items:center; gap:0.5rem;">❌ Générateur CV sans filigrane</li>
                </ul>
                <button disabled style="width:100%; padding: 0.75rem; border-radius: 8px; border: 1px solid #CBD5E1; background: #F1F5F9; color: #64748B; font-weight: 600;">Plan actuel</button>
            </div>

            <!-- Plan Premium -->
            <div style="background: white; border: 2px solid #3B82F6; border-radius: 16px; padding: 2rem; position: relative; box-shadow: 0 20px 25px -5px rgba(59, 130, 246, 0.15);">
                <div style="position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: #3B82F6; color: white; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; padding: 0.25rem 1rem; border-radius: 20px;">Recommandé</div>
                <h3 style="font-size: 1.25rem; font-weight: 700; color: #0F172A; margin: 0 0 1rem 0;">Candidat Premium</h3>
                <div style="font-size: 2.5rem; font-weight: 800; color: #0F172A; margin-bottom: 1.5rem;">2 000 F<span style="font-size: 1rem; color: #64748B; font-weight: 500;">/mois</span></div>
                <ul style="list-style: none; padding: 0; margin: 0 0 2rem 0; color: #475569;">
                    <li style="margin-bottom: 0.75rem; display:flex; align-items:center; gap:0.5rem; font-weight: 600; color:#0F172A;">⭐ Profil Top CVthèque (Vue N°1)</li>
                    <li style="margin-bottom: 0.75rem; display:flex; align-items:center; gap:0.5rem; font-weight: 600; color:#0F172A;">⭐ Badge Profil Vérifié & Pro</li>
                    <li style="margin-bottom: 0.75rem; display:flex; align-items:center; gap:0.5rem; font-weight: 600; color:#0F172A;">⭐ Alertes Emploi Prioritaires</li>
                    <li style="margin-bottom: 0.75rem; display:flex; align-items:center; gap:0.5rem; font-weight: 600; color:#0F172A;">⭐ Générateur CV Premium (PDF)</li>
                </ul>
                <form method="POST" action="javascript:void(0);" onsubmit="return startFedaPayCandidat(this);">
                    <input type="hidden" name="action" value="subscribe_premium">
                    <select name="months" id="candidat_months" style="width:100%; padding:0.75rem; border:1px solid #CBD5E1; border-radius:8px; margin-bottom:1rem; font-weight:500;">
                        <option value="1">1 Mois (2 000 FCFA)</option>
                        <option value="3">3 Mois (5 000 FCFA) - Économisez 1000F</option>
                        <option value="6">6 Mois (9 000 FCFA) - Économisez 3000F</option>
                    </select>
                    <button type="submit" style="width:100%; padding: 0.75rem; border-radius: 8px; border: none; background: #3B82F6; color: white; font-weight: 600; cursor: pointer; transition: background 0.2s;">S'abonner maintenant</button>
                </form>
            </div>
        </div>
    </div>
  </main>
</div>

<script src="https://cdn.fedapay.com/checkout.js?v=1.1.7"></script>
<script>
function switchTab(tabId) {
    // Hide all
    document.getElementById('content-profil').style.display = 'none';
    document.getElementById('content-abonnement').style.display = 'none';
    
    // Reset tabs
    document.getElementById('tab-profil').style.borderBottomColor = 'transparent';
    document.getElementById('tab-profil').style.color = 'var(--text-muted)';
    document.getElementById('tab-abonnement').style.borderBottomColor = 'transparent';
    document.getElementById('tab-abonnement').style.color = 'var(--text-muted)';
    
    // Show active
    document.getElementById('content-' + tabId).style.display = 'block';
    const activeBtn = document.getElementById('tab-' + tabId);
    activeBtn.style.borderBottomColor = 'var(--color-primary)';
    activeBtn.style.color = 'var(--color-primary)';
}

function startFedaPayCandidat(form) {
    const months = document.getElementById('candidat_months').value;
    let amount = 2000;
    if (months == 3) amount = 5000;
    if (months == 6) amount = 9000;

    let widget = FedaPay.init({
        public_key: 'pk_live_aAUfRsADSFFOgUQFEWoH9sG0',
        transaction: {
            amount: amount,
            description: 'Abonnement Candidat Premium - ' + months + ' Mois',
        },
        customer: {
            email: '<?= addslashes($user['email'] ?? '') ?>',
            lastname: '<?= addslashes($user['nom'] ?? '') ?>'
        },
        onComplete: function(resp) {
            const reason = resp.reason || resp.status;
            if (reason === 'CHECKOUT COMPLETE' || reason === 'approved') {
                const txId = resp.transaction ? resp.transaction.id : 'unknown';
                fetch('../api/api-fedapay-success.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        transaction_id: txId,
                        action: 'subscribe_candidat',
                        months: months
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.ok) {
                        alert(data.msg);
                        window.location.reload();
                    } else {
                        alert("Erreur: " + data.msg);
                    }
                });
            }
        }
    });
    
    widget.open();
    return false;
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>





