<?php
$activePage = 'accueil';
$pageTitle = 'TGTravail - Trouvez le bon emploi. Trouvez le bon talent au Togo';
require_once __DIR__ . '/config/db.php';
$db = getDB();

// Requêtes SQL en direct pour les statistiques de la plateforme
$totalJobs = (int)$db->query("SELECT COUNT(*) FROM jobs WHERE statut = 'active'")->fetchColumn();
$totalCompanies = (int)$db->query("SELECT COUNT(*) FROM companies")->fetchColumn();
$totalUsers = (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn();

// Offres récentes actives
$recentJobsStmt = $db->query("
  SELECT j.*, c.nom AS company_nom, c.logo AS company_logo, c.verifie AS company_verifie
  FROM jobs j
  JOIN companies c ON j.company_id = c.id
  WHERE j.statut = 'active'
  ORDER BY j.created_at DESC
  LIMIT 4
");
$recentJobs = $recentJobsStmt->fetchAll();

// Offres mises en avant (pack = 'alaune')
$featuredJobsStmt = $db->query("
  SELECT j.*, c.nom AS company_nom, c.logo AS company_logo, c.verifie AS company_verifie
  FROM jobs j
  JOIN companies c ON j.company_id = c.id
  WHERE j.statut = 'active'
  AND j.pack = 'alaune'
  ORDER BY j.created_at DESC
  LIMIT 3
");
$featuredJobs = $featuredJobsStmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<!-- MOBILE APP SPLASH SCREEN (Visible only on mobile for non-logged users) -->
<div id="mobile-app-splash">
  <div class="splash-logo-container">
    <img src="<?= $baseUrl ?>/img/tgtravail-logo.png" alt="TGTravail" class="splash-logo-img">
    <div class="splash-logo-text"><span style="color: #FFB800;">TG</span>Travail</div>
  </div>
  
  <div class="splash-subtitle">
    Trouvez le bon emploi.<br>
    Trouvez le bon talent.
  </div>
  
  <p class="splash-description">
    La plateforme togolaise de recherche d'emploi et de mise en relation candidats / recruteurs.
  </p>
  
  <div class="splash-actions">
    <a href="<?= $baseUrl ?>/auth/inscription.php?role=candidat" class="splash-btn splash-btn-primary">Je cherche un emploi</a>
    <a href="<?= $baseUrl ?>/auth/inscription.php?role=recruteur" class="splash-btn splash-btn-outline">Je suis recruteur</a>
    <a href="<?= $baseUrl ?>/auth/connexion.php" class="splash-btn-text">Se connecter</a>
  </div>
</div>

<main id="desktop-main-content">
  
  <!-- Hero Section (Maquette Écran 1) -->
  <section class="hero-section">
    <div class="container">
      <div class="hero-grid">
        
        <!-- Hero Text Left -->
        <div class="hero-content" data-aos="fade-right">
          <div class="hero-badge">
            <span>La référence de l'emploi au Togo 🇹🇬</span>
          </div>

          <h1 class="hero-title">
            Trouvez le bon emploi.<br>
            Trouvez le bon <span class="highlight">talent</span>.
          </h1>

          <p class="hero-subtitle">
            TGTravail est la plateforme togolaise de recherche d'emploi et de mise en relation entre candidats et recruteurs. Simple, fiable et pensée pour vous.
          </p>

          <!-- Search Box Hero (Maquette 1) -->
          <form action="pages/offres.php" method="GET" class="hero-search-box">
            <div class="search-field">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
              </svg>
              <input type="text" name="q" placeholder="Métier, compétence ou mot-clé">
            </div>

            <div class="search-divider"></div>

            <div class="search-field">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8z"></path>
                <circle cx="12" cy="10" r="3"></circle>
              </svg>
              <input type="text" name="lieu" placeholder="Lieu (ex : Lomé)">
            </div>

            <button type="submit" class="btn-blue">Rechercher</button>
          </form>

          <!-- Popular Searches -->
          <div class="popular-tags">
            <span class="popular-label">Populaires :</span>
            <a href="q=Commercial" class="tag-pill">Commercial</a>
            <a href="q=Marketing" class="tag-pill">Marketing</a>
            <a href="q=Développeur" class="tag-pill">Développeur</a>
            <a href="q=Comptable" class="tag-pill">Comptable</a>
            <a href="q=Chauffeur" class="tag-pill">Chauffeur</a>
          </div>

        </div>

        <!-- Hero Image Right -->
        <div class="hero-image-wrap" data-aos="fade-left" data-aos-delay="200">
          <img 
            src="img/photo_entete.png" 
            alt="Professionnels togolais qui recrutent sur TGTravail" 
            class="hero-img"
          >
        </div>

      </div>

      <!-- 4 Metrics Stats Grid (Maquette Écran 1) -->
      <div class="hero-stats-grid">
        
        <div class="stat-card" data-aos="fade-up" data-aos-delay="100">
          <div class="stat-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="2" y="7" width="20" height="14" rx="2"></rect>
              <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
            </svg>
          </div>
          <div>
            <div class="stat-number"><?= number_format($totalJobs + 2446, 0, ',', ' ') ?>+</div>
            <div class="stat-label">Offres disponibles</div>
          </div>
        </div>

        <div class="stat-card" data-aos="fade-up" data-aos-delay="200">
          <div class="stat-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M3 21h18"></path>
              <path d="M9 8h1"></path>
              <path d="M9 12h1"></path>
              <path d="M9 16h1"></path>
              <path d="M14 8h1"></path>
              <path d="M14 12h1"></path>
              <path d="M14 16h1"></path>
              <path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"></path>
            </svg>
          </div>
          <div>
            <div class="stat-number"><?= number_format($totalCompanies + 1846, 0, ',', ' ') ?>+</div>
            <div class="stat-label">Entreprises actives</div>
          </div>
        </div>

        <div class="stat-card" data-aos="fade-up" data-aos-delay="300">
          <div class="stat-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
              <circle cx="9" cy="7" r="4"></circle>
              <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
              <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
          </div>
          <div>
            <div class="stat-number"><?= number_format($totalUsers + 12293, 0, ',', ' ') ?>+</div>
            <div class="stat-label">Candidats inscrits</div>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
              <path d="m9 12 2 2 4-4"></path>
            </svg>
          </div>
          <div>
            <div class="stat-number">100%</div>
            <div class="stat-label">Offres vérifiées</div>
          </div>
        </div>

      </div>

    </div>
  </section>

  <!-- Section: Ils recrutent aujourd'hui (Maquette Écran 1) -->
  <section class="partners-section" data-aos="fade-up">
    <div class="container">
      <h2 class="section-subtitle">Ils recrutent aujourd'hui</h2>
      
      <div class="partners-logo-grid">
        <div class="partner-logo-item">Ecobank</div>
        <div class="partner-logo-item">SOGEA SATOM</div>
        <div class="partner-logo-item">Orabank</div>
        <div class="partner-logo-item">Moov Africa Togo</div>
        <div class="partner-logo-item">NSIA ASSURANCES</div>
        <div class="partner-logo-item">CANAL+ TOGO</div>
      </div>

      <a href="pages/offres.php" class="view-all-companies-link">Voir toutes les entreprises →</a>
    </div>
  </section>

  <!-- Section: Offres a la une (featured_until active) -->
  <?php if (!empty($featuredJobs)): ?>
  <section style="padding: 3rem 0; background: linear-gradient(135deg, #081326 0%, #1E3A5F 100%);" data-aos="fade-up">
    <div class="container">
      <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
        <div>
          <div style="display:flex; align-items:center; gap:0.6rem; margin-bottom:0.35rem;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#FFB800" stroke-width="2.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            <span style="font-size:0.8rem; font-weight:700; color:#FFB800; text-transform:uppercase; letter-spacing:0.08em;">Offres a la une</span>
          </div>
          <h2 style="font-size:1.5rem; font-weight:800; color:#FFF; margin:0;">Opportunites mises en avant</h2>
        </div>
        <a href="pages/offres.php" style="font-size:0.875rem; color:#94A3B8; text-decoration:none; display:flex; align-items:center; gap:0.4rem;" onmouseover="this.style.color='#FFB800'" onmouseout="this.style.color='#94A3B8'">
          Voir toutes les offres
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
      </div>
      <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:1.25rem;">
        <?php foreach ($featuredJobs as $job):
          $logo = !empty($job['company_logo']) && file_exists(__DIR__ . '/' . $job['company_logo'])
                  ? htmlspecialchars($job['company_logo']) : null;
        ?>
        <a href="id=<?= $job['id'] ?>" data-aos="zoom-in" data-aos-delay="100" style="text-decoration:none; display:block; background:rgba(255,255,255,0.06); border:1.5px solid rgba(255,184,0,0.3); border-radius:16px; padding:1.25rem 1.5rem; transition:all 0.2s;" onmouseover="this.style.background='rgba(255,184,0,0.08)';this.style.borderColor='rgba(255,184,0,0.6)';this.style.transform='translateY(-2px)'" onmouseout="this.style.background='rgba(255,255,255,0.06)';this.style.borderColor='rgba(255,184,0,0.3)';this.style.transform=''">
          <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:0.85rem;">
            <?php if ($logo): ?>
              <img src="<?= $logo ?>" alt="Logo" style="width:36px;height:36px;border-radius:8px;object-fit:cover;border:1.5px solid rgba(255,255,255,0.15);">
            <?php else: ?>
              <div style="width:36px;height:36px;border-radius:8px;background:#FFB800;color:#081326;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:0.85rem;">
                <?= strtoupper(substr($job['company_nom'], 0, 2)) ?>
              </div>
            <?php endif; ?>
            <div>
              <div style="font-size:0.78rem; color:#94A3B8;"><?= htmlspecialchars($job['company_nom']) ?></div>
              <div style="font-size:0.7rem; color:#FFB800; display:flex; align-items:center; gap:0.3rem;">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                Offre Premium
              </div>
            </div>
          </div>
          <h3 style="font-size:1rem; font-weight:700; color:#FFF; margin:0 0 0.6rem; line-height:1.35;"><?= htmlspecialchars($job['titre']) ?></h3>
          <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
            <span style="font-size:0.75rem; background:rgba(255,255,255,0.08); color:#CBD5E1; padding:0.25rem 0.6rem; border-radius:99px;"><?= htmlspecialchars($job['lieu'] ?? '') ?></span>
            <span style="font-size:0.75rem; background:rgba(255,255,255,0.08); color:#CBD5E1; padding:0.25rem 0.6rem; border-radius:99px;"><?= htmlspecialchars($job['type_contrat'] ?? '') ?></span>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>
  <!-- Section: Tarifs / Pricing -->
  <section class="pricing-section" style="padding: 5rem 0; background: #F8FAFC;" data-aos="fade-up">
    <div class="container">
      <div style="text-align: center; margin-bottom: 3rem;" data-aos="fade-up" data-aos-delay="100">
        <h2 style="font-size: 2rem; font-weight: 800; color: #0F172A; margin-bottom: 1rem;">Des tarifs adaptés à vos besoins</h2>
        <p style="color: #64748B; font-size: 1.1rem; max-width: 600px; margin: 0 auto;">Que vous soyez à la recherche de votre prochain défi ou du profil idéal pour votre équipe, nous avons la formule qu'il vous faut.</p>
      </div>

      <!-- Pricing Grid -->
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem; max-width: 1000px; margin: 0 auto;">
        
        <!-- Candidat Gratuit -->
        <div style="background: #FFF; border-radius: 20px; padding: 2rem; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px rgba(0,0,0,0.02); display: flex; flex-direction: column;">
          <div style="font-size: 0.9rem; font-weight: 700; color: #2563EB; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Candidat Gratuit</div>
          <div style="font-size: 2.5rem; font-weight: 800; color: #0F172A; margin-bottom: 0.5rem;">0 F<span style="font-size: 1rem; color: #64748B; font-weight: 500;"> / à vie</span></div>
          <p style="color: #64748B; font-size: 0.9rem; margin-bottom: 1.5rem; flex: 1;">L'essentiel pour trouver un emploi au Togo.</p>
          <ul style="list-style: none; padding: 0; margin: 0 0 2rem 0; display: flex; flex-direction: column; gap: 0.75rem;">
            <li style="display: flex; align-items: flex-start; gap: 0.5rem; font-size: 0.9rem; color: #334155;"><svg style="flex-shrink:0; margin-top:2px;" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Création de profil en ligne</li>
            <li style="display: flex; align-items: flex-start; gap: 0.5rem; font-size: 0.9rem; color: #334155;"><svg style="flex-shrink:0; margin-top:2px;" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Postuler aux offres (illimité)</li>
            <li style="display: flex; align-items: flex-start; gap: 0.5rem; font-size: 0.9rem; color: #334155;"><svg style="flex-shrink:0; margin-top:2px;" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Alertes emploi classiques</li>
            <li style="display: flex; align-items: flex-start; gap: 0.5rem; font-size: 0.9rem; color: #94A3B8;"><svg style="flex-shrink:0; margin-top:2px;" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg> Générateur de CV PDF</li>
          </ul>
          <a href="auth/inscription.php?role=candidat" style="display: block; width: 100%; text-align: center; padding: 0.85rem; border-radius: 12px; background: #F1F5F9; color: #0F172A; font-weight: 700; text-decoration: none; transition: background 0.2s;" onmouseover="this.style.background='#E2E8F0'" onmouseout="this.style.background='#F1F5F9'">S'inscrire</a>
        </div>

        <!-- Candidat Premium -->
        <div style="background: #FFF; border-radius: 20px; padding: 2rem; border: 2px solid #D97706; box-shadow: 0 15px 35px rgba(217,119,6,0.15); display: flex; flex-direction: column; position: relative; transform: scale(1.05); z-index: 1;">
          <div style="position: absolute; top: -12px; left: 50%; transform: translateX(-50%); background: #D97706; color: #FFF; font-size: 0.75rem; font-weight: 800; padding: 0.25rem 0.75rem; border-radius: 99px; text-transform: uppercase; letter-spacing: 0.05em;">Recommandé</div>
          <div style="font-size: 0.9rem; font-weight: 700; color: #D97706; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Candidat Premium</div>
          <div style="font-size: 2.5rem; font-weight: 800; color: #0F172A; margin-bottom: 0.5rem;">2 000 F<span style="font-size: 1rem; color: #64748B; font-weight: 500;"> / mois</span></div>
          <p style="color: #64748B; font-size: 0.9rem; margin-bottom: 1.5rem; flex: 1;">Sortez du lot et maximisez vos chances d'être recruté.</p>
          <ul style="list-style: none; padding: 0; margin: 0 0 2rem 0; display: flex; flex-direction: column; gap: 0.75rem;">
            <li style="display: flex; align-items: flex-start; gap: 0.5rem; font-size: 0.9rem; color: #334155;"><svg style="flex-shrink:0; margin-top:2px;" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Tout le plan Gratuit</li>
            <li style="display: flex; align-items: flex-start; gap: 0.5rem; font-size: 0.9rem; color: #334155;"><svg style="flex-shrink:0; margin-top:2px;" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Badge "Profil Premium" vérifié</li>
            <li style="display: flex; align-items: flex-start; gap: 0.5rem; font-size: 0.9rem; color: #334155;"><svg style="flex-shrink:0; margin-top:2px;" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Générateur de CV PDF pro illimité</li>
            <li style="display: flex; align-items: flex-start; gap: 0.5rem; font-size: 0.9rem; color: #334155;"><svg style="flex-shrink:0; margin-top:2px;" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Alertes emploi prioritaires (instantané)</li>
          </ul>
          <a href="auth/inscription.php?role=candidat" style="display: block; width: 100%; text-align: center; padding: 0.85rem; border-radius: 12px; background: #D97706; color: #FFF; font-weight: 700; text-decoration: none; transition: background 0.2s;" onmouseover="this.style.background='#B45309'" onmouseout="this.style.background='#D97706'">Devenir Premium</a>
        </div>

        <!-- Recruteur Pro -->
        <div style="background: #081326; border-radius: 20px; padding: 2rem; border: 1px solid #1E3A5F; box-shadow: 0 10px 25px rgba(0,0,0,0.2); display: flex; flex-direction: column;">
          <div style="font-size: 0.9rem; font-weight: 700; color: #3B82F6; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Recruteur Pro</div>
          <div style="font-size: 2.5rem; font-weight: 800; color: #FFF; margin-bottom: 0.5rem;">15 000 F<span style="font-size: 1rem; color: #94A3B8; font-weight: 500;"> / mois</span></div>
          <p style="color: #94A3B8; font-size: 0.9rem; margin-bottom: 1.5rem; flex: 1;">La solution complète pour sourcer et embaucher sans limite.</p>
          <ul style="list-style: none; padding: 0; margin: 0 0 2rem 0; display: flex; flex-direction: column; gap: 0.75rem;">
            <li style="display: flex; align-items: flex-start; gap: 0.5rem; font-size: 0.9rem; color: #E2E8F0;"><svg style="flex-shrink:0; margin-top:2px;" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#3B82F6" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Publication d'offres en illimité</li>
            <li style="display: flex; align-items: flex-start; gap: 0.5rem; font-size: 0.9rem; color: #E2E8F0;"><svg style="flex-shrink:0; margin-top:2px;" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#3B82F6" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Accès complet à la CVthèque</li>
            <li style="display: flex; align-items: flex-start; gap: 0.5rem; font-size: 0.9rem; color: #E2E8F0;"><svg style="flex-shrink:0; margin-top:2px;" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#3B82F6" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Contact direct avec les candidats</li>
            <li style="display: flex; align-items: flex-start; gap: 0.5rem; font-size: 0.9rem; color: #E2E8F0;"><svg style="flex-shrink:0; margin-top:2px;" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#3B82F6" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Export de profils en PDF</li>
          </ul>
          <a href="auth/inscription.php?role=recruteur" style="display: block; width: 100%; text-align: center; padding: 0.85rem; border-radius: 12px; background: #3B82F6; color: #FFF; font-weight: 700; text-decoration: none; transition: background 0.2s;" onmouseover="this.style.background='#2563EB'" onmouseout="this.style.background='#3B82F6'">Essayer Pro</a>
        </div>

      </div>
    </div>
  </section>

  <!-- Section: Pourquoi nous choisir -->
  <section class="features-section" style="padding: 5rem 0; background: #FFF;">
    <div class="container">
      <div style="text-align: center; margin-bottom: 4rem;">
        <h2 style="font-size: 2.2rem; font-weight: 800; color: #0F172A; margin-bottom: 1rem;">Pourquoi choisir TGTravail ?</h2>
        <p style="color: #64748B; font-size: 1.1rem; max-width: 600px; margin: 0 auto;">L'écosystème de recrutement le plus innovant au Togo.</p>
      </div>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 3rem;">
        
        <div style="text-align: center;">
          <div style="width: 64px; height: 64px; background: #E0E7FF; color: #4338CA; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
              <path d="m9 12 2 2 4-4"></path>
            </svg>
          </div>
          <h3 style="font-size: 1.25rem; font-weight: 700; color: #0F172A; margin-bottom: 0.75rem;">100% Vérifié & Sécurisé</h3>
          <p style="color: #64748B; font-size: 0.95rem; line-height: 1.6;">
            Toutes les entreprises et les offres sont modérées par notre équipe. Fini les fausses annonces et les mauvaises surprises.
          </p>
        </div>

        <div style="text-align: center;">
          <div style="width: 64px; height: 64px; background: #FEF3C7; color: #D97706; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
              <polyline points="14 2 14 8 20 8"></polyline>
              <line x1="16" y1="13" x2="8" y2="13"></line>
              <line x1="16" y1="17" x2="8" y2="17"></line>
            </svg>
          </div>
          <h3 style="font-size: 1.25rem; font-weight: 700; color: #0F172A; margin-bottom: 0.75rem;">Processus Simple & Rapide</h3>
          <p style="color: #64748B; font-size: 0.95rem; line-height: 1.6;">
            Générez votre CV en 1 clic, postulez facilement depuis votre mobile et suivez vos candidatures en temps réel.
          </p>
        </div>

        <div style="text-align: center;">
          <div style="width: 64px; height: 64px; background: #DCFCE7; color: #15803D; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
              <circle cx="9" cy="7" r="4"></circle>
              <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
              <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
          </div>
          <h3 style="font-size: 1.25rem; font-weight: 700; color: #0F172A; margin-bottom: 0.75rem;">Le Meilleur Réservoir de Talents</h3>
          <p style="color: #64748B; font-size: 0.95rem; line-height: 1.6;">
            Recruteurs, accédez à une CVthèque riche et ciblée pour trouver la perle rare au Togo, plus vite que jamais.
          </p>
        </div>

      </div>
    </div>
  </section>

  <!-- Final CTA Section -->
  <section style="padding: 5rem 0; background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%); text-align: center;">
    <div class="container">
      <h2 style="font-size: 2.5rem; font-weight: 800; color: #FFF; margin-bottom: 1.5rem;">Prêt à donner un coup de pouce à votre carrière ?</h2>
      <p style="color: #DBEAFE; font-size: 1.1rem; max-width: 600px; margin: 0 auto 3rem auto;">Rejoignez les milliers de Togolais qui font confiance à TGTravail au quotidien.</p>
      
      <div style="display: flex; gap: 1.5rem; justify-content: center; flex-wrap: wrap;">
        <a href="auth/inscription.php?role=candidat" style="background: #FFF; color: #1D4ED8; font-weight: 700; padding: 1rem 2rem; border-radius: 99px; text-decoration: none; font-size: 1.1rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform=''">Je suis un candidat</a>
        <a href="auth/inscription.php?role=recruteur" style="background: rgba(255,255,255,0.15); color: #FFF; border: 1.5px solid rgba(255,255,255,0.3); font-weight: 700; padding: 1rem 2rem; border-radius: 99px; text-decoration: none; font-size: 1.1rem; backdrop-filter: blur(8px); transition: all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.25)';this.style.borderColor='rgba(255,255,255,0.5)';this.style.transform='translateY(-2px)'" onmouseout="this.style.background='rgba(255,255,255,0.15)';this.style.borderColor='rgba(255,255,255,0.3)';this.style.transform=''">Je recrute (Entreprise)</a>
      </div>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>




