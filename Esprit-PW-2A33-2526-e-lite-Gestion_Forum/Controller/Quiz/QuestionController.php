<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Model/Quiz/Question.php';
require_once __DIR__ . '/Validator.php';

class QuestionController {
    private static $responsesColumnEnsured = false;

    private function ensureResponsesColumn(PDO $db) {
        if (self::$responsesColumnEnsured) {
            return;
        }

        try {
            $check = $db->query("SHOW COLUMNS FROM question LIKE 'reponses_json'");
            $exists = $check && $check->fetch();
            if (!$exists) {
                $db->exec("ALTER TABLE question ADD COLUMN reponses_json LONGTEXT NULL AFTER choixD");
            }
            self::$responsesColumnEnsured = true;
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    private function normalizeResponses($data) {
        $responses = $data['reponses'] ?? [];
        if (!is_array($responses)) {
            $responses = [];
        }

        $normalized = [];
        foreach ($responses as $response) {
            $response = trim((string)$response);
            if ($response !== '') {
                $normalized[] = $response;
            }
        }

        return array_values(array_unique($normalized));
    }

    public function extractQuestionResponses($question) {
        if (!is_array($question)) {
            return [];
        }

        $responses = [];
        if (!empty($question['reponses_json'])) {
            $decoded = json_decode($question['reponses_json'], true);
            if (is_array($decoded)) {
                $responses = $decoded;
            }
        }

        if (empty($responses)) {
            $responses = [
                $question['choixA'] ?? '',
                $question['choixB'] ?? '',
                $question['choixC'] ?? '',
                $question['choixD'] ?? '',
            ];
        }

        $normalized = [];
        foreach ($responses as $response) {
            $response = trim((string)$response);
            if ($response !== '') {
                $normalized[] = $response;
            }
        }

        return array_values(array_unique($normalized));
    }

    public function validateQuestion($data) {
        
        Validator::reset();
        
        $enonce = $data['enonce'] ?? '';
        $type = $data['type'] ?? '';
        $bonneReponse = $data['bonneReponse'] ?? '';
        $note = $data['note'] ?? '';
        $niveau = $data['niveau'] ?? 'Débutant';
        
        
        Validator::required('enonce', $enonce, 'Énoncé');
        Validator::string('enonce', $enonce, 'Énoncé', 1, 5000);
        
        Validator::required('type', $type, 'Type');
        Validator::inArray('type', $type, ['QCU', 'Ouverte'], 'Type');
        
        Validator::required('bonneReponse', $bonneReponse, 'Bonne réponse');
        Validator::string('bonneReponse', $bonneReponse, 'Bonne réponse', 1, 500);
        
        Validator::required('note', $note, 'Note');
        Validator::integer('note', $note, 'Note', 1, 1000);
        
        Validator::required('niveau', $niveau, 'Niveau');
        Validator::inArray('niveau', $niveau, ['Débutant', 'Intermédiaire', 'Avancé'], 'Niveau');

        $responses = $this->normalizeResponses($data);

        if ($type === 'QCU') {
            
            if (count($responses) < 2) {
                Validator::addError('responses', 'Une QCU doit avoir au moins 2 réponses.');
            }

            if (!in_array(trim($bonneReponse), $responses, true)) {
                Validator::addError('bonneReponse', 'La bonne réponse doit correspondre à une des réponses proposées.');
            }
        }
        
        
        return !Validator::hasErrors();
    }

    public function afficherQuestions($idQuiz = null) {
        $db = Config::getConnexion();
        try {
            $this->ensureResponsesColumn($db);
            if ($idQuiz !== null) {
                $sql = "SELECT q.idQuestion, q.enonce, q.type AS type, q.choixA, q.choixB, q.choixC, q.choixD, q.reponses_json, q.bonneReponse, q.note, q.explication, q.idQuiz, q.niveau, quiz.titre AS quizTitre FROM question q LEFT JOIN quiz ON q.idQuiz = quiz.idQuiz WHERE q.idQuiz = :idQuiz ORDER BY q.idQuestion DESC";
                $query = $db->prepare($sql);
                $query->execute(['idQuiz' => $idQuiz]);
                return $query;
            }
            $sql = "SELECT q.idQuestion, q.enonce, q.type AS type, q.choixA, q.choixB, q.choixC, q.choixD, q.reponses_json, q.bonneReponse, q.note, q.explication, q.idQuiz, q.niveau, quiz.titre AS quizTitre FROM question q LEFT JOIN quiz ON q.idQuiz = quiz.idQuiz ORDER BY q.idQuestion DESC";
            return $db->query($sql);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function addQuestion($question) {
        
        $db = Config::getConnexion();
        try {
            $this->ensureResponsesColumn($db);
            $responses = $question->getResponses();
            $sql = "INSERT INTO question (enonce, type, choixA, choixB, choixC, choixD, reponses_json, bonneReponse, note, explication, idQuiz, niveau) VALUES (:enonce, :type, :choixA, :choixB, :choixC, :choixD, :reponses_json, :bonneReponse, :note, :explication, :idQuiz, :niveau)";
            $query = $db->prepare($sql);
            $query->execute([
                'enonce' => $question->getEnonce(),
                'type' => $question->getType(),
                'choixA' => $question->getChoixA(),
                'choixB' => $question->getChoixB(),
                'choixC' => $question->getChoixC(),
                'choixD' => $question->getChoixD(),
                'reponses_json' => !empty($responses) ? json_encode($responses, JSON_UNESCAPED_UNICODE) : null,
                'bonneReponse' => $question->getBonneReponse(),
                'note' => $question->getNote(),
                'explication' => $question->getExplication(),
                'idQuiz' => $question->getIdQuiz(),
                'niveau' => $question->getNiveau()
            ]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function updateQuestion($question, $id) {
        
        $db = Config::getConnexion();
        try {
            $this->ensureResponsesColumn($db);
            $responses = $question->getResponses();
            $sql = "UPDATE question SET enonce = :enonce, type = :type, choixA = :choixA, choixB = :choixB, choixC = :choixC, choixD = :choixD, reponses_json = :reponses_json, bonneReponse = :bonneReponse, note = :note, explication = :explication, niveau = :niveau WHERE idQuestion = :idQuestion";
            $query = $db->prepare($sql);
            $query->execute([
                'enonce' => $question->getEnonce(),
                'type' => $question->getType(),
                'choixA' => $question->getChoixA(),
                'choixB' => $question->getChoixB(),
                'choixC' => $question->getChoixC(),
                'choixD' => $question->getChoixD(),
                'reponses_json' => !empty($responses) ? json_encode($responses, JSON_UNESCAPED_UNICODE) : null,
                'bonneReponse' => $question->getBonneReponse(),
                'note' => $question->getNote(),
                'explication' => $question->getExplication(),
                'niveau' => $question->getNiveau(),
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
        
        $db = Config::getConnexion();
        try {
            $this->ensureResponsesColumn($db);
            $sql = "SELECT q.idQuestion, q.enonce, q.type AS type, q.choixA, q.choixB, q.choixC, q.choixD, q.reponses_json, q.bonneReponse, q.note, q.explication, q.idQuiz, q.niveau, quiz.titre AS quizTitre FROM question q LEFT JOIN quiz ON q.idQuiz = quiz.idQuiz WHERE q.idQuestion = :idQuestion";
            $query = $db->prepare($sql);
            $query->execute(['idQuestion' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }
}
?>

