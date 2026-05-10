<?php
/**
 * config.php — Singleton PDO, base unifiée e_lite
 * Contient Users + Courses + Forum dans une seule base.
 * 
 * ✅ Version sécurisée : les secrets sont lus depuis .env
 * Dernière mise à jour : Sécurisation des secrets OAuth
 */

// Charger les variables d'environnement depuis .env
function loadEnv($filePath = __DIR__ . '/.env')
{
    if (!file_exists($filePath)) {
        throw new RuntimeException(".env file not found at: $filePath");
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Ignorer les commentaires
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Analyser les variables
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Retirer les guillemets si présents
            if ((strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) ||
                (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1)) {
                $value = substr($value, 1, -1);
            }

            if (!empty($key)) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
            }
        }
    }
}

// Charger le fichier .env
loadEnv();

class Config
{
    private static ?Config $instance = null;
    private ?PDO $pdo = null;

    // Configuration Base de Données
    private const DB_HOST = '127.0.0.1';
    private const DB_PORT = '3306';
    private const DB_NAME = 'e_lite';
    private const DB_USER = 'root';
    private const DB_PASS = '';

    // Google OAuth - Configuration (lues depuis .env)
    const GOOGLE_REDIRECT_URI  = 'http://localhost/gestioncours/google_callback.php';

    /**
     * Constructeur privé - Singleton
     */
    private function __construct()
    {
        try {
            $dsn = 'mysql:host=' . self::DB_HOST
                 . ';port=' . self::DB_PORT
                 . ';dbname=' . self::DB_NAME
                 . ';charset=utf8mb4';

            $this->pdo = new PDO($dsn, self::DB_USER, self::DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }

    /**
     * Récupère l'instance unique du singleton
     */
    public static function getInstance(): Config
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Récupère la connexion PDO — instance method (used by course/enrollment controllers)
     */
    public function getPDO(): PDO
    {
        if ($this->pdo === null) {
            throw new RuntimeException('Connexion PDO indisponible.');
        }
        return $this->pdo;
    }

    /**
     * Récupère la connexion PDO — static method.
     * Works for both: Config::getConnexion() and Config::getInstance()->getConnexion()
     */
    public static function getConnexion(): PDO
    {
        $instance = self::getInstance();
        if ($instance->pdo === null) {
            throw new RuntimeException('Connexion PDO indisponible.');
        }
        return $instance->pdo;
    }

    /**
     * Version statique pour compatibilité avec Forum/User controllers
     */
    public static function getConnexionStatic(): PDO
    {
        return self::getConnexion();
    }

    /**
     * Returns the current logged-in user ID, or the first user in DB as fallback.
     * Used by Forum FrontOffice views that need a valid user ID.
     */
    public static function getOrCreateFrontOfficeUserId(): int
    {
        // Use session user if logged in
        if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] > 0) {
            return (int)$_SESSION['user_id'];
        }

        // Fallback: get first user in DB
        try {
            $pdo = self::getConnexion();
            $row = $pdo->query('SELECT idUser FROM user ORDER BY idUser ASC LIMIT 1')->fetch();
            if ($row) {
                return (int)$row['idUser'];
            }

            // No users exist — create a demo user
            $email = 'forum-demo@e-lite.local';
            $stmt  = $pdo->prepare(
                'INSERT IGNORE INTO user (nom, prenom, email, motDePasse, idRole, statut)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                'Forum', 'Visiteur', $email,
                password_hash('demo', PASSWORD_DEFAULT),
                2, 'actif',
            ]);
            $id = (int)$pdo->lastInsertId();
            if ($id > 0) return $id;

            // Last resort: find by email
            $q = $pdo->prepare('SELECT idUser FROM user WHERE email = ? LIMIT 1');
            $q->execute([$email]);
            $again = $q->fetch();
            return $again ? (int)$again['idUser'] : 1;

        } catch (Exception $e) {
            error_log('Config::getOrCreateFrontOfficeUserId — ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Récupère les clés Google OAuth depuis les variables d'environnement
     */
    public static function getGoogleClientId(): string
    {
        $clientId = getenv('GOOGLE_CLIENT_ID');
        if (!$clientId) {
            throw new RuntimeException('GOOGLE_CLIENT_ID not found in .env file');
        }
        return $clientId;
    }

    public static function getGoogleClientSecret(): string
    {
        $secret = getenv('GOOGLE_CLIENT_SECRET');
        if (!$secret) {
            throw new RuntimeException('GOOGLE_CLIENT_SECRET not found in .env file');
        }
        return $secret;
    }

    /**
     * Vérifie si Google OAuth est configuré
     */
    public static function isGoogleConfigured(): bool
    {
        try {
            $clientId = self::getGoogleClientId();
            $secret = self::getGoogleClientSecret();
            return !empty($clientId) && !empty($secret);
        } catch (RuntimeException $e) {
            return false;
        }
    }

    /**
     * Commence une transaction MySQL
     */
    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    /**
     * Valide la transaction en cours
     */
    public function commit(): void
    {
        $this->pdo->commit();
    }

    /**
     * Annule la transaction en cours
     */
    public function rollback(): void
    {
        $this->pdo->rollBack();
    }

    /**
     * Échappe une chaîne pour utilisation en SQL (legacy - préférer les requêtes préparées)
     */
    public function escapeString(string $input): string
    {
        return substr($this->pdo->quote($input), 1, -1);
    }
}

// ============================================================
// Global constants for backward compatibility
// ============================================================
if (!defined('GOOGLE_CLIENT_ID')) {
    try {
        define('GOOGLE_CLIENT_ID',     Config::getGoogleClientId());
        define('GOOGLE_CLIENT_SECRET', Config::getGoogleClientSecret());
    } catch (RuntimeException $e) {
        // Si .env n'est pas configuré, utiliser des valeurs vides
        define('GOOGLE_CLIENT_ID',     '');
        define('GOOGLE_CLIENT_SECRET', '');
    }
    define('GOOGLE_REDIRECT_URI',  Config::GOOGLE_REDIRECT_URI);
}

// ============================================================
// Multi-database compatibility (si plusieurs bases)
// ============================================================
class ForumDatabase
{
    public static function getConnexion(): PDO
    {
        return Config::getConnexionStatic();
    }
}

class UserDatabase
{
    public static function getConnexion(): PDO
    {
        return Config::getConnexionStatic();
    }
}

class CourseDatabase
{
    public static function getConnexion(): PDO
    {
        return Config::getConnexionStatic();
    }
}

// ============================================================
// Fonction de debug (à supprimer en production)
// ============================================================
function debugQuery($sql, $params = []) {
    if (defined('DEBUG_MODE') && DEBUG_MODE === true) {
        error_log("SQL: " . $sql);
        error_log("Params: " . print_r($params, true));
    }
}