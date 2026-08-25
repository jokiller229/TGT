<?php
$pageTitle = 'À propos - TGTravail';
$activePage = 'apropos';
require_once __DIR__ . '/../includes/header.php';
?>

<main style="background: var(--bg-body); min-height: 100vh;">
  <!-- Hero À Propos -->
  <section style="background: linear-gradient(135deg, var(--color-primary-dark) 0%, #1E3A8A 100%); padding: 5rem 0; color: white; text-align: center; position: relative; overflow: hidden;">
    <!-- Abstract shapes for decoration -->
    <div style="position: absolute; top: -50px; left: -50px; width: 200px; height: 200px; background: rgba(255,255,255,0.05); border-radius: 50%; blur: 20px;"></div>
    <div style="position: absolute; bottom: -50px; right: -50px; width: 300px; height: 300px; background: rgba(255,255,255,0.05); border-radius: 50%; blur: 20px;"></div>
    
    <div class="container" style="position: relative; z-index: 1;">
      <h1 style="font-size: 3rem; font-weight: 800; margin-bottom: 1.5rem; letter-spacing: -0.02em;">Notre mission : Rapprocher les talents<br> et les opportunités au Togo.</h1>
      <p style="font-size: 1.15rem; color: rgba(255, 255, 255, 0.9); max-width: 700px; margin: 0 auto; line-height: 1.6;">
        TGTravail a été créé avec une conviction simple : le marché de l'emploi togolais regorge de talents qui ne demandent qu'à être découverts. Nous construisons le pont entre les entreprises ambitieuses et les professionnels compétents.
      </p>
    </div>
  </section>

  <!-- Statistiques (Glassmorphism effect) -->
  <section style="padding: 4rem 0; margin-top: -3rem; position: relative; z-index: 10;">
    <div class="container">
      <div style="background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,0.3); border-radius: var(--radius-xl); padding: 2rem; box-shadow: 0 10px 40px rgba(0,0,0,0.08); display: flex; justify-content: space-around; flex-wrap: wrap; gap: 2rem; text-align: center;">
        
        <div style="flex: 1; min-width: 200px;">
          <div style="font-size: 2.5rem; font-weight: 800; color: var(--color-primary-blue); margin-bottom: 0.5rem;">+15 000</div>
          <div style="font-size: 0.9rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Candidats Inscrits</div>
        </div>
        
        <div style="width: 1px; background: var(--border-light); display: none; @media(min-width: 768px) { display: block; }"></div>
        
        <div style="flex: 1; min-width: 200px;">
          <div style="font-size: 2.5rem; font-weight: 800; color: var(--color-primary-blue); margin-bottom: 0.5rem;">+500</div>
          <div style="font-size: 0.9rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Entreprises Partenaires</div>
        </div>
        
        <div style="width: 1px; background: var(--border-light); display: none; @media(min-width: 768px) { display: block; }"></div>
        
        <div style="flex: 1; min-width: 200px;">
          <div style="font-size: 2.5rem; font-weight: 800; color: var(--color-primary-blue); margin-bottom: 0.5rem;">+3 200</div>
          <div style="font-size: 0.9rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Emplois Pourvus</div>
        </div>

      </div>
    </div>
  </section>

  <!-- Valeurs -->
  <section style="padding: 4rem 0;">
    <div class="container">
      <h2 style="font-size: 2rem; font-weight: 800; color: var(--color-primary-dark); text-align: center; margin-bottom: 3rem;">Nos Valeurs Fondamentales</h2>
      
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
        <!-- Valeur 1 -->
        <div style="background: white; border-radius: var(--radius-xl); padding: 2rem; border: 1px solid var(--border-light); box-shadow: var(--shadow-sm); text-align: center;">
          <div style="width: 64px; height: 64px; background: #DBEAFE; color: #1D4ED8; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin: 0 auto 1.5rem;">
            🛡️
          </div>
          <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--color-primary-dark); margin-bottom: 1rem;">Fiabilité</h3>
          <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.6;">
            Toutes les entreprises sur TGTravail sont vérifiées. Nous garantissons un environnement sûr pour la recherche d'emploi et le recrutement.
          </p>
        </div>

        <!-- Valeur 2 -->
        <div style="background: white; border-radius: var(--radius-xl); padding: 2rem; border: 1px solid var(--border-light); box-shadow: var(--shadow-sm); text-align: center;">
          <div style="width: 64px; height: 64px; background: #FEF3C7; color: #B45309; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin: 0 auto 1.5rem;">
            🤝
          </div>
          <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--color-primary-dark); margin-bottom: 1rem;">Proximité</h3>
          <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.6;">
            Une plateforme 100% pensée pour le marché local togolais, comprenant les réalités de nos candidats et de nos entreprises.
          </p>
        </div>

        <!-- Valeur 3 -->
        <div style="background: white; border-radius: var(--radius-xl); padding: 2rem; border: 1px solid var(--border-light); box-shadow: var(--shadow-sm); text-align: center;">
          <div style="width: 64px; height: 64px; background: #ECFDF5; color: #059669; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin: 0 auto 1.5rem;">
            💡
          </div>
          <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--color-primary-dark); margin-bottom: 1rem;">Innovation</h3>
          <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.6;">
            Nous utilisons les dernières technologies pour simplifier vos processus et offrir une expérience utilisateur exceptionnelle.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA CTA -->
  <section style="padding: 2rem 0 6rem;">
    <div class="container">
      <div style="background: var(--color-primary-dark); border-radius: var(--radius-xl); padding: 3rem 2rem; color: white; text-align: center; position: relative; overflow: hidden;">
        <h2 style="font-size: 2rem; font-weight: 800; margin-bottom: 1rem; position: relative; z-index: 1;">Prêt à rejoindre l'aventure ?</h2>
        <p style="font-size: 1.1rem; color: rgba(255,255,255,0.8); margin-bottom: 2rem; position: relative; z-index: 1;">Que vous cherchiez votre prochain talent ou votre futur emploi, TGTravail est là pour vous.</p>
        <div style="display: flex; gap: 1rem; justify-content: center; position: relative; z-index: 1;">
          <a href="<?= $baseUrl ?>/auth/inscription.php?role=candidat" style="background: white; color: var(--color-primary-dark); padding: 0.75rem 1.5rem; border-radius: 9999px; font-weight: 700; text-decoration: none; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">Je suis candidat</a>
          <a href="<?= $baseUrl ?>/auth/inscription.php?role=recruteur" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.3); color: white; padding: 0.75rem 1.5rem; border-radius: 9999px; font-weight: 700; text-decoration: none; transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">Je recrute</a>
        </div>
      </div>
    </div>
  </section>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>




