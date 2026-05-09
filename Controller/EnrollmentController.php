<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Utils/Validator.php';

class EnrollmentController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Config::getInstance()->getConnexion();
    }

    public function listAll(): array
    {
        $sql = 'SELECT idEnrollment, idUser, idCourse, niveauInitial, objectifPersonnel, engagement, modeAcces, dateInscription, progression, derniereActivite, tempsTotalPasse, statut, noteFinale, certificatObtenu FROM enrollment ORDER BY idEnrollment DESC';
        return $this->db->query($sql)->fetchAll();
    }

    public function listByUser(int $idUser): array
    {
        $sql = 'SELECT idEnrollment, idUser, idCourse, niveauInitial, objectifPersonnel, engagement, modeAcces, dateInscription, progression, derniereActivite, tempsTotalPasse, statut, noteFinale, certificatObtenu
                FROM enrollment
                WHERE idUser = :idUser
                ORDER BY dateInscription DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['idUser' => $idUser]);
        return $stmt->fetchAll();
    }

    public function listByCourse(int $idCourse): array
    {
        $sql = 'SELECT idEnrollment, idUser, idCourse, niveauInitial, objectifPersonnel, engagement, modeAcces, dateInscription, progression, derniereActivite, tempsTotalPasse, statut, noteFinale, certificatObtenu
                FROM enrollment
                WHERE idCourse = :idCourse
                ORDER BY dateInscription DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['idCourse' => $idCourse]);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $sql = 'SELECT idEnrollment, idUser, idCourse, niveauInitial, objectifPersonnel, engagement, modeAcces, dateInscription, progression, derniereActivite, tempsTotalPasse, statut, noteFinale, certificatObtenu FROM enrollment WHERE idEnrollment = :idEnrollment';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['idEnrollment' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function add(array $data): array
    {
        Validator::reset();
        $this->validate($data);
        if (Validator::hasErrors()) {
            return ['success' => false, 'errors' => Validator::getErrors(), 'message' => 'Veuillez corriger les erreurs du formulaire.'];
        }

        $sql = 'INSERT INTO enrollment
                (idUser, idCourse, niveauInitial, objectifPersonnel, engagement, modeAcces, progression, derniereActivite, tempsTotalPasse, statut, noteFinale, certificatObtenu)
                VALUES
                (:idUser, :idCourse, :niveauInitial, :objectifPersonnel, :engagement, :modeAcces, :progression, :derniereActivite, :tempsTotalPasse, :statut, :noteFinale, :certificatObtenu)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'idUser' => (int)$data['idUser'],
            'idCourse' => (int)$data['idCourse'],
            'niveauInitial' => (string)$data['niveauInitial'],
            'objectifPersonnel' => trim((string)$data['objectifPersonnel']),
            'engagement' => (int)$data['engagement'],
            'modeAcces' => (string)$data['modeAcces'],
            'progression' => (int)$data['progression'],
            'derniereActivite' => trim((string)($data['derniereActivite'] ?? '')) !== '' ? (string)$data['derniereActivite'] : null,
            'tempsTotalPasse' => (int)$data['tempsTotalPasse'],
            'statut' => (string)$data['statut'],
            'noteFinale' => trim((string)($data['noteFinale'] ?? '')) !== '' ? (float)$data['noteFinale'] : null,
            'certificatObtenu' => !empty($data['certificatObtenu']) ? 1 : 0,
        ]);

        return ['success' => true, 'errors' => [], 'message' => 'Inscription ajoutée avec succès.'];
    }

    public function update(int $id, array $data): array
    {
        if ($this->getById($id) === null) {
            return ['success' => false, 'errors' => ['id' => 'Inscription introuvable.'], 'message' => 'Inscription introuvable.'];
        }

        Validator::reset();
        $this->validate($data);
        if (Validator::hasErrors()) {
            return ['success' => false, 'errors' => Validator::getErrors(), 'message' => 'Veuillez corriger les erreurs du formulaire.'];
        }

        $sql = 'UPDATE enrollment
                SET idUser = :idUser,
                    idCourse = :idCourse,
                    niveauInitial = :niveauInitial,
                    objectifPersonnel = :objectifPersonnel,
                    engagement = :engagement,
                    modeAcces = :modeAcces,
                    progression = :progression,
                    derniereActivite = :derniereActivite,
                    tempsTotalPasse = :tempsTotalPasse,
                    statut = :statut,
                    noteFinale = :noteFinale,
                    certificatObtenu = :certificatObtenu
                WHERE idEnrollment = :idEnrollment';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'idUser' => (int)$data['idUser'],
            'idCourse' => (int)$data['idCourse'],
            'niveauInitial' => (string)$data['niveauInitial'],
            'objectifPersonnel' => trim((string)$data['objectifPersonnel']),
            'engagement' => (int)$data['engagement'],
            'modeAcces' => (string)$data['modeAcces'],
            'progression' => (int)$data['progression'],
            'derniereActivite' => trim((string)($data['derniereActivite'] ?? '')) !== '' ? (string)$data['derniereActivite'] : null,
            'tempsTotalPasse' => (int)$data['tempsTotalPasse'],
            'statut' => (string)$data['statut'],
            'noteFinale' => trim((string)($data['noteFinale'] ?? '')) !== '' ? (float)$data['noteFinale'] : null,
            'certificatObtenu' => !empty($data['certificatObtenu']) ? 1 : 0,
            'idEnrollment' => $id,
        ]);

        return ['success' => true, 'errors' => [], 'message' => 'Inscription modifiée avec succès.'];
    }

    public function delete(int $id): array
    {
        $stmt = $this->db->prepare('DELETE FROM enrollment WHERE idEnrollment = :idEnrollment');
        $stmt->execute(['idEnrollment' => $id]);

        if ($stmt->rowCount() === 0) {
            return ['success' => false, 'errors' => ['id' => 'Inscription introuvable.'], 'message' => 'Inscription introuvable.'];
        }

        return ['success' => true, 'errors' => [], 'message' => 'Inscription supprimée avec succès.'];
    }

    private function validate(array $data): void
    {
        Validator::required('idUser', $data['idUser'] ?? '', 'Utilisateur');
        Validator::integer('idUser', $data['idUser'] ?? '', 'Utilisateur', 1);

        Validator::required('idCourse', $data['idCourse'] ?? '', 'Cours');
        Validator::integer('idCourse', $data['idCourse'] ?? '', 'Cours', 1);

        Validator::required('niveauInitial', $data['niveauInitial'] ?? '', 'Niveau initial');
        Validator::inArray('niveauInitial', $data['niveauInitial'] ?? '', ['debutant', 'intermediaire', 'avance'], 'Niveau initial');

        Validator::required('objectifPersonnel', $data['objectifPersonnel'] ?? '', 'Objectif personnel');
        Validator::string('objectifPersonnel', $data['objectifPersonnel'] ?? '', 'Objectif personnel', 5, 5000);

        Validator::required('engagement', $data['engagement'] ?? '', 'Engagement');
        Validator::integer('engagement', $data['engagement'] ?? '', 'Engagement', 1, 100);

        Validator::required('modeAcces', $data['modeAcces'] ?? '', 'Mode d\'accès');
        Validator::inArray('modeAcces', $data['modeAcces'] ?? '', ['gratuit', 'payant'], 'Mode d\'accès');

        Validator::required('progression', $data['progression'] ?? '', 'Progression');
        Validator::integer('progression', $data['progression'] ?? '', 'Progression', 0, 100);

        Validator::required('tempsTotalPasse', $data['tempsTotalPasse'] ?? '', 'Temps total passé');
        Validator::integer('tempsTotalPasse', $data['tempsTotalPasse'] ?? '', 'Temps total passé', 0);

        Validator::required('statut', $data['statut'] ?? '', 'Statut');
        Validator::inArray('statut', $data['statut'] ?? '', ['actif', 'termine', 'abandonne'], 'Statut');

        if (trim((string)($data['noteFinale'] ?? '')) !== '') {
            Validator::number('noteFinale', $data['noteFinale'], 'Note finale', 0, 100);
        }

        if (isset($data['derniereActivite']) && trim((string)$data['derniereActivite']) !== '') {
            Validator::date('derniereActivite', $data['derniereActivite'], 'Dernière activité');
        }
    }
}
?>
=======
/**
 * Controller - EnrollmentController.php
 * Contrôleur pour la gestion des inscriptions aux cours
 */

