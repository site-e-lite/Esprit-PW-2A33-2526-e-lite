<?php
/**
 * View - courses_list.php (FrontOffice)
 * Page de liste des cours disponibles avec pagination
 */

require_once 'config.php';
require_once '../../Controller/CourseController.php';

$courseController = new CourseController();

// Récupération de la page (pagination)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12; // Nombre de cours par page

$result = $courseController->getCoursesPaginated($page, $limit);
$courses = $result['courses'];
$totalPages = $result['totalPages'];
$totalCourses = $result['totalCourses'];
$currentPage = $result['currentPage'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>e-lite | Nos Cours</title>
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
    <div style="max-width: 1400px; margin: 0 auto; padding: 5%;">
        <!-- En-tête de la page -->
        <div class="section-header">
            <h2>📚 Nos Cours</h2>
            <p>Découvrez nos <?php echo $totalCourses; ?> cours disponibles et développez vos compétences</p>
        </div>

        <!-- Barre de recherche -->
        <div class="search-bar">
            <input type="text" id="searchCourses" placeholder="Rechercher un cours...">
        </div>

        <!-- Contenu principal -->
        <?php if (count($courses) > 0): ?>
            <!-- Grille de cours -->
            <div class="courses-grid">
                <?php foreach ($courses as $course): ?>
                    <div class="glass-card course-card">
                        <!-- Image du cours -->
                        <?php if ($course['image']): ?>
                            <img src="<?php echo htmlspecialchars($course['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($course['titre']); ?>"
                                 class="course-img">
                        <?php else: ?>
                            <div class="course-img" style="display: flex; align-items: center; justify-content: center; font-size: 3.5rem;">📚</div>
                        <?php endif; ?>
                        
                        <!-- Contenu de la carte -->
                        <div class="course-info">
                            <span class="level">
                                <?php echo ucfirst($course['niveau']); ?>
                            </span>

                            <h3 class="course-title">
                                <?php echo htmlspecialchars($course['titre']); ?>
                            </h3>
                            
                            <p class="course-meta">
                                ⏱️ <?php echo (int)$course['duree']; ?> heures
                            </p>
                            
                            <div class="course-meta" style="margin-top: auto; padding-top: 1rem;">
                                <strong><?php echo number_format($course['prix'], 2, ',', ' '); ?> €</strong>
                            </div>
                            
                            <!-- Actions -->
                            <div class="hero-actions" style="margin-top: 1rem; gap: 0.5rem;">
                                <a href="course_detail.php?id=<?php echo $course['idCourse']; ?>" 
                                   class="btn-outline">
                                   Détails
                                </a>
                                <?php if (isset($_SESSION['idUser'])): ?>
                                    <a href="enrollment_form.php?idCourse=<?php echo $course['idCourse']; ?>" 
                                       class="btn-primary">
                                       S'inscrire
                                    </a>
                                <?php else: ?>
                                    <a href="login.php" 
                                       class="btn-primary">
                                       Connexion
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="pagination" style="display: flex; justify-content: center; gap: 0.5rem; flex-wrap: wrap; margin: 3rem 0;">
                <?php if ($currentPage > 1): ?>
                    <a href="courses_list.php?page=1" class="btn-outline" title="Première page">&laquo; Première</a>
                    <a href="courses_list.php?page=<?php echo $currentPage - 1; ?>" class="btn-outline" title="Page précédente">
                        ← Précédent
                    </a>
                <?php endif; ?>

                <!-- Pages numérotées -->
                <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                    <?php if ($i == $currentPage): ?>
                        <span class="btn-primary" style="padding: 0.625rem 0.875rem; cursor: default;">
                            <?php echo $i; ?>
                        </span>
                    <?php else: ?>
                        <a href="courses_list.php?page=<?php echo $i; ?>" class="btn-outline">
                            <?php echo $i; ?>
                        </a>
                    <?php endif; ?>
                <?php endfor; ?>

                <!-- Boutons suivant et dernière page -->
                <?php if ($currentPage < $totalPages): ?>
                    <a href="courses_list.php?page=<?php echo $currentPage + 1; ?>" class="btn-outline" title="Page suivante">
                        Suivant →
                    </a>
                    <a href="courses_list.php?page=<?php echo $totalPages; ?>" class="btn-outline" title="Dernière page">
                        Dernière &raquo;
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="glass-card" style="text-align: center; padding: 3rem;">
                <p style="color: var(--light-gray); font-size: 1.1rem;">📚 Aucun cours disponible pour le moment</p>
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
    // Script de recherche simple
    const searchInput = document.getElementById('searchCourses');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const cards = document.querySelectorAll('.course-card');
            
            cards.forEach(card => {
                const title = card.querySelector('.course-title')?.innerText.toLowerCase() || '';
                if (title.includes(searchTerm)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
            
            const visibleCards = Array.from(cards).filter(card => card.style.display !== 'none');
            console.log(visibleCards.length + ' cours trouvés');
        });
    }
</script>
</body>
</html>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>e-lite | Nom de la page</title>
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

<main>
    <!-- Contenu spécifique à la page -->
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