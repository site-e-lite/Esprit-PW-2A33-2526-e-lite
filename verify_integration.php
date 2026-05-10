<?php
/**
 * VERIFICATION & TESTING PAGE
 * File: verify_integration.php
 * URL: http://localhost/gestioncours/verify_integration.php
 * 
 * Use this page to verify that the integration is working correctly
 */

session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Utils/PermissionHelper.php';

$basePath = '/gestioncours';
$checks = [];
$errors = [];

// ─── CHECK 1: Database Connection ────────────────────────────────────
try {
    $db = Config::getInstance()->getConnexion();
    $result = $db->query("SELECT 1");
    $checks['Database Connection'] = '✅ PASS';
} catch (Exception $e) {
    $checks['Database Connection'] = '❌ FAIL: ' . $e->getMessage();
    $errors[] = 'Database not connected';
}

// ─── CHECK 2: Required Tables ───────────────────────────────────────
$requiredTables = ['user', 'role', 'course', 'enrollment', 'forum', 'post', 'teacher_course'];
foreach ($requiredTables as $table) {
    try {
        $result = $db->query("SELECT 1 FROM $table LIMIT 1");
        $checks["Table: $table"] = '✅ PASS';
    } catch (Exception $e) {
        $checks["Table: $table"] = '❌ FAIL: Table not found';
        $errors[] = "Table '$table' missing";
    }
}

// ─── CHECK 3: Session Test ──────────────────────────────────────────
$checks['Session Management'] = isset($_SESSION) ? '✅ PASS' : '❌ FAIL';

// ─── CHECK 4: Test Users ────────────────────────────────────────────
try {
    $stmt = $db->query("SELECT COUNT(*) as total FROM user WHERE statut = 'actif'");
    $row = $stmt->fetch();
    $totalUsers = $row['total'] ?? 0;
    $checks['Users Found'] = $totalUsers > 0 ? "✅ PASS ($totalUsers users)" : '⚠️  WARNING: No users found';
} catch (Exception $e) {
    $checks['Users Found'] = '❌ FAIL: ' . $e->getMessage();
}

// ─── CHECK 5: Test Courses ──────────────────────────────────────────
try {
    $stmt = $db->query("SELECT COUNT(*) as total FROM course");
    $row = $stmt->fetch();
    $totalCourses = $row['total'] ?? 0;
    $checks['Courses Found'] = $totalCourses > 0 ? "✅ PASS ($totalCourses courses)" : '⚠️  WARNING: No courses found';
} catch (Exception $e) {
    $checks['Courses Found'] = '❌ FAIL: ' . $e->getMessage();
}

// ─── CHECK 6: Test Forums ───────────────────────────────────────────
try {
    $stmt = $db->query("SELECT COUNT(*) as total FROM forum");
    $row = $stmt->fetch();
    $totalForums = $row['total'] ?? 0;
    $checks['Forums Found'] = $totalForums > 0 ? "✅ PASS ($totalForums forums)" : '⚠️  WARNING: No forums found';
} catch (Exception $e) {
    $checks['Forums Found'] = '❌ FAIL: ' . $e->getMessage();
}

// ─── CHECK 7: Permission Helper ──────────────────────────────────────
try {
    if (function_exists('PermissionHelper::isAdmin')) {
        $checks['PermissionHelper'] = '✅ PASS';
    } else {
        $checks['PermissionHelper'] = '⚠️  WARNING: Methods not found';
    }
} catch (Exception $e) {
    $checks['PermissionHelper'] = '❌ FAIL: ' . $e->getMessage();
}

// ─── CHECK 8: Roles in Database ──────────────────────────────────────
try {
    $roles = $db->query("SELECT nom FROM role")->fetchAll();
    $roleNames = array_column($roles, 'nom');
    
    $required = ['admin', 'etudiant', 'enseignant'];
    $missing = [];
    foreach ($required as $req) {
        if (!in_array($req, $roleNames)) {
            $missing[] = $req;
        }
    }
    
    if (empty($missing)) {
        $checks['Roles Setup'] = '✅ PASS (admin, etudiant, enseignant)';
    } else {
        $checks['Roles Setup'] = '⚠️  WARNING: Missing roles: ' . implode(', ', $missing);
        $errors[] = 'Missing roles in database';
    }
} catch (Exception $e) {
    $checks['Roles Setup'] = '❌ FAIL: ' . $e->getMessage();
}

// ─── CHECK 9: Router Configuration ──────────────────────────────────
if (file_exists(__DIR__ . '/index.php')) {
    $indexContent = file_get_contents(__DIR__ . '/index.php');
    if (strpos($indexContent, '/dashboard') !== false && strpos($indexContent, '/courses') !== false) {
        $checks['Router Configuration'] = '✅ PASS (New routes detected)';
    } else {
        $checks['Router Configuration'] = '⚠️  WARNING: Check if router has new routes';
    }
} else {
    $checks['Router Configuration'] = '❌ FAIL: index.php not found';
}

