<?php
/**
 * TGTravail - Module de Connexion et Initialisation Automatique MySQL
 * Supporte les configurations MAMP (port 3306 / 8889, user: root, pass: root ou vide)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Database {
    private static ?PDO $instance = null;
    
    public static function getConnection(): PDO {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $configs = [
            ['host' => '127.0.0.1', 'port' => 3306, 'user' => 'root', 'pass' => 'root'],
            ['host' => '127.0.0.1', 'port' => 3306, 'user' => 'root', 'pass' => ''],
            ['host' => '127.0.0.1', 'port' => 8889, 'user' => 'root', 'pass' => 'root'],
            ['host' => '127.0.0.1', 'port' => 8889, 'user' => 'root', 'pass' => ''],
            ['host' => 'localhost', 'port' => 3306, 'user' => 'root', 'pass' => 'root'],
            ['host' => 'localhost', 'port' => 3306, 'user' => 'root', 'pass' => ''],
        ];

        $dbName = 'tgtravail';
        $connected = false;
        $lastError = '';

        foreach ($configs as $cfg) {
            try {
                // 1. Connexion au serveur MySQL
                $serverPdo = new PDO(
                    "mysql:host={$cfg['host']};port={$cfg['port']};charset=utf8mb4",
                    $cfg['user'],
                    $cfg['pass'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );

                // 2. Création de la base si inexistante
                $serverPdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

                // 3. Connexion directe à la base de données
                $pdo = new PDO(
                    "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$dbName};charset=utf8mb4",
                    $cfg['user'],
                    $cfg['pass'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );

                // 4. Initialisation des tables & données
                self::initializeSchema($pdo);

                self::$instance = $pdo;
                $connected = true;
                break;
            } catch (PDOException $e) {
                $lastError = $e->getMessage();
                continue;
            }
        }

        if (!$connected) {
            die("<div style='font-family:sans-serif;padding:2rem;background:#FEF2F2;color:#991B1B;border-radius:12px;max-width:600px;margin:3rem auto;'>
                <h2>⚠️ Erreur de connexion à la base de données</h2>
                <p>Impossible de se connecter au serveur MySQL local MAMP.</p>
                <p><small>Détail : {$lastError}</small></p>
                <p>Vérifiez que le serveur MySQL de MAMP est bien démarré.</p>
            </div>");
        }

        return self::$instance;
    }

    private static function initializeSchema(PDO $pdo): void {
        $jobsCount = 0;
        try {
            $jobsCount = (int)$pdo->query("SELECT COUNT(*) FROM jobs")->fetchColumn();
        } catch (Exception $e) {
            $jobsCount = 0;
        }

        if ($jobsCount === 0) {
            $schemaFile = __DIR__ . '/schema.sql';
            if (file_exists($schemaFile)) {
                $sql = file_get_contents($schemaFile);
                // Exécution par bloc de requête
                $queries = array_filter(array_map('trim', explode(';', $sql)));
                foreach ($queries as $query) {
                    if (!empty($query)) {
                        try {
                            $pdo->exec($query);
                        } catch (PDOException $ex) {
                            // Ignorer les avertissements mineurs
                        }
                    }
                }
            }
        }
    }
}

// Raccourci global
function getDB(): PDO {
    return Database::getConnection();
}
