<?php
/**
 * PermissionHelper.php
 * Unified permission and access control for the e_lite application.
 * Tables: user, role, enrollment, teacher_course, forum, course
 */
require_once __DIR__ . '/../config.php';

class PermissionHelper
{
    // ── INTERNAL CACHE ───────────────────────────────────────────
    private static array $roleCache = [];

    // ── SESSION ──────────────────────────────────────────────────

    public static function getAuthenticatedUserId(): int|false
    {
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : false;
    }

    // ── ROLE DETECTION ───────────────────────────────────────────

    public static function getUserRole(int $userId): string|false
    {
        if (isset(self::$roleCache[$userId])) {
            return self::$roleCache[$userId];
        }
        try {
            $stmt = Config::getInstance()->getConnexion()->prepare(
                'SELECT r.nom FROM user u JOIN role r ON u.idRole = r.idRole WHERE u.idUser = :id'
            );
            $stmt->execute([':id' => $userId]);
            $row = $stmt->fetch();
            $role = $row ? strtolower(trim($row['nom'])) : false;
            self::$roleCache[$userId] = $role;
            return $role;
        } catch (Exception $e) {
            error_log('PermissionHelper::getUserRole — ' . $e->getMessage());
            return false;
        }
    }

    public static function isAdmin(int $userId): bool
    {
        return self::getUserRole($userId) === 'admin';
    }

    public static function isTeacher(int $userId): bool
    {
        return self::getUserRole($userId) === 'enseignant';
    }

    public static function isStudent(int $userId): bool
    {
        return self::getUserRole($userId) === 'etudiant';
    }

    // ── COURSE PERMISSIONS ───────────────────────────────────────

