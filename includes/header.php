<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>e-lite - L'excellence en e-learning</title>
    <link rel="icon" type="image/png" href="/e-lite/assets/uploads/pic/icon.png">
    <link rel="stylesheet" href="/e-lite/assets/css/style.css">
    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
<header>
    <nav>
        <a href="/e-lite/index.php" class="logo">e-<span>lite</span></a>
        <div class="nav-links">
            <a href="#accueil">Accueil</a>
            <a href="#cours">Cours</a>
            <a href="#forum">Forum</a>
            <a href="#classes">Classes virtuelles</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/e-lite/student/dashboard.php">Tableau de bord</a>
            <?php else: ?>
                <a href="/e-lite/login.php" class="btn-outline">Connexion</a>
            <?php endif; ?>
        </div>
    </nav>
</header>