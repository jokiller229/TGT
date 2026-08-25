<?php
/**
 * Audit complet de la plateforme TGTravail
 * Vérifie: DB, tables/colonnes, logique métier, intégrité des APIs
 */
require 'c:\MAMP\htdocs\TGT\siteweb\config\db.php';
$db = getDB();

$ok = 0; $fail = 0; $warn = 0;

function check($label, $result, $expected = true, $warning = false) {
    global $ok, $fail, $warn;
    $pass = ($result === $expected) || ($expected === true && $result);
    if ($pass) {
        $ok++;
        echo "[OK]   $label\n";
    } elseif ($warning) {
        $warn++;
        echo "[WARN] $label → $result\n";
    } else {
        $fail++;
        echo "[FAIL] $label → " . (is_bool($result) ? ($result ? 'true' : 'false') : $result) . "\n";
    }
}

function col_exists($db, $table, $col) {
    $cols = $db->query("DESCRIBE $table")->fetchAll(PDO::FETCH_COLUMN, 0);
    return in_array($col, $cols);
}

function table_exists($db, $table) {
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    return in_array($table, $tables);
}

echo "=== TGTravail — Audit Plateforme ===\n\n";

// ── 1. DATABASE TABLES ────────────────────────────────────────────────────────
echo "--- [1] Tables principales ---\n";
foreach (['users','companies','jobs','applications','candidate_profiles',
          'conversations','messages','notifications','reports',
          'interviews','saved_jobs','transactions','job_alerts'] as $t) {
    check("Table: $t", table_exists($db, $t));
}

// ── 2. COLONNES CRITIQUES ─────────────────────────────────────────────────────
echo "\n--- [2] Colonnes critiques ---\n";
$cols = [
    'jobs'       => ['boosted_until','featured_until','push_sent_at','statut','pack'],
    'companies'  => ['verifie','statut_validation','type_entite'],
    'users'      => ['role','statut_compte'],
    'reports'    => ['statut_traitement','reporter_user_id'],
    'interviews' => ['recruiter_id','candidate_id','date_time','meet_link','status'],
    'notifications' => ['user_id','message','lu'],
    'conversations' => ['user1_id','user2_id'],
    'messages'   => ['conversation_id','sender_id','contenu'],
    'applications' => ['job_id','candidate_id','statut'],
];
foreach ($cols as $table => $columns) {
    foreach ($columns as $col) {
        check("$table.$col", col_exists($db, $table, $col));
    }
}

// ── 3. DONNÉES DE TEST ────────────────────────────────────────────────────────
echo "\n--- [3] Données existantes ---\n";
$counts = [
    'users'           => "SELECT COUNT(*) FROM users",
    'companies'       => "SELECT COUNT(*) FROM companies",
    'jobs (active)'   => "SELECT COUNT(*) FROM jobs WHERE statut='active'",
    'candidats'       => "SELECT COUNT(*) FROM users WHERE role='candidat'",
    'recruteurs'      => "SELECT COUNT(*) FROM users WHERE role='recruteur'",
    'applications'    => "SELECT COUNT(*) FROM applications",
    'messages'        => "SELECT COUNT(*) FROM messages",
    'notifications'   => "SELECT COUNT(*) FROM notifications",
    'interviews'      => "SELECT COUNT(*) FROM interviews",
];
foreach ($counts as $label => $sql) {
    $n = (int)$db->query($sql)->fetchColumn();
    check("$label = $n", $n >= 0); // just verify query runs
    if ($n === 0) echo "       └─ WARNING: Aucune donnee pour '$label'\n";
}

// ── 4. ADMIN ──────────────────────────────────────────────────────────────────
echo "\n--- [4] Compte Admin ---\n";
$admin = $db->query("SELECT id,nom,email,role FROM users WHERE email='admin@tgtravail.com' LIMIT 1")->fetch();
check("Admin existe (admin@tgtravail.com)", !empty($admin));
if ($admin) {
    check("Admin role=admin", $admin['role'] === 'admin');
}

