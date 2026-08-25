<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('candidat');

$activePage = 'alertes';
$userId = $_SESSION['user_id'];
$pdo = getDB();

$message = '';
$error = '';

// Handling POST actions (Create, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'create') {
        $mots_cles = trim($_POST['mots_cles'] ?? '');
        $categorie = trim($_POST['categorie'] ?? '');
        $type_contrat = trim($_POST['type_contrat'] ?? '');

        if (empty($mots_cles) && empty($categorie) && empty($type_contrat)) {
            $error = 'Veuillez remplir au moins un critère pour votre alerte.';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO job_alerts (user_id, mots_cles, categorie, type_contrat) VALUES (?, ?, ?, ?)");
                $stmt->execute([$userId, $mots_cles, $categorie, $type_contrat]);
                $message = 'Alerte créée avec succès !';
            } catch (Exception $e) {
                $error = 'Erreur lors de la création de l\'alerte.';
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $alertId = (int)($_POST['alert_id'] ?? 0);
        if ($alertId > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM job_alerts WHERE id = ? AND user_id = ?");
                $stmt->execute([$alertId, $userId]);
                $message = 'Alerte supprimée.';
            } catch (Exception $e) {
                $error = 'Erreur lors de la suppression.';
            }
        }
    }
}

// Fetch user's alerts
$stmt = $pdo->prepare("SELECT * FROM job_alerts WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
        <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--color-primary-dark);">Alertes Emploi</h1>
        <p style="color: var(--text-muted); margin-top: 0.25rem;">Gérez vos alertes pour ne rater aucune opportunité.</p>
      </div>
      </div>
      <button class="btn-primary" style="border-radius: var(--radius-md); white-space: nowrap; padding: 0.6rem 1.2rem; font-size: 0.9rem; flex-shrink: 0;" onclick="document.getElementById('alert-modal').style.display='flex'">
        + Créer
      </button>
    </div>

    <?php if ($message): ?>
      <div style="background: #ECFDF5; color: #059669; padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; font-weight: 600; border: 1px solid #A7F3D0;">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div style="background: #FEF2F2; color: #DC2626; padding: 1rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; font-weight: 600; border: 1px solid #FECACA;">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <?php if (empty($alerts)): ?>
      <div class="dashboard-section" style="background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-xl); box-shadow: var(--shadow-sm); display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 5rem 2rem; text-align: center;">
        <div style="width: 80px; height: 80px; background: rgba(16, 185, 129, 0.1); color: #10B981; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem;">
          <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
        </div>
        <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--color-primary-dark); margin-bottom: 0.5rem;">Aucune alerte active</h2>
        <p style="color: var(--text-muted); max-width: 500px; margin-bottom: 2rem;">Créez une alerte pour recevoir un e-mail dès qu'une offre correspondant à vos critères est publiée.</p>
        <button class="btn-primary" onclick="document.getElementById('alert-modal').style.display='flex'">Créer ma première alerte</button>
      </div>
    <?php else: ?>
      <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
        <?php foreach ($alerts as $al): ?>
          <div style="background: var(--bg-surface); border: 1px solid var(--border-light); border-radius: var(--radius-xl); padding: 1.5rem; box-shadow: var(--shadow-sm); position: relative;">
            
            <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--color-primary-dark); margin-bottom: 1rem;">
              <?= !empty($al['mots_cles']) ? htmlspecialchars($al['mots_cles']) : 'Toutes offres' ?>
            </h3>
            
            <div style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 0.5rem;">
              <strong>Catégorie :</strong> <?= !empty($al['categorie']) ? htmlspecialchars($al['categorie']) : 'Toutes' ?>
            </div>
            
            <div style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 1.5rem;">
              <strong>Contrat :</strong> <?= !empty($al['type_contrat']) ? htmlspecialchars($al['type_contrat']) : 'Tous' ?>
            </div>

            <form method="POST" action="../candidat/candidat-alertes.php" onsubmit="return confirm('Voulez-vous vraiment supprimer cette alerte ?');">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="alert_id" value="<?= $al['id'] ?>">
              <button type="submit" style="background: none; border: none; color: #DC2626; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; padding: 0;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                Supprimer
              </button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>
</div>

<!-- Modal Création d'Alerte -->
<div id="alert-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
  <div style="background: white; border-radius: var(--radius-xl); width: 100%; max-width: 500px; padding: 2rem; box-shadow: 0 25px 50px rgba(0,0,0,0.25);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
      <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--color-primary-dark);">Nouvelle Alerte</h2>
      <button onclick="document.getElementById('alert-modal').style.display='none'" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #64748B;">&times;</button>
    </div>
    
    <form method="POST" action="../candidat/candidat-alertes.php">
      <input type="hidden" name="action" value="create">
      
      <div style="margin-bottom: 1.5rem;">
        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #1E293B;">Mots-clés (ex: Développeur, Chef de projet)</label>
        <input type="text" name="mots_cles" class="form-input" style="width: 100%; padding: 0.75rem; border: 1.5px solid #E2E8F0; border-radius: var(--radius-md);" placeholder="Entrez des mots-clés...">
      </div>
      
      <div style="margin-bottom: 1.5rem;">
        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #1E293B;">Catégorie</label>
        <select name="categorie" class="form-input" style="width: 100%; padding: 0.75rem; border: 1.5px solid #E2E8F0; border-radius: var(--radius-md);">
          <option value="">Toutes les catégories</option>
          <option value="Informatique & IT">Informatique & IT</option>
          <option value="Commerce & Vente">Commerce & Vente</option>
          <option value="Marketing & Com">Marketing & Com</option>
          <option value="Finance & Compta">Finance & Compta</option>
          <option value="Ressources Humaines">Ressources Humaines</option>
        </select>
      </div>

      <div style="margin-bottom: 2rem;">
        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #1E293B;">Type de contrat</label>
        <select name="type_contrat" class="form-input" style="width: 100%; padding: 0.75rem; border: 1.5px solid #E2E8F0; border-radius: var(--radius-md);">
          <option value="">Tous les contrats</option>
          <option value="CDI">CDI</option>
          <option value="CDD">CDD</option>
          <option value="Stage">Stage</option>
          <option value="Freelance">Freelance</option>
        </select>
      </div>

      <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; padding: 0.85rem; font-size: 1rem; border-radius: var(--radius-md);">
        Enregistrer l'alerte
      </button>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>




