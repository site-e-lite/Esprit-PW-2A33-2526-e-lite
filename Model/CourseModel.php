<?php
/**
 * Model - CourseModel.php
 * Gestion des opérations sur la table 'course'
 */

require_once __DIR__ . '/../View/FrontOffice/config.php';

class CourseModel {
    private $pdo;

    /**
     * Constructeur du modèle Course
     */
    public function __construct() {
        $this->pdo = Config::getConnexion();
    }

    /**
     * Récupère tous les cours disponibles
     * @return array - Liste de tous les cours
     */
    public function getAllCourses() {
        // ✅ CORRIGÉ : utilisation de TABLE_COURSE
        $sql = "SELECT * FROM " . TABLE_COURSE . " ORDER BY titre ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Récupère les cours filtrés par statut
     * @param string $statut - Le statut à filtrer
     * @return array - Liste des cours avec le statut demandé
     */
    public function getCoursesByStatus($statut) {
        // ✅ CORRIGÉ
        $sql = "SELECT * FROM " . TABLE_COURSE . " WHERE statut = ? ORDER BY titre ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$statut]);
        return $stmt->fetchAll();
    }

    /**
     * Récupère un cours par son ID
     * @param int $idCourse - L'ID du cours
     * @return array - Les données du cours ou null
     */
    public function getCourseById($idCourse) {
        // ✅ CORRIGÉ
        $sql = "SELECT * FROM " . TABLE_COURSE . " WHERE idCourse = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idCourse]);
        return $stmt->fetch();
    }

    /**
     * Ajoute un nouveau cours
     * @param array $data - Les données du cours
     * @return bool - true si succès, false sinon
     */
    public function addCourse($data) {
        // ✅ CORRIGÉ
        $sql = "INSERT INTO " . TABLE_COURSE . " (titre, description, niveau, duree, statut, langue, prix, image, objectifs, prerequis) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            $data['titre'],
            $data['description'],
            $data['niveau'],
            $data['duree'],
            $data['statut'] ?? 'actif',
            $data['langue'] ?? 'fr',
            $data['prix'] ?? 0,
            $data['image'] ?? null,
            $data['objectifs'] ?? null,
            $data['prerequis'] ?? null
        ]);
    }

    /**
     * Met à jour un cours
     * @param int $idCourse - L'ID du cours
     * @param array $data - Les données à mettre à jour
     * @return bool - true si succès, false sinon
     */
    public function updateCourse($idCourse, $data) {
        // ✅ CORRIGÉ
        $sql = "UPDATE " . TABLE_COURSE . " 
                SET titre = ?, description = ?, niveau = ?, duree = ?, statut = ?, 
                    langue = ?, prix = ?, image = ?, objectifs = ?, prerequis = ?
                WHERE idCourse = ?";
        $stmt = $this->pdo->prepare($sql);
        
        return $stmt->execute([
            $data['titre'],
            $data['description'],
            $data['niveau'],
            $data['duree'],
            $data['statut'],
            $data['langue'],
            $data['prix'],
            $data['image'] ?? null,
            $data['objectifs'] ?? null,
            $data['prerequis'] ?? null,
            $idCourse
        ]);
    }

    /**
     * Supprime un cours
     * @param int $idCourse - L'ID du cours
     * @return bool - true si succès, false sinon
     */
    public function deleteCourse($idCourse) {
        // ✅ CORRIGÉ
        $sql = "DELETE FROM " . TABLE_COURSE . " WHERE idCourse = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$idCourse]);
    }

    /**
     * Récupère les cours recommandés basés sur le niveau initial et objectif personnel
     * @param string $niveauInitial - Le niveau initial de l'utilisateur
     * @param string $objectifPersonnel - L'objectif personnel de l'utilisateur
     * @return array - Jusqu'à 3 cours recommandés
     */
    public function getRecommendedCourses($niveauInitial, $objectifPersonnel) {
        // ✅ CORRIGÉ
        $sql = "SELECT * FROM " . TABLE_COURSE . " 
                WHERE niveau = ? OR description LIKE ? 
                AND statut = 'actif'
                LIMIT 3";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$niveauInitial, '%' . $objectifPersonnel . '%']);
        return $stmt->fetchAll();
    }

    /**
     * Récupère le nombre total de cours
     * @return int - Nombre de cours
     */
    public function getTotalCourses() {
        // ✅ CORRIGÉ
        $sql = "SELECT COUNT(*) as total FROM " . TABLE_COURSE;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'];
    }

    /**
     * Récupère les cours avec pagination
     * @param int $limit - Nombre de cours par page
     * @param int $offset - Décalage (page * limit)
     * @return array - Liste des cours paginés
     */
    public function getCoursesPaginated($limit = 10, $offset = 0) {
        // ✅ CORRIGÉ
        $sql = "SELECT * FROM " . TABLE_COURSE . " WHERE statut = 'actif' ORDER BY titre ASC LIMIT ? OFFSET ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Recherche des cours par mot-clé
     * @param string $keyword - Mot-clé de recherche
     * @return array - Résultats de recherche
     */
    public function searchCourses($keyword) {
        // ✅ Utiliser TABLE_COURSE constant
        $sql = "SELECT * FROM " . TABLE_COURSE . " 
                WHERE titre LIKE ? 
                   OR description LIKE ? 
                   OR niveau LIKE ?
                ORDER BY titre ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $searchTerm = '%' . $keyword . '%';
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }
}
?>