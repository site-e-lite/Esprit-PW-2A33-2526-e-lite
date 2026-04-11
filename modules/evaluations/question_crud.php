<?php
require_once __DIR__ . '/../../config/config.php';

class QuestionCrud
{
    public static function create($data) {
        $pdo = Config::getConnexion();
        // TODO : Gérer les types (QCM ou vrai/faux)
        // Pour QCM : choixA, choixB, choixC, choixD, bonneReponse (A,B,C,D)
        // Pour vrai_faux : choixA="Vrai", choixB="Faux", bonneReponse="vrai" ou "faux"
    }

    public static function getByQuiz($quizId) {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("SELECT * FROM question WHERE idQuiz = ?");
        $stmt->execute([$quizId]);
        return $stmt->fetchAll();
    }

    // TODO : update, delete
}
?>