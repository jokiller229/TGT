<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

$db = getDB();

// Handle Actions (Marquer comme traité, Supprimer l'offre)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'], $_POST['signalement_id'])) {
        $sigId = (int)$_POST['signalement_id'];
        
        if ($_POST['action'] === 'traiter') {
            $stmt = $db->prepare("UPDATE signalements SET statut = 'traite' WHERE id = ?");
            $stmt->execute([$sigId]);
            $msg = "Le signalement a été marqué comme traité.";
        }
        
        if ($_POST['action'] === 'supprimer_offre' && isset($_POST['job_id'])) {
            $jobId = (int)$_POST['job_id'];
            // Supprimer l'offre en base (ça va cascader et supprimer le signalement aussi si configuré ainsi, mais sinon on supprime ou met le statut)
            $stmtDel = $db->prepare("DELETE FROM jobs WHERE id = ?");
            $stmtDel->execute([$jobId]);
            $msg = "L'offre a été supprimée et tous les signalements liés ont été effacés.";
        }
    }
}

// Fetch Signalements
$sql = "
    SELECT s.*, j.titre AS job_titre, j.company_id, c.nom AS company_nom
    FROM signalements s
    JOIN jobs j ON s.job_id = j.id
    LEFT JOIN companies c ON j.company_id = c.id
    ORDER BY FIELD(s.statut, 'en attente', 'traite'), s.created_at DESC
";
$signalements = $db->query($sql)->fetchAll();
$pageTitle = 'Gestion des Signalements - Admin TGTravail';
require_once __DIR__ . '/includes/header.php';
?>


    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 2rem;">
      <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--color-primary-dark); margin:0;">Gestion des Signalements</h1>
    </div>

    <?php if (isset($msg)): ?>
        <div style="background: #ECFDF5; color: #059669; padding: 1rem; border-radius: 12px; border: 1px solid #A7F3D0; margin-bottom: 2rem; font-weight: 600;">
          <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Date</th>
            <th>Offre signalée</th>
            <th>Motif & Détails</th>
            <th>Statut</th>
            <th style="text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($signalements)): ?>
            <tr>
              <td colspan="5" style="text-align: center; padding: 3rem; color: var(--text-muted);">Aucun signalement.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($signalements as $sig): ?>
              <tr>
                <td style="white-space: nowrap; color: var(--text-muted); font-size: 0.85rem;">
                  <?= date('d/m/Y H:i', strtotime($sig['created_at'])) ?>
                </td>
                <td>
                  <strong><?= htmlspecialchars($sig['job_titre']) ?></strong><br>
                  <span style="font-size: 0.85rem; color: var(--text-muted);">Entreprise : <?= htmlspecialchars($sig['company_nom'] ?? 'Inconnue') ?></span><br>
                  <a href="../siteweb/offre-detail.php?id=<?= $sig['job_id'] ?>" target="_blank" style="font-size: 0.85rem; color: #2563EB; text-decoration: none; margin-top: 0.25rem; display: inline-block;">Voir l'offre &rarr;</a>
                </td>
                <td style="max-width: 300px;">
                  <span style="font-weight: 700; color: #EF4444; display: block; margin-bottom: 0.25rem;"><?= htmlspecialchars($sig['motif']) ?></span>
                  <p style="margin: 0; font-size: 0.85rem; color: var(--text-muted); line-height: 1.4;">
                    <?= nl2br(htmlspecialchars($sig['details'])) ?>
                  </p>
                </td>
                <td>
                  <span class="badge <?= $sig['statut'] === 'en attente' ? 'badge-yellow' : 'badge-green' ?>">
                    <?= ucfirst($sig['statut']) ?>
                  </span>
                </td>
                <td style="text-align: right; display: flex; flex-direction: column; gap: 0.5rem; align-items: flex-end;">
                  <?php if ($sig['statut'] === 'en attente'): ?>
                    <form method="POST" onsubmit="return confirm('Marquer ce signalement comme traité (l\'offre est valide) ?');">
                      <input type="hidden" name="action" value="traiter">
                      <input type="hidden" name="signalement_id" value="<?= $sig['id'] ?>">
                      <button type="submit" class="btn-primary" style="background:#10B981; width: 160px;">✅ Marquer traité</button>
                    </form>
                    
                    <form method="POST" onsubmit="return confirm('ATTENTION : Voulez-vous vraiment SUPPRIMER cette offre d\'emploi définitivement ?');">
                      <input type="hidden" name="action" value="supprimer_offre">
                      <input type="hidden" name="signalement_id" value="<?= $sig['id'] ?>">
                      <input type="hidden" name="job_id" value="<?= $sig['job_id'] ?>">
                      <button type="submit" class="btn-danger" style="width: 160px;">🗑️ Supprimer l'offre</button>
                    </form>
                  <?php else: ?>
                    <span style="font-size: 0.85rem; color: var(--text-muted); font-style: italic;">Déjà traité</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>


</main>\n</div>\n</body>
</html>
