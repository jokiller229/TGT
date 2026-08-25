<?php
session_start();
require_once __DIR__ . '/config/db.php';

$pageTitle = 'Service Client - Admin';
require_once __DIR__ . '/includes/header.php';
$db = getDB();

$msg = '';
$msgType = '';

// ─── Changer le statut ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $id = (int)$_POST['message_id'];

    if ($_POST['action'] === 'set_status') {
        $statut = in_array($_POST['statut'], ['nouveau','en_cours','resolu']) ? $_POST['statut'] : 'nouveau';
        $db->prepare("UPDATE support_messages SET statut = ? WHERE id = ?")->execute([$statut, $id]);
        $msg = 'Statut mis à jour.'; $msgType = 'green';

    } elseif ($_POST['action'] === 'repondre') {
        $reponse = trim($_POST['reponse'] ?? '');
        if ($reponse) {
            $db->prepare("UPDATE support_messages SET reponse = ?, statut = 'resolu' WHERE id = ?")->execute([$reponse, $id]);
            $msg = 'Réponse enregistrée, message marqué comme résolu.'; $msgType = 'green';
        }

    } elseif ($_POST['action'] === 'delete') {
        $db->prepare("DELETE FROM support_messages WHERE id = ?")->execute([$id]);
        $msg = 'Message supprimé.'; $msgType = 'red';
    }
}

// ─── Filtre par statut ──────────────────────────────────────
$filterStatut = $_GET['statut'] ?? 'tous';
$sql = "SELECT * FROM support_messages";
if ($filterStatut !== 'tous') {
    $sql .= " WHERE statut = " . $db->quote($filterStatut);
}
$sql .= " ORDER BY created_at DESC";
$messages = $db->query($sql)->fetchAll();

// Compteurs
$counts = $db->query("SELECT statut, COUNT(*) as n FROM support_messages GROUP BY statut")->fetchAll(PDO::FETCH_KEY_PAIR);
$total     = array_sum($counts);
$nouveaux  = $counts['nouveau']  ?? 0;
$en_cours  = $counts['en_cours'] ?? 0;
$resolus   = $counts['resolu']   ?? 0;
?>

<!-- Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 1.75rem; font-weight: 800; color: #0F172A; margin: 0;">Service Client 💬</h1>
        <p style="color: #64748B; margin: 0.25rem 0 0;">Messages reçus depuis le formulaire de contact du site.</p>
    </div>
    <div style="display: flex; gap: 1rem; font-size: 0.85rem; font-weight: 700;">
        <span style="background: #FEF2F2; color: #991B1B; padding: 0.4rem 1rem; border-radius: 99px;">🔴 <?= $nouveaux ?> nouveau<?= $nouveaux > 1 ? 'x' : '' ?></span>
        <span style="background: #FFF7ED; color: #92400E; padding: 0.4rem 1rem; border-radius: 99px;">🟠 <?= $en_cours ?> en cours</span>
        <span style="background: #F0FDF4; color: #065F46; padding: 0.4rem 1rem; border-radius: 99px;">🟢 <?= $resolus ?> résolus</span>
    </div>
</div>

<!-- Flash -->
<?php if ($msg): ?>
    <div style="background: <?= $msgType === 'green' ? '#D1FAE5' : '#FEE2E2' ?>; color: <?= $msgType === 'green' ? '#065F46' : '#991B1B' ?>; padding: 1rem 1.5rem; border-radius: 10px; margin-bottom: 1.5rem; font-weight: 600;">
        <?= htmlspecialchars($msg) ?>
    </div>
<?php endif; ?>

<!-- Filtres -->
<div style="display: flex; gap: 0.75rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
    <?php foreach (['tous' => 'Tous ('.$total.')', 'nouveau' => '🔴 Nouveaux ('.$nouveaux.')', 'en_cours' => '🟠 En cours ('.$en_cours.')', 'resolu' => '🟢 Résolus ('.$resolus.')'] as $val => $label): ?>
        <a href="support.php?statut=<?= $val ?>" style="padding: 0.5rem 1.25rem; border-radius: 99px; text-decoration: none; font-weight: 600; font-size: 0.875rem; <?= $filterStatut === $val ? 'background:#2563EB; color:white;' : 'background:#F1F5F9; color:#475569;' ?>">
            <?= $label ?>
        </a>
    <?php endforeach; ?>
</div>

<!-- Liste des messages -->
<?php if (empty($messages)): ?>
    <div style="text-align: center; padding: 5rem 2rem; background: white; border-radius: 16px; border: 1px solid #E2E8F0; color: #94A3B8;">
        <div style="font-size: 3rem; margin-bottom: 1rem;">📭</div>
        <p style="font-weight: 600; font-size: 1.1rem;">Aucun message<?= $filterStatut !== 'tous' ? ' avec ce statut' : '' ?>.</p>
    </div>
