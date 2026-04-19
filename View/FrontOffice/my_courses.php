<?php
/**
 * View - my_courses.php (FrontOffice)
 * Page "Mes cours" pour l'étudiant connecté avec suivi de progression
 */

require_once 'config.php';
require_once '../../Controller/EnrollmentController.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['idUser'])) {
    header('Location: login.php');
    exit;
}

$enrollmentController = new EnrollmentController();
$idUser = $_SESSION['idUser'];
$enrollments = $enrollmentController->getMyEnrollments($idUser);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>e-lite | Mes Cours</title>
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
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h1 style="font-size: 2rem; color: var(--text-main);">📚 Mes Cours</h1>
            <a href="courses_list.php" class="btn-primary">+ Découvrir d'autres cours</a>
        </div>

        <!-- Filtres -->
        <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
            <button class="btn-outline" onclick="filterCourses('all')" data-filter="all" style="border: 2px solid var(--accent);">Tous</button>
            <button class="btn-outline" onclick="filterCourses('actif')" data-filter="actif">En cours</button>
            <button class="btn-outline" onclick="filterCourses('terminé')" data-filter="terminé">Terminés</button>
            <button class="btn-outline" onclick="filterCourses('suspendu')" data-filter="suspendu">Suspendus</button>
        </div>

        <?php if (count($enrollments) > 0): ?>
            <div class="courses-grid">
                <?php foreach ($enrollments as $enrollment): ?>
                    <div class="glass-card" data-status="<?php echo htmlspecialchars($enrollment['statut']); ?>">
                        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 24px 24px 0 0; color: white;">
                            <div style="font-size: 1.2rem; font-weight: bold; margin-bottom: 10px;"><?php echo htmlspecialchars($enrollment['titre']); ?></div>
                            <div style="font-size: 0.85rem; opacity: 0.9;">
                                Niveau: <?php echo ucfirst($enrollment['niveau']); ?> | 
                                <?php echo (int)$enrollment['duree']; ?>h
                            </div>
                        </div>

                        <div class="course-info" style="padding: 20px;">
                            <span style="display: inline-block; padding: 0.5rem 1rem; border-radius: 50px; font-weight: 600; margin-bottom: 10px; background: <?php echo $enrollment['statut'] === 'actif' ? 'rgba(16, 185, 129, 0.1)' : ($enrollment['statut'] === 'terminé' ? 'rgba(100, 150, 200, 0.1)' : 'rgba(255, 193, 7, 0.1)'); ?>; color: <?php echo $enrollment['statut'] === 'actif' ? 'var(--green-eco)' : ($enrollment['statut'] === 'terminé' ? '#00a8ff' : '#ffc107'); ?>;">
                                <?php echo ucfirst($enrollment['statut']); ?>
                            </span>

                            <!-- Progression -->
                            <div style="margin-bottom: 15px;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 0.9rem;">
                                    <span>Progression</span>
                                    <span><strong><?php echo round($enrollment['progression']); ?>%</strong></span>
                                </div>
                                <div style="width: 100%; height: 8px; background: rgba(255,255,255,0.1); border-radius: 4px; overflow: hidden;">
                                    <div style="height: 100%; background: linear-gradient(90deg, var(--green-eco), #20c997); width: <?php echo $enrollment['progression']; ?>%; transition: width 0.3s ease;"></div>
                                </div>
                            </div>

                            <!-- Statistiques -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px; font-size: 0.85rem;">
                                <div style="background: rgba(255,255,255,0.05); padding: 10px; border-radius: 12px;">
                                    <div style="color: var(--light-gray); margin-bottom: 3px;">Inscrit depuis</div>
                                    <div style="font-weight: bold; color: var(--text-main);">
                                        <?php 
                                        $date = new DateTime($enrollment['dateInscription']);
                                        echo $date->format('d/m/Y');
                                        ?>
                                    </div>
                                </div>
                                <div style="background: rgba(255,255,255,0.05); padding: 10px; border-radius: 12px;">
                                    <div style="color: var(--light-gray); margin-bottom: 3px;">Temps passé</div>
                                    <div style="font-weight: bold; color: var(--text-main);">
                                        <?php 
                                        $heures = floor($enrollment['tempsTotalPasse'] / 3600);
                                        $minutes = floor(($enrollment['tempsTotalPasse'] % 3600) / 60);
                                        echo $heures . 'h ' . $minutes . 'm';
                                        ?>
                                    </div>
                                </div>
                                <div style="background: rgba(255,255,255,0.05); padding: 10px; border-radius: 12px;">
                                    <div style="color: var(--light-gray); margin-bottom: 3px;">Dernière activité</div>
                                    <div style="font-weight: bold; color: var(--text-main);">
                                        <?php 
                                        $lastActivity = new DateTime($enrollment['derniereActivite']);
                                        $now = new DateTime();
                                        $diff = $now->diff($lastActivity);
                                        if ($diff->days > 0) {
                                            echo 'il y a ' . $diff->days . 'j';
                                        } elseif ($diff->h > 0) {
                                            echo 'il y a ' . $diff->h . 'h';
                                        } else {
                                            echo 'à l\'instant';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div style="background: rgba(255,255,255,0.05); padding: 10px; border-radius: 12px;">
                                    <div style="color: var(--light-gray); margin-bottom: 3px;">Note finale</div>
                                    <div style="font-weight: bold; color: var(--text-main);">
                                        <?php 
                                        if ($enrollment['noteFinale'] !== null) {
                                            echo round($enrollment['noteFinale'], 1) . '/20';
                                        } else {
                                            echo 'En attente';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div style="display: flex; gap: 10px; margin-top: 15px;">
                                <a href="course_detail.php?id=<?php echo $enrollment['idCourse']; ?>" class="btn-outline" style="flex: 1; text-align: center;">Détails</a>
                                <form method="POST" style="flex: 1;" onsubmit="return confirm('Êtes-vous sûr de vouloir quitter ce cours ?');">
                                    <input type="hidden" name="action" value="unenroll">
                                    <input type="hidden" name="idEnrollment" value="<?php echo $enrollment['idEnrollment']; ?>">
                                    <button type="submit" class="btn-outline" style="width: 100%; background: rgba(239, 68, 68, 0.1); border-color: #ef4444; color: #ef4444; cursor: pointer;">Quitter</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="glass-card" style="text-align: center; padding: 3rem;">
                <h2 style="color: var(--light-gray); margin-bottom: 15px;">Aucun cours pour le moment</h2>
                <p style="color: var(--light-gray); margin-bottom: 20px;">Vous n'êtes inscrit à aucun cours. Découvrez nos cours disponibles et commencez votre apprentissage !</p>
                <a href="courses_list.php" class="btn-primary" style="display: inline-block;">Parcourir nos cours</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="../assets/js/index.js"></script>
    <script>
        /**
         * Fonction de filtrage des cours
         * @param {string} status - Le statut à filtrer
         */
        function filterCourses(status) {
            // Mettre à jour les boutons actifs
            document.querySelectorAll('[data-filter]').forEach(btn => {
                btn.style.borderColor = btn.dataset.filter === status ? 'var(--accent)' : 'rgba(255, 255, 255, 0.2)';
                btn.style.color = btn.dataset.filter === status ? 'var(--accent)' : 'var(--light-gray)';
            });

            // Filtrer les cartes de cours
            document.querySelectorAll('[data-status]').forEach(card => {
                if (status === 'all' || card.dataset.status === status) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Initialiser les mises à jour de progression au chargement
        document.addEventListener('DOMContentLoaded', function() {
            // Le script progression.js sera chargé pour gérer les mises à jour en arrière-plan
        });
    </script>
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
