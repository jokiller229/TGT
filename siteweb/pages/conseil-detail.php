<?php
$pageTitle = 'Détail du conseil - TGTravail';
$activePage = 'conseils';
require_once __DIR__ . '/../config/db.php';
$db = getDB();

$id = (int)($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT * FROM conseils WHERE id = ?");
$stmt->execute([$id]);
$conseil = $stmt->fetch();

if (!$conseil) {
    header("Location: ../pages/conseils.php");
    exit;
}

$pageTitle = htmlspecialchars($conseil['titre']) . ' - Conseils TGTravail';
require_once __DIR__ . '/../includes/header.php';

// Styles by category
if ($conseil['categorie'] === 'CV & Lettre') {
    $bg_grad = 'linear-gradient(135deg, #DBEAFE 0%, #EFF6FF 100%)';
    $bg_pill = '#DBEAFE'; $col_pill = '#1D4ED8';
} elseif ($conseil['categorie'] === 'Entretien') {
    $bg_grad = 'linear-gradient(135deg, #FEF3C7 0%, #FFFBEB 100%)';
    $bg_pill = '#FEF3C7'; $col_pill = '#B45309';
} else {
    $bg_grad = 'linear-gradient(135deg, #ECFDF5 0%, #F0FDF4 100%)';
    $bg_pill = '#ECFDF5'; $col_pill = '#059669';
}
?>

<main style="background: #F8FAFC; min-height: 100vh; padding-bottom: 5rem;">
  <!-- Article Header -->
  <section style="background: <?= $bg_grad ?>; padding: 4rem 0 6rem; text-align: center; position: relative;">
    <div class="container" data-aos="fade-up">
      <div style="display: flex; justify-content: center; gap: 1rem; margin-bottom: 1.5rem;">
        <span style="font-size: 0.85rem; font-weight: 700; background: <?= $bg_pill ?>; color: <?= $col_pill ?>; padding: 0.35rem 1rem; border-radius: 99px;"><?= htmlspecialchars($conseil['categorie']) ?></span>
        <span style="font-size: 0.85rem; font-weight: 600; color: #475569; display: flex; align-items: center; gap: 0.25rem; background: rgba(255,255,255,0.6); padding: 0.35rem 1rem; border-radius: 99px;">
          ⏳ <?= $conseil['temps_lecture'] ?> min
        </span>
      </div>
      <h1 style="font-size: 2.5rem; font-weight: 800; color: #0F172A; max-width: 800px; margin: 0 auto 1.5rem; line-height: 1.3;">
        <?= htmlspecialchars($conseil['titre']) ?>
      </h1>
      <p style="color: #64748B; font-weight: 500;">
        Publié le <?= date('d/m/Y', strtotime($conseil['created_at'])) ?>
      </p>
    </div>
  </section>

  <!-- Article Content -->
  <section style="margin-top: -4rem;">
    <div class="container">
      <div style="max-width: 800px; margin: 0 auto; background: white; border-radius: 24px; padding: 3rem; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); position: relative; z-index: 10;" data-aos="fade-up" data-aos-delay="200">
        
        <div style="font-size: 4rem; text-align: center; margin-bottom: 2rem;">
          <?= htmlspecialchars($conseil['icone']) ?>
        </div>

        <div style="font-size: 1.1rem; line-height: 1.8; color: #334155;">
          <?= nl2br(htmlspecialchars($conseil['contenu'])) ?>
        </div>
        
        <hr style="border: 0; border-top: 1px solid #E2E8F0; margin: 3rem 0;">
        
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <a href="../pages/conseils.php" style="color: #2563EB; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 0.5rem;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            Retour aux conseils
          </a>
        </div>
      </div>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>




