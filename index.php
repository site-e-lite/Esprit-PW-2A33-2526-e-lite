<?php
<<<<<<< HEAD
header('Location: /gestioncours/View/FrontOffice/course/index.php');
exit;
=======
/**
 * Point d'entrée : authentification (main) + route /forum vers le front forum.
 * Sous-dossier XAMPP : les chemins de requête sont normalisés avec le dossier du script.
 */
session_start();
require_once __DIR__ . '/Controller/User/UserController.php';
require_once __DIR__ . '/Controller/Forum/ForumController.php';

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
    default:
        http_response_code(404);
        echo "404 - Page not found: " . htmlspecialchars($request);
}
>>>>>>> 947d1560670f98dea9fd32a6da1b7f0f76c3eb81
