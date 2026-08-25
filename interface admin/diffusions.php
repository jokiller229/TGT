<?php
session_start();
require_once __DIR__ . '/config/db.php';

$pageTitle = 'Diffusions - Admin';
require_once __DIR__ . '/includes/header.php';
$db = getDB();

$success = '';
$error = '';

// --- Handle form submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send') {
    $cible   = $_POST['cible'] ?? 'all';
    $message = trim($_POST['message'] ?? '');
    $type    = $_POST['type'] ?? 'info';

    if (empty($message)) {
        $error = "Le message ne peut pas être vide.";
    } else {
        // Build user query based on target
        switch ($cible) {
            case 'candidats':
                $stmt = $db->query("SELECT id FROM users WHERE role = 'candidat'");
                break;
            case 'recruteurs':
                $stmt = $db->query("SELECT id FROM users WHERE role IN ('recruteur_entreprise', 'recruteur_particulier', 'recruteur')");
                break;
            default: // 'all'
                $stmt = $db->query("SELECT id FROM users WHERE role != 'admin'");
        }
        $users = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($users)) {
            $error = "Aucun utilisateur trouvé pour cette cible.";
        } else {
            // Prefix message with admin broadcast marker
            $prefixed = "[ADMIN] " . $message;
            $ins = $db->prepare("INSERT INTO notifications (user_id, message, lu, created_at) VALUES (?, ?, 0, NOW())");
            foreach ($users as $uid) {
                $ins->execute([$uid, $prefixed]);
            }
            $success = "Notification diffusée à " . count($users) . " utilisateur(s) avec succès.";
        }
    }
}

