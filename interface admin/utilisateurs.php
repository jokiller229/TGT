<?php
session_start();
require_once __DIR__ . '/config/db.php';

$pageTitle = 'Gestion des Utilisateurs - Admin';
require_once __DIR__ . '/includes/header.php';
$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $user_id = (int)$_POST['user_id'];
    $new_status = $_POST['status'];
    if (in_array($new_status, ['actif', 'suspendu', 'banni'])) {
        $db->prepare("UPDATE users SET statut_compte = ? WHERE id = ?")->execute([$new_status, $user_id]);
        $msg = "Statut mis à jour.";
    }
}

// Fetch users
$users = $db->query("
    SELECT * FROM users
    ORDER BY created_at DESC
")->fetchAll();
?>

<div class="table-wrap">
    <div style="padding: 1.5rem; border-bottom: 1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center;">
        <h2 style="font-size: 1.25rem; font-weight: 600; margin:0;">Tous les utilisateurs</h2>
        <?php if (isset($msg)): ?>
            <span class="badge badge-green"><?= htmlspecialchars($msg) ?></span>
        <?php endif; ?>
    </div>
    <?php if (count($users) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Date d'inscription</th>
                    <th>Statut</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td>#<?= $u['id'] ?></td>
                    <td><strong><?= htmlspecialchars($u['nom']) ?></strong></td>
                    <td><?= htmlspecialchars($u['email']) ?>
                        <?php if(!empty($u['telephone'])): ?>
                            <br><span style="font-size:0.8rem; color:#64748b;"><?= htmlspecialchars($u['telephone']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($u['role'] === 'candidat'): ?>
                            <span class="badge badge-green">Candidat</span>
                        <?php elseif($u['role'] === 'recruteur'): ?>
                            <span class="badge badge-gray" style="background:#dbeafe; color:#1e3a8a;">Recruteur</span>
                        <?php else: ?>
                            <span class="badge badge-gray"><?= htmlspecialchars($u['role']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
                    <td>
                        <?php
                            if ($u['statut_compte'] === 'actif') echo '<span class="badge badge-green">ACTIF</span>';
                            elseif ($u['statut_compte'] === 'suspendu') echo '<span class="badge badge-yellow">SUSPENDU</span>';
                            else echo '<span class="badge badge-red">BANNI</span>';
                        ?>
                    </td>
                    <td>
                        <form method="POST" style="display:flex; gap:0.5rem;">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <select name="status" class="btn-outline" style="padding:0.25rem 0.5rem; font-size:0.8rem;">
                                <option value="actif" <?= $u['statut_compte'] === 'actif' ? 'selected' : '' ?>>Actif</option>
                                <option value="suspendu" <?= $u['statut_compte'] === 'suspendu' ? 'selected' : '' ?>>Suspendre</option>
                                <option value="banni" <?= $u['statut_compte'] === 'banni' ? 'selected' : '' ?>>Bannir</option>
                            </select>
                            <input type="hidden" name="action" value="update_status">
                            <button type="submit" class="btn-primary" style="padding:0.25rem 0.5rem; font-size:0.8rem;">OK</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div style="padding: 3rem; text-align: center; color: #64748b;">
            Aucun utilisateur.
        </div>
    <?php endif; ?>
</div>

</div>
</main>
</div>
</body>
</html>
