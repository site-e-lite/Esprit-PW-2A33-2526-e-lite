<?php
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
                    'mysql:host=localhost;dbname=e_lite',
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
}
?>