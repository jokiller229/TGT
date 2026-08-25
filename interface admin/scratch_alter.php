<?php
require 'c:\MAMP\htdocs\TGT\siteweb\config\db.php';
$db = getDB();
try {
    $db->exec("ALTER TABLE reports ADD COLUMN statut_traitement ENUM('nouveau', 'en_cours', 'resolu') DEFAULT 'nouveau'");
    echo "Column statut_traitement added to reports.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
