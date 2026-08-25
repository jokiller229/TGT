<?php
require 'siteweb/config/db.php';
$db = getDB();
$q = $db->query('SHOW TABLES');
print_r($q->fetchAll(PDO::FETCH_COLUMN));
