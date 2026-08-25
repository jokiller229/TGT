<?php
require 'c:\MAMP\htdocs\TGT\siteweb\config\db.php';
$db = getDB();
try {
    $res = $db->query("DESCRIBE reports")->fetchAll(PDO::FETCH_ASSOC);
    print_r($res);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
