<?php
require_once __DIR__ . '/siteweb/config/db.php';
$pdo = getDB();

$sql = "CREATE TABLE IF NOT EXISTS conseils (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    categorie VARCHAR(100) NOT NULL,
    contenu TEXT NOT NULL,
    temps_lecture INT NOT NULL DEFAULT 5,
    icone VARCHAR(50) DEFAULT '📄',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

try {
    $pdo->exec($sql);
    echo "Table 'conseils' créée avec succès.\n";
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}
