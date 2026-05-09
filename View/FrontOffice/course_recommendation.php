<?php
/**
 * View - course_recommendation.php (FrontOffice)
 * Page de recommandation IA de cours
 */

require_once 'config.php';
require_once '../../Controller/CourseController.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['idUser'])) {
    header('Location: login.php');
    exit;
}

$courseController = new CourseController();
$recommendedCourses = [];
$niveauInitial = null;
$objectifPersonnel = null;

// Si le formulaire est soumis, récupérer les recommandations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $niveauInitial = $_POST['niveauInitial'] ?? null;
    $objectifPersonnel = $_POST['objectifPersonnel'] ?? null;

    if ($niveauInitial && $objectifPersonnel) {
        $recommendedCourses = $courseController->getRecommendedCourses($niveauInitial, $objectifPersonnel);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>e-lite | Recommandations IA</title>
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
        <div class="section-header" style="text-align: center; margin-bottom: 40px;">
            <span class="eco-badge">🤖 Recommandation Intelligente</span>
            <h2>Découvrez vos cours recommandés</h2>
            <p>Notre système IA analyse votre profil pour vous recommander les meilleurs cours adaptés à votre niveau et objectifs</p>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 40px;">
            <!-- Formulaire de recommandation -->
            <div class="glass-card">
                <h3 style="font-size: 1.5rem; margin-bottom: 20px; color: var(--text-main);">Vos Préférences</h3>
                <form method="POST">
                    <div class="form-group">
                        <label for="niveauInitial">Votre niveau actuel <span style="color: #ef4444;">*</span></label>
                        <select id="niveauInitial" name="niveauInitial" required>
                            <option value="">-- Sélectionner --</option>
                            <option value="débutant" <?php echo $niveauInitial === 'débutant' ? 'selected' : ''; ?>>Débutant</option>
                            <option value="intermédiaire" <?php echo $niveauInitial === 'intermédiaire' ? 'selected' : ''; ?>>Intermédiaire</option>
                            <option value="avancé" <?php echo $niveauInitial === 'avancé' ? 'selected' : ''; ?>>Avancé</option>
                            <option value="expert" <?php echo $niveauInitial === 'expert' ? 'selected' : ''; ?>>Expert</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="objectifPersonnel">Votre objectif personnel <span style="color: #ef4444;">*</span></label>
                        <textarea id="objectifPersonnel" name="objectifPersonnel" required placeholder="Décrivez ce que vous aimeriez apprendre..."></textarea>
                    </div>

                    <button type="submit" class="btn-primary" style="width: 100%; cursor: pointer;">🔍 Obtenir mes recommandations</button>
                </form>

                <div style="margin-top: 20px; padding: 15px; background: rgba(234, 179, 8, 0.05); border-radius: 12px; border-left: 4px solid var(--accent);">
                    <strong style="color: var(--accent);">💡 Conseil:</strong> <span style="color: var(--light-gray);">Plus vous détaillez votre objectif, meilleures seront les recommandations de notre IA.</span>
                </div>
            </div>

            <!-- Résultats -->
            <div class="glass-card">
                <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                    <div style="font-size: 1.5rem; margin-bottom: 20px; color: var(--text-main); font-weight: 700;">Résultats</div>
                    <div style="font-size: 0.9rem; color: var(--light-gray); margin-bottom: 30px;">
                        Basé sur votre niveau <strong><?php echo ucfirst($niveauInitial); ?></strong> et votre objectif de <strong><?php echo htmlspecialchars(substr($objectifPersonnel, 0, 40)); ?>...</strong>
                    </div>

                    <?php if (count($recommendedCourses) > 0): ?>
                        <div style="display: flex; flex-direction: column; gap: 20px;">
                            <?php 
                            $matchScores = [95, 90, 85]; // Scores de correspondance simulés
                            foreach ($recommendedCourses as $index => $course): 
                            ?>
                                <div style="border: 1px solid var(--glass-border); border-radius: 12px; padding: 20px;">
                                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                                        <div style="font-size: 1.2rem; font-weight: bold; color: var(--text-main);"><?php echo htmlspecialchars($course['titre']); ?></div>
                                        <span style="display: inline-block; padding: 4px 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 20px; font-size: 0.85rem; font-weight: bold;">✓ <?php echo $matchScores[$index] ?? 80; ?>% correspondance</span>
                                    </div>

                                    <div style="display: flex; gap: 20px; margin: 10px 0; font-size: 0.9rem; color: var(--light-gray); flex-wrap: wrap;">
                                        <div>📊 Niveau: <strong><?php echo ucfirst($course['niveau']); ?></strong></div>
                                        <div>⏱️ Durée: <strong><?php echo (int)$course['duree']; ?> h</strong></div>
                                        <div>💰 Prix: <strong><?php echo number_format($course['prix'], 2, ',', ' '); ?> €</strong></div>
                                    </div>

                                    <p style="color: var(--light-gray); line-height: 1.6; margin: 10px 0;"><?php echo htmlspecialchars(substr($course['description'], 0, 100)); ?>...</p>

                                    <div style="display: flex; gap: 8px; margin: 10px 0; flex-wrap: wrap;">
                                        <span style="display: inline-block; padding: 4px 12px; background: rgba(100, 150, 200, 0.1); border-radius: 4px; font-size: 0.8rem; color: var(--light-gray);"><?php echo strtoupper($course['langue']); ?></span>
                                        <span style="display: inline-block; padding: 4px 12px; background: rgba(255, 255, 255, 0.05); border-radius: 4px; font-size: 0.8rem; color: var(--light-gray);">Statut: <?php echo ucfirst($course['statut']); ?></span>
                                    </div>

                                    <div style="display: flex; gap: 10px; margin-top: 15px;">
                                        <a href="course_detail.php?id=<?php echo $course['idCourse']; ?>" class="btn-outline" style="flex: 1; text-align: center;">Détails</a>
                                        <a href="enrollment_form.php?idCourse=<?php echo $course['idCourse']; ?>" class="btn-primary" style="flex: 1; text-align: center; display: block;">S'inscrire</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div style="text-align: center; padding: 40px; color: var(--light-gray);">
                            <p style="font-size: 1.1rem; margin-bottom: 10px;">Aucun cours ne correspond à vos critères de recherche.</p>
                            <p style="font-size: 0.9rem; margin-top: 10px;">Essayez avec d'autres préférences.</p>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; color: var(--light-gray);">
                        <p style="font-size: 1.1rem; margin-bottom: 10px;">📚 Remplissez le formulaire pour découvrir vos recommandations</p>
                        <p style="font-size: 0.9rem;">Notre système IA analysera votre profil et vous proposera les meilleurs cours adaptés.</p>
                    </div>
                <?php endif; ?>
            </div>
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

<script src="../assets/js/index.js"></script>

</body>
</html>
