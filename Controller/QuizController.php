<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Quiz.php';

class QuizController {
    public function afficherQuizs() {
        $sql = "SELECT * FROM quiz ORDER BY idQuiz DESC";
        $db = Config::getConnexion();
        try {
            $liste = $db->query($sql);
            return $liste;
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function afficherQuizsActifs() {
        $sql = "SELECT * FROM quiz WHERE LOWER(TRIM(statut)) = 'actif' ORDER BY idQuiz DESC";
        $db = Config::getConnexion();
        try {
            $liste = $db->query($sql);
            return $liste;
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function addQuiz($quiz) {
        $sql = "INSERT INTO quiz (titre, duree, seuilReussite, niveau, statut, idCourse) VALUES (:titre, :duree, :seuilReussite, :niveau, :statut, :idCourse)";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'titre' => $quiz->getTitre(),
                'duree' => $quiz->getDuree(),
                'seuilReussite' => $quiz->getSeuilReussite(),
                'niveau' => $quiz->getNiveau(),
                'statut' => $quiz->getStatut(),
                'idCourse' => $quiz->getIdCourse()
            ]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function updateQuiz($quiz, $id) {
        $sql = "UPDATE quiz SET titre = :titre, duree = :duree, seuilReussite = :seuilReussite, niveau = :niveau, statut = :statut, idCourse = :idCourse WHERE idQuiz = :idQuiz";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'titre' => $quiz->getTitre(),
                'duree' => $quiz->getDuree(),
                'seuilReussite' => $quiz->getSeuilReussite(),
                'niveau' => $quiz->getNiveau(),
                'statut' => $quiz->getStatut(),
                'idCourse' => $quiz->getIdCourse(),
                'idQuiz' => $id
            ]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function deleteQuiz($id) {
        $db = Config::getConnexion();
        try {
            $db->beginTransaction();
            $query = $db->prepare("DELETE FROM question WHERE idQuiz = :idQuiz");
            $query->execute(['idQuiz' => $id]);
            $query = $db->prepare("DELETE FROM quiz WHERE idQuiz = :idQuiz");
            $query->execute(['idQuiz' => $id]);
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function getQuizById($id) {
        $sql = "SELECT * FROM quiz WHERE idQuiz = :idQuiz";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['idQuiz' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }
}
?>
