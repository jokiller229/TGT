<?php
session_start();
require_once __DIR__ . '/config/db.php';

$pageTitle = 'Modération Recruteurs - Admin';
require_once __DIR__ . '/includes/header.php';
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $company_id = (int)$_POST['company_id'];
    if ($_POST['action'] === 'approuver') {
        $db->prepare("UPDATE companies SET verifie = 1, statut_validation = 'valide' WHERE id = ?")->execute([$company_id]);
        $msg = "Dossier approuvé.";
    } elseif ($_POST['action'] === 'rejeter') {
        $motif = trim($_POST['motif'] ?? '');
        $db->prepare("UPDATE companies SET statut_validation = 'rejete', motif_rejet = ? WHERE id = ?")->execute([$motif, $company_id]);
        $msg = "Dossier rejeté.";
    }
}

$pendingCompanies = $db->query("
    SELECT c.*, u.email, u.nom as user_nom, u.telephone
    FROM companies c 
    JOIN users u ON c.user_id = u.id 
    WHERE c.statut_validation = 'en_attente'
    ORDER BY c.created_at ASC
")->fetchAll();
?>

<div class="table-wrap">
    <div style="padding: 1.5rem; border-bottom: 1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;">
        <h2 style="font-size: 1.25rem; font-weight: 600; margin:0;">Recruteurs en attente de vérification</h2>
        <?php if (isset($msg)): ?>
            <span class="badge badge-green"><?= htmlspecialchars($msg) ?></span>
        <?php endif; ?>
    </div>
    <?php if (count($pendingCompanies) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Entité</th>
                    <th>Contact</th>
                    <th>Document légal</th>
                    <th>Infos</th>
                    <th>Date d'inscription</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pendingCompanies as $c): ?>
                <tr>
                    <td>
                        <?php if($c['type_entite'] === 'entreprise'): ?>
                            <span class="badge badge-gray">ENTREPRISE</span><br>
                        <?php else: ?>
                            <span class="badge badge-green" style="background:#dbeafe; color:#1e3a8a;">PARTICULIER</span><br>
                        <?php endif; ?>
                        <strong><?= htmlspecialchars($c['nom']) ?></strong><br>
                        <span style="font-size:0.8rem; color:#64748b;"><?= htmlspecialchars($c['secteur']) ?> - <?= htmlspecialchars($c['ville']) ?></span>
                    </td>
                    <td>
                        <?= htmlspecialchars($c['user_nom']) ?><br>
                        <span style="font-size:0.8rem; color:#64748b;"><?= htmlspecialchars($c['email']) ?></span>
                        <?php if(!empty($c['telephone'])): ?>
                            <br><span style="font-size:0.8rem; color:#64748b;"><?= htmlspecialchars($c['telephone']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($c['type_entite'] === 'entreprise'): ?>
                            <?= htmlspecialchars($c['rccm'] ?: 'Non renseigné') ?>
                        <?php else: ?>
                            <?php if(!empty($c['cni_document'])): ?>
                                <a href="#" onclick="openDocumentModal('../siteweb/<?= htmlspecialchars($c['cni_document']) ?>'); return false;" style="color: #2563EB; font-weight: 600;">Voir Pièce d'Identité</a>
                            <?php else: ?>
                                <span style="color:#ef4444;">Non renseignée</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($c['type_entite'] === 'entreprise' && !empty($c['site_web'])): ?>
                            <a href="<?= htmlspecialchars($c['site_web']) ?>" target="_blank" style="color: #2563EB;">Site Web</a>
                        <?php elseif($c['type_entite'] === 'particulier' && !empty($c['adresse'])): ?>
                            <span style="font-size:0.85rem;"><?= htmlspecialchars($c['adresse']) ?></span>
                        <?php else: ?>
                            <span style="color:#94a3b8;">-</span>
                        <?php endif; ?>
                    </td>
                    <td><?= date('d/m/Y', strtotime($c['created_at'])) ?></td>
                    <td>
                        <div style="display: flex; gap: 0.5rem;">
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="company_id" value="<?= $c['id'] ?>">
                                <input type="hidden" name="action" value="approuver">
                                <?php if( ($c['type_entite'] === 'entreprise' && !empty($c['rccm'])) || ($c['type_entite'] === 'particulier' && !empty($c['cni_document'])) ): ?>
                                    <button type="submit" class="btn-primary" style="background:#10b981;">Approuver</button>
                                <?php else: ?>
                                    <button type="button" class="btn-outline" disabled title="Dossier incomplet">Incomplet</button>
                                <?php endif; ?>
                            </form>
                            <button type="button" class="btn-danger" onclick="openRejectModal(<?= $c['id'] ?>, '<?= htmlspecialchars(addslashes($c['nom'])) ?>')">Rejeter</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div style="padding: 3rem; text-align: center; color: #64748b;">
            Aucun dossier en attente.
        </div>
    <?php endif; ?>
</div>

<!-- Modal Reject -->
<div id="reject-modal" class="modal-overlay">
    <div class="modal-content">
        <h3 style="margin-top:0; font-size:1.25rem;">Rejeter le dossier : <span id="reject-company-name"></span></h3>
        <form method="POST">
            <input type="hidden" name="action" value="rejeter">
            <input type="hidden" name="company_id" id="reject-company-id">
            <div style="margin-bottom:1rem;">
                <label style="display:block; font-weight:600; margin-bottom:0.5rem;">Motif du rejet :</label>
                <textarea name="motif" rows="4" style="width:100%; padding:0.5rem; border:1px solid #cbd5e1; border-radius:6px;" required placeholder="Ex: CNI illisible, NIF invalide..."></textarea>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:1rem;">
                <button type="button" class="btn-outline" onclick="closeRejectModal()">Annuler</button>
                <button type="submit" class="btn-danger">Confirmer le rejet</button>
            </div>
        </form>
    </div>
</div>

<script>
function openRejectModal(id, nom) {
    document.getElementById('reject-company-id').value = id;
    document.getElementById('reject-company-name').textContent = nom;
    document.getElementById('reject-modal').style.display = 'flex';
}
function closeRejectModal() {
    document.getElementById('reject-modal').style.display = 'none';
}

function openDocumentModal(url) {
    const container = document.getElementById('document-container');
    container.innerHTML = ''; // clear

    // check if it's an image or pdf
    if (url.toLowerCase().endsWith('.pdf')) {
        container.innerHTML = `<iframe src="${url}" width="100%" height="100%" style="border:none;"></iframe>`;
    } else {
        container.innerHTML = `<img src="${url}" style="max-width:100%; max-height:100%; object-fit:contain; display:block; margin: 0 auto; padding: 1rem;" />`;
    }
    
    document.getElementById('document-modal').style.display = 'flex';
}
function closeDocumentModal() {
    document.getElementById('document-modal').style.display = 'none';
    document.getElementById('document-container').innerHTML = ''; // clear memory
}
</script>

<!-- Modal Document Viewer -->
<div id="document-modal" class="modal-overlay">
    <div class="modal-content" style="max-width: 800px; height: 80vh; display: flex; flex-direction: column;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3 style="margin: 0; font-size: 1.25rem;">Visionneuse de document</h3>
            <button type="button" class="btn-outline" onclick="closeDocumentModal()" style="padding: 0.25rem 0.5rem; border: none; font-size: 1.5rem; line-height: 1;">&times;</button>
        </div>
        <div id="document-container" style="flex-grow: 1; border: 1px solid #e2e8f0; border-radius: 8px; overflow: auto; background: #f1f5f9; display: flex; justify-content: center; align-items: center;">
            <!-- Document will be injected here -->
        </div>
    </div>
</div>

</div>
</main>
</div>
</body>
</html>
