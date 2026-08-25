<?php
require_once __DIR__ . '/siteweb/config/db.php';
$pdo = getDB();

$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
$fullSchema = "";
foreach($tables as $t) {
    $create = $pdo->query('SHOW CREATE TABLE `'.$t.'`')->fetch(PDO::FETCH_ASSOC);
    $fullSchema .= $create['Create Table'] . ";\n\n";
}

file_put_contents(__DIR__ . '/full_schema_dump.sql', $fullSchema);
echo "Dumped " . count($tables) . " tables to full_schema_dump.sql\n";
