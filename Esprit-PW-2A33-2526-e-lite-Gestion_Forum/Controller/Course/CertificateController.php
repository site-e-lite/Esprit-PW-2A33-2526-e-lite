<?php
require_once __DIR__ . '/../../config.php';

class CertificateController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Config::getConnexion();
    }

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

    public function generate(int $userId, int $courseId): array
    {
        if ($userId <= 0 || $courseId <= 0) {
            return ['success' => false, 'message' => 'Paramètres invalides.', 'already_existed' => false];
        }

        $existing = $this->getByUserAndCourse($userId, $courseId);
        if ($existing !== null) {
            return [
                'success'         => true,
                'message'         => 'Certificat déjà obtenu.',
                'already_existed' => true,
            ];
        }

        $stmt = $this->db->prepare(
            'INSERT INTO certificates (user_id, course_id, date_obtained)
             VALUES (:userId, :courseId, NOW())'
        );
        $stmt->execute(['userId' => $userId, 'courseId' => $courseId]);

        return [
            'success'         => true,
            'message'         => 'Certificat généré avec succès ! 🎓',
            'already_existed' => false,
        ];
    }
}
?>
