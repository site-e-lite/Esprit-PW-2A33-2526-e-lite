<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/CertificateController.php';

class ProgressController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Config::getConnexion();
    }

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

    public function computeProgress(int $userId, int $courseId): int
    {
        $stmtTotal = $this->db->prepare('SELECT COUNT(*) FROM lesson WHERE idCourse = :courseId');
        $stmtTotal->execute(['courseId' => $courseId]);
        $total = (int)$stmtTotal->fetchColumn();

        if ($total === 0) return 0;

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

    public function getProgress(int $userId, int $courseId): array
    {
        $this->touchLastAccessed($userId, $courseId);

        $percent   = $this->computeProgress($userId, $courseId);
        $lessons   = $this->getLessonsByCourse($courseId);
        $completed = $this->getCompletedLessonIds($userId, $courseId);

        $stmt = $this->db->prepare(
            'SELECT last_accessed FROM progress WHERE user_id = :userId AND course_id = :courseId'
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

    public function markLessonComplete(int $userId, int $lessonId): array
    {
        if ($userId <= 0 || $lessonId <= 0) {
            return ['success' => false, 'message' => 'Paramètres invalides.'];
        }

        $stmt = $this->db->prepare('SELECT idLesson, idCourse FROM lesson WHERE idLesson = :lessonId');
        $stmt->execute(['lessonId' => $lessonId]);
        $lesson = $stmt->fetch();

        if (!$lesson) {
            return ['success' => false, 'message' => 'Leçon introuvable.'];
        }

        $stmt = $this->db->prepare(
            'INSERT IGNORE INTO lesson_completion (user_id, idLesson) VALUES (:userId, :lessonId)'
        );
        $stmt->execute(['userId' => $userId, 'lessonId' => $lessonId]);

        $alreadyDone = $stmt->rowCount() === 0;
        $newPercent  = $this->computeProgress($userId, (int)$lesson['idCourse']);

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
