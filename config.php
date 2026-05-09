<?php
/**
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
    }
}
?>