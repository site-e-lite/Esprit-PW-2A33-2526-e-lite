<?php
/**
 * View - enrollment_form.php (FrontOffice)
 * Formulaire d'inscription à un cours
 */

require_once 'config.php';
require_once '../../Controller/CourseController.php';
require_once '../../Controller/EnrollmentController.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['idUser'])) {
    header('Location: login.php');
    exit;
}

// Récupérer l'ID du cours
$idCourse = isset($_GET['idCourse']) ? (int)$_GET['idCourse'] : null;

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

// Traiter l'inscription si le formulaire est soumis
$message = null;
$messageType = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enrollmentController = new EnrollmentController();
    $_POST['idCourse'] = $idCourse;
    $result = $enrollmentController->enrollUser();
    $message = $result['message'];
    $messageType = $result['success'] ? 'success' : 'error';
    
    if ($result['success']) {
        // Redirection après succès
        header('Location: my_courses.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>e-lite | S'inscrire</title>
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
    <div style="max-width: 800px; margin: 0 auto; padding: 5%;">
        <a href="course_detail.php?id=<?php echo $course['idCourse']; ?>" style="display: inline-block; margin-bottom: 20px; color: var(--accent); text-decoration: none; font-weight: 600;">← Retour au cours</a>

        <div class="glass-card">
            <h1 style="font-size: 1.8rem; margin-bottom: 10px; color: var(--accent);">S'inscrire au cours</h1>

            <!-- Informations du cours -->
            <div style="background: rgba(234, 179, 8, 0.05); border-left: 4px solid var(--accent); padding: 15px; border-radius: 0 12px 12px 0; margin-bottom: 30px;">
                <h3 style="margin: 0 0 10px 0; color: var(--text-main);"><?php echo htmlspecialchars($course['titre']); ?></h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; font-size: 0.9rem;">
                    <div>
                        <strong>Niveau:</strong> <?php echo ucfirst($course['niveau']); ?>
                    </div>
                    <div>
                        <strong>Durée:</strong> <?php echo (int)$course['duree']; ?> heures
                    </div>
                    <div>
                        <strong>Prix:</strong> <?php echo number_format($course['prix'], 2, ',', ' '); ?> €
                    </div>
                    <div>
                        <strong>Langue:</strong> <?php echo strtoupper($course['langue']); ?>
                    </div>
                </div>
            </div>

            <?php if ($message): ?>
                <div style="padding: 12px; margin-bottom: 15px; border-radius: 12px; background: <?php echo $messageType === 'success' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)'; ?>; border-left: 4px solid <?php echo $messageType === 'success' ? 'var(--green-eco)' : '#ef4444'; ?>; color: <?php echo $messageType === 'success' ? 'var(--green-eco)' : '#ef4444'; ?>;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <!-- Niveau Initial -->
                <div class="form-group">
                    <label for="niveauInitial">Votre niveau initial <span style="color: #ef4444;">*</span></label>
                    <select id="niveauInitial" name="niveauInitial" required>
                        <option value="">-- Sélectionner --</option>
                        <option value="débutant">Débutant (première fois)</option>
                        <option value="intermédiaire">Intermédiaire (expérience basique)</option>
                        <option value="avancé">Avancé (bonne expérience)</option>
                        <option value="expert">Expert (très expérimenté)</option>
                    </select>
                </div>

                <!-- Objectif Personnel -->
                <div class="form-group">
                    <label for="objectifPersonnel">Votre objectif personnel <span style="color: #ef4444;">*</span></label>
                    <textarea id="objectifPersonnel" name="objectifPersonnel" required placeholder="Qu'espérez-vous apprendre dans ce cours ?"></textarea>
                </div>

                <!-- Engagement -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div class="form-group">
                        <label for="engagement">Votre engagement (h/semaine) <span style="color: #ef4444;">*</span></label>
                        <select id="engagement" name="engagement" required>
                            <option value="">-- Sélectionner --</option>
                            <option value="5-10">5-10 heures</option>
                            <option value="10-15">10-15 heures</option>
                            <option value="15-20">15-20 heures</option>
                            <option value="20+">Plus de 20 heures</option>
                        </select>
                    </div>

                    <!-- Mode d'accès -->
                    <div class="form-group">
                        <label for="modeAcces">Mode d'accès</label>
                        <select id="modeAcces" name="modeAcces">
                            <option value="online">En ligne</option>
                            <option value="inperson">En personne</option>
                            <option value="hybrid">Hybride</option>
                        </select>
                    </div>
                </div>

                <!-- Conditions d'utilisation -->
                <div style="background: rgba(100, 200, 255, 0.05); border-left: 4px solid #00a8ff; padding: 15px; border-radius: 0 12px 12px 0; margin: 20px 0;">
                    <strong>📋 Conditions:</strong> En vous inscrivant, vous acceptez les conditions d'utilisation et la politique de confidentialité. Vous pouvez quitter le cours à tout moment.
                </div>

                <!-- Boutons -->
                <div style="display: flex; gap: 10px; margin-top: 30px;">
                    <button type="submit" class="btn-primary" style="flex: 1;">Confirmer l'inscription</button>
                    <a href="course_detail.php?id=<?php echo $course['idCourse']; ?>" style="flex: 1; text-decoration: none;">
                        <button type="button" class="btn-outline" style="width: 100%; cursor: pointer;">Annuler</button>
                    </a>
                </div>
            </form>
        </div>
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
