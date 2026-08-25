<?php
require 'siteweb/config/db.php';
$db = getDB();
$db->exec("ALTER TABLE jobs MODIFY COLUMN statut ENUM('active', 'en_attente', 'refusee', 'expiree', 'ouverte', 'suspendue') DEFAULT 'active'");
echo "Table jobs altered successfully.\n";
