<?php
require_once __DIR__ . '/../../config/config.php';

class VirtualClassCrud
{
    public static function create($data) {
        $pdo = Config::getConnexion();
        // TODO : titre, description, lienAcces, plateforme, idCourse
    }

    public static function getById($id) {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("SELECT * FROM virtualclass WHERE idClass = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function getByCourse($courseId) {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("SELECT * FROM virtualclass WHERE idCourse = ?");
        $stmt->execute([$courseId]);
        return $stmt->fetchAll();
    }
}
?>