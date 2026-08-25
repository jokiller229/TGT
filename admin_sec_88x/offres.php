<?php
session_start();
require_once __DIR__ . '/config/db.php';

$pageTitle = 'Modération Offres - Admin';
require_once __DIR__ . '/includes/header.php';
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $job_id = (int)$_POST['job_id'];
    if ($_POST['action'] === 'suspendre') {
        $db->prepare("UPDATE jobs SET statut = 'suspendue' WHERE id = ?")->execute([$job_id]);
        $msg = "Offre suspendue avec succès.";
    } elseif ($_POST['action'] === 'reactiver') {
        $db->prepare("UPDATE jobs SET statut = 'active' WHERE id = ?")->execute([$job_id]);
        $msg = "Offre réactivée.";
    }
}

// Fetch recent jobs
$jobs = $db->query("
    SELECT j.*, c.nom as company_nom, c.type_entite, u.email as user_email
    FROM jobs j
    JOIN companies c ON j.company_id = c.id
    JOIN users u ON c.user_id = u.id
    ORDER BY j.created_at DESC
    LIMIT 100
")->fetchAll();
?>

<div class="table-wrap">
    <div style="padding: 1.5rem; border-bottom: 1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;">
        <h2 style="font-size: 1.25rem; font-weight: 600; margin:0;">Modération des offres (100 dernières)</h2>
        <?php if (isset($msg)): ?>
            <span class="badge badge-green"><?= htmlspecialchars($msg) ?></span>
        <?php endif; ?>
    </div>
    <?php if (count($jobs) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Offre</th>
                    <th>Auteur</th>
                    <th>Détails</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jobs as $j): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($j['titre']) ?></strong><br>
                        <span class="badge badge-gray"><?= htmlspecialchars($j['categorie']) ?></span>
                    </td>
                    <td>
                        <strong><?= htmlspecialchars($j['company_nom']) ?></strong><br>
                        <span style="font-size:0.8rem; color:#64748b;"><?= $j['type_entite'] === 'entreprise' ? 'Entreprise' : 'Particulier' ?></span>
                    </td>
                    <td>
                        <span style="font-size:0.85rem;">
                            <?= htmlspecialchars($j['lieu']) ?><br>
                            <?= number_format($j['salaire_min'], 0, ',', ' ') ?> - <?= number_format($j['salaire_max'], 0, ',', ' ') ?> FCFA
                        </span>
                    </td>
                    <td><?= date('d/m/Y H:i', strtotime($j['created_at'])) ?></td>
                    <td>
                        <?php if($j['statut'] === 'ouverte' || $j['statut'] === 'active'): ?>
                            <span class="badge badge-green">ACTIVE</span>
                        <?php elseif($j['statut'] === 'suspendue'): ?>
                            <span class="badge badge-red">SUSPENDUE</span>
                        <?php else: ?>
                            <span class="badge badge-gray"><?= strtoupper(htmlspecialchars($j['statut'])) ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="job_id" value="<?= $j['id'] ?>">
                            <?php if($j['statut'] === 'ouverte' || $j['statut'] === 'active'): ?>
                                <input type="hidden" name="action" value="suspendre">
                                <button type="submit" class="btn-danger" style="padding:0.25rem 0.5rem; font-size:0.8rem;">Suspendre</button>
                            <?php elseif($j['statut'] === 'suspendue'): ?>
                                <input type="hidden" name="action" value="reactiver">
                                <button type="submit" class="btn-primary" style="padding:0.25rem 0.5rem; font-size:0.8rem; background:#10b981;">Réactiver</button>
                            <?php endif; ?>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div style="padding: 3rem; text-align: center; color: #64748b;">
            Aucune offre.
        </div>
    <?php endif; ?>
</div>

</div>
</main>
</div>
</body>
</html>
