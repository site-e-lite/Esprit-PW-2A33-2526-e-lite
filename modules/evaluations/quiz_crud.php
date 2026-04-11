<?php
require_once __DIR__ . '/../../config/config.php';

class QuizCrud
{
    public static function create($data) {
        $pdo = Config::getConnexion();
        // TODO : Insertion titre, durée, seuilRéussite, niveau, statut, idCourse
    }

    public static function getById($id) {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("SELECT * FROM quiz WHERE idQuiz = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function getByCourse($courseId) {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("SELECT * FROM quiz WHERE idCourse = ?");
        $stmt->execute([$courseId]);
        return $stmt->fetchAll();
    }

    // TODO : update, delete
}
?>