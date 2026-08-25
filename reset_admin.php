<?php
require_once __DIR__ . '/siteweb/config/db.php';
$db = getDB();

$email = 'admin@tgtravail.com';
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

// Check if admin exists
$stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
$admin = $stmt->fetch();

if ($admin) {
    // Update existing
    $db->prepare("UPDATE users SET password = ?, role = 'admin', statut_compte = 'actif' WHERE id = ?")->execute([$hash, $admin['id']]);
    echo "Mot de passe admin mis à jour avec succès.";
} else {
    // Insert new
    $db->prepare("INSERT INTO users (nom, prenom, email, password, role, statut_compte, created_at) VALUES (?, ?, ?, ?, 'admin', 'actif', NOW())")
       ->execute(['Admin', 'Super', $email, $hash]);
    echo "Compte admin créé avec succès.";
}
