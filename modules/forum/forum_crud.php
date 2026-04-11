<?php
require_once __DIR__ . '/../../config/config.php';

class ForumCrud
{
    public static function create($data) {
        $pdo = Config::getConnexion();
        // TODO : titre, description, idCourse (dateCreation auto)
    }

    public static function getById($id) {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("SELECT * FROM forum WHERE idForum = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function getByCourse($courseId) {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("SELECT * FROM forum WHERE idCourse = ?");
        $stmt->execute([$courseId]);
        return $stmt->fetchAll();
    }
}
?>