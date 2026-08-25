<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();
    
    if ($admin && password_verify($password, $admin['password'])) {
        if (isset($admin['statut_compte']) && $admin['statut_compte'] !== 'actif') {
            $error = "Ce compte administrateur est désactivé ou suspendu.";
        } else {
            $_SESSION['admin_id'] = $admin['id'];
            header("Location: dashboard.php");
            exit;
        }
    } else {
        $error = "Identifiants incorrects ou accès refusé.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Administration - TGTravail</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #081326; color: white; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: white; color: #1e293b; padding: 2rem; border-radius: 8px; width: 100%; max-width: 400px; box-shadow: 0 4px 6px rgba(0,0,0,0.3); }
        .form-group { margin-bottom: 1rem; }
        label { display: block; font-weight: 500; margin-bottom: 0.5rem; }
        input { width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box; }
        .btn { width: 100%; padding: 0.75rem; background: #2563EB; color: white; border: none; border-radius: 4px; font-weight: 600; cursor: pointer; margin-top: 1rem; }
        .btn:hover { background: #1d4ed8; }
        .error { color: #ef4444; margin-bottom: 1rem; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2 style="margin-top:0; text-align:center;">Administration</h2>
        <?php if ($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn">Se connecter</button>
        </form>
    </div>
</body>
</html>
