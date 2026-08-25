<?php
require 'c:\MAMP\htdocs\TGT\siteweb\config\db.php';
$db = getDB();

echo "=== Corrections schemas ===\n";

// 1. Vérifier la structure de conversations
$convCols = $db->query('DESCRIBE conversations')->fetchAll(PDO::FETCH_ASSOC);
echo "\nColonnes conversations:\n";
foreach($convCols as $c) echo "  " . $c['Field'] . " (" . $c['Type'] . ")\n";

// 2. Vérifier enum statut des applications
$appStatut = $db->query("SHOW COLUMNS FROM applications LIKE 'statut'")->fetch(PDO::FETCH_ASSOC);
echo "\napplications.statut: " . $appStatut['Type'] . "\n";

// 3. Fix: ajouter created_at à conversations si absent
$cols = array_column($convCols, 'Field');
if (!in_array('created_at', $cols)) {
    try {
        $db->exec("ALTER TABLE conversations ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
        echo "\nAjouté conversations.created_at\n";
    } catch(Exception $e) { echo "\nErreur: " . $e->getMessage() . "\n"; }
} else {
    echo "\nconversations.created_at existe deja\n";
}

// 4. Fix: agrandir l'enum de applications.statut pour inclure 'vue','retenu','refuse','entretien'
try {
    $db->exec("ALTER TABLE applications MODIFY COLUMN statut ENUM('en_attente','vue','retenu','refuse','entretien','accepte') DEFAULT 'en_attente'");
    echo "applications.statut enum élargi\n";
} catch(Exception $e) { echo "Erreur statut: " . $e->getMessage() . "\n"; }

echo "\nDone.\n";
