<?php 
if (session_status() === PHP_SESSION_NONE) session_start(); 
// Always resolve basePath relative to the project root (index.php), not the current script
$_projectRoot = realpath(__DIR__ . '/../..');  // View/layout/ → 2 levels up = project root
$_docRoot     = realpath($_SERVER['DOCUMENT_ROOT']);
$_rel         = str_replace('\\', '/', substr($_projectRoot, strlen($_docRoot)));
$basePath     = rtrim($_rel, '/');
if ($basePath === '.' || $basePath === '') $basePath = '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>e-lite | Éco-Digital Learning</title>
    <link rel="stylesheet" href="<?= $basePath ?>/View/assets/User/index.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<header>
    <nav>
        <div style="display: flex; align-items: center; gap: 1rem;">
            <?php if (isset($_SESSION['user_id'])): ?>
                <button id="sidebarToggle" class="hamburger-btn"><i class="fas fa-bars"></i></button>
            <?php endif; ?>
            <a href="<?= $basePath ?>/" class="logo">e-<span>lite</span></a>
        </div>
        <ul class="nav-links">
            <li><a href="<?= $basePath ?>/">Accueil</a></li>
            <li><a href="<?= $basePath ?>/forum">Forum</a></li>
            <li><a href="<?= $basePath ?>/quiz"><i class="fas fa-tasks" style="margin-right:0.3rem;"></i>Évaluations</a></li>
            <li><a href="<?= $basePath ?>/virtualclass">Classes Virtuelles</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="<?= $basePath ?>/forum/manage">Gestion Forum</a></li>
                <li><a href="<?= $basePath ?>/profile">Profil</a></li>
                <?php if (($_SESSION['user_role'] ?? '') === 'admin'): ?>
                    <li><a href="<?= $basePath ?>/admin/dashboard">Dashboard</a></li>
                    <li><a href="<?= $basePath ?>/quiz/admin">Gestion Quiz</a></li>
                <?php endif; ?>
            <?php endif; ?>
        </ul>
        <div class="auth-buttons">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?= $basePath ?>/logout" class="btn-outline"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
            <?php else: ?>
                <a href="<?= $basePath ?>/login" class="btn-outline"><i class="fas fa-key"></i> Connexion</a>
                <a href="<?= $basePath ?>/register" class="btn-primary"><i class="fas fa-user-plus"></i> Inscription</a>
            <?php endif; ?>
        </div>
    </nav>
</header>
<main>
