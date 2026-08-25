<?php
/**
 * TGTravail - Gestion de l'Authentification & des Rôles en Session
 * Inclut : login, inscription, déconnexion, rôles
 */

require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─── TRAITEMENT INSCRIPTION ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $db = getDB();
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = in_array($_POST['role'] ?? '', ['candidat', 'recruteur']) ? $_POST['role'] : 'candidat';
    $telephone = $_POST['telephone'] ?? null;
    $type_entite = $_POST['type_entite'] ?? 'entreprise';

    $authError = '';
    if (empty($nom) || empty($email) || empty($password)) {
        $authError = 'Tous les champs sont requis.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $authError = 'Adresse email invalide.';
    } elseif (strlen($password) < 6) {
        $authError = 'Le mot de passe doit contenir au moins 6 caractères.';
    } else {
        $check = $db->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $authError = 'Un compte avec cet email existe déjà.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = $db->prepare("INSERT INTO users (nom, email, password, role, telephone, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $insert->execute([$nom, $email, $hash, $role, $telephone]);
            $newId = (int)$db->lastInsertId();

            // Créer profil candidat ou entreprise si nécessaire
            if ($role === 'candidat') {
                $db->prepare("INSERT INTO candidate_profiles (user_id, titre_professionnel, bio, ville, experience_annees, disponibilite, completion_pct) VALUES (?, '', '', 'Lomé', 0, 'Immédiate', 20)")->execute([$newId]);
            } elseif ($role === 'recruteur') {
                $db->prepare("INSERT INTO companies (user_id, nom, secteur, ville, verifie, type_entite) VALUES (?, ?, 'Autre', 'Lomé', 0, ?)")->execute([$newId, $nom, $type_entite]);
            }

            $_SESSION['user_id'] = $newId;
            $_SESSION['user_role'] = $role;
            $_SESSION['auth_success'] = 'Bienvenue sur TGTravail ! Votre compte a été créé avec succès.';

            if ($role === 'recruteur') {
                $redirect = ($type_entite === 'particulier') ? '../recruteur/particulier-dashboard.php' : '../recruteur/recruteur-dashboard.php';
            } else {
                $redirect = '../candidat/candidat-dashboard.php';
            }
            header("Location: $redirect");
            exit;
        }
    }
    $_SESSION['auth_error'] = $authError;
    header("Location: ../auth/inscription.php");
    exit;
}

// ─── TRAITEMENT CONNEXION ─────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $db = getDB();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $authError = '';
    if (empty($email) || empty($password)) {
        $authError = 'Email et mot de passe requis.';
    } else {
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $authError = 'Email ou mot de passe incorrect.';
        } elseif (isset($user['statut_compte']) && $user['statut_compte'] === 'banni') {
            $authError = 'Votre compte a été banni de la plateforme.';
        } elseif (isset($user['statut_compte']) && $user['statut_compte'] === 'suspendu') {
            $authError = 'Votre compte est temporairement suspendu.';
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            if ($user['role'] === 'admin') {
                $_SESSION['admin_id'] = $user['id'];
            }
            $_SESSION['auth_success'] = 'Connexion réussie. Bienvenue, ' . $user['nom'] . ' !';

            $redirect = $user['role'] === 'recruteur' ? '../recruteur/recruteur-dashboard.php' : ($user['role'] === 'admin' ? '../../interface%20admin/dashboard.php' : '../candidat/candidat-dashboard.php');
            header("Location: $redirect");
            exit;
        }
    }
    $_SESSION['auth_error'] = $authError;
    header("Location: ../auth/connexion.php");
    exit;
}

// ─── DÉCONNEXION ──────────────────────────────────────────────────────────────
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../index.php");
    exit;
}

// ─── CHANGEMENT RAPIDE DE RÔLE (démo) ────────────────────────────────────────
if (isset($_GET['switch_role'])) {
    $targetRole = $_GET['switch_role'];
    if (in_array($targetRole, ['candidat', 'recruteur', 'admin'])) {
        $_SESSION['user_role'] = $targetRole;
        if ($targetRole === 'candidat') {
            $_SESSION['user_id'] = 1;
        } elseif ($targetRole === 'recruteur') {
            $_SESSION['user_id'] = 2;
        } elseif ($targetRole === 'admin') {
            $_SESSION['user_id'] = 3;
        }
        $redirectUrl = strtok($_SERVER['REQUEST_URI'], '?');
        header("Location: " . $redirectUrl);
        exit;
    }
}

// ─── HELPERS ──────────────────────────────────────────────────────────────────
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function getCurrentUserId(): int {
    if (!isLoggedIn()) {
        return 0;
    }
    return (int)$_SESSION['user_id'];
}

function getCurrentUser(): ?array {
    $userId = getCurrentUserId();
    if ($userId === 0) return null;
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch() ?: null;
}

function getCurrentCompanyId(): int {
    $userId = getCurrentUserId();
    if ($userId === 0) return 0;
    $db = getDB();
    $stmt = $db->prepare("SELECT id FROM companies WHERE user_id = ?");
    $stmt->execute([$userId]);
    $res = $stmt->fetch();
    return $res ? (int)$res['id'] : 0;
}

function getCurrentRole(): string {
    return $_SESSION['user_role'] ?? 'visiteur';
}

function requireRole(string $role): void {
    if (getCurrentRole() !== $role) {
        header("Location: ../index.php");
        exit;
    }
}

function hasSuperAdmin(): bool {
    if (getCurrentRole() !== 'admin') return false;
    $user = getCurrentUser();
    return $user && isset($user['admin_level']) && $user['admin_level'] === 'superadmin';
}

function requireSuperAdmin(): void {
    if (!hasSuperAdmin()) {
        header("Location: ../admin/admin-dashboard.php?error=unauthorized");
        exit;
    }
}

function requireAdmin(): void {
    if (getCurrentRole() !== 'admin') {
        header("Location: ../index.php?error=unauthorized");
        exit;
    }
}