    /**
     * Check if a user can access a course.
     * - Admin: always yes
     * - Teacher of the course: yes
     * - Student enrolled (actif): yes
     * - Published course: yes for everyone
     */
    public static function canAccessCourse(int $userId, int $courseId): bool
    {
        if (self::isAdmin($userId))                          return true;
        if (self::isTeacherOfCourse($userId, $courseId))     return true;
        if (self::isEnrolled($userId, $courseId, 'actif'))   return true;

        // Published courses are visible to all
        try {
            $stmt = Config::getInstance()->getConnexion()->prepare(
                'SELECT statut FROM course WHERE idCourse = :id'
            );
            $stmt->execute([':id' => $courseId]);
            $row = $stmt->fetch();
            return $row && $row['statut'] === 'publie';
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Check if a user is enrolled in a course.
     * @param string|null $status  If provided, also checks enrollment status.
     */
    public static function isEnrolled(int $userId, int $courseId, ?string $status = null): bool
    {
        try {
            $sql    = 'SELECT 1 FROM enrollment WHERE idUser = :u AND idCourse = :c';
            $params = [':u' => $userId, ':c' => $courseId];
            if ($status !== null) {
                $sql .= ' AND statut = :s';
                $params[':s'] = $status;
            }
            $stmt = Config::getInstance()->getConnexion()->prepare($sql);
            $stmt->execute($params);
            return (bool)$stmt->fetch();
        } catch (Exception $e) {
            error_log('PermissionHelper::isEnrolled — ' . $e->getMessage());
            return false;
        }
    }

    /** Backward-compatible alias */
    public static function isEnrolledInCourse(int $userId, int $courseId, ?string $status = null): bool
    {
        return self::isEnrolled($userId, $courseId, $status);
    }

    /**
     * Check if a user is the teacher of a specific course.
     */
    public static function isTeacherOfCourse(int $userId, int $courseId): bool
    {
        try {
            $stmt = Config::getInstance()->getConnexion()->prepare(
                'SELECT 1 FROM teacher_course WHERE idUser = :u AND idCourse = :c'
            );
            $stmt->execute([':u' => $userId, ':c' => $courseId]);
            return (bool)$stmt->fetch();
        } catch (Exception $e) {
            error_log('PermissionHelper::isTeacherOfCourse — ' . $e->getMessage());
            return false;
        }
    }

    // ── FORUM PERMISSIONS ────────────────────────────────────────

    /**
     * Check if a user can post in a forum.
     * Resolves the course linked to the forum, then checks enrollment/role.
     */
    public static function canPostInForum(int $userId, int $forumId): bool
    {
        $courseId = self::getCourseIdForForum($forumId);
        if ($courseId === null) return false;

        if (self::isAdmin($userId))                          return true;
        if (self::isTeacherOfCourse($userId, $courseId))     return true;
        return self::isEnrolled($userId, $courseId, 'actif');
    }

    /**
     * Check if a user can moderate a forum (delete posts, manage topics).
     * Only teachers of the linked course and admins.
     */
    public static function canModerateForum(int $userId, int $forumId): bool
    {
        $courseId = self::getCourseIdForForum($forumId);
        if ($courseId === null) return self::isAdmin($userId);

        if (self::isAdmin($userId))                      return true;
        return self::isTeacherOfCourse($userId, $courseId);
    }

    // ── ACCESSIBLE RESOURCES ─────────────────────────────────────

    /**
     * Get all courses a user can see.
     * - Admin: all courses
     * - Teacher: courses they teach
     * - Student: published courses + enrolled courses
     */
    public static function getAccessibleCourses(int $userId): array
    {
        try {
            $db = Config::getInstance()->getConnexion();

            if (self::isAdmin($userId)) {
                return $db->query(
                    'SELECT * FROM course ORDER BY idCourse DESC'
                )->fetchAll();
            }

            if (self::isTeacher($userId)) {
                $ids = self::getTeacherCourses($userId);
                if (empty($ids)) return [];
                $ph = implode(',', array_fill(0, count($ids), '?'));
                $stmt = $db->prepare("SELECT * FROM course WHERE idCourse IN ($ph) ORDER BY idCourse DESC");
                $stmt->execute($ids);
                return $stmt->fetchAll();
            }

            // Student: published + enrolled
            $stmt = $db->prepare(
                'SELECT DISTINCT c.*
                 FROM course c
                 LEFT JOIN enrollment e ON e.idCourse = c.idCourse AND e.idUser = :u AND e.statut = "actif"
                 WHERE c.statut = "publie" OR e.idEnrollment IS NOT NULL
                 ORDER BY c.idCourse DESC'
            );
            $stmt->execute([':u' => $userId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log('PermissionHelper::getAccessibleCourses — ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all forums a user can see.
     * - Admin: all forums
     * - Teacher: forums of their courses
     * - Student: forums of courses they are enrolled in (actif)
     */
    public static function getAccessibleForums(int $userId): array
    {
        try {
            $db = Config::getInstance()->getConnexion();

            if (self::isAdmin($userId)) {
                return $db->query(
                    'SELECT f.*, c.titre AS courseTitre,
                            COUNT(DISTINCT p.idPost) AS postCount
                     FROM forum f
                     LEFT JOIN course c ON c.idCourse = f.idCourse
                     LEFT JOIN post p ON p.idForum = f.idForum
                     GROUP BY f.idForum
                     ORDER BY f.dateCreation DESC'
                )->fetchAll();
            }

            if (self::isTeacher($userId)) {
                $ids = self::getTeacherCourses($userId);
                if (empty($ids)) return [];
                $ph = implode(',', array_fill(0, count($ids), '?'));
                $stmt = $db->prepare(
                    "SELECT f.*, c.titre AS courseTitre,
                            COUNT(DISTINCT p.idPost) AS postCount
                     FROM forum f
                     LEFT JOIN course c ON c.idCourse = f.idCourse
                     LEFT JOIN post p ON p.idForum = f.idForum
                     WHERE f.idCourse IN ($ph)
                     GROUP BY f.idForum
                     ORDER BY f.dateCreation DESC"
                );
                $stmt->execute($ids);
                return $stmt->fetchAll();
            }

            // Student: only forums of enrolled courses
            $stmt = $db->prepare(
                'SELECT f.*, c.titre AS courseTitre,
                        COUNT(DISTINCT p.idPost) AS postCount
                 FROM forum f
                 JOIN course c ON c.idCourse = f.idCourse
                 JOIN enrollment e ON e.idCourse = f.idCourse
                 LEFT JOIN post p ON p.idForum = f.idForum
                 WHERE e.idUser = :u AND e.statut = "actif"
                 GROUP BY f.idForum
                 ORDER BY f.dateCreation DESC'
            );
            $stmt->execute([':u' => $userId]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log('PermissionHelper::getAccessibleForums — ' . $e->getMessage());
            return [];
        }
    }

    // ── TEACHER HELPERS ──────────────────────────────────────────

    public static function getTeacherCourses(int $userId): array
    {
        try {
            $stmt = Config::getInstance()->getConnexion()->prepare(
                'SELECT idCourse FROM teacher_course WHERE idUser = :u ORDER BY dateAssigned DESC'
            );
            $stmt->execute([':u' => $userId]);
            return array_column($stmt->fetchAll(), 'idCourse');
        } catch (Exception $e) {
            error_log('PermissionHelper::getTeacherCourses — ' . $e->getMessage());
            return [];
        }
    }

    public static function assignTeacherToCourse(int $userId, int $courseId): bool
    {
        try {
            $stmt = Config::getInstance()->getConnexion()->prepare(
                'INSERT INTO teacher_course (idUser, idCourse)
                 VALUES (:u, :c)
                 ON DUPLICATE KEY UPDATE dateAssigned = NOW()'
            );
            return $stmt->execute([':u' => $userId, ':c' => $courseId]);
        } catch (Exception $e) {
            error_log('PermissionHelper::assignTeacherToCourse — ' . $e->getMessage());
            return false;
        }
    }

    // ── ENROLLMENT DETAILS ───────────────────────────────────────

    public static function getEnrollmentDetails(int $userId, int $courseId): array|false
    {
        try {
            $stmt = Config::getInstance()->getConnexion()->prepare(
                'SELECT * FROM enrollment WHERE idUser = :u AND idCourse = :c'
            );
            $stmt->execute([':u' => $userId, ':c' => $courseId]);
            return $stmt->fetch() ?: false;
        } catch (Exception $e) {
            return false;
        }
    }

    // ── PRIVATE HELPERS ──────────────────────────────────────────

    private static function getCourseIdForForum(int $forumId): ?int
    {
        try {
            $stmt = Config::getInstance()->getConnexion()->prepare(
                'SELECT idCourse FROM forum WHERE idForum = :id'
            );
            $stmt->execute([':id' => $forumId]);
            $row = $stmt->fetch();
            return $row && $row['idCourse'] ? (int)$row['idCourse'] : null;
        } catch (Exception $e) {
            return null;
        }
    }
}
