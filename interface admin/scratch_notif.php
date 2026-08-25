<?php
require 'c:\MAMP\htdocs\TGT\siteweb\config\db.php';
$db = getDB();
$res = $db->query('DESCRIBE notifications')->fetchAll(PDO::FETCH_ASSOC);
print_r($res);
