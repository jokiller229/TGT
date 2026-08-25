<?php
require_once __DIR__ . '/siteweb/config/db.php';
$pdo = getDB();

$schemaFile = __DIR__ . '/siteweb/config/schema.sql';
if (file_exists($schemaFile)) {
    $sql = file_get_contents($schemaFile);
    // On ajoute IF NOT EXISTS à toutes les requêtes de création si ce n'est pas le cas, 
    // le dump mysql le met par défaut? En fait SHOW CREATE TABLE ne met PAS IF NOT EXISTS par défaut.
    // On va modifier la chaine pour ajouter IF NOT EXISTS.
    $sql = str_replace('CREATE TABLE `', 'CREATE TABLE IF NOT EXISTS `', $sql);
    
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    $success = 0;
    foreach ($queries as $query) {
        if (!empty($query)) {
            try {
                $pdo->exec($query);
                $success++;
            } catch (PDOException $ex) {
                echo "Erreur sur une requête : " . $ex->getMessage() . "<br>";
            }
        }
    }
    echo "Opération terminée ! $success requêtes exécutées (les tables manquantes ont été créées).";
} else {
    echo "Fichier schema.sql introuvable.";
}
