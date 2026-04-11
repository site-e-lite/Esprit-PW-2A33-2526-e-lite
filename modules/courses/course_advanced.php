<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/CourseCrud.php';
require_once __DIR__ . '/EnrollmentCrud.php';

class CourseAdvanced
{
    /**
     * FONCTIONNALITÉ AVANCÉE 1 : Recommandation IA de cours (basée niveau et historique)
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public static function recommendCourses($userId, $limit = 5) {
        $pdo = Config::getConnexion();
        // TODO : Récupérer le niveau le plus fréquent des cours suivis
        // TODO : Sélectionner des cours du même niveau non encore suivis
        // Exemple simple :
        $stmt = $pdo->prepare("
            SELECT c.* FROM course c
            WHERE c.niveau = (
                SELECT niveauInitial FROM enrollment WHERE idUser = ? ORDER BY dateInscription DESC LIMIT 1
            )
            AND c.idCourse NOT IN (SELECT idCourse FROM enrollment WHERE idUser = ?)
            AND c.statut = 'publié'
            LIMIT ?
        ");
        $stmt->execute([$userId, $userId, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * FONCTIONNALITÉ AVANCÉE 2 : Suivi intelligent de progression (détection stagnation)
     * @param int $userId
     * @return array Liste des cours avec alerte d'inactivité
     */
    public static function getInactiveEnrollments($userId, $daysThreshold = 7) {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("
            SELECT e.*, c.titre
            FROM enrollment e
            JOIN course c ON e.idCourse = c.idCourse
            WHERE e.idUser = ?
              AND e.statut = 'actif'
              AND e.derniereActivite < DATE_SUB(NOW(), INTERVAL ? DAY)
        ");
        $stmt->execute([$userId, $daysThreshold]);
        return $stmt->fetchAll();
    }

    /**
     * Calcule le temps total passé sur un cours (mise à jour périodique)
     * @param int $enrollmentId
     * @param int $secondsToAdd
     * @return bool
     */
    public static function addTimeSpent($enrollmentId, $secondsToAdd) {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("UPDATE enrollment SET tempsTotalPasse = tempsTotalPasse + ? WHERE idEnrollment = ?");
        return $stmt->execute([$secondsToAdd, $enrollmentId]);
    }
}
?>