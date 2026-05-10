<?php
/**
 * index.php — Main entry point for the unified e-lite application.
 * Handles routing for: auth, forum, admin, profile.
 * Direct PHP file URLs (View/...) still work independently.
 */
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/Controller/User/UserController.php';
require_once __DIR__ . '/Controller/Forum/ForumController.php';

// Resolve base path (works in subdirectory like /gestioncours/)
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
$basePath   = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
if ($basePath === '.' || $basePath === '/') {
    $basePath = '';
}

// Normalize request path
$request = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
if ($basePath !== '' && strpos($request, $basePath) === 0) {
    $request = substr($request, strlen($basePath)) ?: '/';
}
$request = rtrim($request, '/') ?: '/';

$controller = new UserController();

switch ($request) {
    case '':
    case '/':
        header('Location: ' . $basePath . '/View/FrontOffice/course/index.php');
        exit;

    case '/forum':
        require __DIR__ . '/View/Forum/FrontOffice/index.php';
        break;

    case '/forum/manage':
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . $basePath . '/login');
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

    case '/admin/dashboard':
        $controller->adminDashboard();
        break;

    case '/student/dashboard':
        $controller->studentDashboard();
        break;

    case '/login/google':
        if (empty(GOOGLE_CLIENT_ID)) {
            // Google OAuth not configured — redirect back to login
            $_SESSION['flash_error'] = 'La connexion Google n\'est pas configurée.';
            header('Location: ' . $basePath . '/login');
            exit;
        }
        $controller->googleLogin();
        break;

    case '/login/google-callback':
        if (empty(GOOGLE_CLIENT_ID)) {
            header('Location: ' . $basePath . '/login');
            exit;
        }
        $controller->googleCallback();
        break;

    case '/forgot':
        $controller->forgotPassword();
        break;

    case '/reset-password':
        $controller->resetPassword();
        break;

    case '/reset-success':
        $controller->resetSuccess();
        break;

    default:
        http_response_code(404);
        include __DIR__ . '/View/layout/header.php';
        echo '<section style="text-align:center; padding:4rem;"><h2>404 — Page introuvable</h2>
              <p style="color:#aaa;">' . htmlspecialchars($request) . '</p>
              <a href="' . htmlspecialchars($basePath . '/View/FrontOffice/course/index.php') . '" class="btn-primary" style="margin-top:1rem;">Retour à l\'accueil</a></section>';
        include __DIR__ . '/View/layout/footer.php';
}
