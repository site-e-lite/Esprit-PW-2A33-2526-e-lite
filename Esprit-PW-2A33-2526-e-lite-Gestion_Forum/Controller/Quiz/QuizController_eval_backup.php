<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Quiz.php';
require_once __DIR__ . '/Validator.php';

class QuizController {
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

    
    private function ensureQuizLockTable() {
        $db = Config::getConnexion();
        try {
            $sql = "CREATE TABLE IF NOT EXISTS quiz_lock (
                idLock INT AUTO_INCREMENT PRIMARY KEY,
                idQuiz INT NOT NULL,
                idUser INT NULL,
                sessionKey VARCHAR(128) NOT NULL,
                reason VARCHAR(255) NULL,
                isLocked TINYINT(1) NOT NULL DEFAULT 1,
                lockedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
                unlockedAt DATETIME NULL,
                unlockedBy VARCHAR(100) NULL,
                CONSTRAINT fk_lock_quiz FOREIGN KEY (idQuiz) REFERENCES quiz(idQuiz) ON DELETE CASCADE
            )";
            $db->exec($sql);
        } catch (Exception $e) {
        }
    }

    public function getAttemptSessionKey() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['quizAttemptSessionKey'])) {
            $_SESSION['quizAttemptSessionKey'] = hash('sha256', session_id());
        }

        return $_SESSION['quizAttemptSessionKey'];
    }

    
    public function isQuizLockedForUser($idQuiz, $idUser = null, $sessionKey = null) {
        $this->ensureQuizLockTable();
        $db = Config::getConnexion();
        $idQuiz = intval($idQuiz);
        $sessionKey = trim((string)$sessionKey);

        $sql = "SELECT idLock, idQuiz, idUser, sessionKey, reason, isLocked, lockedAt, unlockedAt, unlockedBy
                FROM quiz_lock
                WHERE idQuiz = :idQuiz AND isLocked = 1";

        $params = ['idQuiz' => $idQuiz];

        if (!empty($idUser)) {
            $sql .= " AND (idUser = :idUser OR (idUser IS NULL AND sessionKey = :sessionKey))";
            $params['idUser'] = intval($idUser);
            $params['sessionKey'] = $sessionKey;
        } else {
            $sql .= " AND idUser IS NULL AND sessionKey = :sessionKey";
            $params['sessionKey'] = $sessionKey;
        }

        $sql .= " ORDER BY idLock DESC LIMIT 1";

        try {
            $query = $db->prepare($sql);
            $query->execute($params);
            return $query->fetch();
        } catch (Exception $e) {
            return false;
        }
    }

    
    public function lockQuizForUser($idQuiz, $idUser = null, $sessionKey = null, $reason = null) {
        $this->ensureQuizLockTable();
        $db = Config::getConnexion();

        $idQuiz = intval($idQuiz);
        $idUser = !empty($idUser) ? intval($idUser) : null;
        $sessionKey = trim((string)$sessionKey);
        $reason = trim((string)$reason);

        $existingLock = $this->isQuizLockedForUser($idQuiz, $idUser, $sessionKey);
        if ($existingLock) {
            return intval($existingLock['idLock']);
        }

        try {
            $sql = "INSERT INTO quiz_lock (idQuiz, idUser, sessionKey, reason, isLocked) VALUES (:idQuiz, :idUser, :sessionKey, :reason, 1)";
            $query = $db->prepare($sql);
            $query->execute([
                'idQuiz' => $idQuiz,
                'idUser' => $idUser,
                'sessionKey' => $sessionKey,
                'reason' => $reason !== '' ? $reason : null
            ]);
            return intval($db->lastInsertId());
        } catch (Exception $e) {
            return 0;
        }
    }

    public function getLockedAttempts() {
        $this->ensureQuizLockTable();
        $db = Config::getConnexion();
        $sql = "SELECT ql.idLock, ql.idQuiz, q.titre AS quizTitre, ql.idUser, ql.sessionKey, ql.reason, ql.isLocked, ql.lockedAt, ql.unlockedAt, ql.unlockedBy
                FROM quiz_lock ql
                INNER JOIN quiz q ON ql.idQuiz = q.idQuiz
                WHERE ql.isLocked = 1
                ORDER BY ql.lockedAt DESC";
        try {
            return $db->query($sql);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    
    public function unlockLockById($idLock, $adminName = 'admin') {
        $this->ensureQuizLockTable();
        $db = Config::getConnexion();
        try {
            $sql = "UPDATE quiz_lock SET isLocked = 0, unlockedAt = NOW(), unlockedBy = :unlockedBy WHERE idLock = :idLock";
            $query = $db->prepare($sql);
            $query->execute([
                'idLock' => intval($idLock),
                'unlockedBy' => trim((string)$adminName)
            ]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    private function mapNiveauToDb($niveau) {
        $niveau = strtolower(trim((string)$niveau));
        if ($niveau === 'debutant' || $niveau === 'débutant') {
            return 'Débutant';
        }
        if ($niveau === 'intermediaire' || $niveau === 'intermédiaire') {
            return 'Intermédiaire';
        }
        if ($niveau === 'avance' || $niveau === 'avancé') {
            return 'Avancé';
        }
        return null;
    }

    private function mapStatutToDb($statut) {
        $statut = strtolower(trim((string)$statut));
        if ($statut === 'actif') {
            return 'Actif';
        }
        if ($statut === 'inactif') {
            return 'Inactif';
        }
        return null;
    }

    private function questionHasNiveauColumn() {
        $db = Config::getConnexion();
        try {
            $sql = "SELECT COUNT(*) AS total FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'question' AND COLUMN_NAME = 'niveau'";
            $query = $db->query($sql);
            $row = $query->fetch();
            return !empty($row) && intval($row['total']) > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    public function generateForm() {
        return [
            'niveaux' => ['debutant', 'intermediaire', 'avance'],
            'statuts' => ['actif', 'inactif']
        ];
    }

    public function validateQuiz($data) {
        Validator::reset();
        
        $titre = $data['titre'] ?? '';
        $duree = $data['duree'] ?? '';
        $seuilReussite = $data['seuilReussite'] ?? '';
        $niveau = $data['niveau'] ?? '';
        $statut = $data['statut'] ?? '';
        $idCourse = $data['idCourse'] ?? '';
        
        Validator::required('titre', $titre, 'Titre');
        Validator::string('titre', $titre, 'Titre', 1, 255);
        
        Validator::required('duree', $duree, 'Durée');
        Validator::integer('duree', $duree, 'Durée', 1);
        
        Validator::required('seuilReussite', $seuilReussite, 'Seuil de réussite');
        Validator::integer('seuilReussite', $seuilReussite, 'Seuil de réussite', 40, 100);
        
        Validator::required('niveau', $niveau, 'Niveau');
        Validator::inArray('niveau', $niveau, ['Débutant', 'Intermédiaire', 'Avancé'], 'Niveau');
        
        Validator::required('statut', $statut, 'Statut');
        Validator::inArray('statut', $statut, ['Actif', 'Inactif', 'Brouillon'], 'Statut');
        
        Validator::required('idCourse', $idCourse, 'ID Course');
        Validator::integer('idCourse', $idCourse, 'ID Course', 1);
        
        return !Validator::hasErrors();
    }
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

    public function getQuestionsForAssignment($niveau = null) {
        $sql = "SELECT idQuestion, enonce, idQuiz, niveau FROM question WHERE 1=1";
        $params = [];
        
        
        if (!empty($niveau)) {
            $sql .= " AND (LOWER(TRIM(niveau)) = :niveau OR niveau IS NULL OR TRIM(niveau) = '')";
            $params['niveau'] = strtolower(trim($niveau));
        }
        
        $sql .= " ORDER BY idQuestion DESC";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute($params);
            return $query;
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function getAvailableQuestions($niveau = null, $limit = null) {
        
        
        $db = Config::getConnexion();
        $params = [];
        $sql = "SELECT idQuestion, enonce, type, choixA, choixB, choixC, choixD, reponses_json, bonneReponse, note, explication, idQuiz FROM question WHERE 1=1";

        $questionHasNiveau = $this->questionHasNiveauColumn();
        if ($questionHasNiveau && !empty($niveau)) {
            $niveauLower = strtolower(trim($niveau));
            $niveauDbLower = strtolower($this->mapNiveauToDb($niveau) ?? $niveauLower);
            $sql .= " AND (LOWER(TRIM(niveau)) = :niveauLower OR LOWER(TRIM(niveau)) = :niveauDbLower OR niveau IS NULL OR trim(niveau) = '')";
            $params['niveauLower'] = $niveauLower;
            $params['niveauDbLower'] = $niveauDbLower;
        }

        $sql .= " ORDER BY RAND()";

        if ($limit !== null) {
            $limit = intval($limit);
            if ($limit > 0) {
                $sql .= " LIMIT " . $limit;
            }
        }

        try {
            $query = $db->prepare($sql);
            $query->execute($params);
            return $query->fetchAll();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function getQuestionIdsByQuiz($idQuiz) {
        $sql = "SELECT idQuestion FROM question WHERE idQuiz = :idQuiz";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['idQuiz' => $idQuiz]);
            return $query->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    private function normalizeQuestionIds($questionIds) {
        if (!is_array($questionIds)) {
            return [];
        }

        $normalized = [];
        foreach ($questionIds as $questionId) {
            if (is_numeric($questionId) && intval($questionId) > 0) {
                $normalized[] = intval($questionId);
            }
        }

        return array_values(array_unique($normalized));
    }

    
    public function assignQuestionsToQuiz($idQuiz, $questionIds, $niveauQuiz = null) {
        $db = Config::getConnexion();
        $questionIds = $this->normalizeQuestionIds($questionIds);
        if (empty($questionIds)) {
            return 0;
        }

        try {
            
            if ($niveauQuiz === null) {
                $quizData = $this->getQuizById($idQuiz);
                $niveauQuiz = $quizData['niveau'] ?? null;
            }

            
            if (!empty($niveauQuiz) && !$this->validateQuizQuestionMatch($niveauQuiz, $questionIds)) {
                throw new Exception('Impossible d\'assigner des questions de niveaux différents au quiz.');
            }

            
            $placeholders = implode(',', array_fill(0, count($questionIds), '?'));
            $db->prepare("UPDATE question SET idQuiz = NULL WHERE idQuestion IN ($placeholders)")->execute($questionIds);
            
            
            $params = array_merge([intval($idQuiz)], $questionIds);
            $sql = "UPDATE question SET idQuiz = ? WHERE idQuestion IN ($placeholders)";
            $query = $db->prepare($sql);
            $query->execute($params);
            return $query->rowCount();
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function syncQuizQuestions($db, $idQuiz, $questionIds) {
        $questionIds = $this->normalizeQuestionIds($questionIds);

        
        $quizQuery = $db->prepare("SELECT niveau FROM quiz WHERE idQuiz = :idQuiz");
        $quizQuery->execute(['idQuiz' => $idQuiz]);
        $quizData = $quizQuery->fetch();
        $niveauQuiz = $quizData['niveau'] ?? null;

        
        if (!empty($niveauQuiz) && !empty($questionIds) && !$this->validateQuizQuestionMatch($niveauQuiz, $questionIds)) {
            throw new Exception('Impossible d\'assigner des questions de niveaux différents au quiz.');
        }

        $query = $db->prepare("UPDATE question SET idQuiz = NULL WHERE idQuiz = :idQuiz");
        $query->execute(['idQuiz' => $idQuiz]);

        if (empty($questionIds)) {
            return;
        }

        $placeholders = implode(',', array_fill(0, count($questionIds), '?'));
        $params = array_merge([$idQuiz], $questionIds);
        $query = $db->prepare("UPDATE question SET idQuiz = ? WHERE idQuestion IN ($placeholders)");
        $query->execute($params);
    }

    
    public function generateQuiz($data) {
        Validator::reset();

        $titre = trim($data['titre'] ?? '');
        $duree = $data['duree'] ?? '';
        $seuilReussite = $data['seuilReussite'] ?? '';
        $niveauInput = trim($data['niveau'] ?? '');
        $statutInput = trim($data['statut'] ?? '');
        $idCourse = $data['idCourse'] ?? '';
        $nombreQuestions = $data['nombreQuestions'] ?? '';

        Validator::required('titre', $titre, 'Titre');
        Validator::string('titre', $titre, 'Titre', 1, 255);
        Validator::required('duree', $duree, 'Durée');
        Validator::integer('duree', $duree, 'Durée', 1);
        Validator::required('seuilReussite', $seuilReussite, 'Seuil de réussite');
        Validator::integer('seuilReussite', $seuilReussite, 'Seuil de réussite', 40, 100);
        Validator::required('niveau', $niveauInput, 'Niveau');
        Validator::inArray('niveau', strtolower($niveauInput), ['debutant', 'intermediaire', 'avance'], 'Niveau');
        Validator::required('statut', $statutInput, 'Statut');
        Validator::inArray('statut', strtolower($statutInput), ['actif', 'inactif'], 'Statut');
        Validator::required('idCourse', $idCourse, 'ID Course');
        Validator::integer('idCourse', $idCourse, 'ID Course', 1);
        Validator::required('nombreQuestions', $nombreQuestions, 'Nombre de questions');
        Validator::integer('nombreQuestions', $nombreQuestions, 'Nombre de questions', 1);

        if (Validator::hasErrors()) {
            return ['success' => false, 'errors' => Validator::getErrors()];
        }

        $niveauDb = $this->mapNiveauToDb($niveauInput);
        $statutDb = $this->mapStatutToDb($statutInput);
        if ($niveauDb === null) {
            return ['success' => false, 'errors' => ['niveau' => 'Niveau sélectionné invalide.']];
        }
        if ($statutDb === null) {
            return ['success' => false, 'errors' => ['statut' => 'Statut sélectionné invalide.']];
        }

        $nombreQuestions = intval($nombreQuestions);
        $questionsDisponibles = $this->getAvailableQuestions(strtolower($niveauInput), $nombreQuestions);
        if (count($questionsDisponibles) < $nombreQuestions) {
            return [
                'success' => false,
                'errors' => [
                    'nombreQuestions' => 'Nombre de questions disponibles insuffisant pour générer ce quiz (' . count($questionsDisponibles) . ' disponibles).'
                ]
            ];
        }

        $questionIds = [];
        foreach ($questionsDisponibles as $question) {
            $questionIds[] = intval($question['idQuestion']);
        }

        $quiz = new Quiz(
            $titre,
            intval($duree),
            intval($seuilReussite),
            $niveauDb,
            $statutDb,
            intval($idCourse)
        );

        $db = Config::getConnexion();
        try {
            $db->beginTransaction();

            $sqlQuiz = "INSERT INTO quiz (titre, duree, seuilReussite, niveau, statut, idCourse) VALUES (:titre, :duree, :seuilReussite, :niveau, :statut, :idCourse)";
            $queryQuiz = $db->prepare($sqlQuiz);
            $queryQuiz->execute([
                'titre' => $quiz->getTitre(),
                'duree' => $quiz->getDuree(),
                'seuilReussite' => $quiz->getSeuilReussite(),
                'niveau' => $quiz->getNiveau(),
                'statut' => $quiz->getStatut(),
                'idCourse' => $quiz->getIdCourse()
            ]);

            $idQuiz = intval($db->lastInsertId());

            $assignedCount = $this->assignQuestionsToQuiz($idQuiz, $questionIds);
            if ($assignedCount < $nombreQuestions) {
                throw new Exception('Impossible d\'associer toutes les questions sélectionnées au quiz.');
            }

            $db->commit();

            return [
                'success' => true,
                'idQuiz' => $idQuiz,
                'message' => 'Quiz généré et questions associées avec succès.'
            ];
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            return [
                'success' => false,
                'errors' => ['global' => 'Erreur lors de la génération du quiz : ' . $e->getMessage()]
            ];
        }
    }

    public function getQuizQuestionsForPassage($idQuiz) {
        $sql = "SELECT idQuestion, enonce, type, choixA, choixB, choixC, choixD, reponses_json, bonneReponse, note, explication FROM question WHERE idQuiz = :idQuiz ORDER BY idQuestion ASC";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['idQuiz' => intval($idQuiz)]);
            return $query->fetchAll();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    
    public function evaluateAndSaveQuizResult($idQuiz, $answers, $antiCheatData = [], $idUser = null) {
        $quiz = $this->getQuizById($idQuiz);
        if (!$quiz) {
            return ['success' => false, 'message' => 'Quiz introuvable.'];
        }

        $questions = $this->getQuizQuestionsForPassage($idQuiz);
        if (empty($questions)) {
            return ['success' => false, 'message' => 'Aucune question trouvée pour ce quiz.'];
        }

        $totalPoints = 0.0;
        $scorePoints = 0.0;
        foreach ($questions as $question) {
            $note = floatval($question['note']);
            if ($note <= 0) {
                $note = 1.0;
            }
            $totalPoints += $note;

            $questionId = intval($question['idQuestion']);
            $selected = trim($answers[$questionId] ?? '');
            if ($selected !== '' && strtolower($selected) === strtolower(trim((string)$question['bonneReponse']))) {
                $scorePoints += $note;
            }
        }

        if ($totalPoints <= 0) {
            return ['success' => false, 'message' => 'Impossible de calculer le score.'];
        }

        $pourcentage = round(($scorePoints / $totalPoints) * 100, 2);
        $seuilReussite = floatval($quiz['seuilReussite']);
        $statut = $pourcentage >= $seuilReussite ? 'reussi' : 'echoue';

        $tabSwitchCount = intval($antiCheatData['tabSwitchCount'] ?? 0);
        $inactivityTime = intval($antiCheatData['inactivityTime'] ?? 0);
        $fastAnswerFlag = intval(!empty($antiCheatData['fastAnswerFlag']) && strval($antiCheatData['fastAnswerFlag']) !== '0');

        $db = Config::getConnexion();
        try {
            $sql = "INSERT INTO quiz_result (idQuiz, idUser, score, totalPoints, pourcentage, statut, tabSwitchCount, inactivityTime, fastAnswerFlag) VALUES (:idQuiz, :idUser, :score, :totalPoints, :pourcentage, :statut, :tabSwitchCount, :inactivityTime, :fastAnswerFlag)";
            $query = $db->prepare($sql);
            $query->execute([
                'idQuiz' => intval($idQuiz),
                'idUser' => $idUser,
                'score' => round($scorePoints, 2),
                'totalPoints' => round($totalPoints, 2),
                'pourcentage' => $pourcentage,
                'statut' => $statut,
                'tabSwitchCount' => $tabSwitchCount,
                'inactivityTime' => $inactivityTime,
                'fastAnswerFlag' => $fastAnswerFlag
            ]);
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Résultat calculé mais non enregistré: ' . $e->getMessage()];
        }

        return [
            'success' => true,
            'scorePoints' => round($scorePoints, 2),
            'totalPoints' => round($totalPoints, 2),
            'pourcentage' => $pourcentage,
            'statut' => $statut,
            'tabSwitchCount' => $tabSwitchCount,
            'inactivityTime' => $inactivityTime,
            'fastAnswerFlag' => $fastAnswerFlag
        ];
    }

    public function addQuiz($quiz, $questionIds = []) {
        $sql = "INSERT INTO quiz (titre, duree, seuilReussite, niveau, statut, idCourse) VALUES (:titre, :duree, :seuilReussite, :niveau, :statut, :idCourse)";
        $db = Config::getConnexion();
        try {
            $db->beginTransaction();
            $query = $db->prepare($sql);
            $query->execute([
                'titre' => $quiz->getTitre(),
                'duree' => $quiz->getDuree(),
                'seuilReussite' => $quiz->getSeuilReussite(),
                'niveau' => $quiz->getNiveau(),
                'statut' => $quiz->getStatut(),
                'idCourse' => $quiz->getIdCourse()
            ]);
            $idQuiz = intval($db->lastInsertId());
            $this->syncQuizQuestions($db, $idQuiz, $questionIds);
            $db->commit();
            return $idQuiz;
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function updateQuiz($quiz, $id, $questionIds = []) {
        $sql = "UPDATE quiz SET titre = :titre, duree = :duree, seuilReussite = :seuilReussite, niveau = :niveau, statut = :statut, idCourse = :idCourse WHERE idQuiz = :idQuiz";
        $db = Config::getConnexion();
        try {
            $db->beginTransaction();
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
            $this->syncQuizQuestions($db, intval($id), $questionIds);
            $db->commit();
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function deleteQuiz($id) {
        $db = Config::getConnexion();
        try {
            $query = $db->prepare("DELETE FROM quiz WHERE idQuiz = :idQuiz");
            $query->execute(['idQuiz' => $id]);
        } catch (Exception $e) {
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

    public function validateQuizQuestionMatch($niveau, $questionIds = []) {
        
        if (empty($questionIds) || empty($niveau)) {
            return true;
        }
        
        $db = Config::getConnexion();
        try {
            $placeholders = implode(',', array_fill(0, count($questionIds), '?'));
            $sql = "SELECT COUNT(*) as count_questions, 
                    SUM(CASE WHEN LOWER(TRIM(niveau)) = :niveau THEN 1 ELSE 0 END) as count_matching
                    FROM question WHERE idQuestion IN ($placeholders)";
            
            $query = $db->prepare($sql);
            
            
            foreach ($questionIds as $i => $qId) {
                $query->bindValue($i + 1, intval($qId), PDO::PARAM_INT);
            }
            $query->bindValue(count($questionIds) + 1, strtolower(trim($niveau)), PDO::PARAM_STR);
            
            $query->execute();
            $result = $query->fetch();
            
            
            return ($result['count_questions'] ?? 0) > 0 && 
                   ($result['count_questions'] == $result['count_matching']);
        } catch (Exception $e) {
            return true; 
        }
    }

    public function getAllCourses() {
        $db = Config::getConnexion();
        try {
            $sql = "SELECT idCourse, titre FROM course ORDER BY titre ASC";
            $result = $db->query($sql);
            return $result->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    public function getCourseTitleById($idCourse) {
        $db = Config::getConnexion();
        try {
            $sql = "SELECT titre FROM course WHERE idCourse = :idCourse";
            $query = $db->prepare($sql);
            $query->execute(['idCourse' => intval($idCourse)]);
            $result = $query->fetch();
            return $result ? $result['titre'] : 'N/A';
        } catch (Exception $e) {
            return 'N/A';
        }
    }

    private function tableExists($tableName) {
        $db = Config::getConnexion();
        try {
            $sql = "SELECT COUNT(*) AS total FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :tableName";
            $query = $db->prepare($sql);
            $query->execute(['tableName' => $tableName]);
            $result = $query->fetch();
            return !empty($result) && intval($result['total']) > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    private function getTableColumns($tableName) {
        $db = Config::getConnexion();
        try {
            $sql = "SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :tableName";
            $query = $db->prepare($sql);
            $query->execute(['tableName' => $tableName]);
            $columns = $query->fetchAll(PDO::FETCH_COLUMN);
            return array_map('strtolower', $columns ?: []);
        } catch (Exception $e) {
            return [];
        }
    }

    private function findMatchingColumn(array $availableColumns, array $candidates) {
        foreach ($candidates as $candidate) {
            if (in_array(strtolower($candidate), $availableColumns, true)) {
                return $candidate;
            }
        }

        return null;
    }

    private function resolveQuizResultDateColumn() {
        $availableColumns = $this->getTableColumns('quiz_result');
        return $this->findMatchingColumn($availableColumns, [
            'datePassage',
            'passageDate',
            'passageAt',
            'createdAt',
            'created_at',
            'dateCreation',
            'resultDate',
            'date',
            'submittedAt',
            'submitted_at'
        ]);
    }

    private function resolveQuizResultUserSource() {
        $candidateTables = ['user', 'users', 'utilisateur', 'utilisateurs', 'etudiant', 'etudiants', 'student', 'students'];
        $idCandidates = ['idUser', 'idUtilisateur', 'idEtudiant', 'idStudent', 'user_id', 'utilisateur_id', 'etudiant_id', 'student_id', 'id'];
        $nameCandidates = ['nom', 'prenom', 'email', 'last_name', 'first_name', 'lastname', 'firstname', 'mail'];

        foreach ($candidateTables as $tableName) {
            if (!$this->tableExists($tableName)) {
                continue;
            }

            $columns = $this->getTableColumns($tableName);
            $idColumn = $this->findMatchingColumn($columns, $idCandidates);
            if (!$idColumn) {
                continue;
            }

            $nomColumn = $this->findMatchingColumn($columns, ['nom', 'last_name', 'lastname']);
            $prenomColumn = $this->findMatchingColumn($columns, ['prenom', 'first_name', 'firstname']);
            $emailColumn = $this->findMatchingColumn($columns, ['email', 'mail']);

            if ($nomColumn || $prenomColumn || $emailColumn) {
                return [
                    'table' => $tableName,
                    'idColumn' => $idColumn,
                    'nomColumn' => $nomColumn,
                    'prenomColumn' => $prenomColumn,
                    'emailColumn' => $emailColumn
                ];
            }
        }

        return null;
    }

    public function getQuizResultsForExport($idQuiz = null, $idCourse = null) {
        $db = Config::getConnexion();
        $dateColumn = $this->resolveQuizResultDateColumn();
        $userSource = $this->resolveQuizResultUserSource();

        $selectParts = [
            'qr.idQuiz',
            'qr.idUser',
            'qr.score',
            'qr.totalPoints',
            'qr.pourcentage',
            'qr.statut AS resultatStatut',
            'qr.tabSwitchCount',
            'qr.inactivityTime',
            'qr.fastAnswerFlag',
            'q.titre AS quizTitre',
            'q.idCourse',
            "COALESCE(c.titre, '') AS coursTitre"
        ];

        if (!empty($dateColumn)) {
            $selectParts[] = 'qr.`' . $dateColumn . '` AS datePassage';
        } else {
            $selectParts[] = 'NULL AS datePassage';
        }

        $userJoinSql = '';
        if (!empty($userSource)) {
            $selectParts[] = !empty($userSource['nomColumn']) ? 'u.`' . $userSource['nomColumn'] . '` AS nom' : "'' AS nom";
            $selectParts[] = !empty($userSource['prenomColumn']) ? 'u.`' . $userSource['prenomColumn'] . '` AS prenom' : "'' AS prenom";
            $selectParts[] = !empty($userSource['emailColumn']) ? 'u.`' . $userSource['emailColumn'] . '` AS email' : "'' AS email";
            $userJoinSql = ' LEFT JOIN `' . $userSource['table'] . '` u ON qr.idUser = u.`' . $userSource['idColumn'] . '`';
        } else {
            $selectParts[] = "'' AS nom";
            $selectParts[] = "'' AS prenom";
            $selectParts[] = "'' AS email";
        }

        $sql = 'SELECT ' . implode(', ', $selectParts) . ' FROM quiz_result qr INNER JOIN quiz q ON qr.idQuiz = q.idQuiz LEFT JOIN course c ON q.idCourse = c.idCourse' . $userJoinSql . ' WHERE 1=1';
        $params = [];

        if (!empty($idQuiz)) {
            $sql .= ' AND qr.idQuiz = :idQuiz';
            $params['idQuiz'] = intval($idQuiz);
        }

        if (!empty($idCourse)) {
            $sql .= ' AND q.idCourse = :idCourse';
            $params['idCourse'] = intval($idCourse);
        }

        if (!empty($dateColumn)) {
            $sql .= ' ORDER BY qr.`' . $dateColumn . '` DESC, qr.idQuiz DESC';
        } else {
            $sql .= ' ORDER BY qr.idQuiz DESC';
        }

        try {
            $query = $db->prepare($sql);
            $query->execute($params);
            $rows = $query->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as &$row) {
                $row['nom'] = trim((string)($row['nom'] ?? ''));
                $row['prenom'] = trim((string)($row['prenom'] ?? ''));
                $row['email'] = trim((string)($row['email'] ?? ''));
                $row['quizTitre'] = trim((string)($row['quizTitre'] ?? ''));
                $row['coursTitre'] = trim((string)($row['coursTitre'] ?? ''));
                $row['score'] = is_numeric($row['score'] ?? null) ? round((float)$row['score'], 2) : 0;
                $row['pourcentage'] = is_numeric($row['pourcentage'] ?? null) ? round((float)$row['pourcentage'], 2) : 0;
                $row['statut'] = strtolower(trim((string)($row['resultatStatut'] ?? ''))) === 'reussi' ? 'Réussi' : 'Échoué';
                $row['statutAntiTriche'] = (
                    intval($row['tabSwitchCount'] ?? 0) > 0 ||
                    intval($row['inactivityTime'] ?? 0) > 0 ||
                    intval($row['fastAnswerFlag'] ?? 0) > 0
                ) ? 'Suspect' : 'Normal';

                if (!empty($row['datePassage'])) {
                    $timestamp = strtotime((string)$row['datePassage']);
                    $row['datePassage'] = $timestamp ? date('Y-m-d H:i:s', $timestamp) : (string)$row['datePassage'];
                } else {
                    $row['datePassage'] = 'Non disponible';
                }
            }
            unset($row);

            return $rows;
        } catch (Exception $e) {
            return [];
        }
    }
}
?>
