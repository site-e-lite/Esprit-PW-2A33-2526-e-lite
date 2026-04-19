<?php
/**
 * Configuration FrontOffice
 * Accès à la connexion PDO centralisée + Constantes des tables
 */

// Classe de configuration pour la connexion à la base de données
class Config {
    private static $pdo = null;

    /**
     * Récupère l'instance PDO de la connexion
     * @return PDO - Objet de connexion à la base de données
     */
    public static function getConnexion() {
        if (!isset(self::$pdo)) {
            try {
                self::$pdo = new PDO(
                    'mysql:host=localhost;dbname=e_lite',
                    'root',
                    '',
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
            } catch (Exception $e) {
                die('Erreur de connexion à la base de données : ' . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}

// Démarrage de la session pour accéder aux variables de session (idUser, etc.)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================================================
// CONSTANTES POUR LES NOMS DES TABLES (correspond aux noms réels dans phpMyAdmin)
// ============================================================================
define('TABLE_USER', 'users');
define('TABLE_COURSE', 'course');
define('TABLE_ENROLLMENT', 'enrollments');
define('TABLE_QUIZ', 'quiz');
define('TABLE_QUESTION', 'questions');
define('TABLE_FORUM', 'forum');
define('TABLE_POST', 'post');
define('TABLE_VIRTUALCLASS', 'virtualclass');
define('TABLE_SESSION', 'session');
?>
