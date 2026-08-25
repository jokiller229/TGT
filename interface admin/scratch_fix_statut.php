<?php
require 'c:\MAMP\htdocs\TGT\siteweb\config\db.php';
$db = getDB();

// The enum values are: nouveau, evaluation, entretien, embauche, refuse
// We need to ADD 'en_attente' and 'vue' and keep the existing ones

try {
    $db->exec("ALTER TABLE applications MODIFY COLUMN statut ENUM('nouveau','evaluation','entretien','embauche','refuse','en_attente','vue','retenu','accepte') DEFAULT 'nouveau'");
    echo "applications.statut enum élargi avec succès\n";
    
    // Verify
    $s = $db->query("SHOW COLUMNS FROM applications LIKE 'statut'")->fetch();
    echo "Nouveau type: " . $s['Type'] . "\n";
} catch(Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
    
    // Alternative: check what values exist and if ALTER fails, just document them
    $existing = $db->query("SELECT DISTINCT statut FROM applications")->fetchAll(PDO::FETCH_COLUMN);
    echo "Valeurs existantes: " . implode(', ', $existing) . "\n";
}
