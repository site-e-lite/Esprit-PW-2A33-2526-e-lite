<?php
/**
 * Controller - CourseController.php
 * Contrôleur pour la gestion des cours
 */

require_once __DIR__ . '/../Model/CourseModel.php';

class CourseController {
    private $courseModel;

    /**
     * Constructeur du contrôleur Course
     */
    public function __construct() {
        $this->courseModel = new CourseModel();
    }

    /**
     * Récupère tous les cours
     * @return array - Liste des cours
     */
    public function getAllCourses() {
        return $this->courseModel->getAllCourses();
    }

    /**
     * Récupère un cours par son ID
     * @param int $idCourse - L'ID du cours
     * @return array - Les données du cours
     */
    public function getCourseById($idCourse) {
        if (!is_numeric($idCourse) || $idCourse <= 0) {
            return null;
        }
        return $this->courseModel->getCourseById($idCourse);
    }

    /**
     * Ajoute un nouveau cours (BackOffice)
     * @return array - Tableau avec 'success' (bool) et 'message' (string)
     */
    public function addCourse() {
        // Valider les données avant traitement
        $validation = $this->validateCourseData($_POST);
        if (!$validation['valid']) {
            return ['success' => false, 'message' => 'Erreur: ' . implode(', ', $validation['errors'])];
        }

        $data = [
            'titre' => trim($_POST['titre']),
            'description' => trim($_POST['description']),
            'niveau' => trim($_POST['niveau']),
            'duree' => (int)$_POST['duree'],
            'statut' => $_POST['statut'] ?? 'actif',
            'langue' => $_POST['langue'] ?? 'fr',
            'prix' => (float)($_POST['prix'] ?? 0),
            'objectifs' => $_POST['objectifs'] ?? null,
            'prerequis' => $_POST['prerequis'] ?? null
        ];

        // Gestion de l'upload d'image
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../../assets/images/courses/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = time() . '_' . basename($_FILES['image']['name']);
            $uploadPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $data['image'] = 'assets/images/courses/' . $fileName;
            }
        }

        try {
            if ($this->courseModel->addCourse($data)) {
                return ['success' => true, 'message' => 'Cours ajouté avec succès'];
            } else {
                return ['success' => false, 'message' => 'Erreur lors de l\'ajout du cours'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erreur : ' . $e->getMessage()];
        }
    }

    /**
     * Met à jour un cours (BackOffice)
     * @param int $idCourse - L'ID du cours
     * @return array - Tableau avec 'success' (bool) et 'message' (string)
     */
    public function updateCourse($idCourse) {
        if (!is_numeric($idCourse) || $idCourse <= 0) {
            return ['success' => false, 'message' => 'ID cours invalide'];
        }

        // Récupérer le cours existant
        $course = $this->courseModel->getCourseById($idCourse);
        if (!$course) {
            return ['success' => false, 'message' => 'Cours non trouvé'];
        }

        $data = [
            'titre' => trim($_POST['titre'] ?? $course['titre']),
            'description' => trim($_POST['description'] ?? $course['description']),
            'niveau' => trim($_POST['niveau'] ?? $course['niveau']),
            'duree' => (int)($_POST['duree'] ?? $course['duree']),
            'statut' => $_POST['statut'] ?? $course['statut'],
            'langue' => $_POST['langue'] ?? $course['langue'],
            'prix' => (float)($_POST['prix'] ?? $course['prix']),
            'objectifs' => $_POST['objectifs'] ?? $course['objectifs'],
            'prerequis' => $_POST['prerequis'] ?? $course['prerequis'],
            'image' => $course['image']
        ];

        // Gestion de l'upload d'une nouvelle image
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../../assets/images/courses/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Supprimer l'ancienne image
            if ($course['image'] && file_exists($course['image'])) {
                unlink($course['image']);
            }

            $fileName = time() . '_' . basename($_FILES['image']['name']);
            $uploadPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                $data['image'] = 'assets/images/courses/' . $fileName;
            }
        }

        try {
            if ($this->courseModel->updateCourse($idCourse, $data)) {
                return ['success' => true, 'message' => 'Cours mis à jour avec succès'];
            } else {
                return ['success' => false, 'message' => 'Erreur lors de la mise à jour'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erreur : ' . $e->getMessage()];
        }
    }

    /**
     * Supprime un cours (BackOffice)
     * @param int $idCourse - L'ID du cours
     * @return array - Tableau avec 'success' (bool) et 'message' (string)
     */
    public function deleteCourse($idCourse) {
        if (!is_numeric($idCourse) || $idCourse <= 0) {
            return ['success' => false, 'message' => 'ID cours invalide'];
        }

        $course = $this->courseModel->getCourseById($idCourse);
        if (!$course) {
            return ['success' => false, 'message' => 'Cours non trouvé'];
        }

        try {
            // Supprimer l'image si elle existe
            if ($course['image'] && file_exists($course['image'])) {
                unlink($course['image']);
            }

            if ($this->courseModel->deleteCourse($idCourse)) {
                return ['success' => true, 'message' => 'Cours supprimé avec succès'];
            } else {
                return ['success' => false, 'message' => 'Erreur lors de la suppression'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erreur : ' . $e->getMessage()];
        }
    }

    /**
     * Récupère les cours recommandés basés sur IA (FrontOffice)
     * @param string $niveauInitial - Niveau initial de l'utilisateur
     * @param string $objectifPersonnel - Objectif personnel de l'utilisateur
     * @return array - Jusqu'à 3 cours recommandés
     */
    public function getRecommendedCourses($niveauInitial, $objectifPersonnel) {
        return $this->courseModel->getRecommendedCourses($niveauInitial, $objectifPersonnel);
    }

    /**
     * Récupère les cours avec pagination (FrontOffice)
     * @param int $page - Numéro de page (par défaut 1)
     * @param int $limit - Nombre de cours par page (par défaut 10)
     * @return array - Tableau avec 'courses' et 'totalPages'
     */
    public function getCoursesPaginated($page = 1, $limit = 10) {
        $page = max(1, (int)$page);
        $offset = ($page - 1) * $limit;
        
        $courses = $this->courseModel->getCoursesPaginated($limit, $offset);
        $total = $this->courseModel->getTotalCourses();
        $totalPages = ceil($total / $limit);

        return [
            'courses' => $courses,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalCourses' => $total
        ];
    }

    /**
     * Valide les données d'un cours
     * @param array $data - Les données à valider
     * @return array - ['valid' => bool, 'errors' => array]
     */
    public function validateCourseData($data) {
        $errors = [];

        // Validation Titre
        if (!isset($data['titre']) || strlen(trim($data['titre'])) < 3) {
            $errors['titre'] = 'Titre: minimum 3 caractères';
        }

        // Validation Description
        if (!isset($data['description']) || strlen(trim($data['description'])) < 10) {
            $errors['description'] = 'Description: minimum 10 caractères';
        }

        // Validation Durée
        if (!isset($data['duree']) || !is_numeric($data['duree']) || $data['duree'] < 1) {
            $errors['duree'] = 'Durée: nombre positif (min 1) obligatoire';
        }

        // Validation Prix
        if (isset($data['prix']) && (!is_numeric($data['prix']) || $data['prix'] < 0)) {
            $errors['prix'] = 'Prix: nombre positif ou zéro obligatoire';
        }

        // Validation Niveau
        if (!isset($data['niveau']) || empty($data['niveau'])) {
            $errors['niveau'] = 'Niveau: obligatoire';
        }

        // Validation Image (si fournie)
        if (isset($data['image']) && is_array($data['image'])) {
            $file = $data['image'];
            $validTypes = ['image/jpeg', 'image/png'];
            $maxSize = 20 * 1024 * 1024; // 20MB

            if ($file['error'] === UPLOAD_ERR_OK) {
                if (!in_array($file['type'], $validTypes)) {
                    $errors['image'] = 'Image: JPG ou PNG uniquement';
                }
                if ($file['size'] > $maxSize) {
                    $errors['image'] = 'Image: taille max 20MB';
                }
            }
        }

        return [
            'valid' => count($errors) === 0,
            'errors' => $errors
        ];
    }
}
?>

