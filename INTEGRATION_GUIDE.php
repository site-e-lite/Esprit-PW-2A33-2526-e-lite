<?php
/**
 * ============================================================================
 * INTEGRATION GUIDE: Users, Courses, Forums, and Enrollments
 * ============================================================================
 * 
 * This document provides comprehensive examples for integrating the three
 * main modules: User Management, Course Management, and Forum Management.
 * 
 * ============================================================================
 */

/**
 * ============================================================================
 * 1. PERMISSION CHECKING - Using PermissionHelper
 * ============================================================================
 */

// Example 1.1: Check if user is a teacher
require_once __DIR__ . '/PermissionHelper.php';
$userId = $_SESSION['user_id'];
if (PermissionHelper::isTeacher($userId)) {
    echo "User is a teacher";
}

// Example 1.2: Check if teacher owns a course
if (PermissionHelper::isTeacherOfCourse($userId, $courseId)) {
    echo "This teacher owns this course";
}

// Example 1.3: Get all courses taught by a teacher
$teacherCourses = PermissionHelper::getTeacherCourses($userId);
foreach ($teacherCourses as $courseId) {
    // Process course
}

// Example 1.4: Get all students in a course
$students = PermissionHelper::getCourseStudents($courseId);

// Example 1.5: Check user role
$role = PermissionHelper::getUserRole($userId);
switch ($role) {
    case 'admin':
        // Admin specific logic
        break;
    case 'enseignant':
        // Teacher specific logic
        break;
    case 'etudiant':
        // Student specific logic
        break;
}

// Example 1.6: Assign teacher to course
PermissionHelper::assignTeacherToCourse($teacherId, $courseId);

// Example 1.7: Check admin status
if (PermissionHelper::isAdmin($userId)) {
    // Admin can do anything
}

/**
 * ============================================================================
 * 2. ENROLLMENT VERIFICATION - Using EnrollmentHelper
 * ============================================================================
 */

require_once __DIR__ . '/EnrollmentHelper.php';

// Example 2.1: Check if user can post in forum for a course
if (EnrollmentHelper::canPostInCourseForum($userId, $courseId)) {
    echo "User can post in this course's forum";
} else {
    echo "User must be enrolled or be the teacher";
}

// Example 2.2: Get user's enrollment in a course
$enrollment = PermissionHelper::getEnrollmentDetails($userId, $courseId);
if ($enrollment) {
    echo "Progress: " . $enrollment['progression'] . "%";
    echo "Status: " . $enrollment['statut'];
    echo "Time spent: " . $enrollment['tempsTotalPasse'] . " minutes";
}

// Example 2.3: Get all courses user is enrolled in
$enrolledCourses = EnrollmentHelper::getUserEnrolledCourses($userId);
foreach ($enrolledCourses as $course) {
    echo $course['titre'] . " - " . $course['progression'] . "% complete";
}

// Example 2.4: Update user activity
EnrollmentHelper::updateUserActivity($userId, $courseId);

// Example 2.5: Add study time
EnrollmentHelper::addStudyTime($userId, $courseId, 30); // 30 minutes

// Example 2.6: Check if user completed a course
if (EnrollmentHelper::hasCompletedCourse($userId, $courseId)) {
    echo "User has certificate for this course";
}

// Example 2.7: Get course statistics
$courseStats = EnrollmentHelper::getCourseWithStats($courseId);
echo "Total enrolled: " . $courseStats['totalEnrolled'];
echo "Average progress: " . $courseStats['avgProgress'] . "%";
echo "Forum discussions: " . $courseStats['forumCount'];

// Example 2.8: Verify course access
$access = EnrollmentHelper::verifyCourseAccess($userId, $courseId);
if ($access === false) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

/**
 * ============================================================================
 * 3. FORUM INTEGRATION - Using ForumController
 * ============================================================================
 */

require_once __DIR__ . '/../Controller/Forum/ForumController.php';

$forumController = new ForumController();

// Example 3.1: Get forums accessible to user (with permission checks)
$accessibleForums = $forumController->getAccessibleForums($userId);
foreach ($accessibleForums as $forum) {
    echo $forum['titre'] . " (" . $forum['postCount'] . " posts)";
}

// Example 3.2: Get forums for a specific course
$forumsByCourse = $forumController->getForumsByCourse($courseId, $userId);

// Example 3.3: Check if user can post in forum
if ($forumController->canPostInForum($userId, $forumId)) {
    // Show post form
} else {
    echo "You must be enrolled in this course to post";
}

// Example 3.4: Check forum access
if ($forumController->canAccessCourseForum($userId, $courseId)) {
    // Show forum
}

/**
 * ============================================================================
 * 4. COURSE CONTROLLER PERMISSION CHECKS
 * ============================================================================
 */

require_once __DIR__ . '/../Controller/CourseController.php';

$courseController = new CourseController();

// Example 4.1: Get courses for current user (respects roles)
$userCourses = $courseController->listForUser($userId);

// Example 4.2: Get teacher's own courses
$teacherCourses = $courseController->getTeacherCourses($userId);

