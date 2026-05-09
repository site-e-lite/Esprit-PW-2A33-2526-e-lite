<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/CertificateController.php';

/**
 * ProgressController — suivi de progression basé sur la complétion réelle des leçons.
 *
 * Formule : progress% = (leçons complétées par l'user) / (total leçons du cours) * 100
 *
 * Tables utilisées :
 *   - lesson            : liste des leçons d'un cours
 *   - lesson_completion : une ligne par (user, lesson) quand complétée
 *   - progress          : last_accessed uniquement (progress_percent est calculé)
 */
class ProgressController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Config::getInstance()->getConnexion();
    }

    // ──────────────────────────────────────────────────────────────
    //  LESSONS
    // ──────────────────────────────────────────────────────────────

    /**
     * Retourne toutes les leçons d'un cours, triées par ordre.
     */
    public function getLessonsByCourse(int $courseId): array
    {
        $stmt = $this->db->prepare(
            'SELECT idLesson, idCourse, titre, ordre
             FROM lesson
             WHERE idCourse = :courseId
             ORDER BY ordre ASC'
        );
        $stmt->execute(['courseId' => $courseId]);
        return $stmt->fetchAll();
    }

    /**
     * Retourne les IDs des leçons déjà complétées par un utilisateur pour un cours.
     * Utilisé par la vue pour afficher l'état de chaque leçon.
     */
    public function getCompletedLessonIds(int $userId, int $courseId): array
    {
        $stmt = $this->db->prepare(
            'SELECT lc.idLesson
             FROM lesson_completion lc
             INNER JOIN lesson l ON l.idLesson = lc.idLesson
             WHERE lc.user_id = :userId AND l.idCourse = :courseId'
        );
        $stmt->execute(['userId' => $userId, 'courseId' => $courseId]);
        return array_column($stmt->fetchAll(), 'idLesson');
    }

    // ──────────────────────────────────────────────────────────────
    //  PROGRESS (calculé, non stocké)
    // ──────────────────────────────────────────────────────────────

    /**
     * Calcule la progression réelle : leçons complétées / total leçons * 100.
     * Retourne 0 si le cours n'a pas encore de leçons.
     */
    public function computeProgress(int $userId, int $courseId): int
    {
        // Total leçons du cours
        $stmtTotal = $this->db->prepare(
            'SELECT COUNT(*) FROM lesson WHERE idCourse = :courseId'
        );
        $stmtTotal->execute(['courseId' => $courseId]);
        $total = (int)$stmtTotal->fetchColumn();

        if ($total === 0) return 0;

        // Leçons complétées par cet utilisateur dans ce cours
        $stmtDone = $this->db->prepare(
            'SELECT COUNT(*)
             FROM lesson_completion lc
             INNER JOIN lesson l ON l.idLesson = lc.idLesson
             WHERE lc.user_id = :userId AND l.idCourse = :courseId'
        );
        $stmtDone->execute(['userId' => $userId, 'courseId' => $courseId]);
        $done = (int)$stmtDone->fetchColumn();

        return (int)round(($done / $total) * 100);
    }

    /**
     * Retourne les données de progression complètes pour la vue.
     */
    public function getProgress(int $userId, int $courseId): array
    {
        // Enregistre/met à jour last_accessed
        $this->touchLastAccessed($userId, $courseId);

        $percent  = $this->computeProgress($userId, $courseId);
        $lessons  = $this->getLessonsByCourse($courseId);
        $completed = $this->getCompletedLessonIds($userId, $courseId);

        // last_accessed
        $stmt = $this->db->prepare(
            'SELECT last_accessed FROM progress
             WHERE user_id = :userId AND course_id = :courseId'
        );
        $stmt->execute(['userId' => $userId, 'courseId' => $courseId]);
        $row = $stmt->fetch();

        return [
            'progress_percent' => $percent,
            'last_accessed'    => $row ? $row['last_accessed'] : null,
            'lessons'          => $lessons,
            'completed_ids'    => $completed,
            'total'            => count($lessons),
            'done'             => count($completed),
        ];
    }

    // ──────────────────────────────────────────────────────────────
    //  MARK LESSON COMPLETE
    // ──────────────────────────────────────────────────────────────

    /**
     * Marque une leçon spécifique comme complétée pour un utilisateur.
     * Idempotent : appeler deux fois ne crée pas de doublon (UNIQUE constraint).
     */
    public function markLessonComplete(int $userId, int $lessonId): array
    {
        if ($userId <= 0 || $lessonId <= 0) {
            return ['success' => false, 'message' => 'Paramètres invalides.'];
        }

        // Vérifie que la leçon existe et récupère son cours
        $stmt = $this->db->prepare(
            'SELECT idLesson, idCourse FROM lesson WHERE idLesson = :lessonId'
        );
        $stmt->execute(['lessonId' => $lessonId]);
        $lesson = $stmt->fetch();

        if (!$lesson) {
            return ['success' => false, 'message' => 'Leçon introuvable.'];
        }

        // INSERT IGNORE — la contrainte UNIQUE (user_id, idLesson) évite les doublons
        $stmt = $this->db->prepare(
            'INSERT IGNORE INTO lesson_completion (user_id, idLesson)
             VALUES (:userId, :lessonId)'
        );
        $stmt->execute(['userId' => $userId, 'lessonId' => $lessonId]);

        $alreadyDone = $stmt->rowCount() === 0;

        // Recalcule la progression après complétion
        $newPercent = $this->computeProgress($userId, (int)$lesson['idCourse']);

        // ── Génération automatique du certificat à 100% ──────────
        $certificateGenerated = false;
        if ($newPercent === 100 && !$alreadyDone) {
            $certController       = new CertificateController();
            $certResult           = $certController->generate($userId, (int)$lesson['idCourse']);
            $certificateGenerated = $certResult['success'] && !$certResult['already_existed'];
        }

        return [
            'success'               => true,
            'already_complete'      => $alreadyDone,
            'progress_percent'      => $newPercent,
            'certificate_generated' => $certificateGenerated,
            'message'               => $alreadyDone
                ? 'Leçon déjà complétée.'
                : ($newPercent === 100
                    ? 'Félicitations ! Cours complété à 100%.'
                    : 'Leçon marquée comme complétée.'),
        ];
    }

    // ──────────────────────────────────────────────────────────────
    //  INTERNAL
    // ──────────────────────────────────────────────────────────────

    /**
     * Crée ou met à jour last_accessed dans la table progress.
     * progress_percent n'est plus stocké ici — il est calculé à la volée.
     */
    private function touchLastAccessed(int $userId, int $courseId): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO progress (user_id, course_id, last_accessed)
             VALUES (:userId, :courseId, NOW())
             ON DUPLICATE KEY UPDATE last_accessed = NOW()'
        );
        $stmt->execute(['userId' => $userId, 'courseId' => $courseId]);
    }
}
?>
