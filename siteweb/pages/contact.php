<?php
$activePage = 'contact';
$pageTitle = 'Contact & Support - TGTravail';
require_once __DIR__ . '/../includes/auth.php';

$successMsg = '';
$errorMsg = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../config/db.php';
    $db = getDB();

    $nom     = trim($_POST['nom'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $sujet   = trim($_POST['sujet'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!$nom || !$email || !$sujet || !$message) {
        $errorMsg = 'Tous les champs sont obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = 'Adresse email invalide.';
    } else {
        $db->prepare("INSERT INTO support_messages (nom, email, sujet, message, statut, created_at) VALUES (?, ?, ?, ?, 'nouveau', NOW())")
           ->execute([$nom, $email, $sujet, $message]);
        $successMsg = 'Votre message a bien été envoyé ! Notre équipe vous répondra sous 24-48h.';
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<main style="background: var(--bg-body); min-height: 100vh;">

  <!-- Hero -->
  <section style="background: linear-gradient(135deg, #081326 0%, #1E3A5F 100%); padding: 5rem 0 4rem; color: white; text-align: center; position: relative; overflow: hidden;">
    <div style="position: absolute; inset: 0; background: radial-gradient(circle at 30% 50%, rgba(37,99,235,0.15) 0%, transparent 60%); pointer-events: none;"></div>
    <div class="container" style="position: relative; z-index: 1;">
      <div data-aos="fade-up" style="display: inline-block; background: rgba(255,184,0,0.1); border: 1px solid rgba(255,184,0,0.3); color: #FFB800; padding: 0.35rem 1rem; border-radius: 99px; font-size: 0.85rem; font-weight: 700; margin-bottom: 1.5rem; text-transform: uppercase; letter-spacing: 0.05em;">
        Support Client
      </div>
      <h1 data-aos="fade-up" data-aos-delay="100" style="font-size: 3rem; font-weight: 800; margin-bottom: 1rem; letter-spacing: -0.02em;">Besoin d'aide ? 👋</h1>
      <p data-aos="fade-up" data-aos-delay="200" style="font-size: 1.15rem; color: #CBD5E1; max-width: 600px; margin: 0 auto; line-height: 1.6;">
        Notre équipe est disponible du lundi au vendredi, de 8h à 18h (GMT+0). Réponse garantie sous 24-48h.
      </p>
    </div>
  </section>

  <!-- Cartes de contact rapide -->
  <section style="padding: 3rem 0; background: white; border-bottom: 1px solid #E2E8F0;">
    <div class="container">
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; max-width: 900px; margin: 0 auto;">

        <div data-aos="fade-up" data-aos-delay="100" style="text-align: center; padding: 2rem 1.5rem; border-radius: 16px; border: 1.5px solid #E2E8F0; transition: all 0.2s;" onmouseover="this.style.borderColor='#2563EB'; this.style.boxShadow='0 8px 25px rgba(37,99,235,0.1)'" onmouseout="this.style.borderColor='#E2E8F0'; this.style.boxShadow='none'">
          <div style="font-size: 2.5rem; margin-bottom: 1rem;">📧</div>
          <h3 style="font-size: 1rem; font-weight: 700; color: #0F172A; margin-bottom: 0.5rem;">Email</h3>
          <p style="font-size: 0.875rem; color: #64748B; margin-bottom: 0.75rem;">Réponse sous 24-48h</p>
          <a href="mailto:support@tgtravail.com" style="color: #2563EB; font-weight: 600; font-size: 0.9rem; text-decoration: none;">support@tgtravail.com</a>
        </div>

        <div data-aos="fade-up" data-aos-delay="200" style="text-align: center; padding: 2rem 1.5rem; border-radius: 16px; border: 1.5px solid #E2E8F0; transition: all 0.2s;" onmouseover="this.style.borderColor='#22C55E'; this.style.boxShadow='0 8px 25px rgba(34,197,94,0.1)'" onmouseout="this.style.borderColor='#E2E8F0'; this.style.boxShadow='none'">
          <div style="font-size: 2.5rem; margin-bottom: 1rem;">📱</div>
          <h3 style="font-size: 1rem; font-weight: 700; color: #0F172A; margin-bottom: 0.5rem;">WhatsApp</h3>
          <p style="font-size: 0.875rem; color: #64748B; margin-bottom: 0.75rem;">Réponse rapide</p>
          <a href="https://wa.me/22890000000" target="_blank" style="color: #22C55E; font-weight: 600; font-size: 0.9rem; text-decoration: none;">+228 90 00 00 00</a>
        </div>

        <div data-aos="fade-up" data-aos-delay="300" style="text-align: center; padding: 2rem 1.5rem; border-radius: 16px; border: 1.5px solid #E2E8F0; transition: all 0.2s;" onmouseover="this.style.borderColor='#F59E0B'; this.style.boxShadow='0 8px 25px rgba(245,158,11,0.1)'" onmouseout="this.style.borderColor='#E2E8F0'; this.style.boxShadow='none'">
          <div style="font-size: 2.5rem; margin-bottom: 1rem;">📍</div>
          <h3 style="font-size: 1rem; font-weight: 700; color: #0F172A; margin-bottom: 0.5rem;">Adresse</h3>
          <p style="font-size: 0.875rem; color: #64748B; margin-bottom: 0.25rem;">Studio Orizon</p>
          <p style="font-size: 0.875rem; color: #64748B;">Lomé, Togo</p>
        </div>

        <div data-aos="fade-up" data-aos-delay="400" style="text-align: center; padding: 2rem 1.5rem; border-radius: 16px; border: 1.5px solid #E2E8F0; transition: all 0.2s;" onmouseover="this.style.borderColor='#8B5CF6'; this.style.boxShadow='0 8px 25px rgba(139,92,246,0.1)'" onmouseout="this.style.borderColor='#E2E8F0'; this.style.boxShadow='none'">
          <div style="font-size: 2.5rem; margin-bottom: 1rem;">🕐</div>
          <h3 style="font-size: 1rem; font-weight: 700; color: #0F172A; margin-bottom: 0.5rem;">Horaires</h3>
          <p style="font-size: 0.875rem; color: #64748B; margin-bottom: 0.25rem;">Lun – Ven : 8h – 18h</p>
          <p style="font-size: 0.875rem; color: #64748B;">Heure de Lomé (GMT+0)</p>
        </div>

      </div>
    </div>
  </section>

  <!-- Formulaire de contact -->
  <section style="padding: 5rem 0;">
    <div class="container">
      <div style="max-width: 700px; margin: 0 auto;">

        <div data-aos="fade-up" style="text-align: center; margin-bottom: 3rem;">
          <h2 style="font-size: 2rem; font-weight: 800; color: #0F172A; margin-bottom: 0.75rem;">Envoyer un message</h2>
          <p style="color: #64748B; font-size: 1rem;">Décrivez votre problème ou votre question, nous vous répondrons rapidement.</p>
        </div>

        <?php if ($successMsg): ?>
          <div style="background: #DCFCE7; color: #166534; border: 1px solid #BBF7D0; border-radius: 14px; padding: 1.25rem 1.5rem; margin-bottom: 2rem; font-weight: 600; display: flex; align-items: center; gap: 0.75rem;">
            <span style="font-size: 1.5rem;">✅</span> <?= htmlspecialchars($successMsg) ?>
          </div>
        <?php endif; ?>

        <?php if ($errorMsg): ?>
          <div style="background: #FEF2F2; color: #991B1B; border: 1px solid #FECACA; border-radius: 14px; padding: 1.25rem 1.5rem; margin-bottom: 2rem; font-weight: 600; display: flex; align-items: center; gap: 0.75rem;">
            <span style="font-size: 1.5rem;">⚠️</span> <?= htmlspecialchars($errorMsg) ?>
          </div>
        <?php endif; ?>

        <div data-aos="fade-up" data-aos-delay="100" style="background: white; border-radius: 24px; padding: 2.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid #E2E8F0;">
          <form method="POST" style="display: flex; flex-direction: column; gap: 1.5rem;">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
              <div>
                <label style="display: block; font-weight: 600; font-size: 0.9rem; color: #0F172A; margin-bottom: 0.5rem;">Votre nom *</label>
                <input type="text" name="nom" value="<?= htmlspecialchars($_POST['nom'] ?? ($user['nom'] ?? '')) ?>" placeholder="Jean Kodjo" required style="width: 100%; padding: 0.85rem 1rem; border: 1.5px solid #E2E8F0; border-radius: 12px; font-size: 0.9rem; outline: none; transition: all 0.2s; font-family: inherit; box-sizing: border-box;" onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 3px rgba(37,99,235,0.1)'" onblur="this.style.borderColor='#E2E8F0'; this.style.boxShadow='none'">
              </div>
              <div>
                <label style="display: block; font-weight: 600; font-size: 0.9rem; color: #0F172A; margin-bottom: 0.5rem;">Adresse email *</label>
                <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? ($user['email'] ?? '')) ?>" placeholder="votre@email.com" required style="width: 100%; padding: 0.85rem 1rem; border: 1.5px solid #E2E8F0; border-radius: 12px; font-size: 0.9rem; outline: none; transition: all 0.2s; font-family: inherit; box-sizing: border-box;" onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 3px rgba(37,99,235,0.1)'" onblur="this.style.borderColor='#E2E8F0'; this.style.boxShadow='none'">
              </div>
            </div>

            <div>
              <label style="display: block; font-weight: 600; font-size: 0.9rem; color: #0F172A; margin-bottom: 0.5rem;">Sujet *</label>
              <select name="sujet" required style="width: 100%; padding: 0.85rem 1rem; border: 1.5px solid #E2E8F0; border-radius: 12px; font-size: 0.9rem; outline: none; background: white; font-family: inherit; transition: all 0.2s; box-sizing: border-box;" onfocus="this.style.borderColor='#2563EB'" onblur="this.style.borderColor='#E2E8F0'">
                <option value="">Sélectionnez un sujet...</option>
                <option value="Problème de connexion" <?= ($_POST['sujet'] ?? '') === 'Problème de connexion' ? 'selected' : '' ?>>Problème de connexion / Mot de passe</option>
                <option value="Candidature" <?= ($_POST['sujet'] ?? '') === 'Candidature' ? 'selected' : '' ?>>Question sur une candidature</option>
                <option value="Publication offre" <?= ($_POST['sujet'] ?? '') === 'Publication offre' ? 'selected' : '' ?>>Publier / gérer une offre d'emploi</option>
                <option value="Abonnement" <?= ($_POST['sujet'] ?? '') === 'Abonnement' ? 'selected' : '' ?>>Abonnement & facturation</option>
                <option value="Signalement" <?= ($_POST['sujet'] ?? '') === 'Signalement' ? 'selected' : '' ?>>Signaler une annonce frauduleuse</option>
                <option value="Vérification compte" <?= ($_POST['sujet'] ?? '') === 'Vérification compte' ? 'selected' : '' ?>>Vérification de compte entreprise</option>
                <option value="Autre" <?= ($_POST['sujet'] ?? '') === 'Autre' ? 'selected' : '' ?>>Autre question</option>
              </select>
            </div>

            <div>
              <label style="display: block; font-weight: 600; font-size: 0.9rem; color: #0F172A; margin-bottom: 0.5rem;">Votre message *</label>
              <textarea name="message" rows="6" placeholder="Décrivez votre problème ou votre question en détail..." required style="width: 100%; padding: 0.85rem 1rem; border: 1.5px solid #E2E8F0; border-radius: 12px; font-size: 0.9rem; outline: none; transition: all 0.2s; font-family: inherit; resize: vertical; box-sizing: border-box;" onfocus="this.style.borderColor='#2563EB'; this.style.boxShadow='0 0 0 3px rgba(37,99,235,0.1)'" onblur="this.style.borderColor='#E2E8F0'; this.style.boxShadow='none'"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
            </div>

            <button type="submit" style="width: 100%; background: #2563EB; color: white; border: none; padding: 1rem; border-radius: 12px; font-weight: 700; font-size: 1.05rem; cursor: pointer; transition: background 0.2s; font-family: inherit; display: flex; align-items: center; justify-content: center; gap: 0.5rem;" onmouseover="this.style.background='#1D4ED8'" onmouseout="this.style.background='#2563EB'">
              📩 Envoyer le message
            </button>

          </form>
        </div>

        <!-- FAQ rapide -->
        <div data-aos="fade-up" data-aos-delay="200" style="margin-top: 3rem;">
          <h3 style="font-size: 1.25rem; font-weight: 800; color: #0F172A; margin-bottom: 1.5rem; text-align: center;">Questions fréquentes</h3>
          <div style="display: flex; flex-direction: column; gap: 1rem;">

            <?php
            $faqs = [
              ['q' => 'Comment réinitialiser mon mot de passe ?', 'a' => 'Sur la page de connexion, cliquez sur "Mot de passe oublié" et suivez les instructions envoyées par email.'],
              ['q' => 'Comment vérifier mon compte entreprise ?', 'a' => 'Accédez à votre tableau de bord et suivez la procédure de vérification. Notre équipe valide les dossiers sous 48h.'],
              ['q' => 'Comment annuler mon abonnement ?', 'a' => 'Rendez-vous dans Abonnements > Gérer mon abonnement depuis votre espace recruteur.'],
              ['q' => 'Signaler une offre frauduleuse ?', 'a' => 'Utilisez le bouton "Signaler" présent sur chaque offre, ou contactez-nous directement via ce formulaire.'],
            ];
            foreach ($faqs as $faq):
            ?>
              <details style="background: white; border: 1.5px solid #E2E8F0; border-radius: 12px; overflow: hidden; cursor: pointer;">
                <summary style="padding: 1.1rem 1.25rem; font-weight: 700; color: #0F172A; font-size: 0.95rem; list-style: none; display: flex; align-items: center; justify-content: space-between;">
                  <?= htmlspecialchars($faq['q']) ?>
                  <span style="font-size: 1.25rem; color: #2563EB; flex-shrink: 0; margin-left: 1rem;">+</span>
                </summary>
                <div style="padding: 0 1.25rem 1.1rem; color: #475569; font-size: 0.9rem; line-height: 1.6; border-top: 1px solid #E2E8F0;">
                  <?= htmlspecialchars($faq['a']) ?>
                </div>
              </details>
            <?php endforeach; ?>

          </div>
        </div>

      </div>
    </div>
  </section>

</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>