require_once __DIR__ . '/../Model/EnrollmentModel.php';
require_once __DIR__ . '/../Model/CourseModel.php';

class EnrollmentController {
    private $enrollmentModel;
    private $courseModel;

    /**
     * Constructeur du contrôleur Enrollment
     */
    public function __construct() {
        $this->enrollmentModel = new EnrollmentModel();
        $this->courseModel = new CourseModel();
    }

    /**
     * Récupère les enrollments d'un utilisateur (FrontOffice)
     * @param int $idUser - L'ID de l'utilisateur
     * @return array - Liste des inscriptions de l'utilisateur
     */
    public function getMyEnrollments($idUser) {
        if (!is_numeric($idUser) || $idUser <= 0) {
            return [];
        }
        return $this->enrollmentModel->getEnrollmentsByUser($idUser);
    }

    /**
     * Inscrit un utilisateur à un cours (FrontOffice)
     * @return array - Tableau avec 'success' (bool) et 'message' (string)
     */
    public function enrollUser() {
        // Vérifier que l'utilisateur est connecté
        if (!isset($_SESSION['idUser'])) {
            return ['success' => false, 'message' => 'Vous devez être connecté pour vous inscrire'];
        }

        // Vérifier les données requises
        if (!isset($_POST['idCourse']) || !is_numeric($_POST['idCourse']) || $_POST['idCourse'] <= 0) {
            return ['success' => false, 'message' => 'ID cours invalide'];
        }

        $idUser = $_SESSION['idUser'];
        $idCourse = (int)$_POST['idCourse'];

        // Vérifier que le cours existe
        $course = $this->courseModel->getCourseById($idCourse);
        if (!$course) {
            return ['success' => false, 'message' => 'Le cours n\'existe pas'];
        }

        // Vérifier que l'utilisateur n'est pas déjà inscrit
        if ($this->enrollmentModel->isUserEnrolled($idUser, $idCourse)) {
            return ['success' => false, 'message' => 'Vous êtes déjà inscrit à ce cours'];
        }

        $data = [
            'idUser' => $idUser,
            'idCourse' => $idCourse,
            'niveauInitial' => $_POST['niveauInitial'] ?? null,
            'objectifPersonnel' => $_POST['objectifPersonnel'] ?? null,
            'engagement' => $_POST['engagement'] ?? null,
            'modeAcces' => $_POST['modeAcces'] ?? 'online'
        ];

        try {
            if ($this->enrollmentModel->addEnrollment($data)) {
                return ['success' => true, 'message' => 'Inscription réussie ! Bienvenue dans le cours.'];
            } else {
                return ['success' => false, 'message' => 'Erreur lors de l\'inscription'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erreur : ' . $e->getMessage()];
        }
    }

    /**
     * Met à jour la progression d'une inscription
     * @param int $idEnrollment - L'ID de l'enrollment
     * @return array - Tableau avec 'success' (bool) et 'message' (string)
     */
    public function updateProgress($idEnrollment) {
        if (!isset($_POST['tempsTotalPasse']) || !is_numeric($_POST['tempsTotalPasse'])) {
            return ['success' => false, 'message' => 'Données invalides'];
        }

        $enrollment = $this->enrollmentModel->getEnrollmentById($idEnrollment);
        if (!$enrollment) {
            return ['success' => false, 'message' => 'Inscription non trouvée'];
        }

        $course = $this->courseModel->getCourseById($enrollment['idCourse']);
        if (!$course) {
            return ['success' => false, 'message' => 'Cours non trouvé'];
        }

        $tempsTotalPasse = (int)$_POST['tempsTotalPasse'];
        $dureeCours = (int)$course['duree'];

        try {
            if ($this->enrollmentModel->updateProgress($idEnrollment, $tempsTotalPasse, $dureeCours)) {
                return ['success' => true, 'message' => 'Progression mise à jour'];
            } else {
                return ['success' => false, 'message' => 'Erreur lors de la mise à jour'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erreur : ' . $e->getMessage()];
        }
    }

    /**
     * Met à jour la dernière activité
     * @param int $idEnrollment - L'ID de l'enrollment
     * @return array - Tableau avec 'success' (bool) et 'message' (string)
     */
    public function updateLastActivity($idEnrollment) {
        if (!is_numeric($idEnrollment) || $idEnrollment <= 0) {
            return ['success' => false, 'message' => 'ID enrollment invalide'];
        }

        try {
            if ($this->enrollmentModel->updateLastActivity($idEnrollment)) {
                return ['success' => true, 'message' => 'Dernière activité mise à jour'];
            } else {
                return ['success' => false, 'message' => 'Erreur lors de la mise à jour'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erreur : ' . $e->getMessage()];
        }
    }

    /**
     * Récupère les statistiques d'un utilisateur pour un cours
     * @param int $idUser - L'ID de l'utilisateur
     * @param int $idCourse - L'ID du cours
     * @return array - Statistiques (progression, tempsPasse, etc.)
     */
    public function getStatistics($idUser, $idCourse) {
        if (!is_numeric($idUser) || !is_numeric($idCourse)) {
            return null;
        }
        return $this->enrollmentModel->getStatistiques($idUser, $idCourse);
    }

    /**
     * Quitte un cours (supprime l'inscription)
     * @param int $idEnrollment - L'ID de l'enrollment
     * @return array - Tableau avec 'success' (bool) et 'message' (string)
     */
    public function unenrollCourse($idEnrollment) {
        if (!is_numeric($idEnrollment) || $idEnrollment <= 0) {
            return ['success' => false, 'message' => 'ID enrollment invalide'];
        }

        $enrollment = $this->enrollmentModel->getEnrollmentById($idEnrollment);
        if (!$enrollment) {
            return ['success' => false, 'message' => 'Inscription non trouvée'];
        }

        // Vérifier que c'est bien l'utilisateur connecté qui se désinscrit
        if ($enrollment['idUser'] !== $_SESSION['idUser']) {
            return ['success' => false, 'message' => 'Vous n\'avez pas la permission de faire cette action'];
        }

        try {
            if ($this->enrollmentModel->deleteEnrollment($idEnrollment)) {
                return ['success' => true, 'message' => 'Vous avez quitté le cours avec succès'];
            } else {
                return ['success' => false, 'message' => 'Erreur lors de la suppression'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erreur : ' . $e->getMessage()];
        }
    }

    /**
     * Récupère tous les enrollments d'un cours (BackOffice)
     * @param int $idCourse - L'ID du cours
     * @return array - Liste des inscriptions au cours
     */
    public function getEnrollmentsByCourse($idCourse) {
        if (!is_numeric($idCourse) || $idCourse <= 0) {
            return [];
        }
        return $this->enrollmentModel->getEnrollmentsByCourse($idCourse);
    }

    /**
     * Récupère le nombre d'inscrits à un cours (BackOffice)
     * @param int $idCourse - L'ID du cours
     * @return int - Nombre d'inscrits
     */
    public function getTotalEnrollments($idCourse) {
        if (!is_numeric($idCourse) || $idCourse <= 0) {
            return 0;
        }
        return $this->enrollmentModel->getTotalEnrollments($idCourse);
    }

    /**
     * Met à jour le statut d'une inscription (BackOffice)
     * @param int $idEnrollment - L'ID de l'enrollment
     * @param string $statut - Le nouveau statut
     * @return array - Tableau avec 'success' (bool) et 'message' (string)
     */
    public function updateEnrollmentStatus($idEnrollment, $statut) {
        if (!in_array($statut, ['actif', 'suspendu', 'terminé'])) {
            return ['success' => false, 'message' => 'Statut invalide'];
        }

        try {
            if ($this->enrollmentModel->updateStatus($idEnrollment, $statut)) {
                return ['success' => true, 'message' => 'Statut mis à jour'];
            } else {
                return ['success' => false, 'message' => 'Erreur lors de la mise à jour'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erreur : ' . $e->getMessage()];
        }
    }
}
?>
