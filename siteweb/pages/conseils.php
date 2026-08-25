<?php
$pageTitle = 'Conseils Carrière - TGTravail';
$activePage = 'conseils';
require_once __DIR__ . '/../config/db.php';
$db = getDB();

$q = trim($_GET['q'] ?? '');
$cat = trim($_GET['cat'] ?? '');

$sql = "SELECT * FROM conseils WHERE 1=1";
$params = [];

if ($q !== '') {
    $sql .= " AND (titre LIKE ? OR contenu LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
}
if ($cat !== '') {
    $sql .= " AND categorie = ?";
    $params[] = $cat;
}
$sql .= " ORDER BY created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$conseils = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<main style="background: var(--bg-body); min-height: 100vh;">
  <!-- Hero Conseils -->
  <section style="background: linear-gradient(135deg, #081326 0%, #1E3A5F 100%); padding: 5rem 0; color: white; text-align: center; position: relative; overflow: hidden;">
    <div style="position: absolute; top: -50%; left: -10%; width: 50%; height: 200%; background: radial-gradient(circle, rgba(37,99,235,0.15) 0%, transparent 70%); transform: rotate(30deg); pointer-events: none;"></div>
    <div style="position: absolute; bottom: -50%; right: -10%; width: 50%; height: 200%; background: radial-gradient(circle, rgba(255,184,0,0.1) 0%, transparent 70%); transform: rotate(-30deg); pointer-events: none;"></div>
    
    <div class="container" style="position: relative; z-index: 1;" data-aos="fade-up">
      <div style="display: inline-block; background: rgba(255,184,0,0.1); border: 1px solid rgba(255,184,0,0.3); color: #FFB800; padding: 0.35rem 1rem; border-radius: 99px; font-size: 0.85rem; font-weight: 700; margin-bottom: 1.5rem; letter-spacing: 0.05em; text-transform: uppercase;">
        Ressources & Accompagnement
      </div>
      <h1 style="font-size: 3rem; font-weight: 800; margin-bottom: 1.25rem; letter-spacing: -0.02em; line-height: 1.2;">Boostez votre carrière 🚀</h1>
      <p style="font-size: 1.15rem; color: #CBD5E1; max-width: 650px; margin: 0 auto 2.5rem; line-height: 1.6;">
        Découvrez nos meilleurs conseils pour réussir vos entretiens, optimiser votre CV et vous démarquer auprès des recruteurs au Togo.
      </p>
      
      <!-- Search Input -->
      <form action="../pages/conseils.php" method="GET" style="max-width: 550px; margin: 0 auto; position: relative; box-shadow: 0 10px 30px rgba(0,0,0,0.15); border-radius: 9999px;">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="2" style="position: absolute; left: 20px; top: 50%; transform: translateY(-50%); z-index: 2;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Rechercher un conseil (ex: Entretien, CV...)" style="width: 100%; padding: 1.2rem 1.5rem 1.2rem 3.5rem; border-radius: 9999px; border: 2px solid transparent; outline: none; font-size: 1rem; color: #0F172A; transition: all 0.3s;" onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 4px rgba(37,99,235,0.1)'" onblur="this.style.borderColor='transparent'; this.style.boxShadow='none'">
        <?php if($cat !== ''): ?><input type="hidden" name="cat" value="<?= htmlspecialchars($cat) ?>"><?php endif; ?>
        <button type="submit" style="position: absolute; right: 6px; top: 6px; bottom: 6px; background: #2563EB; color: white; border: none; border-radius: 9999px; padding: 0 1.75rem; font-weight: 700; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#1D4ED8'" onmouseout="this.style.background='#2563EB'">
          Rechercher
        </button>
      </form>
    </div>
  </section>

  <!-- Categories -->
  <section style="padding: 2rem 0; border-bottom: 1px solid var(--border-light); background: white;">
    <div class="container" style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;" data-aos="fade-up" data-aos-delay="100">
      <a href="../pages/conseils.php" style="padding: 0.5rem 1.5rem; border-radius: 9999px; <?= $cat === '' ? 'background: var(--color-primary-blue); color: white;' : 'background: #F1F5F9; color: #475569;' ?> font-weight: 600; text-decoration: none; font-size: 0.9rem;">Tous les articles</a>
      <a href="cat=CV+%26+Lettre" style="padding: 0.5rem 1.5rem; border-radius: 9999px; <?= $cat === 'CV & Lettre' ? 'background: var(--color-primary-blue); color: white;' : 'background: #F1F5F9; color: #475569;' ?> font-weight: 600; text-decoration: none; font-size: 0.9rem;">CV & Lettre</a>
      <a href="cat=Entretien" style="padding: 0.5rem 1.5rem; border-radius: 9999px; <?= $cat === 'Entretien' ? 'background: var(--color-primary-blue); color: white;' : 'background: #F1F5F9; color: #475569;' ?> font-weight: 600; text-decoration: none; font-size: 0.9rem;">Entretien</a>
      <a href="cat=March%C3%A9+de+l%27emploi" style="padding: 0.5rem 1.5rem; border-radius: 9999px; <?= $cat === 'Marché de l\'emploi' ? 'background: var(--color-primary-blue); color: white;' : 'background: #F1F5F9; color: #475569;' ?> font-weight: 600; text-decoration: none; font-size: 0.9rem;">Marché de l'emploi</a>
    </div>
  </section>

  <!-- Grille d'articles -->
  <section style="padding: 4rem 0;">
    <div class="container">
      <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 2.5rem;">
        <?php if(empty($conseils)): ?>
          <div data-aos="fade-up" style="grid-column: 1 / -1; text-align: center; padding: 5rem 2rem; display: flex; flex-direction: column; align-items: center; justify-content: center; width: 100%;">
            <div style="font-size: 4rem; margin-bottom: 1.25rem;">🔍</div>
            <h3 style="font-size: 1.35rem; font-weight: 700; color: #0F172A; margin-bottom: 0.5rem;">Aucun conseil trouvé</h3>
            <p style="color: #64748B; max-width: 400px; margin: 0 auto;">Essayez d'autres mots-clés ou naviguez via les catégories.</p>
          </div>
        <?php else: ?>
          <?php $delay = 100; foreach($conseils as $c): 
            // Theme by category
            if ($c['categorie'] === 'CV & Lettre') {
                $bg_grad = 'linear-gradient(135deg, #DBEAFE 0%, #EFF6FF 100%)';
                $bg_pill = '#DBEAFE'; $col_pill = '#1D4ED8';
                $circle1 = 'rgba(255,255,255,0.4)'; $circle2 = 'rgba(59,130,246,0.1)';
            } elseif ($c['categorie'] === 'Entretien') {
                $bg_grad = 'linear-gradient(135deg, #FEF3C7 0%, #FFFBEB 100%)';
                $bg_pill = '#FEF3C7'; $col_pill = '#B45309';
                $circle1 = 'rgba(255,255,255,0.6)'; $circle2 = 'rgba(245,158,11,0.1)';
            } else {
                $bg_grad = 'linear-gradient(135deg, #ECFDF5 0%, #F0FDF4 100%)';
                $bg_pill = '#ECFDF5'; $col_pill = '#059669';
                $circle1 = 'rgba(255,255,255,0.6)'; $circle2 = 'rgba(16,185,129,0.1)';
            }
          ?>
          <article data-aos="fade-up" data-aos-delay="<?= $delay ?>" style="background: white; border-radius: 20px; overflow: hidden; border: 1px solid #E2E8F0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); display: flex; flex-direction: column;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 20px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1)'; this.style.borderColor='#CBD5E1'" onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 6px -1px rgba(0,0,0,0.05)'; this.style.borderColor='#E2E8F0'">
            <div style="height: 220px; background: <?= $bg_grad ?>; display: flex; align-items: center; justify-content: center; font-size: 4rem; position: relative; overflow: hidden;">
              <div style="position: absolute; top: -20px; right: -20px; width: 100px; height: 100px; background: <?= $circle1 ?>; border-radius: 50%; blur(10px);"></div>
              <div style="position: absolute; bottom: -30px; left: -10px; width: 120px; height: 120px; background: <?= $circle2 ?>; border-radius: 50%;"></div>
              <span style="filter: drop-shadow(0 10px 8px rgba(0,0,0,0.1)); position: relative; z-index: 1;"><?= htmlspecialchars($c['icone']) ?></span>
            </div>
            <div style="padding: 1.75rem; flex: 1; display: flex; flex-direction: column;">
              <div style="display: flex; gap: 0.75rem; margin-bottom: 1.25rem;">
                <span style="font-size: 0.75rem; font-weight: 700; background: <?= $bg_pill ?>; color: <?= $col_pill ?>; padding: 0.35rem 0.85rem; border-radius: 99px;"><?= htmlspecialchars($c['categorie']) ?></span>
                <span style="font-size: 0.75rem; font-weight: 600; color: #64748B; display: flex; align-items: center; gap: 0.25rem;">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                  <?= $c['temps_lecture'] ?> min
                </span>
              </div>
              <h3 style="font-size: 1.35rem; font-weight: 800; color: #0F172A; margin-bottom: 0.85rem; line-height: 1.4;"><?= htmlspecialchars($c['titre']) ?></h3>
              <p style="color: #475569; font-size: 0.95rem; margin-bottom: 1.75rem; line-height: 1.6; flex: 1;">
                <?= htmlspecialchars(mb_strimwidth(strip_tags($c['contenu']), 0, 150, '...')) ?>
              </p>
              <a href="id=<?= $c['id'] ?>" style="color: #2563EB; font-weight: 700; text-decoration: none; display: flex; align-items: center; gap: 0.5rem; width: fit-content; padding-bottom: 2px; border-bottom: 2px solid transparent; transition: border-color 0.2s;" onmouseover="this.style.borderColor='#2563EB'" onmouseout="this.style.borderColor='transparent'">Lire l'article <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></a>
            </div>
          </article>
          <?php $delay += 100; endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>




