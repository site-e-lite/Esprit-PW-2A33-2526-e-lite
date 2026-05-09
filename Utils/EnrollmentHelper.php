<?php
/**
 * EnrollmentHelper - Utility class for enrollment-related operations
 * Handles enrollment verification, progress tracking, and course access control
 */

require_once __DIR__ . '/../config.php';

class EnrollmentHelper
{
    /**
     * Check if a user can post in a forum for a course
     * Requirements: User must be either the teacher or an enrolled student
     * 
     * @param int $userId User ID
     * @param int $courseId Course ID
     * @return bool True if user can post in forum
     */
    public static function canPostInCourseForum(int $userId, int $courseId): bool
    {
        require_once __DIR__ . '/PermissionHelper.php';

        // Teachers can always post in their course forum
        if (PermissionHelper::isTeacherOfCourse($userId, $courseId)) {
            return true;
        }

        // Admins can post anywhere
        if (PermissionHelper::isAdmin($userId)) {
            return true;
        }

        // Students must be actively enrolled
        return PermissionHelper::isEnrolledInCourse($userId, $courseId, 'actif');
    }

    /**
     * Get user's enrollment progress in a course
     * @param int $userId User ID
     * @param int $courseId Course ID
     * @return array|null Enrollment data with progress info
     */
    public static function getUserCourseProgress(int $userId, int $courseId): array|null
    {
        try {
            $pdo = Config::getConnexion();
            $stmt = $pdo->prepare("
                SELECT 
                    e.*,
                    c.titre as courseTitre,
                    c.duree as courseDuree,
                    (SELECT COUNT(*) FROM forum WHERE idCourse = :courseId) as forumCount,
                    (SELECT COUNT(*) FROM post WHERE idForum IN (
                        SELECT IdForum FROM forum WHERE idCourse = :courseId
                    ) AND idUser = :userId) as userPostCount
                FROM enrollment e
                JOIN course c ON e.idCourse = c.idCourse
                WHERE e.idUser = :userId AND e.idCourse = :courseId
            ");
            $stmt->execute([
                ':userId' => $userId,
                ':courseId' => $courseId
            ]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            error_log("Error fetching user course progress: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update user's last activity timestamp in a course
     * @param int $userId User ID
     * @param int $courseId Course ID
     * @return bool True on success
     */
    public static function updateUserActivity(int $userId, int $courseId): bool
    {
        try {
            $pdo = Config::getConnexion();
            $stmt = $pdo->prepare("
                UPDATE enrollment 
                SET derniereActivite = CURRENT_TIMESTAMP
                WHERE idUser = :userId AND idCourse = :courseId
            ");
            return $stmt->execute([
                ':userId' => $userId,
                ':courseId' => $courseId
            ]);
        } catch (Exception $e) {
            error_log("Error updating user activity: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Increment study time for a user in a course
     * @param int $userId User ID
     * @param int $courseId Course ID
     * @param int $minutes Minutes to add
     * @return bool True on success
     */
    public static function addStudyTime(int $userId, int $courseId, int $minutes): bool
    {
        try {
            $pdo = Config::getConnexion();
            $stmt = $pdo->prepare("
                UPDATE enrollment 
                SET tempsTotalPasse = tempsTotalPasse + :minutes
                WHERE idUser = :userId AND idCourse = :courseId
            ");
            return $stmt->execute([
                ':userId' => $userId,
                ':courseId' => $courseId,
                ':minutes' => max(0, $minutes)
            ]);
        } catch (Exception $e) {
            error_log("Error adding study time: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify user can view course content
     * @param int $userId User ID
     * @param int $courseId Course ID
     * @return array|false Enrollment data if access granted, false otherwise
     */
    public static function verifyCourseAccess(int $userId, int $courseId): array|false
    {
        require_once __DIR__ . '/PermissionHelper.php';

        // Admin has full access
        if (PermissionHelper::isAdmin($userId)) {
            return ['access' => 'admin'];
        }

        // Check if teacher
        if (PermissionHelper::isTeacherOfCourse($userId, $courseId)) {
            return ['access' => 'teacher'];
        }

        // Check if enrolled student
        $enrollment = PermissionHelper::getEnrollmentDetails($userId, $courseId);
        if ($enrollment && $enrollment['statut'] === 'actif') {
            return $enrollment;
        }

        return false;
    }

    /**
     * Get all courses a user is enrolled in with enrollment data
     * @param int $userId User ID
     * @param string $status Optional: filter by enrollment status
     * @return array Array of courses with enrollment details
     */
    public static function getUserEnrolledCourses(int $userId, string $status = 'actif'): array
    {
        try {
            $pdo = Config::getConnexion();
            $query = "
                SELECT 
                    c.*,
                    e.progression,
                    e.statut as enrollmentStatus,
                    e.dateInscription,
                    e.progression as progress,
                    e.noteFinale,
                    e.certificatObtenu,
                    (SELECT COUNT(*) FROM forum WHERE idCourse = c.idCourse) as forumCount
                FROM enrollment e
                JOIN course c ON e.idCourse = c.idCourse
                WHERE e.idUser = :userId
            ";
            $params = [':userId' => $userId];

            if ($status !== null) {
                $query .= " AND e.statut = :status";
                $params[':status'] = $status;
            }

            $query .= " ORDER BY e.dateInscription DESC";

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error fetching user enrolled courses: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get course details with enrollment statistics
     * @param int $courseId Course ID
     * @return array|null Course data with statistics
     */
    public static function getCourseWithStats(int $courseId): array|null
    {
        try {
            $pdo = Config::getConnexion();
            $stmt = $pdo->prepare("
                SELECT 
                    c.*,
                    COUNT(DISTINCT e.idUser) as totalEnrolled,
                    SUM(CASE WHEN e.statut = 'actif' THEN 1 ELSE 0 END) as activeStudents,
                    SUM(CASE WHEN e.certificatObtenu = TRUE THEN 1 ELSE 0 END) as certificatesIssued,
                    AVG(e.progression) as avgProgress,
                    AVG(e.noteFinale) as avgFinalGrade,
                    (SELECT COUNT(*) FROM forum WHERE idCourse = :courseId) as forumCount,
                    (SELECT COUNT(*) FROM post WHERE idForum IN (
                        SELECT IdForum FROM forum WHERE idCourse = :courseId
                    )) as totalPosts
                FROM course c
                LEFT JOIN enrollment e ON c.idCourse = e.idCourse
                WHERE c.idCourse = :courseId
                GROUP BY c.idCourse
            ");
            $stmt->execute([':courseId' => $courseId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            error_log("Error fetching course statistics: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if user has completed a course (has certificate)
     * @param int $userId User ID
     * @param int $courseId Course ID
     * @return bool True if user has certificate for course
     */
    public static function hasCompletedCourse(int $userId, int $courseId): bool
    {
        try {
            $pdo = Config::getConnexion();
            $stmt = $pdo->prepare("
                SELECT 1 FROM enrollment 
                WHERE idUser = :userId AND idCourse = :courseId AND certificatObtenu = TRUE
            ");
            $stmt->execute([
                ':userId' => $userId,
                ':courseId' => $courseId
            ]);
            return (bool) $stmt->fetch();
        } catch (Exception $e) {
            error_log("Error checking course completion: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get forum discussion count for a course
     * @param int $courseId Course ID
     * @return int Number of active forum discussions
     */
    public static function getForumDiscussionCount(int $courseId): int
    {
        try {
            $pdo = Config::getConnexion();
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM forum WHERE idCourse = :courseId
            ");
            $stmt->execute([':courseId' => $courseId]);
            return (int) $stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("Error counting forum discussions: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get forum posts count for a course (all discussions combined)
     * @param int $courseId Course ID
     * @return int Total number of posts
     */
    public static function getForumPostCount(int $courseId): int
    {
        try {
            $pdo = Config::getConnexion();
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM post 
                WHERE idForum IN (
                    SELECT IdForum FROM forum WHERE idCourse = :courseId
                )
            ");
            $stmt->execute([':courseId' => $courseId]);
            return (int) $stmt->fetchColumn();
        } catch (Exception $e) {
            error_log("Error counting forum posts: " . $e->getMessage());
            return 0;
        }
    }
}
?>
