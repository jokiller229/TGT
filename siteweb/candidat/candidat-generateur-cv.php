<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('candidat');

$activePage = 'generateur-cv';
$userId = $_SESSION['user_id'];
$pdo = getDB();

// Fetch user & profile info
$stmt = $pdo->prepare("
  SELECT u.nom, u.email, u.telephone, u.avatar, 
         p.titre_professionnel, p.bio, p.ville, p.experience_annees, 
         p.disponibilite, p.type_contrat_souhaite, p.competences 
  FROM users u
  LEFT JOIN candidate_profiles p ON u.id = p.user_id
  WHERE u.id = ?
");
$stmt->execute([$userId]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$profile) {
    die("Profil introuvable.");
}

// Fetch active templates
$stmtTemplates = $pdo->prepare("SELECT id, name, slug, html_content, css_content FROM cv_templates WHERE is_active = 1 ORDER BY id ASC");
$stmtTemplates->execute();
$templates = $stmtTemplates->fetchAll(PDO::FETCH_ASSOC);

$hideHeader = true;
include __DIR__ . '/../includes/header.php';
?>

<!-- HTML2PDF CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<style>
/* Dashboard Styles */
.cv-builder-container {
    display: flex;
    flex-direction: column;
    gap: 2rem;
    max-width: 1000px;
    margin: 0 auto;
}

.cv-controls {
    background: var(--bg-surface);
    padding: 1.5rem;
    border-radius: var(--radius-xl);
    border: 1px solid var(--border-light);
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: var(--shadow-sm);
}

.template-selector {
    display: flex;
    gap: 1rem;
}

.template-btn {
    padding: 0.5rem 1.25rem;
    border-radius: var(--radius-pill);
    border: 1px solid var(--border-light);
    background: transparent;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.2s;
    color: var(--color-primary-dark);
}

.template-btn.active {
    background: var(--color-primary-blue);
    color: white;
    border-color: var(--color-primary-blue);
}

/* CV A4 Container */
.cv-preview-wrapper {
    background: #e2e8f0;
    padding: 2rem;
    border-radius: var(--radius-xl);
    display: flex;
    justify-content: center;
    overflow-x: auto;
}

#cv-document {
    /* Forcer la taille A4 exacte en pixels (96dpi) pour éviter que les mobiles n'écrasent le design */
    width: 794px !important;
    min-width: 794px !important;
    max-width: 794px !important;
    min-height: 1123px !important;
    background: white;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    position: relative;
    overflow: hidden;
    margin: 0 auto;
}

@media (max-width: 768px) {
    .cv-controls {
        flex-direction: column;
        align-items: stretch !important;
        gap: 1rem;
        padding: 1rem !important;
    }
    .template-selector {
        flex-wrap: wrap;
        justify-content: center;
    }
    .template-btn {
        flex: 1 1 calc(33.333% - 1rem);
        padding: 0.5rem;
        font-size: 0.85rem;
        text-align: center;
    }
    .cv-controls .btn-primary {
        width: 100%;
        text-align: center;
        padding: 0.75rem;
        font-size: 0.95rem;
    }
    .cv-preview-wrapper {
        padding: 1rem 0 !important;
        background: transparent !important;
        overflow: hidden !important;
        display: flex;
        justify-content: center;
    }
    #cv-document {
        /* On scale par rapport à 794px pour que ça rentre dans les ~360px d'un mobile */
        transform: scale(0.42);
        transform-origin: top center;
        margin-bottom: -650px; /* Compensation du scale pour la hauteur (1123 * 0.58 environ) */
    }
}
@media (max-width: 480px) {
    #cv-document {
        transform: scale(0.40);
        margin-bottom: -670px;
    }
}

/* Les modèles de CV spécifiques (.tpl-moderne, .tpl-classique...) sont désormais chargés dynamiquement depuis la base de données (table cv_templates) */

/* Canva-Lite Editor Styles */
[contenteditable="true"] {
    outline: none;
    transition: background 0.2s, box-shadow 0.2s;
    border-radius: 4px;
}
[contenteditable="true"]:hover {
    background: rgba(0, 0, 0, 0.03);
    box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.05);
    cursor: text;
}
[contenteditable="true"]:focus {
    background: rgba(255, 255, 255, 0.9);
    box-shadow: 0 0 0 2px var(--color-primary-blue);
}
.draggable-block {
    cursor: grab;
    position: relative;
    z-index: 10;
}
.draggable-block:active {
    cursor: grabbing;
    z-index: 100;
}
.draggable-block:hover {
    outline: 1px dashed rgba(0,0,0,0.2);
}
</style>

