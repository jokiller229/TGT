<?php
$sql = file_get_contents(__DIR__ . '/full_schema_dump.sql');
$clean = preg_replace('/ AUTO_INCREMENT=\d+/', '', $sql);
file_put_contents(__DIR__ . '/siteweb/config/schema.sql', $clean);
echo "Schema mis a jour dans siteweb/config/schema.sql\n";
