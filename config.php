<?php
/**
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
                'INSERT INTO user (nom, prenom, email, motDePasse, role) VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                'Forum',
                'Visiteur',
                $email,
                password_hash('demo', PASSWORD_DEFAULT),
                'etudiant',
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
    }
}
?>