<?php
/**
 * View - course_list.php (BackOffice)
 * Liste des cours avec modification et suppression
 */

require_once '../FrontOffice/config.php';
require_once '../../Controller/CourseController.php';


$courseController = new CourseController();
$courses = $courseController->getAllCourses();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>e-lite | Gestion des Cours</title>
    <link rel="stylesheet" href="../assets/index.css">
</head>
<body>
<script src="../assets/js/index.js"></script>
<header>
    <nav>
        <a href="dashboard.php" class="logo">e-<span>lite</span></a>
        <ul class="nav-links">
            <li><a href="course_list.php">Gestion Cours</a></li>
            <li><a href="quizzes_list.php">Gestion Quiz</a></li>
            <li><a href="forums_list.php">Gestion Forums</a></li>
        </ul>
        <div class="auth-buttons">
            <button class="btn-icon">👤</button>
        </div>
    </nav>
</header>

<main>
    <div class="gestion-section">
        <!-- En-tête de la page -->
        <div class="section-header">
            <h2>📚 Gestion des Cours</h2>
            <p>Gérez tous vos cours et créez des nouveaux contenus de formation</p>
        </div>

        <!-- Barre d'action -->
        <div style="margin-bottom: 2rem; display: flex; justify-content: flex-end;">
            <a href="course_add.php" class="btn-primary">+ Ajouter un Cours</a>
        </div>

        <!-- BARRE DE RECHERCHE SIMPLE -->
        <div style="margin-bottom: 1.5rem; display: flex; gap: 1rem;">
            <div style="flex: 1;">
                <input type="text" name="search" class="form-control" 
                       placeholder="🔍 Rechercher un cours (titre, niveau, statut)..."
                       style="padding: 0.75rem 1rem; border: 2px solid #e0e0e0; border-radius: 8px;">
            </div>
        </div>

        <!-- Tableau des cours -->
        <?php if (count($courses) > 0): ?>
            <div class="glass-card">
                <div style="overflow-x: auto;">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>Niveau</th>
                                <th>Durée (h)</th>
                                <th>Prix (€)</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($courses as $course): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($course['titre']); ?></strong>
                                        <br>
                                        <small style="opacity: 0.7;"><?php echo htmlspecialchars(substr($course['description'], 0, 50)) . '...'; ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars(ucfirst($course['niveau'])); ?></td>
                                    <td><?php echo (int)$course['duree']; ?></td>
                                    <td><?php echo number_format($course['prix'], 2, ',', ' '); ?> €</td>
                                    <td>
                                        <span class="badge <?php 
                                            echo $course['statut'] === 'actif' ? 'badge-success' : 
                                                 ($course['statut'] === 'inactif' ? 'badge-warning' : 'badge-danger');
                                        ?>">
                                            <?php echo ucfirst($course['statut']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="course_update.php?id=<?php echo $course['idCourse']; ?>" class="btn-outline">Éditer</a>
                                            <a href="course_update.php?id=<?php echo $course['idCourse']; ?>" class="btn btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce cours ?')">
                                                Supprimer
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php else: ?>
            <!-- Message d'absence de cours -->
            <div class="glass-card">
                <div class="alert alert-info">
                    <p>📚 Aucun cours disponible</p>
                    <p style="margin: 1rem 0 0; opacity: 0.8;">Créez votre premier cours pour commencer.</p>
                </div>
                <div style="text-align: center; padding: 2rem 0;">
                    <a href="course_add.php" class="btn-primary">Créer le premier cours</a>
                </div>
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

<script>
    // Traitement des suppressions via AJAX
    document.querySelectorAll('form').forEach(form => {
        if (form.querySelector('[name="action"]')?.value === 'delete') {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                // Implémentation de la suppression
                const idCourse = form.querySelector('[name="idCourse"]').value;
                // Redirection vers une page de suppression ou AJAX
                window.location.href = 'course_update.php?id=' + idCourse + '&delete=1';
            });
        }
    });
</script>

</body>
</html>