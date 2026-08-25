<?php
$activePage = 'messages';
$pageTitle = 'Messagerie - TGTravail';
require_once __DIR__ . '/../includes/auth.php';
requireRole('recruteur');

$db = getDB();
$currentUserId = getCurrentUserId();
$companyId = getCurrentCompanyId();
if (!$companyId) { header("Location: ../index.php"); exit; }

// Require Verified Company
$stmtComp = getDB()->prepare("SELECT verifie FROM companies WHERE id = ?");
$stmtComp->execute([$companyId]);
$companyData = $stmtComp->fetch();
if (!$companyData || !$companyData['verifie']) {
    header('Location: ../recruteur/recruteur-dashboard.php');
    exit;
}


$user = getCurrentUser();

$stmtComp = $db->prepare("SELECT nom, logo FROM companies WHERE id = ?");
$stmtComp->execute([$companyId]);
$company = $stmtComp->fetch();
$companyName = $company['nom'] ?? 'Mon Entreprise';
$companyLogo = $company['logo'] ?? null;

// Active conversation
$convId = (int)($_GET['conv_id'] ?? 0);

// Send message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message_submit'])) {
    $msgText = trim($_POST['message'] ?? '');
    if (!empty($msgText) && $convId) {
        $insertMsg = $db->prepare("INSERT INTO messages (conversation_id, sender_id, message, is_read, created_at) VALUES (?, ?, ?, 0, NOW())");
        $insertMsg->execute([$convId, $currentUserId, $msgText]);
        $db->prepare("UPDATE conversations SET updated_at = NOW() WHERE id = ?")->execute([$convId]);
        header("Location: ../recruteur/messages.php?conv_id={$convId}");
        exit;
    }
}

