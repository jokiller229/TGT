<?php
require 'c:\MAMP\htdocs\TGT\siteweb\config\db.php';
$db = getDB();

// Add boosted_until and featured_until to jobs
try {
    $db->exec("ALTER TABLE jobs ADD COLUMN boosted_until DATETIME NULL DEFAULT NULL");
    echo "Added boosted_until\n";
} catch(Exception $e) { echo "boosted_until: " . $e->getMessage() . "\n"; }

try {
    $db->exec("ALTER TABLE jobs ADD COLUMN featured_until DATETIME NULL DEFAULT NULL");
    echo "Added featured_until\n";
} catch(Exception $e) { echo "featured_until: " . $e->getMessage() . "\n"; }

// Also add push_sent column to track push notifications per job
try {
    $db->exec("ALTER TABLE jobs ADD COLUMN push_sent_at DATETIME NULL DEFAULT NULL");
    echo "Added push_sent_at\n";
} catch(Exception $e) { echo "push_sent_at: " . $e->getMessage() . "\n"; }

echo "Done.\n";