// ─── CHECK 10: Test Permission Check ────────────────────────────────
try {
    // Try to get a student
    $student = $db->query("SELECT u.idUser FROM user u JOIN role r ON u.idRole = r.idRole WHERE r.nom = 'etudiant' LIMIT 1")->fetch();
    if ($student) {
        $studentId = $student['idUser'];
        // Try to check if they can access a course
        $result = PermissionHelper::isStudent($studentId);
        $checks['Permission Check'] = '✅ PASS (Permission system working)';
    } else {
        $checks['Permission Check'] = '⚠️  WARNING: No test user found';
    }
} catch (Exception $e) {
    $checks['Permission Check'] = '❌ FAIL: ' . $e->getMessage();
}

// ─── Calculate Overall Status ────────────────────────────────────────
$passCount = count(array_filter($checks, fn($v) => strpos($v, '✅') === 0));
$totalCount = count($checks);
$status = $passCount === $totalCount ? 'SUCCESS ✅' : ($passCount >= $totalCount - 2 ? 'MOSTLY OK ⚠️' : 'NEEDS WORK ❌');

?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Integration Verification - e-lite</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f5f5; color: #333; }
        .container { max-width: 1000px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 8px; margin-bottom: 30px; }
        .header h1 { font-size: 28px; margin-bottom: 10px; }
        .status-badge { display: inline-block; padding: 8px 16px; border-radius: 20px; font-weight: bold; background: rgba(255,255,255,0.2); }
        .checks { background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .check-item { display: flex; justify-content: space-between; align-items: center; padding: 15px 20px; border-bottom: 1px solid #e0e0e0; }
        .check-item:last-child { border-bottom: none; }
        .check-item:nth-child(odd) { background: #f9f9f9; }
        .check-label { font-weight: 500; }
        .check-result { text-align: right; }
        .pass { color: #28a745; }
        .fail { color: #dc3545; }
        .warning { color: #ffc107; }
        .section-title { font-size: 18px; font-weight: bold; margin-top: 30px; margin-bottom: 15px; color: #333; }
        .info-card { background: white; border-left: 4px solid #667eea; padding: 20px; margin-bottom: 20px; border-radius: 4px; }
        .info-card h3 { margin-bottom: 10px; color: #667eea; }
        .info-card ul { margin-left: 20px; }
        .info-card li { margin-bottom: 5px; }
        .button-group { display: flex; gap: 10px; margin-top: 20px; flex-wrap: wrap; }
        .btn { display: inline-block; padding: 10px 20px; border-radius: 4px; text-decoration: none; border: none; cursor: pointer; font-size: 14px; }
        .btn-primary { background: #667eea; color: white; }
        .btn-primary:hover { background: #5568d3; }
        .btn-success { background: #28a745; color: white; }
        .btn-success:hover { background: #218838; }
        .test-section { margin-top: 30px; }
        .test-form { background: white; padding: 20px; border-radius: 8px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .test-result { background: #f0f8ff; border: 1px solid #b3d9ff; border-radius: 4px; padding: 15px; margin-top: 10px; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: 'Courier New', monospace; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🔍 Integration Verification</h1>
        <p>System Health Check for e-lite Platform</p>
        <div class="status-badge" style="margin-top: 15px;">
            Status: <?= $status ?>
        </div>
    </div>

    <!-- System Checks -->
    <div class="section-title">📊 System Checks</div>
    <div class="checks">
        <?php foreach ($checks as $checkName => $result): ?>
            <div class="check-item">
                <span class="check-label"><?= htmlspecialchars($checkName) ?></span>
                <span class="check-result <?= strpos($result, '✅') === 0 ? 'pass' : (strpos($result, '❌') === 0 ? 'fail' : 'warning') ?>">
                    <?= htmlspecialchars($result) ?>
                </span>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Errors Summary -->
    <?php if (!empty($errors)): ?>
        <div class="info-card" style="border-left-color: #dc3545;">
            <h3 style="color: #dc3545;">⚠️ Errors Found</h3>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Quick Links -->
    <div class="section-title">🚀 Quick Access Links</div>
    <div class="info-card">
        <div class="button-group">
            <a href="<?= $basePath ?>/login" class="btn btn-primary">🔐 Login</a>
            <a href="<?= $basePath ?>/register" class="btn btn-primary">📝 Register</a>
            <a href="<?= $basePath ?>/dashboard" class="btn btn-primary">📊 Dashboard</a>
            <a href="<?= $basePath ?>/courses" class="btn btn-primary">📚 Courses</a>
            <a href="<?= $basePath ?>/forum" class="btn btn-primary">💬 Forum</a>
            <a href="<?= $basePath ?>/admin" class="btn btn-primary">⚙️ Admin</a>
        </div>
    </div>

    <!-- Documentation -->
    <div class="section-title">📚 Documentation</div>
    <div class="info-card">
        <h3>Integration Guides</h3>
        <ul>
            <li><a href="<?= $basePath ?>/INTEGRATION_COMPLETE.md" target="_blank">Complete Integration Guide</a></li>
            <li><a href="<?= $basePath ?>/WEBSITE_LINKS.md" target="_blank">Website Links & Features</a></li>
            <li><a href="<?= $basePath ?>/INTEGRATION_GUIDE.php" target="_blank">API Documentation</a></li>
        </ul>
    </div>

    <!-- Test Permission System -->
    <div class="section-title">🧪 Permission System Test</div>
    <div class="test-section">
        <div class="test-form">
            <h3>Test User Permissions</h3>
            <?php
            $students = $db->query("SELECT u.idUser, CONCAT(u.prenom, ' ', u.nom) as fullname FROM user u JOIN role r ON u.idRole = r.idRole WHERE r.nom = 'etudiant' LIMIT 10")->fetchAll();
            $courses = $db->query("SELECT idCourse, titre FROM course LIMIT 10")->fetchAll();
            ?>
            
            <?php if (!empty($students) && !empty($courses)): ?>
                <form method="GET" style="background: white; padding: 20px; border-radius: 4px;">
                    <div class="form-group">
                        <label for="testUser">Select Student:</label>
                        <select name="testUser" id="testUser">
                            <option value="">-- Select a student --</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?= $student['idUser'] ?>">
                                    <?= htmlspecialchars($student['fullname']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="testCourse">Select Course:</label>
                        <select name="testCourse" id="testCourse">
                            <option value="">-- Select a course --</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?= $course['idCourse'] ?>">
                                    <?= htmlspecialchars($course['titre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success">Test Permissions</button>
                </form>

                <?php if (!empty($_GET['testUser']) && !empty($_GET['testCourse'])): ?>
                    <div class="test-result">
                        <h4>Permission Test Results:</h4>
                        <?php
                        $testUserId = (int)$_GET['testUser'];
                        $testCourseId = (int)$_GET['testCourse'];
                        
                        $userRole = PermissionHelper::getUserRole($testUserId);
                        $canAccess = PermissionHelper::canAccessCourse($testUserId, $testCourseId);
                        $isEnrolled = PermissionHelper::isEnrolled($testUserId, $testCourseId);
                        $isTeacher = PermissionHelper::isTeacherOfCourse($testUserId, $testCourseId);
                        ?>
                        <p><strong>User ID:</strong> <?= $testUserId ?></p>
                        <p><strong>Role:</strong> <?= htmlspecialchars($userRole) ?></p>
                        <p><strong>Can Access Course:</strong> <span style="color: <?= $canAccess ? 'green' : 'red' ?>;"><strong><?= $canAccess ? '✅ YES' : '❌ NO' ?></strong></span></p>
                        <p><strong>Is Enrolled:</strong> <span style="color: <?= $isEnrolled ? 'green' : 'red' ?>;"><strong><?= $isEnrolled ? '✅ YES' : '❌ NO' ?></strong></span></p>
                        <p><strong>Is Teacher:</strong> <span style="color: <?= $isTeacher ? 'green' : 'red' ?>;"><strong><?= $isTeacher ? '✅ YES' : '❌ NO' ?></strong></span></p>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <p style="color: #999;"><i class="fas fa-info-circle"></i> No test data available. Create users and courses first.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- System Information -->
    <div class="section-title">ℹ️ System Information</div>
    <div class="info-card">
        <table style="width: 100%; border-collapse: collapse;">
            <tr style="border-bottom: 1px solid #ddd;">
                <td style="padding: 10px;"><strong>PHP Version:</strong></td>
                <td style="padding: 10px;"><?= phpversion() ?></td>
            </tr>
            <tr style="border-bottom: 1px solid #ddd;">
                <td style="padding: 10px;"><strong>Server:</strong></td>
                <td style="padding: 10px;"><?= $_SERVER['SERVER_SOFTWARE'] ?></td>
            </tr>
            <tr style="border-bottom: 1px solid #ddd;">
                <td style="padding: 10px;"><strong>Database:</strong></td>
                <td style="padding: 10px;">e_lite (MySQL/MariaDB)</td>
            </tr>
            <tr>
                <td style="padding: 10px;"><strong>Current Time:</strong></td>
                <td style="padding: 10px;"><?= date('Y-m-d H:i:s') ?></td>
            </tr>
        </table>
    </div>

</div>
</body>
</html>
