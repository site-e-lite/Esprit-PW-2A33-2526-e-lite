<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Utils/Validator.php';
require_once __DIR__ . '/../Utils/PermissionHelper.php';

class CourseController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Config::getInstance()->getConnexion();
    }

    /**
     * Get courses for a specific teacher (only their own courses)
     * @param int $teacherId Teacher ID
     * @return array Array of courses taught by this teacher
     */
    public function getTeacherCourses(int $teacherId): array
    {
        try {
            $courseIds = PermissionHelper::getTeacherCourses($teacherId);
            if (empty($courseIds)) {
                return [];
            }

            $placeholders = implode(',', array_fill(0, count($courseIds), '?'));
            $sql = "SELECT idCourse, titre, description, niveau, duree, statut, langue, prix, image, objectifs, prerequis 
                    FROM course 
                    WHERE idCourse IN ($placeholders) 
                    ORDER BY titre ASC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($courseIds);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error fetching teacher courses: " . $e->getMessage());
            return [];
        }
    }

    /**
     * List courses with permission check for teachers
     * Teachers only see their own courses; admins see all
     * @param int|null $userId User ID (optional, for permission check)
     * @return array Array of courses
     */
    public function listForUser(?int $userId = null): array
    {
        // If no user specified, return all published courses
        if ($userId === null) {
            return $this->listPublished();
        }

        // Check if user is admin
        if (PermissionHelper::isAdmin($userId)) {
            return $this->listAll();
        }

        // Check if user is teacher
        if (PermissionHelper::isTeacher($userId)) {
            return $this->getTeacherCourses($userId);
        }

        // Regular users/students see published courses
        return $this->listPublished();
    }

    /**
     * Check if a user has permission to edit a course
     * @param int $userId User ID
     * @param int $courseId Course ID
     * @return bool True if user can edit the course
     */
    public function canEditCourse(int $userId, int $courseId): bool
    {
        // Admins can edit any course
        if (PermissionHelper::isAdmin($userId)) {
            return true;
        }

        // Teachers can only edit their own courses
        if (PermissionHelper::isTeacher($userId)) {
            return PermissionHelper::isTeacherOfCourse($userId, $courseId);
        }

        return false;
    }

    /**
     * Check if a user has permission to delete a course
     * @param int $userId User ID
     * @param int $courseId Course ID
     * @return bool True if user can delete the course
     */
    public function canDeleteCourse(int $userId, int $courseId): bool
    {
        // Only admins can delete courses
        return PermissionHelper::isAdmin($userId);
    }

    /**
     * Get course details with permission verification
     * @param int $courseId Course ID
     * @param int|null $userId User ID for access verification
     * @return array|null Course data if accessible, null otherwise
     */
    public function getByIdWithAccess(int $courseId, ?int $userId = null): ?array
    {
        $course = $this->getById($courseId);
        if ($course === null) {
            return null;
        }

        // If no user specified, check if course is published
        if ($userId === null) {
            return ($course['statut'] === 'publie') ? $course : null;
        }

        // Admin can view any course
        if (PermissionHelper::isAdmin($userId)) {
            return $course;
        }

        // Teachers can view their own courses
        if (PermissionHelper::isTeacher($userId) && PermissionHelper::isTeacherOfCourse($userId, $courseId)) {
            return $course;
        }

        // Students can only view published courses they're enrolled in or published courses
        if (PermissionHelper::isStudent($userId)) {
            // For students: can view if published, or if enrolled
            if ($course['statut'] === 'publie') {
                return $course;
            }
            if (PermissionHelper::isEnrolledInCourse($userId, $courseId)) {
                return $course;
            }
        }

        return null;
    }

    public function listAll(): array
    {
        $sql = 'SELECT idCourse, titre, description, niveau, duree, statut, langue, prix, image, objectifs, prerequis FROM course ORDER BY idCourse DESC';
        return $this->db->query($sql)->fetchAll();
    }

    public function listPublished(): array
    {
        $sql = 'SELECT idCourse, titre, description, niveau, duree, statut, langue, prix, image, objectifs, prerequis FROM course WHERE statut = :statut ORDER BY idCourse DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['statut' => 'publie']);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $sql = 'SELECT idCourse, titre, description, niveau, duree, statut, langue, prix, image, objectifs, prerequis FROM course WHERE idCourse = :idCourse';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['idCourse' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function add(array $data): array
    {
        // Permission check: only admins and teachers can add courses
        $userId = PermissionHelper::getAuthenticatedUserId();
        if (!$userId || (!PermissionHelper::isAdmin($userId) && !PermissionHelper::isTeacher($userId))) {
            return ['success' => false, 'errors' => ['permission' => 'Non autorisé'], 'message' => 'Vous n\'avez pas la permission d\'ajouter un cours.'];
        }

        Validator::reset();
        $this->validate($data);
        if (Validator::hasErrors()) {
            return ['success' => false, 'errors' => Validator::getErrors(), 'message' => 'Veuillez corriger les erreurs du formulaire.'];
        }

        $sql = 'INSERT INTO course (titre, description, niveau, duree, statut, langue, prix, image, objectifs, prerequis)
                VALUES (:titre, :description, :niveau, :duree, :statut, :langue, :prix, :image, :objectifs, :prerequis)';
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'titre' => trim((string)$data['titre']),
            'description' => trim((string)$data['description']),
            'niveau' => (string)$data['niveau'],
            'duree' => (int)$data['duree'],
            'statut' => (string)$data['statut'],
            'langue' => trim((string)$data['langue']),
            'prix' => (float)$data['prix'],
            'image' => trim((string)($data['image'] ?? '')) !== '' ? trim((string)$data['image']) : null,
            'objectifs' => trim((string)($data['objectifs'] ?? '')) !== '' ? trim((string)$data['objectifs']) : null,
            'prerequis' => trim((string)($data['prerequis'] ?? '')) !== '' ? trim((string)$data['prerequis']) : null,
        ]);

        if ($result) {
            $courseId = $this->db->lastInsertId();
            
            // Assign course to teacher if they created it (not admin)
            if (PermissionHelper::isTeacher($userId)) {
                PermissionHelper::assignTeacherToCourse($userId, $courseId);
            }

            return ['success' => true, 'errors' => [], 'message' => 'Cours ajouté avec succès.'];
        }

        return ['success' => false, 'errors' => [], 'message' => 'Erreur lors de l\'ajout du cours.'];
    }

    public function update(int $id, array $data): array
    {
        // Permission check
        $userId = PermissionHelper::getAuthenticatedUserId();
        if (!$userId) {
            return ['success' => false, 'errors' => ['permission' => 'Non authentifié'], 'message' => 'Vous devez être connecté pour modifier un cours.'];
        }

        if (!$this->canEditCourse($userId, $id)) {
            return ['success' => false, 'errors' => ['permission' => 'Non autorisé'], 'message' => 'Vous n\'avez pas la permission de modifier ce cours.'];
        }

        if ($this->getById($id) === null) {
            return ['success' => false, 'errors' => ['id' => 'Cours introuvable.'], 'message' => 'Cours introuvable.'];
        }

        Validator::reset();
        $this->validate($data);
        if (Validator::hasErrors()) {
            return ['success' => false, 'errors' => Validator::getErrors(), 'message' => 'Veuillez corriger les erreurs du formulaire.'];
        }

        $sql = 'UPDATE course
                SET titre = :titre,
                    description = :description,
                    niveau = :niveau,
                    duree = :duree,
                    statut = :statut,
                    langue = :langue,
                    prix = :prix,
                    image = :image,
                    objectifs = :objectifs,
                    prerequis = :prerequis
                WHERE idCourse = :idCourse';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'titre' => trim((string)$data['titre']),
            'description' => trim((string)$data['description']),
            'niveau' => (string)$data['niveau'],
            'duree' => (int)$data['duree'],
            'statut' => (string)$data['statut'],
            'langue' => trim((string)$data['langue']),
            'prix' => (float)$data['prix'],
            'image' => trim((string)($data['image'] ?? '')) !== '' ? trim((string)$data['image']) : null,
            'objectifs' => trim((string)($data['objectifs'] ?? '')) !== '' ? trim((string)$data['objectifs']) : null,
            'prerequis' => trim((string)($data['prerequis'] ?? '')) !== '' ? trim((string)$data['prerequis']) : null,
            'idCourse' => $id,
        ]);

        return ['success' => true, 'errors' => [], 'message' => 'Cours modifié avec succès.'];
    }

    public function delete(int $id): array
    {
        // Permission check: only admins can delete
        $userId = PermissionHelper::getAuthenticatedUserId();
        if (!$userId) {
            return ['success' => false, 'errors' => ['permission' => 'Non authentifié'], 'message' => 'Vous devez être connecté pour supprimer un cours.'];
        }

        if (!$this->canDeleteCourse($userId, $id)) {
            return ['success' => false, 'errors' => ['permission' => 'Non autorisé'], 'message' => 'Seul un administrateur peut supprimer un cours.'];
        }

        $sql = 'DELETE FROM course WHERE idCourse = :idCourse';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['idCourse' => $id]);

        if ($stmt->rowCount() === 0) {
            return ['success' => false, 'errors' => ['id' => 'Cours introuvable.'], 'message' => 'Cours introuvable.'];
        }

        return ['success' => true, 'errors' => [], 'message' => 'Cours supprimé avec succès.'];
    }

    private function validate(array $data): void
    {
        Validator::required('titre', $data['titre'] ?? '', 'Titre');
        Validator::string('titre', $data['titre'] ?? '', 'Titre', 3, 100);

        Validator::required('description', $data['description'] ?? '', 'Description');
        Validator::string('description', $data['description'] ?? '', 'Description', 10, 5000);

        Validator::required('niveau', $data['niveau'] ?? '', 'Niveau');
        Validator::inArray('niveau', $data['niveau'] ?? '', ['debutant', 'intermediaire', 'avance'], 'Niveau');

        Validator::required('duree', $data['duree'] ?? '', 'Durée');
        Validator::integer('duree', $data['duree'] ?? '', 'Durée', 1, 2000);

        Validator::required('statut', $data['statut'] ?? '', 'Statut');
        Validator::inArray('statut', $data['statut'] ?? '', ['brouillon', 'publie', 'archive'], 'Statut');

        Validator::required('langue', $data['langue'] ?? '', 'Langue');
        Validator::string('langue', $data['langue'] ?? '', 'Langue', 2, 30);

        Validator::required('prix', $data['prix'] ?? 0, 'Prix');
        Validator::number('prix', $data['prix'] ?? 0, 'Prix', 0, 999999);

        Validator::url('image', $data['image'] ?? '', 'Image');

        if (trim((string)($data['objectifs'] ?? '')) !== '') {
            Validator::string('objectifs', $data['objectifs'], 'Objectifs', 3, 5000);
        }

        if (trim((string)($data['prerequis'] ?? '')) !== '') {
            Validator::string('prerequis', $data['prerequis'], 'Prérequis', 3, 5000);
        }
    }
}
?>
