<?php
/**
 * Model - EnrollmentModel.php
 * Gestion des opérations sur la table 'enrollments'
 */

require_once __DIR__ . '/../View/FrontOffice/config.php';

class EnrollmentModel {
    private $pdo;

    /**
     * Constructeur du modèle Enrollment
     */
    public function __construct() {
        $this->pdo = Config::getConnexion();
    }

    /**
     * Récupère tous les enrollments
     * @return array - Liste de tous les enrollments
     */
    public function getAllEnrollments() {
        // ✅ CORRIGÉ : utilisation de TABLE_ENROLLMENT
        $sql = "SELECT e.*, u.nom as nomUtilisateur, c.titre as titreCours 
                FROM " . TABLE_ENROLLMENT . " e
                JOIN " . TABLE_USER . " u ON e.idUser = u.idUser
                JOIN " . TABLE_COURSE . " c ON e.idCourse = c.idCourse
                ORDER BY e.dateInscription DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Récupère les enrollments d'un utilisateur
     * @param int $idUser - L'ID de l'utilisateur
     * @return array - Liste des cours auquel l'utilisateur est inscrit
     */
    public function getEnrollmentsByUser($idUser) {
        // ✅ CORRIGÉ
        $sql = "SELECT e.*, c.titre, c.description, c.duree, c.niveau, c.prix, c.image
                FROM " . TABLE_ENROLLMENT . " e
                JOIN " . TABLE_COURSE . " c ON e.idCourse = c.idCourse
                WHERE e.idUser = ?
                ORDER BY e.dateInscription DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idUser]);
        return $stmt->fetchAll();
    }

    /**
     * Récupère les enrollments d'un cours
     * @param int $idCourse - L'ID du cours
     * @return array - Liste des utilisateurs inscrits au cours
     */
    public function getEnrollmentsByCourse($idCourse) {
        // ✅ CORRIGÉ
        $sql = "SELECT e.*, u.nom, u.email, u.prenom
                FROM " . TABLE_ENROLLMENT . " e
                JOIN " . TABLE_USER . " u ON e.idUser = u.idUser
                WHERE e.idCourse = ?
                ORDER BY e.dateInscription DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idCourse]);
        return $stmt->fetchAll();
    }

    /**
     * Récupère un enrollment par son ID
     * @param int $idEnrollment - L'ID de l'enrollment
     * @return array - Les données de l'enrollment
     */
    public function getEnrollmentById($idEnrollment) {
        // ✅ CORRIGÉ
        $sql = "SELECT * FROM " . TABLE_ENROLLMENT . " WHERE idEnrollment = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idEnrollment]);
        return $stmt->fetch();
    }

    /**
     * Vérifie si un utilisateur est déjà inscrit à un cours
     * @param int $idUser - L'ID de l'utilisateur
     * @param int $idCourse - L'ID du cours
     * @return bool - true si inscrit, false sinon
     */
    public function isUserEnrolled($idUser, $idCourse) {
        // ✅ CORRIGÉ
        $sql = "SELECT COUNT(*) as count FROM " . TABLE_ENROLLMENT . " WHERE idUser = ? AND idCourse = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idUser, $idCourse]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    /**
     * Ajoute une nouvelle inscription
     * @param array $data - Les données d'inscription
     * @return bool - true si succès, false sinon
     */
    public function addEnrollment($data) {
        // Vérifier d'abord si l'utilisateur n'est pas déjà inscrit
        if ($this->isUserEnrolled($data['idUser'], $data['idCourse'])) {
            return false;
        }

        // ✅ CORRIGÉ
        $sql = "INSERT INTO " . TABLE_ENROLLMENT . " 
                (idUser, idCourse, niveauInitial, objectifPersonnel, engagement, modeAcces, 
                 dateInscription, progression, derniereActivite, tempsTotalPasse, statut, noteFinale, certificatObtenu) 
                VALUES (?, ?, ?, ?, ?, ?, NOW(), 0, NOW(), 0, 'actif', NULL, 0)";
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            $data['idUser'],
            $data['idCourse'],
            $data['niveauInitial'] ?? null,
            $data['objectifPersonnel'] ?? null,
            $data['engagement'] ?? null,
            $data['modeAcces'] ?? 'online'
        ]);
    }

    /**
     * Met à jour un enrollment
     * @param int $idEnrollment - L'ID de l'enrollment
     * @param array $data - Les données à mettre à jour
     * @return bool - true si succès, false sinon
     */
    public function updateEnrollment($idEnrollment, $data) {
        // ✅ CORRIGÉ
        $sql = "UPDATE " . TABLE_ENROLLMENT . " SET 
                niveauInitial = ?, objectifPersonnel = ?, engagement = ?, 
                modeAcces = ?, statut = ?, noteFinale = ?, certificatObtenu = ?
                WHERE idEnrollment = ?";
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            $data['niveauInitial'] ?? null,
            $data['objectifPersonnel'] ?? null,
            $data['engagement'] ?? null,
            $data['modeAcces'] ?? 'online',
            $data['statut'] ?? 'actif',
            $data['noteFinale'] ?? null,
            $data['certificatObtenu'] ?? 0,
            $idEnrollment
        ]);
    }

    /**
     * Met à jour la progression et la dernière activité d'un enrollment
     * @param int $idEnrollment - L'ID de l'enrollment
     * @param int $tempsTotalPasse - Le temps total passé (en secondes)
     * @param int $dureeCours - La durée du cours (en heures)
     * @return bool - true si succès, false sinon
     */
    public function updateProgress($idEnrollment, $tempsTotalPasse, $dureeCours) {
        // Calcul de la progression : (tempsTotalPasse / (duree_cours * 3600)) * 100
        $progression = ($tempsTotalPasse / ($dureeCours * 3600)) * 100;
        $progression = min(100, $progression); // Max 100%

        // ✅ CORRIGÉ
        $sql = "UPDATE " . TABLE_ENROLLMENT . " SET 
                tempsTotalPasse = ?, 
                progression = ?, 
                derniereActivite = NOW()
                WHERE idEnrollment = ?";
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([$tempsTotalPasse, $progression, $idEnrollment]);
    }

    /**
     * Met à jour la dernière activité (appelée à chaque connexion)
     * @param int $idEnrollment - L'ID de l'enrollment
     * @return bool - true si succès, false sinon
     */
    public function updateLastActivity($idEnrollment) {
        // ✅ CORRIGÉ
        $sql = "UPDATE " . TABLE_ENROLLMENT . " SET derniereActivite = NOW() WHERE idEnrollment = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$idEnrollment]);
    }

    /**
     * Supprime une inscription
     * @param int $idEnrollment - L'ID de l'enrollment
     * @return bool - true si succès, false sinon
     */
    public function deleteEnrollment($idEnrollment) {
        // ✅ CORRIGÉ
        $sql = "DELETE FROM " . TABLE_ENROLLMENT . " WHERE idEnrollment = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$idEnrollment]);
    }

    /**
     * Récupère les statistiques d'un utilisateur pour un cours
     * @param int $idUser - L'ID de l'utilisateur
     * @param int $idCourse - L'ID du cours
     * @return array - Statistiques
     */
    public function getStatistiques($idUser, $idCourse) {
        // ✅ CORRIGÉ
        $sql = "SELECT e.*, c.duree, c.titre
                FROM " . TABLE_ENROLLMENT . " e
                JOIN " . TABLE_COURSE . " c ON e.idCourse = c.idCourse
                WHERE e.idUser = ? AND e.idCourse = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idUser, $idCourse]);
        return $stmt->fetch();
    }

    /**
     * Récupère le nombre total d'inscrits pour un cours
     * @param int $idCourse - L'ID du cours
     * @return int - Nombre d'inscrits
     */
    public function getTotalEnrollments($idCourse) {
        // ✅ CORRIGÉ
        $sql = "SELECT COUNT(*) as total FROM " . TABLE_ENROLLMENT . " WHERE idCourse = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idCourse]);
        $result = $stmt->fetch();
        return $result['total'];
    }

    /**
     * Met à jour le statut d'un enrollment
     * @param int $idEnrollment - L'ID de l'enrollment
     * @param string $statut - Le nouveau statut (actif, suspendu, terminé)
     * @return bool - true si succès, false sinon
     */
    public function updateStatus($idEnrollment, $statut) {
        // ✅ CORRIGÉ
        $sql = "UPDATE " . TABLE_ENROLLMENT . " SET statut = ? WHERE idEnrollment = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$statut, $idEnrollment]);
    }
}
?>