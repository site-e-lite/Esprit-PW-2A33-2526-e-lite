<?php
// Vérification du chemin
$css_path = __DIR__ . '/../assets/index.css';
echo "<!-- CSS path: " . $css_path . " -->\n";
echo "<!-- CSS exists: " . (file_exists($css_path) ? 'YES' : 'NO') . " -->\n";
?>
<?php
/**
 * View - course_add.php (BackOffice)
 * Formulaire d'ajout de nouveau cours
 */

require_once '../FrontOffice/config.php';
require_once '../../Controller/CourseController.php';



// Traiter le formulaire si soumis
$message = null;
$messageType = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courseController = new CourseController();
    $result = $courseController->addCourse();
    $message = $result['message'];
    $messageType = $result['success'] ? 'success' : 'error';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>e-lite | Ajouter un Cours</title>
    <link rel="stylesheet" href="../assets/index.css">
</head>
<body>
<script src="../assets/js/index.js"></script>

<style>
    .form-error { border-color: #ff4757 !important; background-color: rgba(255, 71, 87, 0.05); }
    .error-msg { color: #ff4757; font-size: 0.875rem; margin-top: 0.5rem; }
</style>

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

<main style="padding-top: 5rem;">
    <div class="form-container">
        <div class="section-header">
            <h1>Ajouter un Nouveau Cours</h1>
            <p>Remplissez tous les champs requis pour créer un nouveau cours</p>
        </div>

        <?php if ($message): ?>
            <div class="alert <?php echo $messageType === 'success' ? 'alert-success' : 'alert-error'; ?>">
                <span>ℹ️</span>
                <p><?php echo htmlspecialchars($message); ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <!-- Titre et Niveau -->
            <div class="form-row">
                <div class="form-group">
                    <label for="titre" class="form-label">Titre du cours <span class="required">*</span></label>
                    <input type="text" id="titre" name="titre" class="form-control" 
                       required placeholder="Ex: Python Avancé" minlength="3">
                </div>
                <div class="form-group">
                    <label for="niveau" class="form-label">Niveau <span class="required">*</span></label>
                    <select id="niveau" name="niveau" class="form-control" required>
                        <option value="">-- Sélectionner --</option>
                        <option value="débutant">Débutant</option>
                        <option value="intermédiaire">Intermédiaire</option>
                        <option value="avancé">Avancé</option>
                        <option value="expert">Expert</option>
                    </select>
                </div>
            </div>

            <!-- Durée et Prix -->
            <div class="form-row">
                <div class="form-group">
                    <label for="duree" class="form-label">Durée (en heures) <span class="required">*</span></label>
                    <input type="number" id="duree" name="duree" class="form-control" 
                       required placeholder="Durée en heures" min="1">
                </div>
                <div class="form-group">
                    <label for="prix" class="form-label">Prix (en €)</label>
                    <input type="number" id="prix" name="prix" class="form-control" 
                       placeholder="Prix en €" min="0" step="0.01">
                </div>
            </div>

            <!-- Langue et Statut -->
            <div class="form-row">
                <div class="form-group">
                    <label for="langue" class="form-label">Langue</label>
                    <select id="langue" name="langue" class="form-control">
                        <option value="fr">Français</option>
                        <option value="en">Anglais</option>
                        <option value="es">Espagnol</option>
                        <option value="de">Allemand</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="statut" class="form-label">Statut</label>
                    <select id="statut" name="statut" class="form-control">
                        <option value="actif">Actif</option>
                        <option value="inactif">Inactif</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>
            </div>

            <!-- Description -->
            <div class="form-group">
                <label for="description" class="form-label">Description <span class="required">*</span></label>
                <textarea id="description" name="description" class="form-control" 
                   required placeholder="Décrivez le cours..." minlength="10" rows="4"></textarea>
            </div>

            <!-- Objectifs -->
            <div class="form-group">
                <label for="objectifs" class="form-label">Objectifs d'apprentissage</label>
                <textarea id="objectifs" name="objectifs" class="form-control" 
                   placeholder="Listez les objectifs pédagogiques du cours..."></textarea>
            </div>

            <!-- Prérequis -->
            <div class="form-group">
                <label for="prerequis" class="form-label">Prérequis</label>
                <textarea id="prerequis" name="prerequis" class="form-control" 
                   placeholder="Les prérequis pour suivre ce cours..."></textarea>
            </div>

            <!-- Image -->
            <div class="form-group">
                <label for="image" class="form-label">Image du cours</label>
                <input type="file" id="image" name="image" class="form-control" 
                   accept=".jpg,.jpeg,.png">
                <small style="color: var(--light-gray); margin-top: 0.4rem; display: block;">Formats acceptés : jpg, jpeg, png (max 5MB)</small>
            </div>

            <!-- Boutons -->
            <div class="form-actions">
                <button type="submit" class="btn-primary">✓ Ajouter le Cours</button>
                <a href="course_list.php" style="text-decoration: none;">
                    <button type="button" class="btn-outline">↩ Annuler</button>
                </a>
            </div>
        </form>
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