<?php
require_once __DIR__ . '/../config.php';

/**
 * RatingController — gère toute la logique métier du système de notation.
 *
 * Respecte le pattern MVC :
 *  - Toutes les requêtes SQL sont ici (jamais dans les vues)
 *  - Utilise des prepared statements pour sécuriser les entrées
 *  - Retourne des arrays exploitables par les vues
 */
class RatingController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Config::getInstance()->getConnexion();
    }

    /**
     * Récupère la note d'un utilisateur pour un cours donné.
     * Retourne null si l'utilisateur n'a pas encore noté ce cours.
     */
    public function getUserRating(int $userId, int $courseId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, user_id, course_id, rating
             FROM ratings
             WHERE user_id = :user_id AND course_id = :course_id'
        );
        $stmt->execute(['user_id' => $userId, 'course_id' => $courseId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Calcule la note moyenne d'un cours, arrondie à 1 décimale.
     * Retourne aussi le nombre total d'avis.
     * Retourne ['average' => null, 'count' => 0] si aucune note.
     */
    public function getAverageRating(int $courseId): array
    {
        $stmt = $this->db->prepare(
            'SELECT ROUND(AVG(rating), 1) AS average, COUNT(*) AS total
             FROM ratings
             WHERE course_id = :course_id'
        );
        $stmt->execute(['course_id' => $courseId]);
        $row = $stmt->fetch();

        return [
            'average' => $row['total'] > 0 ? (float)$row['average'] : null,
            'count'   => (int)$row['total'],
        ];
    }

    /**
     * Crée ou met à jour la note d'un utilisateur pour un cours.
     * Un utilisateur ne peut avoir qu'une seule note par cours
     * (contrainte UNIQUE en base + INSERT ... ON DUPLICATE KEY UPDATE).
     */
    public function addOrUpdateRating(int $userId, int $courseId, int $rating): array
    {
        // Validation : la note doit être entre 1 et 5
        if ($rating < 1 || $rating > 5) {
            return ['success' => false, 'message' => 'La note doit être entre 1 et 5.'];
        }

        if ($userId <= 0 || $courseId <= 0) {
            return ['success' => false, 'message' => 'Paramètres invalides.'];
        }

        $existing = $this->getUserRating($userId, $courseId);

        // INSERT si première note, UPDATE si modification
        if ($existing === null) {
            $stmt = $this->db->prepare(
                'INSERT INTO ratings (user_id, course_id, rating)
                 VALUES (:user_id, :course_id, :rating)'
            );
        } else {
            $stmt = $this->db->prepare(
                'UPDATE ratings
                 SET rating = :rating
                 WHERE user_id = :user_id AND course_id = :course_id'
            );
        }

        $stmt->execute([
            'user_id'   => $userId,
            'course_id' => $courseId,
            'rating'    => $rating,
        ]);

        $action = $existing === null ? 'ajoutée' : 'mise à jour';
        return ['success' => true, 'message' => "Votre note a été {$action}.", 'rating' => $rating];
    }
}
?>
