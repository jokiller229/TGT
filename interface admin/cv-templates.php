<?php
session_start();
require_once __DIR__ . '/../siteweb/config/db.php';
$pdo = getDB();

// Redirect if not admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../siteweb/auth/connexion.php");
    exit;
}

$action = $_GET['action'] ?? 'list';
$error = '';
$success = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_template'])) {
        $id = $_POST['id'] ?? '';
        $name = trim($_POST['name']);
        $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($name));
        $html_content = $_POST['html_content'];
        $css_content = $_POST['css_content'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        if (empty($name) || empty($html_content)) {
            $error = 'Le nom et le contenu HTML sont requis.';
        } else {
            if ($id) {
                // Update
                $stmt = $pdo->prepare("UPDATE cv_templates SET name=?, slug=?, html_content=?, css_content=?, is_active=? WHERE id=?");
                $stmt->execute([$name, $slug, $html_content, $css_content, $is_active, $id]);
                $success = "Modèle mis à jour.";
            } else {
                // Insert
                try {
                    $stmt = $pdo->prepare("INSERT INTO cv_templates (name, slug, html_content, css_content, is_active) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $slug, $html_content, $css_content, $is_active]);
                    $success = "Nouveau modèle créé.";
                } catch(PDOException $e) {
                    $error = "Erreur: Ce nom (ou un nom similaire) existe peut-être déjà.";
                }
            }
            if(empty($error)) $action = 'list';
        }
    } elseif (isset($_POST['delete_template'])) {
        $id = $_POST['id'];
        $pdo->prepare("DELETE FROM cv_templates WHERE id = ?")->execute([$id]);
        $success = "Modèle supprimé.";
        $action = 'list';
    }
}

