<?php
require_once __DIR__ . '/../config.php';

/**
 * CertificateController — gère la génération et la consultation des certificats.
 *
 * Respecte le pattern MVC :
 *  - Toute la logique SQL est ici, jamais dans les vues
 *  - Prepared statements sur toutes les requêtes
 *  - Idempotent : generate() ne crée pas de doublon
 */
class CertificateController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Config::getInstance()->getConnexion();
    }

    // ──────────────────────────────────────────────────────────────
    //  LECTURE
    // ──────────────────────────────────────────────────────────────

    /**
     * Retourne un certificat par son ID, en vérifiant qu'il appartient à l'utilisateur.
     * Retourne null si introuvable ou si l'utilisateur ne correspond pas.
     */
    public function getById(int $certId, int $userId): ?array
    {
        if ($certId <= 0 || $userId <= 0) return null;

        $stmt = $this->db->prepare(
            'SELECT c.id, c.user_id, c.course_id, c.date_obtained,
                    co.titre AS course_titre
             FROM certificates c
             INNER JOIN course co ON co.idCourse = c.course_id
             WHERE c.id = :certId AND c.user_id = :userId'
        );
        $stmt->execute(['certId' => $certId, 'userId' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Retourne le certificat d'un utilisateur pour un cours donné.
     * Retourne null si aucun certificat n'existe.
     */
    public function getByUserAndCourse(int $userId, int $courseId): ?array
    {
        if ($userId <= 0 || $courseId <= 0) return null;

        $stmt = $this->db->prepare(
            'SELECT c.id, c.user_id, c.course_id, c.date_obtained,
                    co.titre AS course_titre
             FROM certificates c
             INNER JOIN course co ON co.idCourse = c.course_id
             WHERE c.user_id = :userId AND c.course_id = :courseId'
        );
        $stmt->execute(['userId' => $userId, 'courseId' => $courseId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Retourne tous les certificats d'un utilisateur,
     * avec le titre du cours associé.
     */
    public function getByUser(int $userId): array
    {
        if ($userId <= 0) return [];

        $stmt = $this->db->prepare(
            'SELECT c.id, c.user_id, c.course_id, c.date_obtained,
                    co.titre AS course_titre
             FROM certificates c
             INNER JOIN course co ON co.idCourse = c.course_id
             WHERE c.user_id = :userId
             ORDER BY c.date_obtained DESC'
        );
        $stmt->execute(['userId' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Retourne tous les certificats (vue admin).
     */
    public function listAll(): array
    {
        $stmt = $this->db->query(
            'SELECT c.id, c.user_id, c.course_id, c.date_obtained,
                    co.titre AS course_titre
             FROM certificates c
             INNER JOIN course co ON co.idCourse = c.course_id
             ORDER BY c.date_obtained DESC'
        );
        return $stmt->fetchAll();
    }

    // ──────────────────────────────────────────────────────────────
    //  GÉNÉRATION
    // ──────────────────────────────────────────────────────────────

    /**
     * Génère un certificat pour un utilisateur qui a complété un cours à 100%.
     * Idempotent : si le certificat existe déjà, retourne success=true sans doublon.
     *
     * @return array ['success' => bool, 'message' => string, 'already_existed' => bool]
     */
    public function generate(int $userId, int $courseId): array
    {
        if ($userId <= 0 || $courseId <= 0) {
            return ['success' => false, 'message' => 'Paramètres invalides.', 'already_existed' => false];
        }

        // Vérifie si le certificat existe déjà
        $existing = $this->getByUserAndCourse($userId, $courseId);
        if ($existing !== null) {
            return [
                'success'        => true,
                'message'        => 'Certificat déjà obtenu.',
                'already_existed' => true,
            ];
        }

        // INSERT avec prepared statement
        $stmt = $this->db->prepare(
            'INSERT INTO certificates (user_id, course_id, date_obtained)
             VALUES (:userId, :courseId, NOW())'
        );
        $stmt->execute(['userId' => $userId, 'courseId' => $courseId]);

        return [
            'success'        => true,
            'message'        => 'Certificat généré avec succès ! 🎓',
            'already_existed' => false,
        ];
    }
}
?>
