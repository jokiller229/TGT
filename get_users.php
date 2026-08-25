<?php require 'c:/MAMP/htdocs/TGT/siteweb/config/db.php'; \ = getDB(); \ = \->query('SELECT email, role FROM utilisateurs LIMIT 10'); print_r(\->fetchAll(PDO::FETCH_ASSOC)); ?>
