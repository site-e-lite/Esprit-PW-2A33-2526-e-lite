<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/QuestionCrud.php';

class EvalAdvanced
{
    /**
     * FONCTIONNALITÉ AVANCÉE 1 : Calcul automatique du score
     * @param int $quizId
     * @param array $userAnswers [idQuestion => réponse choisie]
     * @return array ['points' => float, 'total' => float, 'percentage' => float, 'passed' => bool]
     */
    public static function calculateScore($quizId, $userAnswers) {
        $questions = QuestionCrud::getByQuiz($quizId);
        $total = 0;
        $earned = 0;
        foreach ($questions as $q) {
            $total += $q['note'];
            if (isset($userAnswers[$q['idQuestion']]) && $userAnswers[$q['idQuestion']] === $q['bonneReponse']) {
                $earned += $q['note'];
            }
        }
        $percentage = ($total > 0) ? ($earned / $total) * 100 : 0;
        
        $quiz = QuizCrud::getById($quizId);
        $passed = $percentage >= $quiz['seuilReussite'];
        
        return [
            'points' => $earned,
            'total' => $total,
            'percentage' => $percentage,
            'passed' => $passed
        ];
    }

    /**
     * FONCTIONNALITÉ AVANCÉE 2 : Adaptation du niveau (IA simple)
     * Suggère un niveau de quiz en fonction des performances passées
     * @param int $userId
     * @param int $courseId
     * @return string niveau suggéré ('débutant','intermédiaire','avancé')
     */
    public static function suggestQuizLevel($userId, $courseId) {
        $pdo = Config::getConnexion();
        // TODO : Récupérer les scores précédents de l'utilisateur pour ce cours
        // Si moyenne > 80% -> suggérer niveau supérieur, sinon même niveau
        // Pour l'instant, retour par défaut
        return 'débutant';
    }
}
?>