$templateToEdit = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM cv_templates WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $templateToEdit = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modèles de CV - Administration</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../siteweb/css/style.css">
    <style>
        .template-card { background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center; }
        .template-actions { display: flex; gap: 0.5rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
        .form-group input[type="text"] { width: 100%; padding: 0.75rem; border: 1px solid #ccc; border-radius: 4px; }
        .form-group textarea { width: 100%; padding: 0.75rem; border: 1px solid #ccc; border-radius: 4px; height: 300px; font-family: monospace; font-size: 13px; }
    </style>
</head>
<body class="dashboard-body">

<?php include __DIR__ . '/includes/sidebar.php'; ?>
<?php include __DIR__ . '/includes/header.php'; ?>

<main class="dashboard-content">
    <div class="dashboard-header" style="margin-bottom: 2rem;">
        <div>
            <h1 style="font-size: 1.75rem; font-weight: 800; color: #1E293B;">Gestion des Modèles de CV</h1>
            <p style="color: #64748B;">Créez et modifiez les modèles de CV disponibles pour les candidats.</p>
        </div>
        <?php if($action === 'list'): ?>
            <a href="cv-templates.php?action=create" class="btn-primary">+ Nouveau Modèle</a>
        <?php else: ?>
            <a href="cv-templates.php" class="btn-outline">Retour à la liste</a>
        <?php endif; ?>
    </div>
    
    <?php if ($error): ?><div style="background: #FEE2E2; color: #B91C1C; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div style="background: #D1FAE5; color: #065F46; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;"><?= htmlspecialchars($success) ?></div><?php endif; ?>

    <?php if ($action === 'list'): ?>
        <div class="templates-list">
            <?php
            $templates = $pdo->query("SELECT * FROM cv_templates ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
            if (empty($templates)): ?>
                <p>Aucun modèle de CV trouvé.</p>
            <?php else: ?>
                <?php foreach($templates as $tpl): ?>
                    <div class="template-card">
                        <div>
                            <h3 style="margin:0; font-size: 1.1rem; color: #0F172A;"><?= htmlspecialchars($tpl['name']) ?></h3>
                            <span style="font-size: 0.85rem; color: #64748B;">Statut: <?= $tpl['is_active'] ? '<span style="color:green;">Actif</span>' : '<span style="color:red;">Inactif</span>' ?></span>
                        </div>
                        <div class="template-actions">
                            <a href="cv-templates.php?action=edit&id=<?= $tpl['id'] ?>" class="btn-outline" style="padding: 0.5rem 1rem;">Modifier</a>
                            <form method="POST" onsubmit="return confirm('Vraiment supprimer ce modèle ?');" style="margin:0;">
                                <input type="hidden" name="id" value="<?= $tpl['id'] ?>">
                                <button type="submit" name="delete_template" class="btn-outline" style="padding: 0.5rem 1rem; color: red; border-color: red;">Supprimer</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <div style="margin-top: 2rem; background: #FFF; padding: 1.5rem; border-radius: 8px; border-left: 4px solid var(--color-primary-blue);">
            <h3>💡 Comment fonctionnent les modèles dynamiques ?</h3>
            <p>Les modèles utilisent des variables (placeholders) qui sont automatiquement remplacées par les données du candidat. Voici la liste des variables disponibles :</p>
            <ul style="line-height: 1.6;">
                <li><code>{{nom}}</code> : Prénom et Nom</li>
                <li><code>{{titre}}</code> : Titre professionnel</li>
                <li><code>{{email}}</code>, <code>{{telephone}}</code>, <code>{{ville}}</code> : Coordonnées</li>
                <li><code>{{bio}}</code> : Biographie détaillée</li>
                <li><code>{{experience}}</code>, <code>{{contrat}}</code>, <code>{{disponibilite}}</code> : Préférences de poste</li>
                <li><code>{{avatar_html}}</code> : Balise image de profil ou initiales</li>
                <li><code>{{competences_html_badges}}</code> : Compétences formatées en badges</li>
                <li><code>{{competences_html_bullets}}</code> : Compétences formatées avec bordure (classique)</li>
                <li><code>{{competences_html_tags}}</code> : Compétences formatées en fond noir (minimaliste)</li>
            </ul>
            <p><strong>N'oubliez pas !</strong> Pour que le glisser-déposer fonctionne, entourez les blocs de la classe <code>draggable-block</code>, et pour l'édition de texte, ajoutez l'attribut <code>contenteditable="true"</code>.</p>
        </div>

    <?php elseif ($action === 'create' || $action === 'edit'): ?>
        <form method="POST" action="cv-templates.php" style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
            <input type="hidden" name="id" value="<?= $templateToEdit ? $templateToEdit['id'] : '' ?>">
            
            <div class="form-group">
                <label>Nom du modèle (ex: Créatif, Tech, Cadre Supérieur...)</label>
                <input type="text" name="name" value="<?= htmlspecialchars($templateToEdit['name'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label>Code CSS</label>
                <textarea name="css_content" placeholder=".tpl-monmodele { ... }"><?= htmlspecialchars($templateToEdit['css_content'] ?? '') ?></textarea>
                <small style="color: #64748B;">Tous les styles doivent idéalement commencer par la classe liée à votre modèle (ex: .tpl-monmodele) pour ne pas casser le reste du site.</small>
            </div>
            
            <div class="form-group">
                <label>Code HTML</label>
                <textarea name="html_content" required placeholder="<div class='draggable-block'>...</div>"><?= htmlspecialchars($templateToEdit['html_content'] ?? '') ?></textarea>
            </div>
            
            <div class="form-group" style="display:flex; align-items:center; gap:0.5rem;">
                <input type="checkbox" name="is_active" id="is_active" <?= (!$templateToEdit || $templateToEdit['is_active']) ? 'checked' : '' ?>>
                <label for="is_active" style="margin:0;">Activer ce modèle pour les candidats</label>
            </div>
            
            <button type="submit" name="save_template" class="btn-primary" style="margin-top: 1rem;">Enregistrer le modèle</button>
        </form>
    <?php endif; ?>

</main>
<script>
    // Script d'interaction basique si besoin
</script>
</body>
</html>
