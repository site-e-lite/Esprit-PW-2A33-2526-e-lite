<?php
/**
 * View - course_detail.php (FrontOffice)
 * Page détail d'un cours
 */

require_once 'config.php';
require_once '../../Controller/CourseController.php';
require_once '../../Controller/EnrollmentController.php';

// Récupérer l'ID du cours
$idCourse = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$idCourse) {
    header('Location: courses_list.php');
    exit;
}

$courseController = new CourseController();
$course = $courseController->getCourseById($idCourse);

if (!$course) {
    header('Location: courses_list.php');
    exit;
}

// Vérifier si l'utilisateur est déjà inscrit (si connecté)
$isEnrolled = false;
if (isset($_SESSION['idUser'])) {
    $enrollmentController = new EnrollmentController();
    $enrollments = $enrollmentController->getMyEnrollments($_SESSION['idUser']);
    foreach ($enrollments as $enrollment) {
        if ($enrollment['idCourse'] === $idCourse) {
            $isEnrolled = true;
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>e-lite | <?php echo htmlspecialchars($course['titre']); ?></title>
    <link rel="stylesheet" href="../assets/index.css">
</head>
<body>

<header>
    <nav>
        <a href="courses_list.php" class="logo">e-<span>lite</span></a>
        <ul class="nav-links">
            <li><a href="courses_list.php">Cours</a></li>
            <li><a href="my_courses.php">Mes cours</a></li>
            <li><a href="course_recommendation.php">Recommandations IA</a></li>
        </ul>
        <div class="auth-buttons">
            <button class="btn-icon">👤</button>
        </div>
    </nav>
</header>

<main style="padding-top: 5rem;">
    <div style="max-width: 1200px; margin: 0 auto; padding: 5%;">
        <a href="courses_list.php" style="display: inline-block; margin-bottom: 20px; color: var(--accent); text-decoration: none; font-weight: 600;">← Retour aux cours</a>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 40px;">
            <div class="glass-card" style="display: flex; align-items: center; justify-content: center; border-radius: 24px; min-height: 300px;">
                <?php if ($course['image']): ?>
                    <img src="<?php echo htmlspecialchars($course['image']); ?>" alt="<?php echo htmlspecialchars($course['titre']); ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 24px;">
                <?php else: ?>
                    <span style="font-size: 5rem;">📚</span>
                <?php endif; ?>
            </div>

            <div class="glass-card">
                <h1 style="font-size: 2rem; margin-bottom: 20px;"><?php echo htmlspecialchars($course['titre']); ?></h1>
                
                <div style="display: flex; gap: 20px; margin-bottom: 20px; flex-wrap: wrap;">
                    <div>
                        <span class="level">Niveau: <?php echo ucfirst($course['niveau']); ?></span>
                    </div>
                    <div style="color: var(--light-gray);">
                        📚 <?php echo (int)$course['duree']; ?> heures
                    </div>
                    <div style="color: var(--light-gray);">
                        🌐 <?php echo strtoupper($course['langue']); ?>
                    </div>
                </div>

                <div class="level" style="margin-bottom: 20px;">
                    Statut: <?php echo ucfirst($course['statut']); ?>
                </div>

                <div style="font-size: 1.8rem; font-weight: 700; color: var(--green-eco); margin-bottom: 20px;">
                    <?php if ($course['prix'] > 0): ?>
                        <?php echo number_format($course['prix'], 2, ',', ' '); ?> €
                    <?php else: ?>
                        GRATUIT
                    <?php endif; ?>
                </div>

                <div class="hero-actions">
                    <?php if (isset($_SESSION['idUser'])): ?>
                        <?php if ($isEnrolled): ?>
                            <span style="display: inline-block; padding: 1rem 2.2rem; background: var(--green-eco); color: white; border-radius: 50px; font-weight: 600;">✓ Vous êtes inscrit</span>
                            <a href="my_courses.php" class="btn-primary">Voir mon cours</a>
                        <?php else: ?>
                            <a href="enrollment_form.php?idCourse=<?php echo $course['idCourse']; ?>" class="btn-primary">S'inscrire au cours</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="login.php" class="btn-primary">Connexion</a>
                        <a href="courses_list.php" class="btn-outline">Retour</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Description -->
        <div class="glass-card">
            <h2 style="font-size: 1.3rem; margin-bottom: 15px; color: var(--accent); border-bottom: 2px solid var(--accent); padding-bottom: 10px;">À propos du cours</h2>
            <p style="line-height: 1.8; color: var(--light-gray);">
                <?php echo nl2br(htmlspecialchars($course['description'])); ?>
            </p>
        </div>

        <!-- Objectifs d'apprentissage -->
        <?php if ($course['objectifs']): ?>
            <div class="glass-card">
                <h2 style="font-size: 1.3rem; margin-bottom: 15px; color: var(--accent); border-bottom: 2px solid var(--accent); padding-bottom: 10px;">Objectifs d'apprentissage</h2>
                <ul style="list-style-position: inside; margin-left: 20px;">
                    <?php 
                    $objectifs = explode("\n", $course['objectifs']);
                    foreach ($objectifs as $objectif): 
                        if (trim($objectif)):
                    ?>
                        <li style="margin-bottom: 10px; color: var(--light-gray);"><?php echo htmlspecialchars(trim($objectif)); ?></li>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Prérequis -->
        <?php if ($course['prerequis']): ?>
            <div class="glass-card">
                <h2 style="font-size: 1.3rem; margin-bottom: 15px; color: var(--accent); border-bottom: 2px solid var(--accent); padding-bottom: 10px;">Prérequis</h2>
                <p style="line-height: 1.8; color: var(--light-gray);">
                    <?php echo nl2br(htmlspecialchars($course['prerequis'])); ?>
                </p>
            </div>
        <?php endif; ?>
    </div>
</main>

<footer>
    <div class="footer-links">
        <a href="#">À propos</a>
        <a href="#">Contact</a>
        <a href="#">Mentions légales</a>
    </div>
    <p>&copy; 2025 e-lite - Plateforme d'apprentissage éco-responsable</p>
</footer>

</body>
</html>