<?php else: ?>
    <div style="display: flex; flex-direction: column; gap: 1.25rem;">
        <?php foreach ($messages as $m):
            $statutColor = match($m['statut']) {
                'nouveau'  => ['bg' => '#FEF2F2', 'text' => '#991B1B', 'dot' => '🔴'],
                'en_cours' => ['bg' => '#FFF7ED', 'text' => '#92400E', 'dot' => '🟠'],
                'resolu'   => ['bg' => '#F0FDF4', 'text' => '#065F46', 'dot' => '🟢'],
                default    => ['bg' => '#F1F5F9', 'text' => '#475569', 'dot' => '⚪'],
            };
        ?>
        <div style="background: white; border-radius: 16px; border: 1.5px solid <?= $m['statut'] === 'nouveau' ? '#FECACA' : '#E2E8F0' ?>; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.04);">

            <!-- Header de la carte -->
            <div style="padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem; border-bottom: 1px solid #F1F5F9;">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div style="width: 42px; height: 42px; border-radius: 50%; background: #EFF6FF; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: 700; color: #2563EB; flex-shrink: 0;">
                        <?= mb_strtoupper(mb_substr($m['nom'], 0, 1)) ?>
                    </div>
                    <div>
                        <div style="font-weight: 700; color: #0F172A; font-size: 0.95rem;"><?= htmlspecialchars($m['nom']) ?></div>
                        <div style="font-size: 0.8rem; color: #64748B;"><?= htmlspecialchars($m['email']) ?> • <?= date('d/m/Y à H:i', strtotime($m['created_at'])) ?></div>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <span style="background: <?= $statutColor['bg'] ?>; color: <?= $statutColor['text'] ?>; padding: 0.3rem 0.85rem; border-radius: 99px; font-size: 0.8rem; font-weight: 700;">
                        <?= $statutColor['dot'] ?> <?= ucfirst(str_replace('_', ' ', $m['statut'])) ?>
                    </span>
                    <span class="badge badge-gray" style="font-size: 0.78rem;"><?= htmlspecialchars($m['sujet']) ?></span>
                </div>
            </div>

            <!-- Corps du message -->
            <div style="padding: 1.25rem 1.5rem;">
                <p style="color: #374151; line-height: 1.7; margin: 0; font-size: 0.9rem; white-space: pre-wrap;"><?= htmlspecialchars($m['message']) ?></p>

                <?php if ($m['reponse']): ?>
                    <div style="margin-top: 1rem; padding: 1rem; background: #F0FDF4; border-left: 4px solid #22C55E; border-radius: 8px;">
                        <div style="font-size: 0.75rem; font-weight: 700; color: #15803D; margin-bottom: 0.4rem;">✅ RÉPONSE DE L'ÉQUIPE</div>
                        <p style="color: #166534; font-size: 0.9rem; line-height: 1.6; margin: 0; white-space: pre-wrap;"><?= htmlspecialchars($m['reponse']) ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Actions -->
            <div style="padding: 1rem 1.5rem; background: #F8FAFC; border-top: 1px solid #F1F5F9; display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">

                <!-- Changer statut -->
                <form method="POST" style="display: flex; gap: 0.5rem; align-items: center;">
                    <input type="hidden" name="action" value="set_status">
                    <input type="hidden" name="message_id" value="<?= $m['id'] ?>">
                    <select name="statut" style="padding: 0.4rem 0.75rem; border: 1px solid #CBD5E1; border-radius: 8px; font-size: 0.8rem; background: white; cursor: pointer;">
                        <option value="nouveau"  <?= $m['statut'] === 'nouveau'  ? 'selected' : '' ?>>🔴 Nouveau</option>
                        <option value="en_cours" <?= $m['statut'] === 'en_cours' ? 'selected' : '' ?>>🟠 En cours</option>
                        <option value="resolu"   <?= $m['statut'] === 'resolu'   ? 'selected' : '' ?>>🟢 Résolu</option>
                    </select>
                    <button type="submit" class="btn-outline" style="font-size: 0.8rem; padding: 0.35rem 0.75rem;">Appliquer</button>
                </form>

                <!-- Répondre -->
                <button onclick="this.closest('.card-actions').nextElementSibling.style.display = this.closest('.card-actions').nextElementSibling.style.display === 'none' ? 'block' : 'none'" class="btn-primary" style="font-size: 0.8rem; padding: 0.4rem 1rem;" id="btn-reply-<?= $m['id'] ?>" onclick="document.getElementById('reply-<?= $m['id'] ?>').style.display='block'">
                    💬 Répondre
                </button>

                <!-- Supprimer -->
                <form method="POST" style="margin-left: auto;" onsubmit="return confirm('Supprimer définitivement ce message ?')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="message_id" value="<?= $m['id'] ?>">
                    <button type="submit" class="btn-danger" style="font-size: 0.8rem; padding: 0.4rem 0.75rem;">🗑</button>
                </form>
            </div>

            <!-- Formulaire de réponse (masqué) -->
            <div id="reply-<?= $m['id'] ?>" style="display: none; padding: 1.25rem 1.5rem; background: #F0F9FF; border-top: 1px solid #BAE6FD;">
                <form method="POST">
                    <input type="hidden" name="action" value="repondre">
                    <input type="hidden" name="message_id" value="<?= $m['id'] ?>">
                    <label style="display: block; font-weight: 700; font-size: 0.85rem; color: #0F172A; margin-bottom: 0.5rem;">Votre réponse (visible pour vous en interne) :</label>
                    <textarea name="reponse" rows="4" placeholder="Tapez votre réponse..." style="width: 100%; padding: 0.75rem; border: 1.5px solid #BAE6FD; border-radius: 10px; font-size: 0.875rem; font-family: inherit; resize: vertical; box-sizing: border-box; outline: none;"><?= htmlspecialchars($m['reponse'] ?? '') ?></textarea>
                    <div style="display: flex; gap: 0.75rem; margin-top: 0.75rem;">
                        <button type="submit" class="btn-primary" style="font-size: 0.875rem; padding: 0.5rem 1.5rem;">✅ Enregistrer la réponse</button>
                        <button type="button" onclick="document.getElementById('reply-<?= $m['id'] ?>').style.display='none'" class="btn-outline" style="font-size: 0.875rem; padding: 0.5rem 1rem;">Annuler</button>
                    </div>
                </form>
            </div>

        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
// Toggle réponse
document.querySelectorAll('[id^="btn-reply-"]').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.id.replace('btn-reply-', '');
        const panel = document.getElementById('reply-' + id);
        if (panel) panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
    });
});
</script>

</main>
</div>
</body>
</html>
