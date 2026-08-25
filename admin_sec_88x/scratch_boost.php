<?php
require 'c:\MAMP\htdocs\TGT\siteweb\config\db.php';
$db = getDB();

// Check if boosted/featured columns exist in jobs table
$cols = $db->query('DESCRIBE jobs')->fetchAll(PDO::FETCH_COLUMN, 0);
echo "Existing jobs columns:\n";
echo implode(", ", $cols) . "\n\n";

// Check if boost-related tables exist
$tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
echo "All tables:\n";
echo implode(", ", $tables) . "\n";
