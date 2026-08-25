<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('candidat');

$activePage = 'mes-candidatures';
$userId = $_SESSION['user_id'];
$pdo = getDB();

// Fetch candidatures
$stmt = $pdo->prepare("
  SELECT a.*, j.titre, j.lieu as ville, j.type_contrat, comp.nom as raison_sociale, comp.logo as logo_url 
  FROM applications a
  JOIN jobs j ON a.job_id = j.id
  JOIN companies comp ON j.company_id = comp.id
  WHERE a.candidate_id = ?
  ORDER BY a.created_at DESC
");
$stmt->execute([$userId]);
$candidatures = $stmt->fetchAll();

$hideHeader = true;
include __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-wrapper">
  <?php include __DIR__ . '/../includes/candidat_sidebar.php'; ?>

  <main class="dashboard-content-main">

    <div class="dashboard-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem; flex-wrap:wrap; gap:1rem;">
      <div class="dashboard-header-left" style="display:flex; align-items:center; gap:1rem;">
        <button class="dashboard-mobile-btn" onclick="document.querySelector('.dashboard-sidebar-dark').classList.toggle('open'); event.stopPropagation();">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>
        <div>
        <h1 style="font-size:1.75rem; font-weight:800; color:var(--color-primary-dark);">Mes candidatures</h1>
        <p style="color:var(--text-muted); margin-top:0.25rem;">
          <?= count($candidatures) ?> candidature<?= count($candidatures) > 1 ? 's' : '' ?> envoyée<?= count($candidatures) > 1 ? 's' : '' ?>
        </p>
      </div>
      </div>
      <a href="../pages/offres.php" style="display:inline-flex; align-items:center; gap:0.4rem; padding:0.6rem 1.25rem; border-radius:10px; background:#2563EB; color:#FFF; font-weight:700; font-size:0.875rem; text-decoration:none;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        Parcourir les offres
      </a>
    </div>

    <?php if (empty($candidatures)): ?>
      <!-- État vide engageant -->
      <div style="text-align:center; padding:4rem 2rem; background:var(--bg-surface); border:1px solid var(--border-light); border-radius:20px;">
        <div style="width:72px; height:72px; background:#EFF6FF; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1.25rem;">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        </div>
        <h2 style="font-size:1.2rem; font-weight:800; color:#081326; margin:0 0 0.5rem;">Vous n'avez pas encore postulé</h2>
        <p style="color:#64748B; font-size:0.9rem; margin:0 0 1.5rem; max-width:360px; margin-left:auto; margin-right:auto;">Des centaines d'offres vous attendent. Trouvez celle qui correspond à votre profil et postulez en 2 minutes.</p>
        <a href="../pages/offres.php" style="display:inline-block; padding:0.75rem 1.75rem; border-radius:12px; background:#2563EB; color:#FFF; font-weight:700; font-size:0.9rem; text-decoration:none;">
          Découvrir les offres disponibles
        </a>
      </div>

    <?php else: ?>

      <!-- Filtres par statut -->
      <?php
        $counts = ['tous' => count($candidatures)];
        $groupMap = [
          'pending'   => ['nouveau','en_attente','evaluation'],
          'entretien' => ['entretien'],
          'refuse'    => ['refuse','refusee'],
        ];
        foreach ($groupMap as $key => $vals) {
            $counts[$key] = count(array_filter($candidatures, fn($c) => in_array($c['statut'], $vals)));
        }
        $activeFilter = $_GET['filter'] ?? 'tous';
      ?>
      <div style="display:flex; gap:0.5rem; margin-bottom:1.5rem; flex-wrap:wrap;">
        <?php
          $tabs = [
            'tous'      => ['label' => 'Toutes',         'color' => '#2563EB', 'bg' => '#EFF6FF'],
            'pending'   => ['label' => 'En attente',     'color' => '#D97706', 'bg' => '#FFFBEB'],
            'entretien' => ['label' => 'Entretien',      'color' => '#059669', 'bg' => '#ECFDF5'],
            'refuse'    => ['label' => 'Non retenues',   'color' => '#DC2626', 'bg' => '#FEF2F2'],
          ];
          foreach ($tabs as $key => $tab):
            $isActive = ($activeFilter === $key);
        ?>
          <a href="?filter=<?= $key ?>"
             style="display:inline-flex; align-items:center; gap:0.4rem; padding:0.5rem 1.1rem; border-radius:99px; font-size:0.82rem; font-weight:700; text-decoration:none; border:1.5px solid <?= $isActive ? $tab['color'] : '#E2E8F0' ?>; background:<?= $isActive ? $tab['bg'] : '#FFF' ?>; color:<?= $isActive ? $tab['color'] : '#64748B' ?>; transition:all 0.15s;">
            <?= $tab['label'] ?>
            <span style="background:<?= $tab['color'] ?>; color:#FFF; font-size:0.7rem; padding:0.1rem 0.45rem; border-radius:99px;"><?= $counts[$key] ?></span>
          </a>
        <?php endforeach; ?>
      </div>

      <div style="background:var(--bg-surface); border:1px solid var(--border-light); border-radius:20px; overflow:hidden; box-shadow:var(--shadow-sm);">
        <?php
          // Apply filter
          $filtered = $candidatures;
          if ($activeFilter === 'pending') {
              $filtered = array_filter($candidatures, fn($c) => in_array($c['statut'], ['nouveau','en_attente','evaluation']));
          } elseif ($activeFilter === 'entretien') {
              $filtered = array_filter($candidatures, fn($c) => in_array($c['statut'], ['entretien']));
          } elseif ($activeFilter === 'refuse') {
              $filtered = array_filter($candidatures, fn($c) => in_array($c['statut'], ['refuse','refusee']));
          }

          if (empty($filtered)):
        ?>
          <div style="text-align:center; padding:3rem 1rem; color:#64748B;">
            <p style="font-size:0.95rem;">Aucune candidature dans cette catégorie.</p>
          </div>
        <?php else: ?>
          <table style="width:100%; border-collapse:collapse; text-align:left;">
            <thead>
              <tr style="background:#F8FAFC; border-bottom:2px solid #E2E8F0;">
                <th style="padding:1rem 1.5rem; color:#64748B; font-size:0.8rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em;">Poste</th>
                <th style="padding:1rem; color:#64748B; font-size:0.8rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em;">Entreprise</th>
                <th style="padding:1rem; color:#64748B; font-size:0.8rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em;">Envoyée le</th>
                <th style="padding:1rem; color:#64748B; font-size:0.8rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em;">Statut</th>
                <th style="padding:1rem; color:#64748B; font-size:0.8rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em;"></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($filtered as $cand):
                $statut = $cand['statut'];
                $statusConfig = [
                  'nouveau'    => ['Envoyée',          '#64748B', '#F1F5F9'],
                  'en_attente' => ['En attente',       '#D97706', '#FFFBEB'],
                  'evaluation' => ['En évaluation',    '#7C3AED', '#F5F3FF'],
                  'vue'        => ['Vue',               '#2563EB', '#EFF6FF'],
                  'retenu'     => ['Retenu(e)',         '#059669', '#ECFDF5'],
                  'entretien'  => ['Entretien prévu',  '#059669', '#ECFDF5'],
                  'embauche'   => ['Recruté(e)',        '#065F46', '#D1FAE5'],
                  'accepte'    => ['Acceptée',         '#065F46', '#D1FAE5'],
                  'refuse'     => ['Non retenu(e)',     '#DC2626', '#FEF2F2'],
                  'refusee'    => ['Non retenu(e)',     '#DC2626', '#FEF2F2'],
                ];
                [$badgeText, $badgeColor, $badgeBg] = $statusConfig[$statut] ?? [ucfirst($statut), '#64748B', '#F1F5F9'];
                $dateAffichee = $cand['created_at'] ?? null;
              ?>
              <tr style="border-bottom:1px solid #F1F5F9; transition:background 0.15s;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background=''">
                <td style="padding:1.25rem 1.5rem;">
                  <div style="font-weight:700; color:#081326; margin-bottom:0.2rem;"><?= htmlspecialchars($cand['titre']) ?></div>
                  <div style="font-size:0.78rem; color:#94A3B8;">
                    <?= htmlspecialchars($cand['ville'] ?? '') ?>
                    <?= !empty($cand['type_contrat']) ? ' · ' . htmlspecialchars($cand['type_contrat']) : '' ?>
                  </div>
                </td>
                <td style="padding:1.25rem 1rem;">
                  <div style="display:flex; align-items:center; gap:0.6rem;">
                    <div style="width:30px; height:30px; border-radius:8px; background:#081326; color:#FFB800; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:0.8rem; flex-shrink:0;">
                      <?= strtoupper(substr($cand['raison_sociale'], 0, 2)) ?>
                    </div>
                    <span style="font-weight:600; color:#334155; font-size:0.875rem;"><?= htmlspecialchars($cand['raison_sociale']) ?></span>
                  </div>
                </td>
                <td style="padding:1.25rem 1rem; color:#94A3B8; font-size:0.85rem;">
                  <?= $dateAffichee ? date('d/m/Y', strtotime($dateAffichee)) : '—' ?>
                </td>
                <td style="padding:1.25rem 1rem;">
                  <span style="display:inline-block; padding:0.3rem 0.85rem; border-radius:99px; font-size:0.75rem; font-weight:700; background:<?= $badgeBg ?>; color:<?= $badgeColor ?>;">
                    <?= $badgeText ?>
                  </span>
                </td>
                <td style="padding:1.25rem 1rem; text-align:right;">
                  <a href="id=<?= $cand['job_id'] ?>" style="font-size:0.8rem; color:#2563EB; text-decoration:none; font-weight:600;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">Voir l'offre</a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>

    <?php endif; ?>

  </main>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>




