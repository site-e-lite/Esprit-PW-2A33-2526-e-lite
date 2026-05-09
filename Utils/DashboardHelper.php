<?php
/**
 * Dashboard Statistics Helper
 * Provides comprehensive statistics for users, courses, and forums
 */

require_once __DIR__ . '/../config.php';

class DashboardHelper
{
    /**
     * Get comprehensive dashboard statistics for logged-in user
     * @param int $userId User ID
     * @return array Dashboard data
     */
    public static function getUserDashboard(int $userId): array
    {
        require_once __DIR__ . '/PermissionHelper.php';
        require_once __DIR__ . '/EnrollmentHelper.php';

        $userRole = PermissionHelper::getUserRole($userId);
        $pdo = Config::getConnexion();

        $dashboard = [
            'userId' => $userId,
            'role' => $userRole,
            'timestamp' => date('Y-m-d H:i:s'),
            'modules' => []
        ];

        switch ($userRole) {
            case 'etudiant':
                $dashboard['modules'] = self::getStudentDashboard($userId);
                break;
            case 'enseignant':
                $dashboard['modules'] = self::getTeacherDashboard($userId);
                break;
            case 'admin':
                $dashboard['modules'] = self::getAdminDashboard($userId);
                break;
        }

        return $dashboard;
    }

