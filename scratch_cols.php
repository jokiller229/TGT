<?php
require_once __DIR__ . '/siteweb/config/db.php';
$db = getDB();
print_r($db->query('SELECT id, email, role FROM users')->fetchAll(PDO::FETCH_ASSOC));
