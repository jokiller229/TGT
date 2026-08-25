<?php
require_once __DIR__ . '/siteweb/config/db.php';

$pdo = getDB();

try {
    // 1. Création de la table
    $sql = "
    CREATE TABLE IF NOT EXISTS cv_templates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        slug VARCHAR(100) NOT NULL UNIQUE,
        html_content TEXT NOT NULL,
        css_content TEXT NOT NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($sql);
    echo "Table cv_templates created successfully.\n";

    // 2. Préparation des modèles par défaut
    
    $moderneHTML = <<<HTML
        <div class="cv-sidebar draggable-block">
             <div class="avatar-box">{{avatar_html}}</div>
             <div class="cv-section-title" contenteditable="true">Contact</div>
             <div class="cv-contact-item" contenteditable="true">📧 {{email}}</div>
             <div class="cv-contact-item" contenteditable="true">📱 {{telephone}}</div>
             <div class="cv-contact-item" contenteditable="true">📍 {{ville}}</div>
             
             <div class="cv-section-title" contenteditable="true">Compétences</div>
             <div class="cv-text" contenteditable="true">{{competences_html_badges}}</div>
          </div>
          <div class="cv-main draggable-block">
             <div class="cv-name" contenteditable="true">{{nom}}</div>
             <div class="cv-title" contenteditable="true">{{titre}}</div>
             
             <div class="cv-section-title" contenteditable="true">Profil Professionnel</div>
             <div class="cv-text" contenteditable="true" style="text-align: justify;">{{bio}}</div>
             
             <div class="cv-section-title" contenteditable="true">Expériences & Objectifs</div>
             <div class="cv-text" contenteditable="true">
                <strong style="color:var(--color-primary-dark);">Expérience cumulée :</strong> {{experience}} ans<br><br>
                <strong style="color:var(--color-primary-dark);">Contrat recherché :</strong> {{contrat}}<br><br>
                <strong style="color:var(--color-primary-dark);">Disponibilité :</strong> {{disponibilite}}<br><br><br>
                <i style="color: #64748B;">[Cliquez ici pour remplacer ce texte par le détail de vos expériences...]</i>
             </div>
          </div>
HTML;

    $moderneCSS = <<<CSS
.tpl-moderne {
    display: flex;
    min-height: 100%;
    font-family: 'Inter', sans-serif;
    color: #334155;
    background: #FFF;
}
.tpl-moderne .cv-sidebar {
    width: 32%;
    background: var(--color-primary-dark);
    color: rgba(255,255,255,0.85);
    padding: 25mm 12mm;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.tpl-moderne .cv-main {
    width: 68%;
    padding: 25mm 15mm;
}
.tpl-moderne .avatar-box {
    width: 38mm;
    height: 38mm;
    border-radius: 50%;
    border: 3px solid rgba(255,255,255,0.15);
    margin-bottom: 8mm;
    overflow: hidden;
    background: #FFF;
    display: flex; align-items: center; justify-content: center;
    font-size: 24pt; font-weight: 800; color: var(--color-primary-dark);
}
.tpl-moderne .cv-name {
    font-size: 28pt; font-weight: 900; color: #0F172A; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 2mm; line-height: 1.1;
}
.tpl-moderne .cv-title {
    font-size: 13pt; color: var(--color-primary-blue); font-weight: 600; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 12mm;
}
.tpl-moderne .cv-section-title {
    font-size: 13pt; font-weight: 800; color: #0F172A; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid #E2E8F0; padding-bottom: 3mm; margin-bottom: 5mm; margin-top: 10mm;
}
.tpl-moderne .cv-sidebar .cv-section-title {
    color: #FFF; border-bottom: 1px solid rgba(255,255,255,0.2); margin-top: 8mm; width: 100%; text-align: left; padding-bottom: 2mm;
}
.tpl-moderne .cv-text { font-size: 10pt; line-height: 1.6; }
.tpl-moderne .cv-sidebar .cv-text { width: 100%; text-align: left; }
.tpl-moderne .cv-contact-item { font-size: 9pt; margin-bottom: 4mm; display: flex; align-items: center; gap: 8px; width: 100%; word-break: break-all; }
.tpl-moderne .skill-badge { display: inline-block; background: rgba(255,255,255,0.15); padding: 5px 12px; border-radius: 20px; margin: 0 5px 6px 0; font-size: 9pt; color: #FFF; font-weight: 500; }
CSS;

    $classiqueHTML = <<<HTML
        <div class="cv-header draggable-block">
            <div class="cv-name" contenteditable="true">{{nom}}</div>
            <div class="cv-title" contenteditable="true">{{titre}}</div>
            <div class="cv-contact" contenteditable="true">
                {{email}} &nbsp;&nbsp;|&nbsp;&nbsp; {{telephone}} &nbsp;&nbsp;|&nbsp;&nbsp; {{ville}}
            </div>
        </div>
        
        <div class="draggable-block">
            <div class="cv-section-title" contenteditable="true">Résumé Professionnel</div>
            <div class="cv-text" contenteditable="true">{{bio}}</div>
        </div>
        
        <div class="draggable-block">
            <div class="cv-section-title" contenteditable="true">Domaines d'Expertise</div>
            <div class="cv-text" contenteditable="true">{{competences_html_bullets}}</div>
        </div>
        
        <div class="draggable-block">
            <div class="cv-section-title" contenteditable="true">Parcours & Expériences</div>
            <div class="cv-text" contenteditable="true">
                <strong>Expérience globale :</strong> {{experience}} ans d'expérience cumulée.<br><br>
                <i style="color: #64748B;">[Cliquez ici pour décrire vos postes précédents...]</i><br><br>
                <strong>Type de contrat visé :</strong> {{contrat}} | <strong>Disponibilité :</strong> {{disponibilite}}
            </div>
        </div>
HTML;

    $classiqueCSS = <<<CSS
.tpl-classique {
    padding: 25mm;
    color: #1E293B;
    font-family: 'Playfair Display', Georgia, serif;
    background: #FAFAFA;
}
.tpl-classique .cv-header {
    text-align: center; margin-bottom: 15mm;
}
.tpl-classique .cv-name {
    font-size: 34pt; font-weight: 700; color: #0F172A; margin-bottom: 2mm; letter-spacing: 1px;
}
.tpl-classique .cv-title {
    font-size: 15pt; font-style: italic; color: #475569;
}
.tpl-classique .cv-contact {
    font-family: 'Inter', sans-serif; font-size: 9pt; color: #64748B; margin-top: 8mm; text-transform: uppercase; letter-spacing: 1.5px;
    border-top: 1px solid #CBD5E1; border-bottom: 1px solid #CBD5E1; padding: 4mm 0;
}
.tpl-classique .cv-section-title {
    font-size: 15pt; font-weight: 700; color: #0F172A; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 6mm; margin-top: 12mm; text-align: center;
}
.tpl-classique .cv-text { font-family: 'Inter', sans-serif; font-size: 10.5pt; line-height: 1.7; color: #334155; text-align: justify; }
.tpl-classique .skill-bullet { font-weight: 600; color: #0F172A; display: inline-block; margin: 0 15px 5px 0; border: 1px solid #E2E8F0; padding: 3px 10px; border-radius: 4px; font-size: 9.5pt;}
CSS;


    $minimalisteHTML = <<<HTML
        <div class="cv-header draggable-block">
            <div>
                <div class="cv-name" contenteditable="true">{{nom}}</div>
                <div class="cv-title" contenteditable="true">{{titre}}</div>
            </div>
            <div class="cv-contact" contenteditable="true">
                {{email}}<br>
                {{telephone}}<br>
                {{ville}}
            </div>
        </div>
        
        <div class="cv-section draggable-block">
            <div class="cv-section-title" contenteditable="true">Profil</div>
            <div class="cv-section-content cv-text" contenteditable="true" style="text-align: justify;">{{bio}}</div>
        </div>
        
        <div class="cv-section draggable-block">
            <div class="cv-section-title" contenteditable="true">Expertise</div>
            <div class="cv-section-content cv-text" contenteditable="true">{{competences_html_tags}}</div>
        </div>
        
        <div class="cv-section draggable-block">
            <div class="cv-section-title" contenteditable="true">Carrière</div>
            <div class="cv-section-content cv-text" contenteditable="true">
                <strong>Disponibilité :</strong> {{disponibilite}}<br>
                <strong>Contrat :</strong> {{contrat}}<br><br>
                <i style="color: #999;">[Cliquez ici pour lister vos expériences...]</i>
            </div>
        </div>
HTML;

    $minimalisteCSS = <<<CSS
.tpl-minimaliste {
    padding: 25mm 20mm;
    color: #000;
    font-family: 'Inter', sans-serif;
    background: #FFF;
}
.tpl-minimaliste .cv-header {
    display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 4px solid #000; padding-bottom: 6mm; margin-bottom: 12mm;
}
.tpl-minimaliste .cv-name { font-size: 38pt; font-weight: 900; letter-spacing: -1.5px; line-height: 1; text-transform: uppercase; }
.tpl-minimaliste .cv-title { font-size: 14pt; font-weight: 600; margin-top: 3mm; letter-spacing: -0.5px; color: #555; }
.tpl-minimaliste .cv-contact { text-align: right; font-size: 9.5pt; line-height: 1.6; font-weight: 500; }
.tpl-minimaliste .cv-section { display: flex; margin-bottom: 10mm; page-break-inside: avoid; }
.tpl-minimaliste .cv-section-title { width: 30%; font-size: 11pt; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; padding-right: 15mm; }
.tpl-minimaliste .cv-section-content { width: 70%; font-size: 10.5pt; line-height: 1.6; }
.tpl-minimaliste .skill-text { display: inline-block; background: #000; color: #FFF; padding: 4px 10px; margin: 0 5px 6px 0; font-size: 9pt; font-weight: 600; border-radius: 3px; text-transform: uppercase; letter-spacing: 0.5px; }
CSS;

    $stmt = $pdo->prepare("INSERT IGNORE INTO cv_templates (name, slug, html_content, css_content) VALUES (?, ?, ?, ?)");
    
    $stmt->execute(['Moderne', 'moderne', $moderneHTML, $moderneCSS]);
    $stmt->execute(['Classique', 'classique', $classiqueHTML, $classiqueCSS]);
    $stmt->execute(['Minimaliste', 'minimaliste', $minimalisteHTML, $minimalisteCSS]);
    
    echo "Default templates inserted.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
