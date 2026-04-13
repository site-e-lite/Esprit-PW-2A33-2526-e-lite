<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Question.php';

class QuestionController {
    public function afficherQuestions($idQuiz = null) {
        $db = Config::getConnexion();
        try {
            if ($idQuiz !== null) {
                $sql = "SELECT q.*, quiz.titre AS quizTitre FROM question q LEFT JOIN quiz ON q.idQuiz = quiz.idQuiz WHERE q.idQuiz = :idQuiz ORDER BY q.idQuestion DESC";
                $query = $db->prepare($sql);
                $query->execute(['idQuiz' => $idQuiz]);
                return $query;
            }
            $sql = "SELECT q.*, quiz.titre AS quizTitre FROM question q LEFT JOIN quiz ON q.idQuiz = quiz.idQuiz ORDER BY q.idQuestion DESC";
            return $db->query($sql);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function addQuestion($question) {
        $sql = "INSERT INTO question (enonce, type, choixA, choixB, choixC, choixD, bonneReponse, note, explication, idQuiz) VALUES (:enonce, :type, :choixA, :choixB, :choixC, :choixD, :bonneReponse, :note, :explication, :idQuiz)";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'enonce' => $question->getEnonce(),
                'type' => $question->getType(),
                'choixA' => $question->getChoixA(),
                'choixB' => $question->getChoixB(),
                'choixC' => $question->getChoixC(),
                'choixD' => $question->getChoixD(),
                'bonneReponse' => $question->getBonneReponse(),
                'note' => $question->getNote(),
                'explication' => $question->getExplication(),
                'idQuiz' => $question->getIdQuiz()
            ]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function updateQuestion($question, $id) {
        $sql = "UPDATE question SET enonce = :enonce, type = :type, choixA = :choixA, choixB = :choixB, choixC = :choixC, choixD = :choixD, bonneReponse = :bonneReponse, note = :note, explication = :explication, idQuiz = :idQuiz WHERE idQuestion = :idQuestion";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'enonce' => $question->getEnonce(),
                'type' => $question->getType(),
                'choixA' => $question->getChoixA(),
                'choixB' => $question->getChoixB(),
                'choixC' => $question->getChoixC(),
                'choixD' => $question->getChoixD(),
                'bonneReponse' => $question->getBonneReponse(),
                'note' => $question->getNote(),
                'explication' => $question->getExplication(),
                'idQuiz' => $question->getIdQuiz(),
                'idQuestion' => $id
            ]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function deleteQuestion($id) {
        $sql = "DELETE FROM question WHERE idQuestion = :idQuestion";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['idQuestion' => $id]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function getQuestionById($id) {
        $sql = "SELECT q.*, quiz.titre AS quizTitre FROM question q LEFT JOIN quiz ON q.idQuiz = quiz.idQuiz WHERE q.idQuestion = :idQuestion";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['idQuestion' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }
}
?>