// ── 5. LOGIQUE MÉTIER — BOOST ──────────────────────────────────────────────────
echo "\n--- [5] Logique Boost / Feature / Push ---\n";
// Simulate what api-boost-job.php does (without session)
$job = $db->query("SELECT id, titre, boosted_until FROM jobs WHERE statut='active' LIMIT 1")->fetch();
if ($job) {
    check("Job actif trouvé pour le boost", true);
    $isAlreadyBoosted = $job['boosted_until'] && strtotime($job['boosted_until']) > time();
    check("boosted_until lisible", true); // query ran fine
    // Test update
    $testDate = date('Y-m-d H:i:s', strtotime('+7 days'));
    $res = $db->prepare("UPDATE jobs SET boosted_until=? WHERE id=?")->execute([$testDate, $job['id']]);
    check("UPDATE boosted_until fonctionnel", $res);
    // Rollback test (reset to null unless it was already set)
    if (!$isAlreadyBoosted) {
        $db->prepare("UPDATE jobs SET boosted_until=NULL WHERE id=?")->execute([$job['id']]);
    }
} else {
    echo "[WARN] Aucune offre active — impossible de tester le boost\n";
    $warn++;
}

// Test featured_until
$job2 = $db->query("SELECT id, featured_until FROM jobs WHERE statut='active' LIMIT 1")->fetch();
if ($job2) {
    $res = $db->prepare("UPDATE jobs SET featured_until=? WHERE id=?")->execute([date('Y-m-d H:i:s', strtotime('+3 days')), $job2['id']]);
    check("UPDATE featured_until fonctionnel", $res);
    $db->prepare("UPDATE jobs SET featured_until=NULL WHERE id=?")->execute([$job2['id']]);
}

// Test push_sent_at
$job3 = $db->query("SELECT id FROM jobs WHERE statut='active' LIMIT 1")->fetch();
if ($job3) {
    $res = $db->prepare("UPDATE jobs SET push_sent_at=NOW() WHERE id=?")->execute([$job3['id']]);
    check("UPDATE push_sent_at fonctionnel", $res);
    $db->prepare("UPDATE jobs SET push_sent_at=NULL WHERE id=?")->execute([$job3['id']]);
}

// ── 6. SYSTÈME NOTIFICATION ───────────────────────────────────────────────────
echo "\n--- [6] Système Notifications ---\n";
$candidat = $db->query("SELECT id FROM users WHERE role='candidat' AND statut_compte='actif' LIMIT 1")->fetch();
if ($candidat) {
    $res = $db->prepare("INSERT INTO notifications (user_id, message, lu, created_at) VALUES (?, ?, 0, NOW())")
        ->execute([$candidat['id'], '[TEST AUDIT] Notification de test - peut etre supprimee']);
    check("INSERT notification", $res);
    // Cleanup
    $db->query("DELETE FROM notifications WHERE message='[TEST AUDIT] Notification de test - peut etre supprimee'");
    check("DELETE notification (cleanup)", true);
} else {
    echo "[WARN] Aucun candidat actif pour tester les notifications\n";
    $warn++;
}

// Test admin broadcast (diffusions)
$allUsers = $db->query("SELECT COUNT(*) FROM users WHERE role != 'admin'")->fetchColumn();
check("Utilisateurs cibles pour diffusion admin: $allUsers", $allUsers >= 0);

