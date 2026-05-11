<?php
/**
 * Point d'entrée : authentification (main) + route /forum vers le front forum.
 * Sous-dossier XAMPP : les chemins de requête sont normalisés avec le dossier du script.
 */
session_start();
require_once __DIR__ . '/Controller/User/UserController.php';
require_once __DIR__ . '/Controller/Forum/ForumController.php';
require_once __DIR__ . '/Controller/VirtualClass/VirtualClassController.php';
require_once __DIR__ . '/Controller/VirtualClass/SessionController.php';
require_once __DIR__ . '/Controller/Quiz/QuizController.php';
require_once __DIR__ . '/Controller/Quiz/QuestionController.php';

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
$basePath     = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
if ($basePath === '.' || $basePath === '/') {
    $basePath = '';
}

$request = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
if ($basePath !== '' && strpos($request, $basePath) === 0) {
    $request = substr($request, strlen($basePath)) ?: '/';
}
$request = rtrim($request, '/') ?: '/';

$controller = new UserController();

switch ($request) {
    case '':
    case '/':
        include __DIR__ . '/View/layout/header.php';
        $bp = $basePath === '' ? '' : $basePath;
        echo '<div class="hero"><h1>Welcome to e-lite</h1><p>'
            . '<a href="' . htmlspecialchars($bp . '/login') . '" class="btn-primary">Login</a> '
            . '<a href="' . htmlspecialchars($bp . '/register') . '" class="btn-outline">Register</a></p>';
        echo '<p style="margin-top:1rem;"><a href="' . htmlspecialchars($bp . '/forum') . '">Accéder au forum</a></p></div>';
        include __DIR__ . '/View/layout/footer.php';
        break;
    case '/forum':
        require __DIR__ . '/View/Forum/FrontOffice/index.php';
        break;
    case '/forum/manage':
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . ($basePath === '' ? '' : $basePath) . '/login');
            exit;
        }
        require __DIR__ . '/View/Forum/BackOffice/forum.php';
        break;
    case '/login':
        $controller->login();
        break;
    case '/logout':
        $controller->logout();
        break;
    case '/register':
        $controller->register();
        break;
    case '/profile':
        $controller->profile();
        break;
    case '/profile/delete':
        $controller->deleteAccount();
        break;
    case '/profile/remove-photo':
        $controller->removePhoto();
        break;
    case '/profile/regenerate-code':
        http_response_code(404);
        echo "404 - Page not found: " . htmlspecialchars($request);
        break;
    case '/admin/dashboard':
        $controller->adminDashboard();
        break;
    case '/student/dashboard':
        $controller->studentDashboard();
        break;
    case '/login/google':
        $controller->googleLogin();
        break;
    case '/login/google-callback':
        $controller->googleCallback();
        break;
    case '/forgot':
        $controller->forgotPassword();
        break;
    case '/verify-code':
        $controller->verifyCode();
        break;
    case '/reset-password':
        $controller->resetPassword();
        break;
    case '/reset-success':
        $controller->resetSuccess();
        break;

    // ── Classes Virtuelles ──────────────────────────────
    case '/virtualclass':
        if (!isset($_SESSION['user_id'])) { header('Location: ' . $basePath . '/login'); exit; }
        require __DIR__ . '/View/VirtualClass/BackOffice/virtualclasses_list.php';
        break;
    case '/virtualclass/add':
        if (!isset($_SESSION['user_id'])) { header('Location: ' . $basePath . '/login'); exit; }
        require __DIR__ . '/View/VirtualClass/BackOffice/virtualclass_add.php';
        break;
    case (preg_match('#^/virtualclass/edit/(\d+)$#', $request, $m) ? $request : '!'):
        if (!isset($_SESSION['user_id'])) { header('Location: ' . $basePath . '/login'); exit; }
        $_GET['id'] = $m[1];
        require __DIR__ . '/View/VirtualClass/BackOffice/virtualclass_edit.php';
        break;
    case '/virtualclass/sessions':
        if (!isset($_SESSION['user_id'])) { header('Location: ' . $basePath . '/login'); exit; }
        require __DIR__ . '/View/VirtualClass/BackOffice/sessions_list.php';
        break;
    case '/virtualclass/sessions/add':
        if (!isset($_SESSION['user_id'])) { header('Location: ' . $basePath . '/login'); exit; }
        require __DIR__ . '/View/VirtualClass/BackOffice/session_add.php';
        break;
    case (preg_match('#^/virtualclass/sessions/edit/(\d+)$#', $request, $m2) ? $request : '!!'):
        if (!isset($_SESSION['user_id'])) { header('Location: ' . $basePath . '/login'); exit; }
        $_GET['id'] = $m2[1];
        require __DIR__ . '/View/VirtualClass/BackOffice/session_edit.php';
        break;
    case '/virtualclass/dashboard':
        if (!isset($_SESSION['user_id'])) { header('Location: ' . $basePath . '/login'); exit; }
        require __DIR__ . '/View/VirtualClass/BackOffice/virtualclass_dashboard.php';
        break;
    case (preg_match('#^/virtualclass/qr/(\d+)$#', $request, $mqr) ? $request : '!!!'):
        if (!isset($_SESSION['user_id'])) { header('Location: ' . $basePath . '/login'); exit; }
        $_GET['id'] = $mqr[1];
        require __DIR__ . '/View/VirtualClass/BackOffice/virtualclass_qr_mail.php';
        break;

    // ── Quiz / Évaluations ──────────────────────────────────────────────────
    case '/quiz':
        require __DIR__ . '/View/Quiz/FrontOffice/index.php';
        break;

    case (preg_match('#^/quiz/passer$#', $request) ? $request : '!quiz_passer'):
        require __DIR__ . '/View/Quiz/FrontOffice/quiz.php';
        break;

    // BackOffice Quiz (admin uniquement)
    case '/quiz/admin':
        if (!isset($_SESSION['user_id'])) { header('Location: ' . $basePath . '/login'); exit; }
        require __DIR__ . '/View/Quiz/BackOffice/quizzes_list.php';
        break;

    case '/quiz/admin/ajouter':
        if (!isset($_SESSION['user_id'])) { header('Location: ' . $basePath . '/login'); exit; }
        require __DIR__ . '/View/Quiz/BackOffice/quiz_add.php';
        break;

    case '/quiz/admin/modifier':
        if (!isset($_SESSION['user_id'])) { header('Location: ' . $basePath . '/login'); exit; }
        require __DIR__ . '/View/Quiz/BackOffice/quiz_update.php';
        break;

    case '/quiz/admin/generer':
        if (!isset($_SESSION['user_id'])) { header('Location: ' . $basePath . '/login'); exit; }
        require __DIR__ . '/View/Quiz/BackOffice/quiz/generate.php';
        break;

    case '/quiz/admin/verrous':
        if (!isset($_SESSION['user_id'])) { header('Location: ' . $basePath . '/login'); exit; }
        require __DIR__ . '/View/Quiz/BackOffice/quiz_locks.php';
        break;

    case '/quiz/admin/export':
        if (!isset($_SESSION['user_id'])) { header('Location: ' . $basePath . '/login'); exit; }
        require __DIR__ . '/View/Quiz/BackOffice/quiz_results_export.php';
        break;

    case '/quiz/admin/questions':
        if (!isset($_SESSION['user_id'])) { header('Location: ' . $basePath . '/login'); exit; }
        require __DIR__ . '/View/Quiz/BackOffice/questions_list.php';
        break;

    case '/quiz/admin/questions/ajouter':
    case '/quiz/admin/question/ajouter':
        if (!isset($_SESSION['user_id'])) { header('Location: ' . $basePath . '/login'); exit; }
        require __DIR__ . '/View/Quiz/BackOffice/question_add.php';
        break;

    case '/quiz/admin/questions/modifier':
    case '/quiz/admin/question/modifier':
        if (!isset($_SESSION['user_id'])) { header('Location: ' . $basePath . '/login'); exit; }
        require __DIR__ . '/View/Quiz/BackOffice/question_update.php';
        break;

    default:
        http_response_code(404);
        echo "404 - Page not found: " . htmlspecialchars($request);
}
