<?php
/**
 * Migration : Création de la table support_messages
 * Lancer une seule fois depuis le navigateur.
 */
require_once __DIR__ . '/siteweb/config/db.php';
$db = getDB();

$db->exec("
CREATE TABLE IF NOT EXISTS support_messages (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nom         VARCHAR(150)  NOT NULL,
    email       VARCHAR(255)  NOT NULL,
    sujet       VARCHAR(255)  NOT NULL,
    message     TEXT          NOT NULL,
    statut      ENUM('nouveau','en_cours','resolu') NOT NULL DEFAULT 'nouveau',
    reponse     TEXT          NULL,
    created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME      ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

echo '<p style=\"color:green;font-family:sans-serif;\">✅ Table <strong>support_messages</strong> créée (ou déjà existante).</p>';
