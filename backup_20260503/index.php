<?php
session_start();
require_once __DIR__ . '/Controller/UserController.php';

$request = $_SERVER['REQUEST_URI'];
$request = strtok($request, '?');
$request = rtrim($request, '/');

$controller = new UserController();

switch ($request) {
    case '':
    case '/':
        include __DIR__ . '/View/layout/header.php';
        echo '<div class="hero"><h1>Welcome to e-lite</h1><p><a href="/login" class="btn-primary">Login</a> <a href="/register" class="btn-outline">Register</a></p></div>';
        include __DIR__ . '/View/layout/footer.php';
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
