<?php
session_start();
require_once __DIR__ . '/config/db.php';

$pageTitle = 'Gestion des Conseils / Blog - Admin';
require_once __DIR__ . '/includes/header.php';
$db = getDB();

$msg = '';
$msgType = '';

// ─── SUPPRIMER ───────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'delete') {
        $id = (int)$_POST['conseil_id'];
        $db->prepare("DELETE FROM conseils WHERE id = ?")->execute([$id]);
        $msg = 'Article supprimé avec succès.';
        $msgType = 'green';
    }
    // ─── AJOUTER ───────────────────────────────────────────
    elseif ($_POST['action'] === 'add') {
        $titre       = trim($_POST['titre'] ?? '');
        $categorie   = trim($_POST['categorie'] ?? '');
        $contenu     = trim($_POST['contenu'] ?? '');
        $temps       = (int)($_POST['temps_lecture'] ?? 5);
        $icone       = trim($_POST['icone'] ?? '📝');

        if ($titre && $categorie && $contenu) {
            $db->prepare("INSERT INTO conseils (titre, categorie, contenu, temps_lecture, icone, created_at) VALUES (?, ?, ?, ?, ?, NOW())")
               ->execute([$titre, $categorie, $contenu, $temps, $icone]);
            $msg = 'Article publié avec succès !';
            $msgType = 'green';
        } else {
            $msg = 'Tous les champs obligatoires doivent être remplis.';
            $msgType = 'red';
        }
    }
}

// ─── LISTER ───────────────────────────────────────────────
$conseils = $db->query("SELECT * FROM conseils ORDER BY created_at DESC")->fetchAll();
?>

<!-- Header de la page -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 1.75rem; font-weight: 800; color: #0F172A; margin: 0;">Conseils / Blog</h1>
        <p style="color: #64748B; margin: 0.25rem 0 0;">Gérez les articles publiés sur la page Conseils du site.</p>
    </div>
    <button onclick="document.getElementById('modal-add').style.display='flex'" class="btn-primary" style="padding: 0.75rem 1.5rem; border-radius: 10px; font-size: 0.95rem; display: flex; align-items: center; gap: 0.5rem;">
        + Nouvel article
    </button>
</div>

<!-- Message flash -->
<?php if ($msg): ?>
    <div style="background: <?= $msgType === 'green' ? '#D1FAE5' : '#FEE2E2' ?>; color: <?= $msgType === 'green' ? '#065F46' : '#991B1B' ?>; padding: 1rem 1.5rem; border-radius: 10px; margin-bottom: 1.5rem; font-weight: 600;">
        <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<!-- Tableau des articles -->
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Icône</th>
                <th>Titre</th>
                <th>Catégorie</th>
                <th>Temps lecture</th>
                <th>Date de publication</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($conseils)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 3rem; color: #94A3B8;">
                        Aucun article publié pour le moment.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($conseils as $c): ?>
                    <tr>
                        <td style="font-size: 1.5rem;"><?= htmlspecialchars($c['icone']) ?></td>
                        <td style="font-weight: 600; color: #0F172A; max-width: 300px;">
                            <?= htmlspecialchars($c['titre']) ?>
                        </td>
                        <td>
                            <span class="badge <?= match($c['categorie']) {
                                'CV & Lettre' => 'badge-green',
                                'Entretien'   => 'badge-yellow',
                                default       => 'badge-gray'
                            } ?>"><?= htmlspecialchars($c['categorie']) ?></span>
                        </td>
                        <td><?= (int)$c['temps_lecture'] ?> min</td>
                        <td style="color: #64748B; font-size: 0.85rem;">
                            <?= date('d/m/Y à H:i', strtotime($c['created_at'])) ?>
                        </td>
                        <td>
                            <a href="../siteweb/conseil-detail.php?id=<?= $c['id'] ?>" target="_blank" class="btn-outline" style="font-size: 0.8rem; padding: 0.35rem 0.75rem; margin-right: 0.5rem;">👁 Voir</a>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Supprimer cet article ?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="conseil_id" value="<?= $c['id'] ?>">
                                <button type="submit" class="btn-danger" style="font-size: 0.8rem; padding: 0.35rem 0.75rem;">🗑 Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- MODAL : Ajouter un article -->
<div id="modal-add" class="modal-overlay" style="display: none; align-items: center; justify-content: center;">
    <div class="modal-content" style="max-width: 600px; width: 100%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 style="font-size: 1.25rem; font-weight: 800; margin: 0;">Nouvel article</h2>
            <button onclick="document.getElementById('modal-add').style.display='none'" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #94A3B8;">✕</button>
        </div>
        <form method="POST" style="display: flex; flex-direction: column; gap: 1rem;">
            <input type="hidden" name="action" value="add">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <label style="display: block; font-weight: 600; font-size: 0.875rem; margin-bottom: 0.4rem; color: #0F172A;">Icône (emoji) *</label>
                    <input type="text" name="icone" value="📝" style="width: 100%; padding: 0.7rem 1rem; border: 1.5px solid #E2E8F0; border-radius: 10px; font-size: 1.5rem; text-align: center;" maxlength="4">
                </div>
                <div>
                    <label style="display: block; font-weight: 600; font-size: 0.875rem; margin-bottom: 0.4rem; color: #0F172A;">Catégorie *</label>
                    <select name="categorie" style="width: 100%; padding: 0.7rem 1rem; border: 1.5px solid #E2E8F0; border-radius: 10px; font-size: 0.9rem; background: white;">
                        <option value="CV & Lettre">CV & Lettre</option>
                        <option value="Entretien">Entretien</option>
                        <option value="Marché de l'emploi">Marché de l'emploi</option>
                    </select>
                </div>
            </div>

            <div>
                <label style="display: block; font-weight: 600; font-size: 0.875rem; margin-bottom: 0.4rem; color: #0F172A;">Titre de l'article *</label>
                <input type="text" name="titre" placeholder="Ex: 5 conseils pour réussir votre entretien" required style="width: 100%; padding: 0.7rem 1rem; border: 1.5px solid #E2E8F0; border-radius: 10px; font-size: 0.9rem;">
            </div>

            <div>
                <label style="display: block; font-weight: 600; font-size: 0.875rem; margin-bottom: 0.4rem; color: #0F172A;">Temps de lecture (minutes)</label>
                <input type="number" name="temps_lecture" value="5" min="1" max="60" style="width: 100%; padding: 0.7rem 1rem; border: 1.5px solid #E2E8F0; border-radius: 10px; font-size: 0.9rem;">
            </div>

            <div>
                <label style="display: block; font-weight: 600; font-size: 0.875rem; margin-bottom: 0.4rem; color: #0F172A;">Contenu de l'article *</label>
                <textarea name="contenu" rows="8" placeholder="Écrivez le contenu complet de l'article ici..." required style="width: 100%; padding: 0.7rem 1rem; border: 1.5px solid #E2E8F0; border-radius: 10px; font-size: 0.9rem; font-family: inherit; resize: vertical;"></textarea>
            </div>

            <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 0.5rem;">
                <button type="button" onclick="document.getElementById('modal-add').style.display='none'" class="btn-outline">Annuler</button>
                <button type="submit" class="btn-primary" style="padding: 0.75rem 2rem;">Publier l'article</button>
            </div>
        </form>
    </div>
</div>

</main>
</div>
</body>
</html>
