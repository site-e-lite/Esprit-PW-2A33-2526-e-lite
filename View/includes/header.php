<?php
if (!isset($baseUrl)) {
    $baseUrl = '/gestioncours';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Gestion Cours') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/View/assets/index.css">
</head>
<body>
<header id="main-header">
    <nav>
        <a href="<?= $baseUrl ?>/View/FrontOffice/course/index.php" class="logo">e-lite<span>.</span></a>
        <ul class="nav-links">
            <li><a href="<?= $baseUrl ?>/View/FrontOffice/course/index.php">Accueil</a></li>
            <li><a href="<?= $baseUrl ?>/View/FrontOffice/course/index.php#cours">Cours</a></li>
            <li><a href="<?= $baseUrl ?>/View/FrontOffice/certificate/index.php"><i class="fas fa-certificate"></i> Mes Certificats</a></li>
            <li><a href="<?= $baseUrl ?>/View/BackOffice/course/list.php">BackOffice</a></li>
            <li><a href="<?= $baseUrl ?>/View/BackOffice/support_course/list.php">Supports</a></li>
            <li><a href="<?= $baseUrl ?>/View/BackOffice/enrollment/list.php">Inscriptions</a></li>
        </ul>
        <div class="auth-buttons">
            <a class="btn-outline" href="<?= $baseUrl ?>/View/BackOffice/course/add.php"><i class="fas fa-plus"></i> Ajouter un cours</a>
        </div>
    </nav>
</header>
<main class="page-shell">
