<?php
require_once __DIR__ . '/../../config/config.php';

class CourseCrud
{
    public static function create($data) {
        $pdo = Config::getConnexion();
        // TODO : Insérer un cours (titre, description, niveau, durée, statut, langue, prix, image, objectifs, prerequis)
        // $stmt = $pdo->prepare("INSERT INTO course (...) VALUES (...)");
        // $stmt->execute([...]);
        // return $pdo->lastInsertId();
    }

    public static function getById($id) {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("SELECT * FROM course WHERE idCourse = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function update($id, $data) {
        $pdo = Config::getConnexion();
        // TODO : Mise à jour
    }

    public static function delete($id) {
        $pdo = Config::getConnexion();
        // TODO : Suppression ou archivage (statut = 'archivé')
    }

    public static function getAll($filters = []) {
        $pdo = Config::getConnexion();
        // TODO : Filtres (niveau, langue, statut, etc.)
        $sql = "SELECT * FROM course WHERE 1=1";
        // Ajouter conditions dynamiquement
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll();
    }
}
?>