// Conversations list
$convsStmt = $db->prepare("
  SELECT c.*,
         CASE WHEN c.user1_id = ? THEN u2.nom ELSE u1.nom END AS contact_nom,
         CASE WHEN c.user1_id = ? THEN u2.avatar ELSE u1.avatar END AS contact_avatar,
         CASE WHEN c.user1_id = ? THEN u2.id ELSE u1.id END AS contact_id,
         comp.nom AS company_nom,
         comp.logo AS company_logo,
         (SELECT m2.message FROM messages m2 WHERE m2.conversation_id = c.id ORDER BY m2.created_at DESC LIMIT 1) AS last_message,
         (SELECT COUNT(*) FROM messages m3 WHERE m3.conversation_id = c.id AND m3.sender_id != ? AND m3.is_read = 0) AS unread_count
  FROM conversations c
  JOIN users u1 ON c.user1_id = u1.id
  JOIN users u2 ON c.user2_id = u2.id
  LEFT JOIN companies comp ON c.company_id = comp.id
  WHERE c.user1_id = ? OR c.user2_id = ?
  ORDER BY c.updated_at DESC
");
$convsStmt->execute([$currentUserId, $currentUserId, $currentUserId, $currentUserId, $currentUserId, $currentUserId]);
$conversations = $convsStmt->fetchAll();

// If no conv_id set, default to first
$userSelectedConv = isset($_GET['conv_id']);
if (!$convId && count($conversations) > 0) {
    $convId = $conversations[0]['id'];
}

// Active conversation info
$activeConv = null;
foreach ($conversations as $c) {
    if ($c['id'] == $convId) { $activeConv = $c; break; }
}

// Messages for active conversation
$messages = [];
if ($convId) {
    $msgsStmt = $db->prepare("SELECT m.*, u.nom AS sender_nom, u.avatar AS sender_avatar FROM messages m JOIN users u ON m.sender_id = u.id WHERE m.conversation_id = ? ORDER BY m.created_at ASC");
    $msgsStmt->execute([$convId]);
    $messages = $msgsStmt->fetchAll();
    // Mark as read
    $db->prepare("UPDATE messages SET is_read = 1 WHERE conversation_id = ? AND sender_id != ?")->execute([$convId, $currentUserId]);
}

require_once __DIR__ . '/../includes/header.php';
?>

<style>
@media (max-width: 768px) {
  .chat-list-panel { width: 100% !important; border-right: none !important; display: <?= $userSelectedConv ? 'none' : 'flex' ?> !important; }
  .chat-active-panel { display: <?= $userSelectedConv ? 'flex' : 'none' ?> !important; width: 100% !important; }
  .chat-back-btn { display: flex !important; }
}
@media (min-width: 769px) {
  .chat-back-btn { display: none !important; }
}
</style>

<div class="dashboard-wrapper" style="height:100vh; overflow:hidden;">

  <!-- Dark Sidebar Left -->
  <?php require __DIR__ . '/../includes/recruiter_sidebar.php'; ?>

  <!-- Main content: full-height 2-col chat -->
  <div style="flex:1; display:flex; flex-direction:column; overflow:hidden;">

    <!-- Topbar -->
    <div class="dashboard-topbar" style="flex-shrink:0;">
      <div class="dashboard-header-left" style="display:flex; align-items:center; gap:1rem;">
        <button class="dashboard-mobile-btn" onclick="document.querySelector('.dashboard-sidebar-dark').classList.toggle('open'); event.stopPropagation();">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>
        <div>
        <h1 class="user-greeting">Messages</h1>
        <p style="font-size:0.9rem; color:var(--text-muted);">Vos conversations avec les candidats</p>
      </div>
      </div>
      <div style="display:flex; align-items:center; gap:1.25rem;">
        <div class="company-selector-dropdown">
          <span style="color:#2563EB;">●</span>
          <span><?= htmlspecialchars($companyName) ?></span>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
        </div>
        <a href="../pages/notifications.php" style="position:relative; color:var(--text-muted); display:flex;" title="Notifications">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
          <span style="position:absolute; top:2px; right:2px; width:8px; height:8px; background:#3B82F6; border-radius:50%; border:2px solid #F8FAFC;"></span>
        </a>
        <a href="../recruteur/parametres.php" title="Paramètres" style="display:flex;">
          <?php if (!empty($companyLogo) && file_exists(__DIR__ . '/' . $companyLogo)): ?>
            <img src="<?= htmlspecialchars($companyLogo) ?>" alt="Logo" style="width:42px; height:42px; border-radius:50%; object-fit:cover; border:2px solid #E2E8F0;">
          <?php else: ?>
            <div style="width:42px; height:42px; border-radius:50%; background:#081326; color:#FFB800; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.9rem; text-transform:uppercase; border:2px solid #E2E8F0;">
              <?= htmlspecialchars(strtoupper(substr($user['nom'], 0, 2))) ?>
            </div>
          <?php endif; ?>
        </a>
      </div>
    </div>

    <!-- 2-Column Chat Interface -->
    <div style="flex:1; display:flex; overflow:hidden; background:#F8FAFC;">

      <!-- LEFT: Conversations list -->
      <div class="chat-list-panel" style="width:320px; flex-shrink:0; background:#FFF; border-right:1px solid #E2E8F0; display:flex; flex-direction:column; overflow:hidden;">

        <!-- Search -->
        <div style="padding:1rem 1.25rem; border-bottom:1px solid #F1F5F9;">
          <div style="background:#F8FAFC; border:1px solid #E2E8F0; border-radius:12px; display:flex; align-items:center; gap:0.5rem; padding:0.6rem 1rem;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <input type="text" id="conv-search" placeholder="Rechercher une conversation" style="border:none; outline:none; background:transparent; font-size:0.875rem; color:#334155; width:100%; font-family:inherit;">
          </div>
        </div>

        <!-- Conversations -->
        <div style="flex:1; overflow-y:auto;" id="conv-list">
          <?php if (empty($conversations)): ?>
            <div style="padding:3rem 1.5rem; text-align:center; color:#94A3B8;">
              <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity:0.4; margin-bottom:1rem;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
              <p style="font-size:0.875rem; font-weight:600; color:#64748B;">Aucune conversation</p>
              <p style="font-size:0.8rem; margin-top:0.25rem;">Contactez un candidat depuis son profil.</p>
            </div>
          <?php else: ?>
            <?php foreach ($conversations as $c):
              $isActive = ($c['id'] == $convId);
              $displayName = $c['contact_nom'];
              $initials = strtoupper(substr($displayName, 0, 2));
              $lastMsg = $c['last_message'] ?? 'Cliquez pour afficher...';
              $unread = (int)$c['unread_count'];
              $bgColors = ['#081326','#DC2626','#0284C7','#EA580C','#7C3AED','#059669'];
              $bgColor = $bgColors[$c['id'] % count($bgColors)];
            ?>
            <a href="conv_id=<?= $c['id'] ?>" style="display:flex; align-items:center; gap:0.875rem; padding:1rem 1.25rem; border-bottom:1px solid #F8FAFC; text-decoration:none; background:<?= $isActive ? '#EFF6FF' : '#FFF' ?>; transition:background 0.15s;"
               onmouseover="this.style.background='<?= $isActive ? '#DBEAFE' : '#F8FAFC' ?>'"
               onmouseout="this.style.background='<?= $isActive ? '#EFF6FF' : '#FFF' ?>'">

              <!-- Avatar -->
              <div style="width:46px; height:46px; border-radius:50%; background:<?= $bgColor ?>; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.8rem; color:#FFB800; flex-shrink:0; position:relative; overflow:hidden;">
                <?php if (!empty($c['contact_avatar']) && (file_exists(__DIR__ . '/' . $c['contact_avatar']) || str_starts_with($c['contact_avatar'], 'http'))): ?>
                   <img src="<?= htmlspecialchars($c['contact_avatar']) ?>" alt="Avatar" style="width:100%; height:100%; object-fit:cover;">
                <?php else: ?>
                   <?= $initials ?>
                <?php endif; ?>
                <span style="position:absolute; bottom:1px; right:1px; width:11px; height:11px; background:#22C55E; border-radius:50%; border:2px solid #FFF;"></span>
              </div>

              <div style="flex:1; min-width:0;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.2rem;">
                  <span style="font-size:0.9rem; font-weight:<?= $isActive ? '700' : '600' ?>; color:#0F172A; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:140px;"><?= htmlspecialchars($displayName) ?></span>
                  <span style="font-size:0.72rem; color:#94A3B8; flex-shrink:0;"><?= date('H:i', strtotime($c['updated_at'])) ?></span>
                </div>
                <div style="display:flex; align-items:center; justify-content:space-between;">
                  <p style="font-size:0.8rem; color:<?= $unread ? '#334155' : '#94A3B8' ?>; font-weight:<?= $unread ? '600' : '400' ?>; margin:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:170px;">
                    <?= htmlspecialchars(mb_substr($lastMsg, 0, 40)) ?><?= mb_strlen($lastMsg) > 40 ? '...' : '' ?>
                  </p>
                  <?php if ($unread > 0): ?>
                    <span style="background:#2563EB; color:#FFF; font-size:0.7rem; font-weight:700; width:18px; height:18px; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0;"><?= $unread ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </a>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- RIGHT: Active Chat -->
      <div class="chat-active-panel" style="flex:1; display:flex; flex-direction:column; overflow:hidden; background:#FFF;">

        <?php if ($activeConv): ?>

          <!-- Chat Header -->
          <div style="padding:1rem 1.5rem; border-bottom:1px solid #E2E8F0; display:flex; align-items:center; justify-content:space-between; flex-shrink:0;">
            <div style="display:flex; align-items:center; gap:0.875rem;">
              <a href="messages.php" class="chat-back-btn" style="color: #64748B; background: none; border: none; cursor: pointer; align-items: center; justify-content: center; text-decoration: none;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
              </a>
              <?php
                $dn = $activeConv['contact_nom'];
                $ini = strtoupper(substr($dn, 0, 2));
                $bgColors = ['#081326','#DC2626','#0284C7','#EA580C','#7C3AED','#059669'];
                $bg = $bgColors[$activeConv['id'] % count($bgColors)];
              ?>
              <div style="width:42px; height:42px; border-radius:50%; background:<?= $bg ?>; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:0.8rem; color:#FFB800; position:relative; flex-shrink:0; overflow:hidden;">
                <?php if (!empty($activeConv['contact_avatar']) && (file_exists(__DIR__ . '/' . $activeConv['contact_avatar']) || str_starts_with($activeConv['contact_avatar'], 'http'))): ?>
                   <img src="<?= htmlspecialchars($activeConv['contact_avatar']) ?>" alt="Avatar" style="width:100%; height:100%; object-fit:cover;">
                <?php else: ?>
                   <?= $ini ?>
                <?php endif; ?>
                <span style="position:absolute; bottom:1px; right:1px; width:11px; height:11px; background:#22C55E; border-radius:50%; border:2px solid #FFF;"></span>
              </div>
              <div>
                <h3 style="font-size:0.95rem; font-weight:700; color:#0F172A; margin:0;"><?= htmlspecialchars($dn) ?></h3>
                <span style="font-size:0.75rem; color:#22C55E; font-weight:600;">● En ligne</span>
              </div>
            </div>
            <div style="display:flex; align-items:center; gap:0.5rem;">
              <button style="background:none; border:1px solid #E2E8F0; border-radius:10px; padding:0.45rem 0.65rem; cursor:pointer; color:#64748B; display:flex; align-items:center;" title="Rechercher" onclick="alert('Recherche dans la conversation')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
              </button>
              <button style="background:none; border:1px solid #E2E8F0; border-radius:10px; padding:0.45rem 0.65rem; cursor:pointer; color:#64748B; display:flex; align-items:center;" title="Options">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="1"></circle><circle cx="19" cy="12" r="1"></circle><circle cx="5" cy="12" r="1"></circle></svg>
              </button>
            </div>
          </div>

          <!-- Messages Scroll -->
          <div id="chat-scroll" style="flex:1; overflow-y:auto; padding:1.5rem; display:flex; flex-direction:column; gap:1rem; background:#F8FAFC;">
            <?php if (empty($messages)): ?>
              <div style="text-align:center; color:#94A3B8; margin-top:3rem;">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity:0.35; margin-bottom:1rem;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                <p style="font-size:0.9rem; font-weight:600; color:#64748B;">Aucun message pour le moment</p>
                <p style="font-size:0.8rem;">Envoyez le premier message !</p>
              </div>
            <?php else: ?>
              <?php
              $prevDate = '';
              $lastMsgId = 0;
              foreach ($messages as $m):
                $lastMsgId = $m['id'];
                $isMe = ($m['sender_id'] == $currentUserId);
                $msgDate = date('d M Y', strtotime($m['created_at']));
                $today = date('d M Y');
                $displayDate = ($msgDate === $today) ? "Aujourd'hui" : $msgDate;
              ?>
              <?php if ($msgDate !== $prevDate): ?>
                <div style="text-align:center; margin:0.5rem 0;">
                  <span style="font-size:0.75rem; color:#94A3B8; background:#E2E8F0; padding:0.25rem 0.85rem; border-radius:99px;"><?= $displayDate ?></span>
                </div>
                <?php $prevDate = $msgDate; ?>
              <?php endif; ?>

              <div style="display:flex; justify-content:<?= $isMe ? 'flex-end' : 'flex-start' ?>; align-items:flex-end; gap:0.5rem;">
                <?php if (!$isMe): ?>
                  <div style="width:32px; height:32px; border-radius:50%; background:<?= $bg ?>; display:flex; align-items:center; justify-content:center; font-size:0.65rem; font-weight:700; color:#FFB800; flex-shrink:0; overflow:hidden;">
                    <?php if (!empty($activeConv['contact_avatar']) && (file_exists(__DIR__ . '/' . $activeConv['contact_avatar']) || str_starts_with($activeConv['contact_avatar'], 'http'))): ?>
                       <img src="<?= htmlspecialchars($activeConv['contact_avatar']) ?>" alt="Avatar" style="width:100%; height:100%; object-fit:cover;">
                    <?php else: ?>
                       <?= $ini ?>
                    <?php endif; ?>
                  </div>
                <?php endif; ?>
                <div style="max-width:62%; background:<?= $isMe ? '#2563EB' : '#FFF' ?>; color:<?= $isMe ? '#FFF' : '#0F172A' ?>; padding:0.75rem 1rem; border-radius:<?= $isMe ? '18px 18px 4px 18px' : '18px 18px 18px 4px' ?>; box-shadow:0 1px 3px rgba(0,0,0,0.08); border:<?= $isMe ? 'none' : '1px solid #E2E8F0' ?>;">
                  <p style="margin:0; font-size:0.9rem; line-height:1.5;"><?= nl2br(htmlspecialchars($m['message'])) ?></p>
                  <span style="display:block; font-size:0.7rem; color:<?= $isMe ? 'rgba(255,255,255,0.65)' : '#94A3B8' ?>; text-align:right; margin-top:0.3rem;">
                    <?= date('H:i', strtotime($m['created_at'])) ?>
                    <?php if ($isMe): ?>
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.8)" stroke-width="2.5" style="display:inline;vertical-align:middle;margin-left:2px;"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <?php endif; ?>
                  </span>
                </div>
              </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

          <!-- Input Bar -->
          <form id="chat-form" style="padding:1rem 1.5rem; border-top:1px solid #E2E8F0; display:flex; align-items:center; gap:0.75rem; background:#FFF; flex-shrink:0;">
            <button type="button" style="background:none; border:none; cursor:pointer; color:#64748B; padding:0.5rem; display:flex; border-radius:10px; transition:background 0.15s;" onmouseover="this.style.background='#F1F5F9'" onmouseout="this.style.background='none'" title="Joindre un fichier">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>
            </button>
            <input type="text" id="msg-input" class="chat-input-field"
              placeholder="Écrire un message..."
              autocomplete="off" required
              style="flex:1; background:#F8FAFC; border:1.5px solid #E2E8F0; border-radius:24px; padding:0.7rem 1.25rem; font-size:0.9rem; font-family:inherit; color:#0F172A; outline:none; transition:border-color 0.2s;"
              onfocus="this.style.borderColor='#2563EB'" onblur="this.style.borderColor='#E2E8F0'">
            <button type="submit" style="width:42px; height:42px; background:#2563EB; border:none; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background 0.2s; flex-shrink:0;" title="Envoyer" onmouseover="this.style.background='#1D4ED8'" onmouseout="this.style.background='#2563EB'">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#FFF" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
            </button>
          </form>

        <?php else: ?>
          <!-- Empty State when no conv selected -->
          <div style="flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; color:#94A3B8; gap:1rem;">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity:0.3;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
            <p style="font-size:1rem; font-weight:600; color:#64748B;">Sélectionnez une conversation</p>
            <p style="font-size:0.875rem;">Vos échanges avec les candidats apparaîtront ici.</p>
          </div>
        <?php endif; ?>

      </div>
    </div>
  </div>
