<?php
/**
 * Integrated Dashboard - Shows statistics from Users, Courses, and Forums modules
 * Includes role-based views: Student, Teacher, and Admin dashboards
 */

session_start();
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../Utils/DashboardHelper.php';
require_once __DIR__ . '/../../../Utils/PermissionHelper.php';

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: /gestioncours/View/User/auth/login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$dashboardData = DashboardHelper::getUserDashboard($userId);
$userRole = $dashboardData['role'];

include __DIR__ . '/../../includes/header.php';
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1>Dashboard - <?php echo ucfirst($userRole); ?></h1>
        <p class="timestamp">Last updated: <?php echo $dashboardData['timestamp']; ?></p>
    </div>

    <?php if ($userRole === 'etudiant'): ?>
        <!-- STUDENT DASHBOARD -->
        <div class="dashboard-grid">
            <!-- Courses Section -->
            <section class="dashboard-section courses-section">
                <h2>My Courses</h2>
                <div class="stats-cards">
                    <div class="stat-card">
                        <h3><?php echo $dashboardData['modules']['courses']['total']; ?></h3>
                        <p>Total Enrolled</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $dashboardData['modules']['courses']['active']; ?></h3>
                        <p>Active Courses</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $dashboardData['modules']['courses']['completed']; ?></h3>
                        <p>Completed</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $dashboardData['modules']['statistics']['certificatesEarned']; ?></h3>
                        <p>Certificates</p>
                    </div>
                </div>

                <!-- Course Progress -->
                <div class="course-progress-list">
                    <?php foreach ($dashboardData['modules']['courses']['courses'] as $course): ?>
                        <div class="course-item">
                            <div class="course-header">
                                <h4><?php echo htmlspecialchars($course['title']); ?></h4>
                                <span class="status-badge <?php echo strtolower($course['status']); ?>">
                                    <?php echo ucfirst($course['status']); ?>
                                </span>
                            </div>
                            <div class="course-progress-bar">
                                <div class="progress-fill" style="width: <?php echo $course['progress']; ?>%"></div>
                            </div>
                            <div class="course-meta">
                                <span><?php echo $course['progress']; ?>% Complete</span>
                                <span><?php echo $course['forums']; ?> Discussions • <?php echo $course['forumPosts']; ?> Posts</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Forum Activity Section -->
            <section class="dashboard-section forum-section">
                <h2>Forum Activity</h2>
                <div class="forum-stats">
                    <div class="stat-card">
                        <h3><?php echo $dashboardData['modules']['forum']['totalPosts']; ?></h3>
                        <p>Your Posts</p>
                    </div>
                </div>

                <h3>Recent Posts</h3>
                <div class="forum-posts-list">
                    <?php if (empty($dashboardData['modules']['forum']['recentActivity'])): ?>
                        <p class="empty-state">No forum activity yet. Start a discussion!</p>
                    <?php else: ?>
                        <?php foreach ($dashboardData['modules']['forum']['recentActivity'] as $post): ?>
                            <div class="forum-post-item">
                                <h4><?php echo htmlspecialchars($post['forumTitle']); ?></h4>
                                <p><?php echo substr(htmlspecialchars($post['contenu']), 0, 150) . '...'; ?></p>
                                <small><?php echo date('M d, Y H:i', strtotime($post['datePost'])); ?></small>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Study Statistics -->
            <section class="dashboard-section stats-section">
                <h2>Study Statistics</h2>
                <div class="stats-cards">
                    <div class="stat-card">
                        <h3><?php echo round($dashboardData['modules']['statistics']['avgCourseProgress']); ?>%</h3>
                        <p>Average Progress</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo floor($dashboardData['modules']['statistics']['hoursStudied'] / 60); ?></h3>
                        <p>Hours Studied</p>
                    </div>
                </div>
            </section>
        </div>

    <?php elseif ($userRole === 'enseignant'): ?>
        <!-- TEACHER DASHBOARD -->
        <div class="dashboard-grid">
            <!-- My Courses -->
            <section class="dashboard-section courses-section">
                <h2>My Courses</h2>
                <div class="stats-cards">
                    <div class="stat-card">
                        <h3><?php echo $dashboardData['modules']['courses']['total']; ?></h3>
                        <p>Total Courses</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $dashboardData['modules']['students']['active']; ?></h3>
                        <p>Active Students</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $dashboardData['modules']['forum']['discussions']; ?></h3>
                        <p>Forum Discussions</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $dashboardData['modules']['forum']['posts']; ?></h3>
                        <p>Total Posts</p>
                    </div>
                </div>

                <!-- Course List -->
                <div class="course-management-list">
                    <?php foreach ($dashboardData['modules']['courses']['courses'] as $course): ?>
                        <div class="course-management-item">
                            <h4><?php echo htmlspecialchars($course['titre']); ?></h4>
                            <div class="course-stats">
                                <span><?php echo $course['totalEnrolled']; ?> students</span>
                                <span><?php echo $course['activeStudents']; ?> active</span>
                                <span><?php echo round($course['avgProgress']); ?>% avg progress</span>
                            </div>
                            <div class="course-actions">
                                <a href="/gestioncours/View/BackOffice/course/edit.php?id=<?php echo $course['idCourse']; ?>" class="btn-small">Edit</a>
                                <a href="/gestioncours/View/Forum/FrontOffice/index.php?courseId=<?php echo $course['idCourse']; ?>" class="btn-small">Forum</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Students Overview -->
            <section class="dashboard-section students-section">
                <h2>Student Overview</h2>
                <div class="stats-cards">
                    <div class="stat-card">
                        <h3><?php echo $dashboardData['modules']['students']['total']; ?></h3>
                        <p>Total Students</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo round($dashboardData['modules']['statistics']['avgStudentProgress']); ?>%</h3>
                        <p>Avg Progress</p>
                    </div>
                </div>

                <h3>Top Students</h3>
                <div class="students-list">
                    <?php 
                    $topStudents = array_slice($dashboardData['modules']['students']['list'], 0, 5);
                    foreach ($topStudents as $student): 
                    ?>
                        <div class="student-item">
                            <div class="student-info">
                                <span class="student-name"><?php echo htmlspecialchars($student['nom'] . ' ' . $student['prenom']); ?></span>
                                <span class="student-email"><?php echo htmlspecialchars($student['email']); ?></span>
                            </div>
                            <div class="student-progress">
                                <span><?php echo round($student['avgProgress']); ?>% progress</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Forum Engagement -->
            <section class="dashboard-section forum-section">
                <h2>Recent Forum Activity</h2>
                <div class="forum-posts-list">
                    <?php if (empty($dashboardData['modules']['forum']['recentPosts'])): ?>
                        <p class="empty-state">No forum activity yet</p>
                    <?php else: ?>
                        <?php foreach (array_slice($dashboardData['modules']['forum']['recentPosts'], 0, 5) as $post): ?>
                            <div class="forum-post-item">
                                <h5><?php echo htmlspecialchars($post['courseTitre']); ?> - <?php echo htmlspecialchars($post['forumTitle']); ?></h5>
                                <p><?php echo htmlspecialchars($post['nom'] . ' ' . $post['prenom']); ?></p>
                                <small><?php echo date('M d, Y H:i', strtotime($post['datePost'])); ?></small>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </div>

    <?php elseif ($userRole === 'admin'): ?>
        <!-- ADMIN DASHBOARD -->
        <div class="dashboard-grid admin-grid">
            <!-- Platform Overview -->
            <section class="dashboard-section overview-section">
                <h2>Platform Overview</h2>
                <div class="stats-cards large">
                    <div class="stat-card">
                        <h3><?php echo $dashboardData['modules']['users']['etudiant'] ?? 0; ?></h3>
                        <p>Students</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $dashboardData['modules']['users']['enseignant'] ?? 0; ?></h3>
                        <p>Teachers</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $dashboardData['modules']['courses']['total']; ?></h3>
                        <p>Courses</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $dashboardData['modules']['enrollments']['total']; ?></h3>
                        <p>Enrollments</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $dashboardData['modules']['forum']['discussions']; ?></h3>
                        <p>Forum Topics</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $dashboardData['modules']['forum']['posts']; ?></h3>
                        <p>Forum Posts</p>
                    </div>
                </div>
            </section>

            <!-- Course Statistics -->
            <section class="dashboard-section courses-section">
                <h2>Top Courses by Enrollment</h2>
                <div class="courses-ranking">
                    <?php foreach ($dashboardData['modules']['courses']['topCourses'] as $course): ?>
                        <div class="ranking-item">
                            <span class="course-title"><?php echo htmlspecialchars($course['titre']); ?></span>
                            <span class="student-count"><?php echo $course['studentCount']; ?> students</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Enrollment Analytics -->
            <section class="dashboard-section enrollments-section">
                <h2>Enrollment Analytics</h2>
                <div class="stats-cards">
                    <div class="stat-card">
                        <h3><?php echo $dashboardData['modules']['enrollments']['active']; ?></h3>
                        <p>Active Enrollments</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $dashboardData['modules']['enrollments']['certificatesIssued']; ?></h3>
                        <p>Certificates Issued</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $dashboardData['modules']['statistics']['completionRate']; ?>%</h3>
                        <p>Completion Rate</p>
                    </div>
                </div>
            </section>

            <!-- Forum Analytics -->
            <section class="dashboard-section forum-analytics">
                <h2>Forum Analytics</h2>
                <div class="stats-cards">
                    <div class="stat-card">
                        <h3><?php echo $dashboardData['modules']['forum']['ratings']; ?></h3>
                        <p>Total Ratings</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $dashboardData['modules']['forum']['avgRating']; ?>/5</h3>
                        <p>Average Rating</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $dashboardData['modules']['statistics']['engagementRate']; ?>%</h3>
                        <p>Engagement Rate</p>
                    </div>
                </div>

                <h3>Top Forums</h3>
                <div class="forums-ranking">
                    <?php foreach ($dashboardData['modules']['forum']['topForums'] as $forum): ?>
                        <div class="ranking-item">
                            <span class="forum-title">
                                <?php echo htmlspecialchars($forum['courseTitre']); ?> - <?php echo htmlspecialchars($forum['titre']); ?>
                            </span>
                            <span class="post-count"><?php echo $forum['postCount']; ?> posts</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Recent Activity -->
            <section class="dashboard-section activity-section">
                <h2>Recent Platform Activity</h2>
                <div class="activity-feed">
                    <?php foreach ($dashboardData['modules']['activity'] as $activity): ?>
                        <div class="activity-item">
                            <span class="activity-type"><?php echo strtoupper($activity['type']); ?></span>
                            <span class="activity-content">
                                <?php echo htmlspecialchars($activity['nom'] . ' ' . $activity['prenom']); ?> 
                                (<?php echo $activity['type'] === 'post' ? 'posted in' : 'enrolled in'; ?>)
                                <?php echo htmlspecialchars($activity['title']); ?>
                            </span>
                            <span class="activity-date"><?php echo date('M d, Y H:i', strtotime($activity['date'])); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>

    <?php endif; ?>