    /**
     * Get student dashboard with their enrolled courses and participation
     * @param int $userId Student ID
     * @return array Student-specific dashboard data
     */
    private static function getStudentDashboard(int $userId): array
    {
        require_once __DIR__ . '/EnrollmentHelper.php';

        $pdo = Config::getConnexion();

        try {
            // Enrolled courses with progress
            $enrolledCourses = EnrollmentHelper::getUserEnrolledCourses($userId, 'actif');

            // Forum participation
            $stmtForumPosts = $pdo->prepare("
                SELECT COUNT(*) as totalPosts FROM post 
                WHERE idUser = :userId
            ");
            $stmtForumPosts->execute([':userId' => $userId]);
            $forumPostCount = $stmtForumPosts->fetchColumn();

            // Recent forum activity
            $stmtRecentActivity = $pdo->prepare("
                SELECT p.*, f.titre as forumTitle, f.idCourse 
                FROM post p
                JOIN forum f ON p.idForum = f.idForum
                WHERE p.idUser = :userId
                ORDER BY p.datePost DESC
                LIMIT 5
            ");
            $stmtRecentActivity->execute([':userId' => $userId]);
            $recentActivity = $stmtRecentActivity->fetchAll(PDO::FETCH_ASSOC);

            // Course progress summary
            $progressSummary = [];
            foreach ($enrolledCourses as $course) {
                $progressSummary[] = [
                    'courseId' => $course['idCourse'],
                    'title' => $course['titre'],
                    'progress' => $course['progression'],
                    'status' => $course['enrollmentStatus'],
                    'enrolled' => $course['dateInscription'],
                    'forums' => EnrollmentHelper::getForumDiscussionCount($course['idCourse']),
                    'forumPosts' => EnrollmentHelper::getForumPostCount($course['idCourse'])
                ];
            }

            return [
                'courses' => [
                    'total' => count($enrolledCourses),
                    'active' => count(array_filter($enrolledCourses, fn($c) => $c['enrollmentStatus'] === 'actif')),
                    'completed' => count(array_filter($enrolledCourses, fn($c) => $c['certificatObtenu'])),
                    'courses' => $progressSummary
                ],
                'forum' => [
                    'totalPosts' => $forumPostCount,
                    'recentActivity' => $recentActivity
                ],
                'statistics' => [
                    'avgCourseProgress' => self::calculateAverageProgress($enrolledCourses),
                    'certificatesEarned' => count(array_filter($enrolledCourses, fn($c) => $c['certificatObtenu'])),
                    'hoursStudied' => array_sum(array_map(fn($c) => $c['tempsTotalPasse'] ?? 0, $enrolledCourses))
                ]
            ];
        } catch (Exception $e) {
            error_log("Error fetching student dashboard: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get teacher dashboard with their courses and student statistics
     * @param int $teacherId Teacher ID
     * @return array Teacher-specific dashboard data
     */
    private static function getTeacherDashboard(int $teacherId): array
    {
        require_once __DIR__ . '/PermissionHelper.php';
        require_once __DIR__ . '/EnrollmentHelper.php';

        $pdo = Config::getConnexion();

        try {
            // Get all courses taught by this teacher
            $teacherCourses = PermissionHelper::getTeacherCourses($teacherId);

            if (empty($teacherCourses)) {
                return [
                    'courses' => ['total' => 0, 'courses' => []],
                    'students' => ['total' => 0, 'active' => 0],
                    'forum' => ['discussions' => 0, 'posts' => 0]
                ];
            }

            $coursesList = [];
            $totalStudents = 0;
            $totalForumDiscussions = 0;
            $totalForumPosts = 0;

            foreach ($teacherCourses as $courseId) {
                $courseStats = EnrollmentHelper::getCourseWithStats($courseId);
                if ($courseStats) {
                    $coursesList[] = $courseStats;
                    $totalStudents += $courseStats['activeStudents'] ?? 0;
                    $totalForumDiscussions += $courseStats['forumCount'] ?? 0;
                    $totalForumPosts += $courseStats['totalPosts'] ?? 0;
                }
            }

            // Get student list with progress
            $placeholders = implode(',', array_fill(0, count($teacherCourses), '?'));
            $stmtStudents = $pdo->prepare("
                SELECT DISTINCT 
                    u.idUser, u.nom, u.prenom, u.email,
                    COUNT(DISTINCT e.idCourse) as enrolledCourses,
                    AVG(e.progression) as avgProgress,
                    SUM(e.tempsTotalPasse) as totalTimeSpent
                FROM enrollment e
                JOIN user u ON e.idUser = u.idUser
                WHERE e.idCourse IN ($placeholders) AND e.statut = 'actif'
                GROUP BY u.idUser
                ORDER BY u.nom, u.prenom
            ");
            $stmtStudents->execute($teacherCourses);
            $students = $stmtStudents->fetchAll(PDO::FETCH_ASSOC);

            // Recent forum posts in teacher's courses
            $stmtRecentPosts = $pdo->prepare("
                SELECT p.*, f.titre as forumTitle, u.nom, u.prenom, c.titre as courseTitre
                FROM post p
                JOIN forum f ON p.idForum = f.idForum
                JOIN course c ON f.idCourse = c.idCourse
                JOIN user u ON p.idUser = u.idUser
                WHERE f.idCourse IN ($placeholders)
                ORDER BY p.datePost DESC
                LIMIT 10
            ");
            $stmtRecentPosts->execute($teacherCourses);
            $recentPosts = $stmtRecentPosts->fetchAll(PDO::FETCH_ASSOC);

            return [
                'courses' => [
                    'total' => count($teacherCourses),
                    'courses' => $coursesList
                ],
                'students' => [
                    'total' => count($students),
                    'active' => $totalStudents,
                    'list' => $students
                ],
                'forum' => [
                    'discussions' => $totalForumDiscussions,
                    'posts' => $totalForumPosts,
                    'recentPosts' => $recentPosts
                ],
                'statistics' => [
                    'avgStudentProgress' => count($students) > 0 ? round(array_sum(array_map(fn($s) => $s['avgProgress'] ?? 0, $students)) / count($students), 2) : 0,
                    'totalForumEngagement' => $totalForumPosts,
                    'totalStudentHours' => array_sum(array_map(fn($s) => $s['totalTimeSpent'] ?? 0, $students))
                ]
            ];
        } catch (Exception $e) {
            error_log("Error fetching teacher dashboard: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get admin dashboard with platform-wide statistics
     * @param int $adminId Admin ID
     * @return array Admin-specific dashboard data
     */
    private static function getAdminDashboard(int $adminId): array
    {
        $pdo = Config::getConnexion();

        try {
            // User statistics
            $userStats = $pdo->query("
                SELECT r.nom as role, COUNT(u.idUser) as count
                FROM user u
                JOIN role r ON u.idRole = r.idRole
                WHERE u.statut = 'actif'
                GROUP BY r.nom
            ")->fetchAll(PDO::FETCH_KEY_PAIR);

            // Course statistics
            $totalCourses = $pdo->query("SELECT COUNT(*) FROM course")->fetchColumn();
            $publishedCourses = $pdo->query("SELECT COUNT(*) FROM course WHERE statut = 'publie'")->fetchColumn();

            // Enrollment statistics
            $totalEnrollments = $pdo->query("SELECT COUNT(*) FROM enrollment")->fetchColumn();
            $activeEnrollments = $pdo->query("SELECT COUNT(*) FROM enrollment WHERE statut = 'actif'")->fetchColumn();
            $certificatesIssued = $pdo->query("SELECT COUNT(*) FROM enrollment WHERE certificatObtenu = TRUE")->fetchColumn();

            // Forum statistics
            $totalForums = $pdo->query("SELECT COUNT(*) FROM forum")->fetchColumn();
            $totalPosts = $pdo->query("SELECT COUNT(*) FROM post")->fetchColumn();
            $totalRatings = $pdo->query("SELECT COUNT(*) FROM forum_rating")->fetchColumn();
            $avgRating = $pdo->query("SELECT ROUND(AVG(note), 2) FROM forum_rating")->fetchColumn() ?? 0;

            // Top courses by enrollment
            $topCourses = $pdo->query("
                SELECT c.idCourse, c.titre, COUNT(e.idEnrollment) as studentCount
                FROM course c
                LEFT JOIN enrollment e ON c.idCourse = e.idCourse
                GROUP BY c.idCourse
                ORDER BY studentCount DESC
                LIMIT 5
            ")->fetchAll(PDO::FETCH_ASSOC);

            // Most active forums
            $activeForum = $pdo->query("
                SELECT f.idForum, f.titre, COUNT(p.idPost) as postCount, c.titre as courseTitre
                FROM forum f
                LEFT JOIN post p ON f.idForum = p.idForum
                LEFT JOIN course c ON f.idCourse = c.idCourse
                GROUP BY f.idForum
                ORDER BY postCount DESC
                LIMIT 5
            ")->fetchAll(PDO::FETCH_ASSOC);

            // Recent activity
            $recentActivity = $pdo->query("
                SELECT 'post' as type, p.datePost as date, u.nom, u.prenom, p.contenu, f.titre as title
                FROM post p
                JOIN forum f ON p.idForum = f.idForum
                JOIN user u ON p.idUser = u.idUser
                UNION ALL
                SELECT 'enrollment' as type, e.dateInscription as date, u.nom, u.prenom, c.titre, c.titre as title
                FROM enrollment e
                JOIN course c ON e.idCourse = c.idCourse
                JOIN user u ON e.idUser = u.idUser
                ORDER BY date DESC
                LIMIT 10
            ")->fetchAll(PDO::FETCH_ASSOC);

            return [
                'users' => $userStats,
                'courses' => [
                    'total' => $totalCourses,
                    'published' => $publishedCourses,
                    'topCourses' => $topCourses
                ],
                'enrollments' => [
                    'total' => $totalEnrollments,
                    'active' => $activeEnrollments,
                    'certificatesIssued' => $certificatesIssued
                ],
                'forum' => [
                    'total' => $totalForums,
                    'posts' => $totalPosts,
                    'ratings' => $totalRatings,
                    'avgRating' => $avgRating,
                    'topForums' => $activeForum
                ],
                'statistics' => [
                    'completionRate' => $totalEnrollments > 0 ? round(($certificatesIssued / $totalEnrollments) * 100, 2) : 0,
                    'engagementRate' => $totalEnrollments > 0 ? round(($totalPosts / $totalEnrollments) * 100, 2) : 0
                ],
                'activity' => $recentActivity
            ];
        } catch (Exception $e) {
            error_log("Error fetching admin dashboard: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Calculate average progress from array of courses
     * @param array $courses Array of course data
     * @return float Average progress percentage
     */
    private static function calculateAverageProgress(array $courses): float
    {
        if (empty($courses)) {
            return 0;
        }
        $total = array_sum(array_map(fn($c) => $c['progression'] ?? 0, $courses));
        return round($total / count($courses), 2);
    }

    /**
     * Get module-specific statistics summary
     * @return array Summary of all modules
     */
    public static function getPlatformSummary(): array
    {
        $pdo = Config::getConnexion();

        try {
            return [
                'users' => [
                    'total' => $pdo->query("SELECT COUNT(*) FROM user WHERE statut = 'actif'")->fetchColumn(),
                    'teachers' => $pdo->query("SELECT COUNT(u.idUser) FROM user u JOIN role r ON u.idRole = r.idRole WHERE r.nom = 'enseignant' AND u.statut = 'actif'")->fetchColumn(),
                    'students' => $pdo->query("SELECT COUNT(u.idUser) FROM user u JOIN role r ON u.idRole = r.idRole WHERE r.nom = 'etudiant' AND u.statut = 'actif'")->fetchColumn()
                ],
                'courses' => [
                    'total' => $pdo->query("SELECT COUNT(*) FROM course")->fetchColumn(),
                    'published' => $pdo->query("SELECT COUNT(*) FROM course WHERE statut = 'publie'")->fetchColumn()
                ],
                'enrollments' => [
                    'total' => $pdo->query("SELECT COUNT(*) FROM enrollment")->fetchColumn(),
                    'active' => $pdo->query("SELECT COUNT(*) FROM enrollment WHERE statut = 'actif'")->fetchColumn()
                ],
                'forum' => [
                    'discussions' => $pdo->query("SELECT COUNT(*) FROM forum")->fetchColumn(),
                    'posts' => $pdo->query("SELECT COUNT(*) FROM post")->fetchColumn()
                ]
            ];
        } catch (Exception $e) {
            error_log("Error fetching platform summary: " . $e->getMessage());
            return [];
        }
    }
}
?>
