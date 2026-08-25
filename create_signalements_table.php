<?php
require_once __DIR__ . '/siteweb/config/db.php';
$db = getDB();

try {
    $db->exec("
        CREATE TABLE IF NOT EXISTS signalements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            job_id INT NOT NULL,
            user_id INT NULL,
            motif VARCHAR(255) NOT NULL,
            details TEXT NOT NULL,
            statut ENUM('en attente', 'traite') DEFAULT 'en attente',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (job_id) REFERENCES jobs(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "Table 'signalements' créée avec succès.\n";
} catch (PDOException $e) {
    echo "Erreur lors de la création de la table: " . $e->getMessage() . "\n";
}