// Example 4.3: Check edit permission
if ($courseController->canEditCourse($userId, $courseId)) {
    // Show edit form
}

// Example 4.4: Check delete permission
if ($courseController->canDeleteCourse($userId, $courseId)) {
    // Show delete button
}

// Example 4.5: Get course with permission check
$course = $courseController->getByIdWithAccess($courseId, $userId);
if ($course === null) {
    echo "You don't have access to this course";
}

// Example 4.6: Add new course (automatically assigns to teacher)
$result = $courseController->add([
    'titre' => 'Advanced PHP',
    'description' => 'Deep dive into PHP',
    'niveau' => 'avance',
    'duree' => 40,
    'statut' => 'brouillon',
    'langue' => 'fr',
    'prix' => 99.99,
    'image' => 'https://example.com/image.jpg'
]);

// Example 4.7: Update course (with permission check)
$result = $courseController->update($courseId, [
    'titre' => 'Updated Course Title',
    'description' => '...',
    'niveau' => 'intermediaire',
    'duree' => 50,
    'statut' => 'publie',
    'langue' => 'fr',
    'prix' => 79.99
]);

/**
 * ============================================================================
 * 5. DASHBOARD - Using DashboardHelper
 * ============================================================================
 */

require_once __DIR__ . '/DashboardHelper.php';

// Example 5.1: Get complete user dashboard
$dashboard = DashboardHelper::getUserDashboard($userId);

// Student dashboard
if ($dashboard['role'] === 'etudiant') {
    $enrolledCourses = $dashboard['modules']['courses']['courses'];
    $forumPosts = $dashboard['modules']['forum']['totalPosts'];
    $avgProgress = $dashboard['modules']['statistics']['avgCourseProgress'];
}

// Teacher dashboard
if ($dashboard['role'] === 'enseignant') {
    $myCourses = $dashboard['modules']['courses']['courses'];
    $studentList = $dashboard['modules']['students']['list'];
    $recentPostsInForum = $dashboard['modules']['forum']['recentPosts'];
}

// Admin dashboard
if ($dashboard['role'] === 'admin') {
    $totalUsers = $dashboard['modules']['users'];
    $topCourses = $dashboard['modules']['courses']['topCourses'];
    $completionRate = $dashboard['modules']['statistics']['completionRate'];
    $engagementRate = $dashboard['modules']['statistics']['engagementRate'];
}

// Example 5.2: Get platform summary
$summary = DashboardHelper::getPlatformSummary();
echo "Total students: " . $summary['users']['students'];
echo "Total courses: " . $summary['courses']['total'];
echo "Active enrollments: " . $summary['enrollments']['active'];

/**
 * ============================================================================
 * 6. WORKFLOW EXAMPLES
 * ============================================================================
 */

/**
 * Workflow 6.1: Teacher Creating a Course
 * 1. Check user is teacher
 * 2. Create course (auto-assigns to teacher)
 * 3. Assign course to forum
 * 4. Create initial forum discussion
 */
function teacherCreateCourse($courseData) {
    require_once __DIR__ . '/PermissionHelper.php';
    require_once __DIR__ . '/../Controller/CourseController.php';
    require_once __DIR__ . '/../Controller/Forum/ForumController.php';

    $userId = $_SESSION['user_id'];
    
    // Step 1: Verify user is teacher
    if (!PermissionHelper::isTeacher($userId)) {
        return ['error' => 'Only teachers can create courses'];
    }

    // Step 2: Create course
    $courseController = new CourseController();
    $result = $courseController->add($courseData);
    if (!$result['success']) {
        return $result;
    }

    // Course is now auto-assigned to this teacher

    return ['success' => true];
}

/**
 * Workflow 6.2: Student Accessing Course Forum
 * 1. Check user is enrolled
 * 2. Get course forums
 * 3. Get accessible forums only
 * 4. Record activity
 */
function studentAccessCourseForum($courseId) {
    require_once __DIR__ . '/PermissionHelper.php';
    require_once __DIR__ . '/EnrollmentHelper.php';
    require_once __DIR__ . '/../Controller/Forum/ForumController.php';

    $userId = $_SESSION['user_id'];

    // Step 1: Verify enrollment
    if (!PermissionHelper::isEnrolledInCourse($userId, $courseId)) {
        return ['error' => 'You are not enrolled in this course'];
    }

    // Step 2: Get forums
    $forumController = new ForumController();
    $forums = $forumController->getForumsByCourse($courseId, $userId);

    // Step 3: Record activity
    EnrollmentHelper::updateUserActivity($userId, $courseId);

    return ['forums' => $forums];
}

/**
 * Workflow 6.3: Admin Dashboard Review
 * 1. Verify admin role
 * 2. Get comprehensive statistics
 * 3. Identify trends and issues
 */
function adminReviewDashboard() {
    require_once __DIR__ . '/PermissionHelper.php';
    require_once __DIR__ . '/DashboardHelper.php';

    $userId = $_SESSION['user_id'];

    if (!PermissionHelper::isAdmin($userId)) {
        return ['error' => 'Admin access required'];
    }

    $dashboard = DashboardHelper::getUserDashboard($userId);
    $summary = DashboardHelper::getPlatformSummary();

    return [
        'dashboard' => $dashboard,
        'summary' => $summary
    ];
}

