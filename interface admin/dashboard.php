<?php
session_start();
require_once __DIR__ . '/config/db.php';

$pageTitle = 'Vue d\'ensemble - Admin';
require_once __DIR__ . '/includes/header.php';
$db = getDB();

// Global Stats
$stats = [
    'users' => $db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'recruteurs' => $db->query("SELECT COUNT(*) FROM users WHERE role = 'recruteur'")->fetchColumn(),
    'candidats' => $db->query("SELECT COUNT(*) FROM users WHERE role = 'candidat'")->fetchColumn(),
    'offres' => $db->query("SELECT COUNT(*) FROM jobs WHERE statut = 'active'")->fetchColumn()
];

// Alertes prioritaires
$alert_companies = $db->query("SELECT COUNT(*) FROM companies WHERE statut_validation = 'en_attente' AND created_at < DATE_SUB(NOW(), INTERVAL 3 DAY)")->fetchColumn();
$alert_reports = $db->query("SELECT COUNT(*) FROM signalements WHERE statut = 'en attente'")->fetchColumn();

// Activités récentes
$recent_jobs = $db->query("
    SELECT j.titre, j.created_at, c.nom as company_nom 
    FROM jobs j 
    JOIN companies c ON j.company_id = c.id 
    ORDER BY j.created_at DESC LIMIT 5
")->fetchAll();

$recent_users = $db->query("
    SELECT nom, role, created_at 
    FROM users 
    ORDER BY created_at DESC LIMIT 5
")->fetchAll();
?>

    <h1 style="font-size: 1.75rem; margin-bottom: 0.5rem; color: #1e293b;">Tableau de bord Administrateur</h1>
    <p style="color: #64748b; margin-bottom: 2rem;">Gérez la plateforme TGTravail et suivez l'activité globale.</p>

    <?php if ($alert_companies > 0 || $alert_reports > 0): ?>
    <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
        <h2 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 0.5rem; margin-top: 0;">🚨 Alertes Prioritaires</h2>
        <ul style="margin: 0; padding-left: 1.5rem;">
            <?php if ($alert_companies > 0): ?>
                <li><strong><?= $alert_companies ?></strong> dossier(s) recruteur(s) en attente depuis plus de 3 jours. <a href="recruteurs.php" style="color: #991b1b; font-weight: bold;">Gérer</a></li>
            <?php endif; ?>
            <?php if ($alert_reports > 0): ?>
                <li><strong><?= $alert_reports ?></strong> nouveau(x) signalement(s) non traité(s). <a href="signalements.php" style="color: #991b1b; font-weight: bold;">Gérer</a></li>
            <?php endif; ?>
        </ul>
    </div>
    <?php endif; ?>

    <?php if(isset($_GET['msg']) && $_GET['msg'] === 'success'): ?>
        <div style="background:#d1fae5; color:#065f46; padding:1rem; border-radius:4px; margin-bottom:2rem;">
            Entreprise validée avec succès ! Le recruteur a maintenant accès à toutes les fonctionnalités.
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="stat-card" style="background: white; padding: 1.5rem; border-radius: 12px; border-bottom: 4px solid #3b82f6; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="color: #64748b; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Total Utilisateurs</div>
                <div style="font-size: 2.25rem; font-weight: 800; margin-top: 0.25rem; color: #0f172a;"><?= $stats['users'] ?></div>
            </div>
            <div style="background: #eff6ff; padding: 1rem; border-radius: 50%; color: #3b82f6;">
                <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
        </div>
        
        <div class="stat-card" style="background: white; padding: 1.5rem; border-radius: 12px; border-bottom: 4px solid #10b981; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="color: #64748b; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Candidats</div>
                <div style="font-size: 2.25rem; font-weight: 800; margin-top: 0.25rem; color: #0f172a;"><?= $stats['candidats'] ?></div>
            </div>
            <div style="background: #ecfdf5; padding: 1rem; border-radius: 50%; color: #10b981;">
                <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
        </div>
        
        <div class="stat-card" style="background: white; padding: 1.5rem; border-radius: 12px; border-bottom: 4px solid #f59e0b; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="color: #64748b; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Recruteurs</div>
                <div style="font-size: 2.25rem; font-weight: 800; margin-top: 0.25rem; color: #0f172a;"><?= $stats['recruteurs'] ?></div>
            </div>
            <div style="background: #fffbeb; padding: 1rem; border-radius: 50%; color: #f59e0b;">
                <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            </div>
        </div>
        
        <div class="stat-card" style="background: white; padding: 1.5rem; border-radius: 12px; border-bottom: 4px solid #8b5cf6; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="color: #64748b; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Offres Ouvertes</div>
                <div style="font-size: 2.25rem; font-weight: 800; margin-top: 0.25rem; color: #0f172a;"><?= $stats['offres'] ?></div>
            </div>
            <div style="background: #f5f3ff; padding: 1rem; border-radius: 50%; color: #8b5cf6;">
                <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
            </div>
        </div>
    </div>

    <!-- Layout principal : Activités & Actions -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        
        <!-- Bloc Activités Récentes -->
        <div style="background: white; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 1.5rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 1rem;">
                <h2 style="font-size: 1.25rem; font-weight: 700; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: #3b82f6;"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    Dernières offres publiées
                </h2>
                <a href="offres.php" style="color: #3b82f6; text-decoration: none; font-size: 0.85rem; font-weight: 600;">Tout voir &rarr;</a>
            </div>
            
            <?php if(empty($recent_jobs)): ?>
                <div style="text-align: center; padding: 2rem; color: #64748b;">Aucune offre récente.</div>
            <?php else: ?>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <?php foreach($recent_jobs as $rj): ?>
                    <li style="padding: 1rem 0; border-bottom: 1px dashed #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong style="display: block; color: #1e293b;"><?= htmlspecialchars($rj['titre']) ?></strong>
                            <span style="font-size: 0.85rem; color: #64748b;"><?= htmlspecialchars($rj['company_nom']) ?></span>
                        </div>
                        <span style="font-size: 0.75rem; font-weight: 600; color: #94a3b8; background: #f8fafc; padding: 0.25rem 0.5rem; border-radius: 4px;">
                            <?= date('d/m/Y', strtotime($rj['created_at'])) ?>
                        </span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <!-- Bloc Actions & Nouveaux Utilisateurs -->
        <div style="display: flex; flex-direction: column; gap: 2rem;">
            
            <!-- Actions Rapides -->
            <div style="background: white; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 1.5rem;">
                <h2 style="font-size: 1.1rem; font-weight: 700; color: #0f172a; margin: 0 0 1rem 0; display: flex; align-items: center; gap: 0.5rem;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: #8b5cf6;"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                    Actions Rapides
                </h2>
                <div style="display: grid; gap: 0.75rem;">
                    <a href="recruteurs.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; border-radius: 8px; background: #f8fafc; color: #1e293b; text-decoration: none; font-weight: 600; font-size: 0.9rem; transition: background 0.2s;">
                        <span style="background: #dbeafe; color: #1e40af; padding: 0.4rem; border-radius: 6px;"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg></span>
                        Valider Recruteurs
                    </a>
                    <a href="diffusions.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; border-radius: 8px; background: #f8fafc; color: #1e293b; text-decoration: none; font-weight: 600; font-size: 0.9rem; transition: background 0.2s;">
                        <span style="background: #fef3c7; color: #b45309; padding: 0.4rem; border-radius: 6px;"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 2 11 13"/><path d="M22 2 15 22 11 13 2 9 22 2z"/></svg></span>
                        Diffuser un message
                    </a>
                    <a href="signalements.php" style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem; border-radius: 8px; background: #f8fafc; color: #1e293b; text-decoration: none; font-weight: 600; font-size: 0.9rem; transition: background 0.2s;">
                        <span style="background: #fee2e2; color: #b91c1c; padding: 0.4rem; border-radius: 6px;"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg></span>
                        Voir Signalements
                    </a>
                </div>
            </div>

            <!-- Derniers Inscrits -->
            <div style="background: white; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); padding: 1.5rem;">
                <h2 style="font-size: 1.1rem; font-weight: 700; color: #0f172a; margin: 0 0 1rem 0; display: flex; align-items: center; gap: 0.5rem;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: #10b981;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    Derniers Inscrits
                </h2>
                <?php if(empty($recent_users)): ?>
                    <div style="text-align: center; font-size: 0.85rem; color: #64748b;">Aucun utilisateur.</div>
                <?php else: ?>
                    <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.75rem;">
                        <?php foreach($recent_users as $ru): ?>
                        <li style="display: flex; align-items: center; justify-content: space-between;">
                            <div>
                                <strong style="display: block; font-size: 0.9rem; color: #1e293b;"><?= htmlspecialchars($ru['nom'] ?: 'Anonyme') ?></strong>
                                <span style="font-size: 0.75rem; color: #64748b; text-transform: capitalize;"><?= htmlspecialchars($ru['role']) ?></span>
                            </div>
                            <span style="font-size: 0.7rem; color: #94a3b8;"><?= date('d/m', strtotime($ru['created_at'])) ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            
        </div>
    </div>
</main>
</div>
</body>
</html>
