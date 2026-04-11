<?php
require_once __DIR__ . '/../config/config.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /e-lite/login.php');
        exit;
    }
}

function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

// Fonction de connexion sécurisée (utilisée par login.php)
function authenticate($email, $password) {
    $pdo = Config::getConnexion();
    // TODO : Préparer et exécuter la requête pour récupérer l'utilisateur par email
    // TODO : Vérifier le mot de passe avec password_verify()
    // TODO : Si valide, enregistrer les informations en session (id, nom, rôle)
}

// Fonction de déconnexion
function logout() {
    session_destroy();
    header('Location: /e-lite/login.php');
    exit;
}

// Redirection selon le rôle après login
function redirectBasedOnRole() {
    if (hasRole('admin')) {
        header('Location: /e-lite/admin/dashboard.php');
    } elseif (hasRole('formateur')) {
        header('Location: /e-lite/admin/dashboard.php'); // ou formateur/dashboard.php
    } else {
        header('Location: /e-lite/student/dashboard.php');
    }
    exit;
}
?>