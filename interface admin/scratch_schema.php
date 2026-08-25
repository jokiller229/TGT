<?php
require 'c:\MAMP\htdocs\TGT\siteweb\config\db.php';
$db = getDB();
echo "=== JOBS ===\n";
$cols = $db->query('DESCRIBE jobs')->fetchAll(PDO::FETCH_ASSOC);
foreach($cols as $c) echo $c['Field'].' ('.$c['Type'].")\n";
echo "\n=== COMPANIES ===\n";
$cols = $db->query('DESCRIBE companies')->fetchAll(PDO::FETCH_ASSOC);
foreach($cols as $c) echo $c['Field'].' ('.$c['Type'].")\n";
echo "\n=== USERS ===\n";
$cols = $db->query('DESCRIBE users')->fetchAll(PDO::FETCH_ASSOC);
foreach($cols as $c) echo $c['Field'].' ('.$c['Type'].")\n";