</div>

<style>
.dashboard-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.dashboard-header {
    margin-bottom: 30px;
}

.dashboard-header h1 {
    font-size: 2.5rem;
    margin-bottom: 10px;
}

.timestamp {
    color: #666;
    font-size: 0.9rem;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.admin-grid {
    grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
}

.dashboard-section {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.dashboard-section h2 {
    font-size: 1.5rem;
    margin-bottom: 15px;
    border-bottom: 2px solid #007bff;
    padding-bottom: 10px;
}

.dashboard-section h3 {
    font-size: 1.1rem;
    margin-top: 15px;
    margin-bottom: 10px;
}

.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.stats-cards.large {
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
}

.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 8px;
    text-align: center;
}

.stat-card h3 {
    font-size: 2rem;
    margin: 0 0 10px 0;
    color: white;
}

.stat-card p {
    margin: 0;
    font-size: 0.9rem;
    opacity: 0.9;
}

.course-item,
.course-management-item,
.student-item,
.forum-post-item,
.ranking-item,
.activity-item {
    padding: 15px;
    border-left: 4px solid #007bff;
    margin-bottom: 12px;
    background: #f8f9fa;
    border-radius: 4px;
}

.course-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.course-header h4 {
    margin: 0;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: bold;
}

.status-badge.actif {
    background: #d4edda;
    color: #155724;
}

.status-badge.inactif {
    background: #f8d7da;
    color: #721c24;
}

.course-progress-bar {
    height: 8px;
    background: #ddd;
    border-radius: 4px;
    overflow: hidden;
    margin: 10px 0;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
}

.course-meta {
    display: flex;
    justify-content: space-between;
    font-size: 0.85rem;
    color: #666;
}

.empty-state {
    text-align: center;
    color: #999;
    padding: 30px;
    font-style: italic;
}

.course-management-item {
    border-left: 4px solid #28a745;
}

.course-stats {
    display: flex;
    gap: 15px;
    margin: 10px 0;
    font-size: 0.9rem;
}

.course-stats span {
    background: white;
    padding: 5px 10px;
    border-radius: 4px;
}

.course-actions {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}

.btn-small {
    padding: 6px 12px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    font-size: 0.85rem;
}

.btn-small:hover {
    background: #0056b3;
}

.student-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.student-info {
    display: flex;
    flex-direction: column;
}

.student-name {
    font-weight: bold;
}

.student-email {
    font-size: 0.85rem;
    color: #666;
}

.forum-post-item h4,
.forum-post-item h5 {
    margin: 0 0 5px 0;
    color: #007bff;
}

.forum-post-item p {
    margin: 5px 0;
    font-size: 0.9rem;
}

.forum-post-item small {
    color: #999;
}

.ranking-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    background: white;
    border-radius: 4px;
    margin-bottom: 8px;
    border-left: 3px solid #ffc107;
}

.course-title,
.forum-title {
    font-weight: 500;
    flex: 1;
}

.student-count,
.post-count {
    background: #007bff;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.85rem;
}

.activity-feed {
    max-height: 400px;
    overflow-y: auto;
}

.activity-item {
    display: grid;
    grid-template-columns: 80px 1fr 150px;
    gap: 10px;
    align-items: start;
    padding: 12px;
    border-left: 3px solid #17a2b8;
}

.activity-type {
    background: #17a2b8;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: bold;
}

.activity-date {
    text-align: right;
    color: #999;
    font-size: 0.85rem;
}
</style>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
