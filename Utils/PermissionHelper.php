<?php
/**
 * PermissionHelper - Utility class for permission and access control checks
 * Handles teacher-course ownership, enrollment verification, and role-based access
 */

require_once __DIR__ . '/../config.php';

class PermissionHelper
{
    /**
     * Check if a user is a teacher of a specific course
     * @param int $userId User ID
     * @param int $courseId Course ID
     * @return bool True if user is the teacher of the course
     */
    public static function isTeacherOfCourse(int $userId, int $courseId): bool
    {
        try {
            $pdo = Config::getConnexion();
            $stmt = $pdo->prepare("
                SELECT 1 FROM teacher_course 
                WHERE idUser = :userId AND idCourse = :courseId
            ");
            $stmt->execute([
                ':userId' => $userId,
                ':courseId' => $courseId
            ]);
            return (bool) $stmt->fetch();
        } catch (Exception $e) {
            error_log("Error checking teacher course: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if a user is enrolled in a course
     * @param int $userId User ID
     * @param int $courseId Course ID
     * @param string $status Optional: only check for specific enrollment status (e.g., 'actif')
     * @return bool True if user is enrolled
     */
    public static function isEnrolledInCourse(int $userId, int $courseId, string $status = null): bool
    {
        try {
            $pdo = Config::getConnexion();
            $query = "SELECT 1 FROM enrollment WHERE idUser = :userId AND idCourse = :courseId";
            $params = [':userId' => $userId, ':courseId' => $courseId];

            if ($status !== null) {
                $query .= " AND statut = :status";
                $params[':status'] = $status;
            }

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            return (bool) $stmt->fetch();
        } catch (Exception $e) {
            error_log("Error checking enrollment: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all courses taught by a specific teacher
     * @param int $userId Teacher ID
     * @return array Array of course IDs
     */
    public static function getTeacherCourses(int $userId): array
    {
        try {
            $pdo = Config::getConnexion();
            $stmt = $pdo->prepare("
                SELECT idCourse FROM teacher_course 
                WHERE idUser = :userId 
                ORDER BY dateAssigned DESC
            ");
            $stmt->execute([':userId' => $userId]);
            return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'idCourse');
        } catch (Exception $e) {
            error_log("Error fetching teacher courses: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all students enrolled in a course
     * @param int $courseId Course ID
     * @param string $status Optional: filter by enrollment status
     * @return array Array of student IDs
     */
    public static function getCourseStudents(int $courseId, string $status = 'actif'): array
    {
        try {
            $pdo = Config::getConnexion();
            $query = "
                SELECT DISTINCT idUser FROM enrollment 
                WHERE idCourse = :courseId
            ";
            $params = [':courseId' => $courseId];

            if ($status !== null) {
                $query .= " AND statut = :status";
                $params[':status'] = $status;
            }

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'idUser');
        } catch (Exception $e) {
            error_log("Error fetching course students: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if a user is an admin
     * @param int $userId User ID
     * @return bool True if user has admin role
     */
    public static function isAdmin(int $userId): bool
    {
        try {
            $pdo = Config::getConnexion();
            $stmt = $pdo->prepare("
                SELECT 1 FROM user u 
                JOIN role r ON u.idRole = r.idRole 
                WHERE u.idUser = :userId AND r.nom = 'admin'
            ");
            $stmt->execute([':userId' => $userId]);
            return (bool) $stmt->fetch();
        } catch (Exception $e) {
            error_log("Error checking admin status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if a user is a teacher
     * @param int $userId User ID
     * @return bool True if user has teacher role
     */
    public static function isTeacher(int $userId): bool
    {
        try {
            $pdo = Config::getConnexion();
            $stmt = $pdo->prepare("
                SELECT 1 FROM user u 
                JOIN role r ON u.idRole = r.idRole 
                WHERE u.idUser = :userId AND r.nom = 'enseignant'
            ");
            $stmt->execute([':userId' => $userId]);
            return (bool) $stmt->fetch();
        } catch (Exception $e) {
            error_log("Error checking teacher status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if a user is a student
     * @param int $userId User ID
     * @return bool True if user has student role
     */
    public static function isStudent(int $userId): bool
    {
        try {
            $pdo = Config::getConnexion();
            $stmt = $pdo->prepare("
                SELECT 1 FROM user u 
                JOIN role r ON u.idRole = r.idRole 
                WHERE u.idUser = :userId AND r.nom = 'etudiant'
            ");
            $stmt->execute([':userId' => $userId]);
            return (bool) $stmt->fetch();
        } catch (Exception $e) {
            error_log("Error checking student status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Assign a teacher to a course
     * @param int $userId Teacher ID
     * @param int $courseId Course ID
     * @return bool True on success
     */
    public static function assignTeacherToCourse(int $userId, int $courseId): bool
    {
        try {
            // Check if user is a teacher
            if (!self::isTeacher($userId)) {
                error_log("User {$userId} is not a teacher");
                return false;
            }

            $pdo = Config::getConnexion();
            $stmt = $pdo->prepare("
                INSERT INTO teacher_course (idUser, idCourse) 
                VALUES (:userId, :courseId)
                ON DUPLICATE KEY UPDATE dateAssigned = CURRENT_TIMESTAMP
            ");
            return $stmt->execute([
                ':userId' => $userId,
                ':courseId' => $courseId
            ]);
        } catch (Exception $e) {
            error_log("Error assigning teacher to course: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove a teacher from a course
     * @param int $userId Teacher ID
     * @param int $courseId Course ID
     * @return bool True on success
     */
    public static function removeTeacherFromCourse(int $userId, int $courseId): bool
    {
        try {
            $pdo = Config::getConnexion();
            $stmt = $pdo->prepare("
                DELETE FROM teacher_course 
                WHERE idUser = :userId AND idCourse = :courseId
            ");
            return $stmt->execute([
                ':userId' => $userId,
                ':courseId' => $courseId
            ]);
        } catch (Exception $e) {
            error_log("Error removing teacher from course: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get enrollment details for a user-course combination
     * @param int $userId User ID
     * @param int $courseId Course ID
     * @return array|false Enrollment data or false if not found
     */
    public static function getEnrollmentDetails(int $userId, int $courseId): array|false
    {
        try {
            $pdo = Config::getConnexion();
            $stmt = $pdo->prepare("
                SELECT * FROM enrollment 
                WHERE idUser = :userId AND idCourse = :courseId
            ");
            $stmt->execute([
                ':userId' => $userId,
                ':courseId' => $courseId
            ]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
        } catch (Exception $e) {
            error_log("Error fetching enrollment details: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify session and get user ID
     * @return int|false User ID if authenticated, false otherwise
     */
    public static function getAuthenticatedUserId(): int|false
    {
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : false;
    }

    /**
     * Get user role
     * @param int $userId User ID
     * @return string|false Role name or false if not found
     */
    public static function getUserRole(int $userId): string|false
    {
        try {
            $pdo = Config::getConnexion();
            $stmt = $pdo->prepare("
                SELECT r.nom FROM user u 
                JOIN role r ON u.idRole = r.idRole 
                WHERE u.idUser = :userId
            ");
            $stmt->execute([':userId' => $userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['nom'] : false;
        } catch (Exception $e) {
            error_log("Error fetching user role: " . $e->getMessage());
            return false;
        }
    }
}
?>