</div>

<script>
// Auto scroll to bottom
const chatScroll = document.getElementById('chat-scroll');
if (chatScroll) chatScroll.scrollTop = chatScroll.scrollHeight;

// Search filter
const searchInput = document.getElementById('conv-search');
if (searchInput) {
  searchInput.addEventListener('input', () => {
    const q = searchInput.value.toLowerCase();
    document.querySelectorAll('#conv-list a').forEach(item => {
      const name = item.querySelector('span')?.textContent?.toLowerCase() || '';
      item.style.display = name.includes(q) ? '' : 'none';
    });
  });
}

<?php if ($activeConv): ?>
  let lastMsgId = <?= isset($lastMsgId) ? $lastMsgId : 0 ?>;
  const convId = <?= $convId ?>;
  
  // Fonction pour ajouter un message à l'UI
  function appendMessage(msg, isMe) {
    const div = document.createElement('div');
    div.style.cssText = `display:flex; justify-content:${isMe ? 'flex-end' : 'flex-start'}; align-items:flex-end; gap:0.5rem; margin-bottom:1rem;`;
    
    let html = '';
    if (!isMe) {
      html += `
        <div style="width:32px; height:32px; border-radius:50%; background:<?= $bg ?>; display:flex; align-items:center; justify-content:center; font-size:0.65rem; font-weight:700; color:#FFB800; flex-shrink:0; overflow:hidden;">
          <?php if (!empty($activeConv['contact_avatar']) && (file_exists(__DIR__ . '/' . $activeConv['contact_avatar']) || str_starts_with($activeConv['contact_avatar'], 'http'))): ?>
             <img src="<?= htmlspecialchars($activeConv['contact_avatar']) ?>" style="width:100%; height:100%; object-fit:cover;">
          <?php else: ?>
             <?= $ini ?>
          <?php endif; ?>
        </div>
      `;
    }
    
    html += `
      <div style="max-width:62%; background:${isMe ? '#2563EB' : '#FFF'}; color:${isMe ? '#FFF' : '#0F172A'}; padding:0.75rem 1rem; border-radius:${isMe ? '18px 18px 4px 18px' : '18px 18px 18px 4px'}; box-shadow:0 1px 3px rgba(0,0,0,0.08); border:${isMe ? 'none' : '1px solid #E2E8F0'};">
        <p style="margin:0; font-size:0.9rem; line-height:1.5;">${msg.message.replace(/\\n/g, '<br>')}</p>
        <span style="display:block; font-size:0.7rem; color:${isMe ? 'rgba(255,255,255,0.65)' : '#94A3B8'}; text-align:right; margin-top:0.3rem;">
          ${msg.time}
          ${isMe ? '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.8)" stroke-width="2.5" style="display:inline;vertical-align:middle;margin-left:2px;"><polyline points="20 6 9 17 4 12"></polyline></svg>' : ''}
        </span>
      </div>
    `;
    
    div.innerHTML = html;
    chatScroll.appendChild(div);
    chatScroll.scrollTop = chatScroll.scrollHeight;
  }

  // Polling AJAX
  setInterval(() => {
    fetch(`api-get-messages.php?conv_id=${convId}&last_id=${lastMsgId}`)
      .then(r => r.json())
      .then(data => {
        if (data.status === 'success' && data.messages.length > 0) {
          data.messages.forEach(m => {
            appendMessage(m, false);
            lastMsgId = Math.max(lastMsgId, m.id);
          });
        }
      });
  }, 3000);

  // Envoi AJAX
  const chatForm = document.getElementById('chat-form');
  const msgInput = document.getElementById('msg-input');
  
  function sendMessage() {
    const txt = msgInput.value.trim();
    if (!txt) return;
    
    msgInput.value = '';
    
    fetch('../api/api-send-message.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({ conv_id: convId, message: txt })
    })
    .then(r => r.json())
    .then(data => {
      if (data.status === 'success') {
        appendMessage(data, true);
        lastMsgId = Math.max(lastMsgId, data.id);
      }
    }).catch(err => console.error(err));
  }
  
  chatForm.addEventListener('submit', (e) => {
    e.preventDefault();
    sendMessage();
  });

  // Send on Enter
  msgInput.addEventListener('keydown', e => {
    if (e.key === 'Enter' && !e.shiftKey) { 
      e.preventDefault();
      sendMessage();
    }
  });
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>