<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/candidat_sidebar.php'; ?>

  <main class="dashboard-content-main">
    <div class="dashboard-header" style="margin-bottom: 2rem;">
      <div class="dashboard-header-left" style="display:flex; align-items:center; gap:1rem;">
        <button class="dashboard-mobile-btn" onclick="document.querySelector('.dashboard-sidebar-dark').classList.toggle('open'); event.stopPropagation();">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>
        <div>
          <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--color-primary-dark);">Générateur de CV</h1>
          <p style="color: var(--text-muted); margin-top: 0.25rem;">Choisissez un modèle et générez un PDF professionnel à partir de votre profil.</p>
        </div>
      </div>
    </div>

    <div class="cv-builder-container">
      <!-- Controls -->
      <div class="cv-controls">
        <div class="template-selector">
          <?php if (empty($templates)): ?>
            <p style="color:var(--text-muted); font-size:0.9rem;">Aucun modèle disponible.</p>
          <?php else: ?>
            <?php foreach ($templates as $index => $tpl): ?>
              <button class="template-btn <?= $index === 0 ? 'active' : '' ?>" onclick="switchTemplate(event, '<?= htmlspecialchars($tpl['slug']) ?>')">
                <?= htmlspecialchars($tpl['name']) ?>
              </button>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
        <div style="display:flex; align-items:center; gap:1rem;">
          <input type="color" id="theme-color" value="#0f172a" title="Couleur du thème" style="width:40px; height:40px; border:none; border-radius:8px; cursor:pointer;" onchange="changeThemeColor(this.value)">
          <button class="btn-primary" onclick="downloadPDF()">
            📥 Télécharger mon CV (PDF)
          </button>
        </div>
          📥 Télécharger mon CV (PDF)
        </button>
      </div>

      <!-- Preview Area -->
      <div class="cv-preview-wrapper">
        <p style="text-align:center; color:var(--text-muted); font-size:0.85rem; margin-bottom:1rem; width:100%;">💡 Astuce : Cliquez sur n'importe quel texte pour le modifier. Glissez-déposez les blocs pour les déplacer.</p>
        <div id="cv-document" class="tpl-<?= !empty($templates) ? htmlspecialchars($templates[0]['slug']) : '' ?>">
          <!-- Le contenu est généré dynamiquement par JavaScript lors du premier chargement -->
        </div>
      </div>
    </div>
  </main>
</div>

<script>
// Data for rendering different templates
const cvData = {
    nom: <?= json_encode($profile['nom'] ?? '') ?>,
    titre: <?= json_encode($profile['titre_professionnel'] ?? '') ?>,
    email: <?= json_encode($profile['email'] ?? '') ?>,
    telephone: <?= json_encode($profile['telephone'] ?? '') ?>,
    ville: <?= json_encode($profile['ville'] ?? '') ?>,
    bio: <?= json_encode(nl2br(htmlspecialchars($profile['bio'] ?? ''))) ?>,
    experience: <?= json_encode((int)($profile['experience_annees'] ?? 0)) ?>,
    disponibilite: <?= json_encode($profile['disponibilite'] ?? '') ?>,
    contrat: <?= json_encode($profile['type_contrat_souhaite'] ?? '') ?>,
    competences: <?= json_encode(array_filter(array_map('trim', explode(',', $profile['competences'] ?? '')))) ?>,
    avatar: <?= json_encode(!empty($profile['avatar']) && file_exists(__DIR__ . '/../' . $profile['avatar']) ? '../' . $profile['avatar'] : '') ?>
};

const dbTemplates = <?= json_encode($templates) ?>;

function renderDynamicTemplate(slug) {
    const tpl = dbTemplates.find(t => t.slug === slug);
    if (!tpl) return '';
    
    // Inject CSS
    let styleTag = document.getElementById('dynamic-tpl-style');
    if (!styleTag) {
        styleTag = document.createElement('style');
        styleTag.id = 'dynamic-tpl-style';
        document.head.appendChild(styleTag);
    }
    styleTag.innerHTML = tpl.css_content;
    
    // Process HTML variables
    let html = tpl.html_content;
    html = html.replace(/{{nom}}/g, cvData.nom);
    html = html.replace(/{{titre}}/g, cvData.titre);
    html = html.replace(/{{email}}/g, cvData.email);
    html = html.replace(/{{telephone}}/g, cvData.telephone);
    html = html.replace(/{{ville}}/g, cvData.ville);
    html = html.replace(/{{bio}}/g, cvData.bio || 'Aucune biographie renseignée.');
    html = html.replace(/{{experience}}/g, cvData.experience);
    html = html.replace(/{{disponibilite}}/g, cvData.disponibilite);
    html = html.replace(/{{contrat}}/g, cvData.contrat);
    
    let badges = cvData.competences.length > 0 ? cvData.competences.map(c => `<span class='skill-badge'>\${c}</span>`).join('') : '<i>Non renseigné</i>';
    let bullets = cvData.competences.length > 0 ? cvData.competences.map(c => `<span class="skill-bullet">\${c}</span>`).join('') : '<i>Non renseigné</i>';
    let tags = cvData.competences.length > 0 ? cvData.competences.map(c => `<span class='skill-text'>\${c}</span>`).join('') : '<i>Non renseigné</i>';
    
    html = html.replace(/{{competences_html_badges}}/g, badges);
    html = html.replace(/{{competences_html_bullets}}/g, bullets);
    html = html.replace(/{{competences_html_tags}}/g, tags);
    
    let avatarHtml = cvData.avatar ? `<img src="\${cvData.avatar}" style="width:100%; height:100%; object-fit:cover;" />` : cvData.nom.substring(0,2).toUpperCase();
    html = html.replace(/{{avatar_html}}/g, avatarHtml);
    
    return html;
}

