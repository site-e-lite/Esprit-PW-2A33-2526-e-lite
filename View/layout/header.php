<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>e-lite | Éco-Digital Learning</title>
    <link rel="stylesheet" href="/View/assets/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<header>
    <nav>
        <div style="display: flex; align-items: center; gap: 1rem;">
            <?php if (isset($_SESSION['user_id'])): ?>
                <button id="sidebarToggle" class="hamburger-btn"><i class="fas fa-bars"></i></button>
            <?php endif; ?>
            <a href="/" class="logo">e-<span>lite</span></a>
        </div>
        <ul class="nav-links">
            <li><a href="/">Accueil</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="/profile">Profil</a></li>
                <?php 
                $role = strtolower($_SESSION['role_nom'] ?? '');
                if (in_array($role, ['admin', 'administrateur', 'formateur'])): ?>
                    <li><a href="/admin/dashboard">Dashboard</a></li>
                <?php endif; ?>
            <?php endif; ?>
        </ul>
        <div class="auth-buttons">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/logout" class="btn-outline"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
            <?php else: ?>
                <a href="/login" class="btn-outline"><i class="fas fa-key"></i> Connexion</a>
                <a href="/register" class="btn-primary"><i class="fas fa-user-plus"></i> Inscription</a>
            <?php endif; ?>
        </div>
    </nav>
</header>
<main>
