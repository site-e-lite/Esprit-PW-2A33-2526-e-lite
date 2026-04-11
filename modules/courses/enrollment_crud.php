<?php
require_once __DIR__ . '/../../config/config.php';

class EnrollmentCrud
{
    /**
     * Inscrit un étudiant à un cours
     * @param int $userId
     * @param int $courseId
     * @param array $data (niveauInitial, objectifPersonnel, engagement, modeAcces)
     * @return int|false
     */
    public static function enroll($userId, $courseId, $data) {
        $pdo = Config::getConnexion();
        // TODO : Insérer dans enrollment avec dateInscription = NOW(), progression = 0
        // $stmt = $pdo->prepare("INSERT INTO enrollment (idUser, idCourse, niveauInitial, objectifPersonnel, engagement, modeAcces, dateInscription) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        // return $pdo->lastInsertId();
    }

    public static function getById($enrollmentId) {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("SELECT * FROM enrollment WHERE idEnrollment = ?");
        $stmt->execute([$enrollmentId]);
        return $stmt->fetch();
    }

    /**
     * Met à jour la progression
     * @param int $enrollmentId
     * @param int $progress (pourcentage)
     * @return bool
     */
    public static function updateProgress($enrollmentId, $progress) {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("UPDATE enrollment SET progression = ?, derniereActivite = NOW() WHERE idEnrollment = ?");
        return $stmt->execute([$progress, $enrollmentId]);
    }

    /**
     * Récupère les inscriptions d'un utilisateur
     * @param int $userId
     * @return array
     */
    public static function getByUser($userId) {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("
            SELECT e.*, c.titre, c.niveau, c.duree 
            FROM enrollment e 
            JOIN course c ON e.idCourse = c.idCourse 
            WHERE e.idUser = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Récupère les inscrits à un cours
     * @param int $courseId
     * @return array
     */
    public static function getByCourse($courseId) {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("
            SELECT e.*, u.nom, u.prenom, u.email 
            FROM enrollment e 
            JOIN user u ON e.idUser = u.idUser 
            WHERE e.idCourse = ?
        ");
        $stmt->execute([$courseId]);
        return $stmt->fetchAll();
    }
}
?>