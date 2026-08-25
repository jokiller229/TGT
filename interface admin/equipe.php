<?php
session_start();
require_once __DIR__ . '/config/db.php';

$pageTitle = 'Équipe (Admins) - TGTravail';
require_once __DIR__ . '/includes/header.php';
$db = getDB();

$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add_admin') {
        $nom = trim($_POST['nom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($nom) || empty($email) || empty($password)) {
            $error = 'Tous les champs sont obligatoires.';
        } else {
            // Check if email already exists
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Un utilisateur avec cet email existe déjà.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO users (nom, email, password, role) VALUES (?, ?, ?, 'admin')");
                if ($stmt->execute([$nom, $email, $hash])) {
                    $msg = 'Nouvel administrateur ajouté avec succès.';
                } else {
                    $error = 'Erreur lors de la création du compte.';
                }
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'revoke_admin') {
        $admin_id = (int)($_POST['admin_id'] ?? 0);
        // Ne pas se supprimer soi-même
        if ($admin_id === (int)$_SESSION['admin_id']) {
            $error = 'Vous ne pouvez pas révoquer votre propre compte.';
        } else {
            $stmt = $db->prepare("UPDATE users SET role = 'candidat' WHERE id = ? AND role = 'admin'");
            if ($stmt->execute([$admin_id])) {
                $msg = 'Accès administrateur révoqué.';
            }
        }
    }
}

// Fetch all admins
$admins = $db->query("SELECT id, nom, email, created_at FROM users WHERE role = 'admin' ORDER BY created_at DESC")->fetchAll();
?>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 style="font-size: 1.75rem; margin-bottom: 0.5rem; color: #1e293b;">Gestion de l'équipe</h1>
            <p style="color: #64748b; margin: 0;">Gérez les accès administrateurs à la plateforme.</p>
        </div>
        <button onclick="document.getElementById('add-admin-modal').style.display='flex';" class="btn-primary" style="display: flex; align-items: center; gap: 0.5rem;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"></path></svg>
            Ajouter un membre
        </button>
    </div>

    <?php if ($msg): ?>
        <div style="background:#d1fae5; color:#065f46; padding:1rem; border-radius:4px; margin-bottom:2rem; border-left: 4px solid #10b981;">
            <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div style="background:#fee2e2; color:#b91c1c; padding:1rem; border-radius:4px; margin-bottom:2rem; border-left: 4px solid #ef4444;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Date d'ajout</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($admins as $admin): ?>
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <div style="width: 40px; height: 40px; border-radius: 50%; background: #081326; color: #FFB800; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                                <?= strtoupper(substr($admin['nom'], 0, 2)) ?>
                            </div>
                            <div>
                                <strong style="color: #0f172a;"><?= htmlspecialchars($admin['nom']) ?></strong>
                                <?php if($admin['id'] == $_SESSION['admin_id']): ?>
                                    <span class="badge badge-green" style="margin-left: 0.5rem;">Vous</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($admin['email']) ?></td>
                    <td><?= date('d/m/Y', strtotime($admin['created_at'])) ?></td>
                    <td>
                        <?php if($admin['id'] != $_SESSION['admin_id']): ?>
                            <form method="POST" onsubmit="return confirm('Voulez-vous vraiment révoquer les accès de cet administrateur ?');">
                                <input type="hidden" name="action" value="revoke_admin">
                                <input type="hidden" name="admin_id" value="<?= $admin['id'] ?>">
                                <button type="submit" class="btn-danger" style="background: transparent; color: #ef4444; border: 1px solid #ef4444; padding: 0.35rem 0.75rem;">Révoquer</button>
                            </form>
                        <?php else: ?>
                            <span style="color: #94a3b8; font-size: 0.85rem;">Aucune action</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal Add Admin -->
    <div id="add-admin-modal" class="modal-overlay">
        <div class="modal-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h3 style="margin: 0; font-size: 1.25rem; color: #0f172a;">Ajouter un Administrateur</h3>
                <button type="button" class="btn-outline" onclick="document.getElementById('add-admin-modal').style.display='none';" style="padding: 0.25rem 0.5rem; border: none; font-size: 1.5rem; line-height: 1;">&times;</button>
            </div>
            
            <form method="POST">
                <input type="hidden" name="action" value="add_admin">
                
                <div style="margin-bottom: 1rem;">
                    <label style="display:block; font-weight:600; margin-bottom:0.5rem; font-size: 0.9rem;">Nom complet</label>
                    <input type="text" name="nom" required style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
                </div>
                
                <div style="margin-bottom: 1rem;">
                    <label style="display:block; font-weight:600; margin-bottom:0.5rem; font-size: 0.9rem;">Adresse Email</label>
                    <input type="email" name="email" required style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
                </div>
                
                <div style="margin-bottom: 1.5rem;">
                    <label style="display:block; font-weight:600; margin-bottom:0.5rem; font-size: 0.9rem;">Mot de passe provisoire</label>
                    <input type="password" name="password" required style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;">
                </div>
                
                <div style="display: flex; justify-content: flex-end; gap: 1rem;">
                    <button type="button" class="btn-outline" onclick="document.getElementById('add-admin-modal').style.display='none';">Annuler</button>
                    <button type="submit" class="btn-primary">Créer le compte</button>
                </div>
            </form>
        </div>
    </div>

</main>
</div>
</body>
</html>
