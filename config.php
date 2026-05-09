<?php
/**
<<<<<<< HEAD
 * Singleton PDO pour centraliser la connexion à la base e_lite.
 */
class Config
{
    private static ?Config $instance = null;
    private ?PDO $pdo = null;

    private const DB_HOST = '127.0.0.1';
    private const DB_PORT = '3306';
    private const DB_NAME = 'e_lite';
    private const DB_USER = 'root';
    private const DB_PASS = '';

    private function __construct()
    {
        $dsn = 'mysql:host=' . self::DB_HOST
            . ';port=' . self::DB_PORT
            . ';dbname=' . self::DB_NAME
            . ';charset=utf8mb4';

        $this->pdo = new PDO(
            $dsn,
            self::DB_USER,
            self::DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }

    public static function getInstance(): Config
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnexion(): PDO
    {
        if ($this->pdo === null) {
            throw new RuntimeException('Connexion PDO indisponible.');
        }
        return $this->pdo;
=======
 * Connexion PDO vers la base MySQL nommée elite_forum (à créer / importer depuis une sauvegarde SQL hors dépôt).
 */
class Config
{
    private static $pdo = null;
    const GEMINI_API_KEY = 'AIzaSyCmrp8wdCRI2ym2b9AnEMqMQoLOMlKYiyA';
    const GROQ_API_KEY = '';

    public static function getConnexion()
    {
        if (!isset(self::$pdo)) {
            try {
                self::$pdo = new PDO(
                    'mysql:host=localhost;dbname=elite_forum;charset=utf8mb4',
                    'root',
                    '',
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
            } catch (Exception $e) {
                die('Erreur de connexion : ' . $e->getMessage());
            }
        }
        return self::$pdo;
    }

    /**
     * Premier utilisateur en base, sinon création d'un compte démo pour le front-office.
     * Nécessaire car post.idUser est NOT NULL + FK vers user (un id fictif 8 échouait si la table user était vide).
     */
    public static function getOrCreateFrontOfficeUserId(): int
    {
        $db = self::getConnexion();
        $row = $db->query('SELECT idUser FROM user ORDER BY idUser ASC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return (int) $row['idUser'];
        }

        $email = 'forum-demo@e-lite.local';
        try {
            $stmt = $db->prepare(
                'INSERT INTO user (nom, prenom, email, motDePasse, idRole) VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                'Forum',
                'Visiteur',
                $email,
                password_hash('demo', PASSWORD_DEFAULT),
                2,
            ]);
            return (int) $db->lastInsertId();
        } catch (PDOException $e) {
            $q = $db->prepare('SELECT idUser FROM user WHERE email = ?');
            $q->execute([$email]);
            $again = $q->fetch(PDO::FETCH_ASSOC);
            if ($again) {
                return (int) $again['idUser'];
            }
            $fallback = $db->query('SELECT idUser FROM user ORDER BY idUser ASC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
            if ($fallback) {
                return (int) $fallback['idUser'];
            }
            throw $e;
        }
>>>>>>> 947d1560670f98dea9fd32a6da1b7f0f76c3eb81
    }
}
?>