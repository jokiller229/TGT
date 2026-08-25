<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['company_id'])) {
    $db = getDB();
    $companyId = (int)$_POST['company_id'];
    
    $stmt = $db->prepare("UPDATE companies SET verifie = 1 WHERE id = ?");
    $stmt->execute([$companyId]);
    
    header("Location: dashboard.php?msg=success");
    exit;
}

header("Location: dashboard.php");
exit;