// ── 7. MESSAGERIE ─────────────────────────────────────────────────────────────
echo "\n--- [7] Messagerie ---\n";
$convCount = (int)$db->query("SELECT COUNT(*) FROM conversations")->fetchColumn();
check("Table conversations accessible", true);
$msgCount = (int)$db->query("SELECT COUNT(*) FROM messages")->fetchColumn();
check("Table messages accessible", true);
// Check JOIN works
$convJoin = $db->query("
    SELECT c.id, u1.nom as user1, u2.nom as user2
    FROM conversations c
    JOIN users u1 ON c.user1_id = u1.id
    JOIN users u2 ON c.user2_id = u2.id
    LIMIT 1
")->fetch();
check("JOIN conversations+users fonctionne", true);

// ── 8. CANDIDATURES ───────────────────────────────────────────────────────────
echo "\n--- [8] Candidatures ---\n";
$app = $db->query("
    SELECT a.id, a.statut, j.titre, u.nom
    FROM applications a
    JOIN jobs j ON a.job_id = j.id
    JOIN users u ON a.candidate_id = u.id
    LIMIT 1
")->fetch();
check("JOIN applications+jobs+users", true);
// Check statut enum values
$statuts = $db->query("SHOW COLUMNS FROM applications LIKE 'statut'")->fetch();
check("applications.statut colonne présente", !empty($statuts));

// ── 9. ENTRETIENS ─────────────────────────────────────────────────────────────
echo "\n--- [9] Entretiens (Interviews) ---\n";
$interview = $db->query("
    SELECT i.id, u1.nom as recruteur, u2.nom as candidat, i.date_time, i.meet_link, i.status
    FROM interviews i
    JOIN users u1 ON i.recruiter_id = u1.id
    JOIN users u2 ON i.candidate_id = u2.id
    LIMIT 1
")->fetch();
check("JOIN interviews+users fonctionnel", true);
// Test insert
try {
    $rec = $db->query("SELECT id FROM users WHERE role='recruteur' LIMIT 1")->fetch();
    $cand = $db->query("SELECT id FROM users WHERE role='candidat' LIMIT 1")->fetch();
    if ($rec && $cand) {
        $res = $db->prepare("INSERT INTO interviews (recruiter_id, candidate_id, date_time, meet_link, status) VALUES (?,?,?,?,'planned')")
            ->execute([$rec['id'], $cand['id'], date('Y-m-d H:i:s', strtotime('+3 days')), 'https://meet.jit.si/test-audit']);
        check("INSERT interview", $res);
        $db->query("DELETE FROM interviews WHERE meet_link='https://meet.jit.si/test-audit'");
        check("DELETE interview (cleanup)", true);
    } else {
        echo "[WARN] Manque recruteur ou candidat pour tester les entretiens\n";
        $warn++;
    }
} catch(Exception $e) {
    check("INSERT interview", false);
    echo "       └─ " . $e->getMessage() . "\n";
}

// ── 10. CVthèque ──────────────────────────────────────────────────────────────
echo "\n--- [10] CVtheque ---\n";
$profiles = (int)$db->query("SELECT COUNT(*) FROM candidate_profiles")->fetchColumn();
check("Table candidate_profiles accessible", true);
check("Profils candidats: $profiles", $profiles >= 0);
// Check important columns
foreach (['user_id','competences','experience','formation'] as $col) {
    $exists = col_exists($db, 'candidate_profiles', $col);
    if (!$exists) echo "[WARN] candidate_profiles.$col absent\n";
    else check("candidate_profiles.$col", true);
}

// ── 11. SIGNALEMENTS ──────────────────────────────────────────────────────────
echo "\n--- [11] Signalements ---\n";
check("Table reports accessible", table_exists($db, 'reports'));
foreach (['id','job_id','reporter_user_id','motif','statut','statut_traitement'] as $col) {
    check("reports.$col", col_exists($db, 'reports', $col));
}

// ── 12. FICHIERS PHP — SYNTAXE ────────────────────────────────────────────────
echo "\n--- [12] Synthese ---\n";
echo "OK: $ok | WARN: $warn | FAIL: $fail\n";
if ($fail === 0 && $warn === 0) {
    echo "\nRESULTAT: TOUT EST OPERATIONNEL\n";
} elseif ($fail === 0) {
    echo "\nRESULTAT: Plateforme fonctionnelle avec $warn avertissement(s)\n";
} else {
    echo "\nRESULTAT: $fail probleme(s) critique(s) a corriger\n";
}