function switchTemplate(e, templateSlug) {
    if (e) {
        document.querySelectorAll('.template-btn').forEach(btn => btn.classList.remove('active'));
        if (e.target) e.target.classList.add('active');
    }
    
    const container = document.getElementById('cv-document');
    container.className = 'tpl-' + templateSlug;
    container.innerHTML = renderDynamicTemplate(templateSlug);
}

// Initial render
window.addEventListener('DOMContentLoaded', () => {
    if(dbTemplates.length > 0) {
        switchTemplate(null, dbTemplates[0].slug);
    }
});

function downloadPDF() {
    const element = document.getElementById('cv-document');
    
    // Désactiver le scale temporairement pour l'export PDF (pour ne pas avoir une image floue/réduite)
    element.style.transform = 'none';
    
    const opt = {
      margin:       0,
      filename:     'CV_' + cvData.nom.replace(/\s+/g, '_') + '.pdf',
      image:        { type: 'jpeg', quality: 0.98 },
      html2canvas:  { scale: 2 },
      jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };

    html2pdf().set(opt).from(element).save().then(() => {
        // Restaurer le scale (le CSS media query reprend le relais si on vide l'attribut)
        element.style.transform = '';
    });
}

function changeThemeColor(color) {
    const doc = document.getElementById('cv-document');
    doc.style.setProperty('--color-primary-dark', color);
    doc.style.setProperty('--color-primary-blue', color);
}

// === Canva-Lite Drag & Drop Engine ===
let isDragging = false;
let currentBlock = null;
let startX, startY, initialX, initialY;

function onDragStart(e) {
    const block = e.target.closest('.draggable-block');
    if (!block) return;
    
    // Ignore if clicking on an editable text area that is currently focused
    if (e.target.isContentEditable && document.activeElement === e.target) return;

    isDragging = true;
    currentBlock = block;
    
    // Get current transform translate values
    const style = window.getComputedStyle(currentBlock);
    const matrix = new WebKitCSSMatrix(style.transform);
    initialX = matrix.m41;
    initialY = matrix.m42;
    
    startX = e.clientX || e.touches[0].clientX;
    startY = e.clientY || e.touches[0].clientY;
    
    if (!e.target.isContentEditable && e.type !== 'touchstart') {
        e.preventDefault();
    }
}

function onDragMove(e) {
    if (!isDragging || !currentBlock) return;
    
    const clientX = e.clientX || (e.touches ? e.touches[0].clientX : 0);
    const clientY = e.clientY || (e.touches ? e.touches[0].clientY : 0);
    
    const dx = clientX - startX;
    const dy = clientY - startY;
    
    // Convert screen pixel delta to potentially scaled delta if on mobile, 
    // but for simplicity, we apply raw delta. For perfect 1:1 follow on mobile, 
    // we would divide by the scale factor (0.45).
    const scale = window.innerWidth <= 768 ? (window.innerWidth <= 480 ? 0.42 : 0.45) : 1;
    
    currentBlock.style.transform = `translate(${initialX + (dx / scale)}px, ${initialY + (dy / scale)}px)`;
    
    if (e.type === 'touchmove') e.preventDefault();
}

function onDragEnd(e) {
    isDragging = false;
    currentBlock = null;
}

// Mouse events
document.addEventListener('mousedown', onDragStart);
document.addEventListener('mousemove', onDragMove);
document.addEventListener('mouseup', onDragEnd);

// Touch events for mobile
document.addEventListener('touchstart', onDragStart, {passive: false});
document.addEventListener('touchmove', onDragMove, {passive: false});
document.addEventListener('touchend', onDragEnd);

</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>




