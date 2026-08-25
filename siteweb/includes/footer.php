<?php if (!isLoggedIn()): ?>
  <!-- Site Footer -->
  <footer class="site-footer">
    <div class="container">
      <div class="footer-grid">
        
        <div class="footer-brand">
          <a href="<?= $baseUrl ?>/index.php" class="logo-brand">
            <img src="<?= $baseUrl ?>/img/tgtravail-logo.png" alt="TGTravail" class="logo-img" style="width: 38px; height: auto; border-radius: 8px;">
            <span><span class="tg">TG</span><span style="color:#FFF;">Travail</span></span>
          </a>
          <p>Plateforme de référence pour l'emploi, le stage et les opportunités professionnelles au Togo.</p>
        </div>

        <div>
          <h4 class="footer-col-title">Pour les candidats</h4>
          <ul class="footer-links">
            <li><a href="<?= $baseUrl ?>/pages/offres.php">Toutes les offres</a></li>
            <li><a href="<?= $baseUrl ?>/auth/connexion.php">Mon profil &amp; CV</a></li>
            <li><a href="<?= $baseUrl ?>/auth/connexion.php">Messagerie</a></li>
            <li><a href="<?= $baseUrl ?>/pages/offres.php">Alertes emploi</a></li>
          </ul>
        </div>

        <div>
          <h4 class="footer-col-title">Pour les recruteurs</h4>
          <ul class="footer-links">
            <li><a href="<?= $baseUrl ?>/auth/connexion.php">Publier une offre</a></li>
            <li><a href="<?= $baseUrl ?>/auth/connexion.php">Tableau de bord</a></li>
            <li><a href="<?= $baseUrl ?>/auth/connexion.php">Candidatures reçues</a></li>
            <li><a href="<?= $baseUrl ?>/recruteur/abonnements.php">Grille tarifaire (FCFA)</a></li>
          </ul>
        </div>

        <div>
          <h4 class="footer-col-title">Plateforme Orizon</h4>
          <ul class="footer-links">
            <li><a href="<?= $baseUrl ?>/pages/a-propos.php">À propos de TGTravail</a></li>
            <li><a href="<?= $baseUrl ?>/pages/conseils.php">Conseils &amp; Blog</a></li>
            <li><a href="<?= $baseUrl ?>/pages/contact.php">Sécurité anti-fraude</a></li>
            <li><a href="<?= $baseUrl ?>/pages/contact.php">Contact &amp; Support Lomé</a></li>
          </ul>
        </div>

      </div>

      <div class="footer-bottom">
        <span>© <?= date('Y') ?> TGTravail Togo • Conçu par Studio Orizon</span>
        <span>Lomé, Togo • Connecté à MySQL MAMP • T-Money & Flooz intégrés</span>
      </div>
    </div>
  </footer>
  <?php endif; ?>

  <button id="scrollToTopBtn" style="display:none; position:fixed; bottom:2rem; right:2rem; width:48px; height:48px; border-radius:50%; background:#2563EB; color:#FFF; border:none; box-shadow:0 10px 25px rgba(37,99,235,0.4); cursor:pointer; z-index:998; align-items:center; justify-content:center; transition:all 0.3s;" onclick="window.scrollTo({top:0, behavior:'smooth'})">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 15l-6-6-6 6"/></svg>
  </button>

  <script>
    const scrollToTopBtn = document.getElementById('scrollToTopBtn');
    if (scrollToTopBtn) {
      window.addEventListener('scroll', () => {
        if (window.scrollY > 800) {
          scrollToTopBtn.style.display = 'flex';
        } else {
          scrollToTopBtn.style.display = 'none';
        }
      });
    }
  </script>

  <script src="<?= $baseUrl ?>/js/app.js?v=<?= filemtime(__DIR__ . '/../js/app.js') ?>"></script>
  
  <!-- AOS Animation Script -->
  <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
  <script>
    if (typeof AOS !== 'undefined') {
      AOS.init({
        duration: 800,
        once: true,
        offset: 50,
      });
    }
  </script>
</body>
</html>