/**
 * ============================================================================
 * 7. CRITICAL INTEGRATION POINTS
 * ============================================================================
 */

/**
 * When user posts in forum:
 * 1. Verify enrollment: EnrollmentHelper::canPostInCourseForum()
 * 2. Update activity: EnrollmentHelper::updateUserActivity()
 * 3. Record post in database
 */
function createForumPost($courseId, $forumId, $content) {
    require_once __DIR__ . '/EnrollmentHelper.php';

    $userId = $_SESSION['user_id'];

    if (!EnrollmentHelper::canPostInCourseForum($userId, $courseId)) {
        return ['error' => 'You cannot post in this forum'];
    }

    EnrollmentHelper::updateUserActivity($userId, $courseId);
    
    // Create post in database
    // ...
}

/**
 * When teacher views their courses:
 * 1. Get courses: CourseController::getTeacherCourses()
 * 2. Load statistics: EnrollmentHelper::getCourseWithStats()
 * 3. Get student feedback/activity
 */
function getTeacherCourseOverview($teacherId) {
    require_once __DIR__ . '/../Controller/CourseController.php';
    require_once __DIR__ . '/EnrollmentHelper.php';

    $courseController = new CourseController();
    $courses = $courseController->getTeacherCourses($teacherId);

    foreach ($courses as $courseId) {
        $stats = EnrollmentHelper::getCourseWithStats($courseId);
        // Display stats to teacher
    }
}

/**
 * When a student enrolls:
 * 1. Create enrollment record
 * 2. Grant access to forums
 * 3. Update dashboard
 */
function enrollStudent($userId, $courseId) {
    // Create enrollment record in enrollment table
    // User now has forum access via EnrollmentHelper::canPostInCourseForum()
}

/**
 * ============================================================================
 * 8. DATABASE QUERIES REFERENCE
 * ============================================================================
 */

// Get all active students in a teacher's courses
$sql = "
    SELECT DISTINCT u.* 
    FROM user u
    JOIN enrollment e ON u.idUser = e.idUser
    JOIN teacher_course tc ON e.idCourse = tc.idCourse
    WHERE tc.idUser = :teacherId AND e.statut = 'actif'
";

// Get forum participation by student
$sql = "
    SELECT u.*, COUNT(p.idPost) as postCount
    FROM user u
    LEFT JOIN post p ON u.idUser = p.idUser
    JOIN forum f ON p.idForum = f.idForum
    WHERE f.idCourse = :courseId
    GROUP BY u.idUser
";

// Get course completion metrics
$sql = "
    SELECT 
        c.titre,
        COUNT(e.idEnrollment) as totalEnrolled,
        SUM(CASE WHEN e.certificatObtenu THEN 1 ELSE 0 END) as completed,
        AVG(e.progression) as avgProgress
    FROM course c
    LEFT JOIN enrollment e ON c.idCourse = e.idCourse
    GROUP BY c.idCourse
";

// Get most active forums
$sql = "
    SELECT f.*, COUNT(p.idPost) as postCount
    FROM forum f
    LEFT JOIN post p ON f.idForum = p.idForum
    GROUP BY f.idForum
    ORDER BY postCount DESC
";

/**
 * ============================================================================
 * SUMMARY OF KEY FILES AND CLASSES
 * ============================================================================
 * 
 * PermissionHelper (/Utils/PermissionHelper.php):
 *   - isTeacherOfCourse() - Check teacher ownership
 *   - isEnrolledInCourse() - Check student enrollment
 *   - getTeacherCourses() - Get teacher's courses
 *   - getCourseStudents() - Get enrolled students
 *   - isAdmin() / isTeacher() / isStudent() - Role checks
 *   - assignTeacherToCourse() - Assign teacher
 * 
 * EnrollmentHelper (/Utils/EnrollmentHelper.php):
 *   - canPostInCourseForum() - Forum post permission
 *   - getUserCourseProgress() - Student progress
 *   - updateUserActivity() - Track activity
 *   - addStudyTime() - Record study time
 *   - getUserEnrolledCourses() - Student's courses
 *   - getCourseWithStats() - Course statistics
 * 
 * DashboardHelper (/Utils/DashboardHelper.php):
 *   - getUserDashboard() - Role-based dashboard
 *   - getStudentDashboard() - Student view
 *   - getTeacherDashboard() - Teacher view
 *   - getAdminDashboard() - Admin view
 *   - getPlatformSummary() - Platform statistics
 * 
 * CourseController (/Controller/CourseController.php):
 *   - listForUser() - List with permission check
 *   - getTeacherCourses() - Teacher's courses
 *   - canEditCourse() - Edit permission
 *   - canDeleteCourse() - Delete permission
 *   - getByIdWithAccess() - Get with access check
 * 
 * ForumController (/Controller/Forum/ForumController.php):
 *   - canAccessCourseForum() - Forum access check
 *   - canPostInForum() - Post permission
 *   - getAccessibleForums() - User's forums
 *   - getForumsByCourse() - Course forums
 */

?>