// --- Load history of broadcasts ---
$broadcasts = $db->query("
    SELECT message, MIN(created_at) as sent_at, COUNT(*) as nb_destinataires
    FROM notifications
    WHERE message LIKE '[ADMIN]%'
    GROUP BY message, DATE(created_at)
    ORDER BY sent_at DESC
    LIMIT 20
")->fetchAll();
?>

<div style="max-width: 900px;">

    <!-- Page Header -->
    <div class="dashboard-topbar" style="margin-bottom: 2rem;">
        <div>
            <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--color-primary-dark); margin-bottom: 0.25rem;">Diffusions</h1>
            <p style="color: var(--text-muted); font-size: 0.95rem;">Envoyez des notifications a tous vos utilisateurs ou a une cible precise.</p>
        </div>
    </div>

    <!-- Success / Error alerts -->
    <?php if ($success): ?>
        <div style="background: var(--status-green-bg); border: 1px solid var(--status-green-border); color: var(--status-green-text); padding: 1rem 1.25rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 13.01 9 10.01"/></svg>
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div style="background: var(--status-red-bg); border: 1px solid #FCA5A5; color: var(--status-red-text); padding: 1rem 1.25rem; border-radius: var(--radius-md); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <!-- Compose Form -->
    <div class="table-wrap" style="margin-bottom: 2rem;">
        <div style="padding: 1.5rem 1.75rem; border-bottom: 1px solid var(--border-light);">
            <h2 style="font-size: 1.1rem; font-weight: 700; color: var(--color-primary-dark); margin: 0; display: flex; align-items: center; gap: 0.6rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1E40AF" stroke-width="2"><path d="M3 11l19-9-9 19-2-8-8-2z"/></svg>
                Nouvelle diffusion
            </h2>
        </div>
        <form method="POST" style="padding: 1.75rem; display: flex; flex-direction: column; gap: 1.25rem;">
            <input type="hidden" name="action" value="send">

            <div>
                <label style="display: block; font-weight: 600; font-size: 0.9rem; color: var(--text-main); margin-bottom: 0.5rem;">
                    Destinataires
                </label>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <?php $targets = [
                        'all'        => ['label' => 'Tous les utilisateurs',  'icon' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'],
                        'candidats'  => ['label' => 'Candidats uniquement',   'icon' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>'],
                        'recruteurs' => ['label' => 'Recruteurs uniquement',  'icon' => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>'],
                    ]; ?>
                    <?php foreach ($targets as $val => $info): ?>
                        <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1rem; border: 2px solid var(--border-light); border-radius: var(--radius-md); cursor: pointer; transition: all 0.15s ease; background: var(--bg-surface);"
                               onmouseover="this.style.borderColor='#1E40AF'" onmouseout="if(!this.querySelector('input').checked) this.style.borderColor='var(--border-light)'"
                               id="label-<?= $val ?>">
                            <input type="radio" name="cible" value="<?= $val ?>" <?= $val === 'all' ? 'checked' : '' ?>
                                   style="accent-color: #1E40AF;"
                                   onchange="document.querySelectorAll('[id^=label-]').forEach(el => el.style.borderColor='var(--border-light)'); document.getElementById('label-<?= $val ?>').style.borderColor='#1E40AF'">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><?= $info['icon'] ?></svg>
                            <span style="font-size: 0.9rem; font-weight: 500; color: var(--text-main);"><?= $info['label'] ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div>
                <label for="message" style="display: block; font-weight: 600; font-size: 0.9rem; color: var(--text-main); margin-bottom: 0.5rem;">
                    Message de la notification
                </label>
                <textarea id="message" name="message" rows="4" maxlength="255"
                          placeholder="Ex. : La plateforme sera en maintenance le 25 août de 22h00 à 23h00..."
                          style="width: 100%; padding: 0.85rem 1rem; border: 1.5px solid var(--border-light); border-radius: var(--radius-md); font-family: inherit; font-size: 0.95rem; color: var(--text-main); resize: vertical; outline: none; transition: border-color 0.15s;"
                          onfocus="this.style.borderColor='#1E40AF'" onblur="this.style.borderColor='var(--border-light)'"
                          required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                <div style="text-align: right; font-size: 0.8rem; color: var(--text-muted); margin-top: 0.3rem;">Max. 255 caractères</div>
            </div>

            <div style="display: flex; justify-content: flex-end;">
                <button type="submit"
                        style="background: linear-gradient(135deg, #1E40AF, #2563EB); color: white; border: none; padding: 0.75rem 1.75rem; border-radius: var(--radius-pill); font-weight: 700; font-size: 0.95rem; cursor: pointer; display: flex; align-items: center; gap: 0.6rem; transition: all 0.2s ease; box-shadow: 0 4px 12px rgba(37,99,235,0.3);"
                        onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 6px 20px rgba(37,99,235,0.4)';"
                        onmouseout="this.style.transform=''; this.style.boxShadow='0 4px 12px rgba(37,99,235,0.3)';">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 11l19-9-9 19-2-8-8-2z"/></svg>
                    Diffuser la notification
                </button>
            </div>
        </form>
    </div>

    <!-- History -->
    <div class="table-wrap">
        <div style="padding: 1.25rem 1.75rem; border-bottom: 1px solid var(--border-light);">
            <h2 style="font-size: 1.05rem; font-weight: 700; color: var(--color-primary-dark); margin: 0; display: flex; align-items: center; gap: 0.6rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748B" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                Historique des diffusions
            </h2>
        </div>
        <?php if (count($broadcasts) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Message</th>
                        <th>Destinataires</th>
                        <th>Envoyé le</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($broadcasts as $b): ?>
                        <tr>
                            <td style="max-width: 450px;">
                                <div style="font-size: 0.9rem; color: var(--text-main); display: flex; align-items: flex-start; gap: 0.5rem;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#3B82F6" stroke-width="2" style="flex-shrink:0; margin-top:2px;"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                                    <span><?= htmlspecialchars(preg_replace('/^\[ADMIN\] /', '', $b['message'])) ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-gray"><?= $b['nb_destinataires'] ?> utilisateur(s)</span>
                            </td>
                            <td style="color: var(--text-muted); font-size: 0.875rem;">
                                <?= date('d/m/Y H:i', strtotime($b['sent_at'])) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div style="padding: 3rem; text-align: center; color: var(--text-muted);">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 1rem; display: block; opacity: 0.4;"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                Aucune diffusion envoyee pour l'instant.
            </div>
        <?php endif; ?>
    </div>
</div>

</main>
</div>
</body>
</html>
