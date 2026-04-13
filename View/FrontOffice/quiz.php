<?php
require_once __DIR__ . '/../../Controller/QuizController.php';
require_once __DIR__ . '/../../Controller/QuestionController.php';

$quizController = new QuizController();
$questionController = new QuestionController();

$idQuiz = intval($_GET['id'] ?? 0);
$quizData = null;
$questions = [];
$errorMessage = '';
$resultMessage = '';
$resultType = '';
$score = null;
$totalPoints = 0;
$correctPoints = 0;

if ($idQuiz <= 0) {
    $errorMessage = 'Quiz invalide ou introuvable.';
} else {
    $quizData = $quizController->getQuizById($idQuiz);
    if (!$quizData) {
        $errorMessage = 'Quiz introuvable.';
    } elseif (strtolower(trim($quizData['statut'])) !== 'actif') {
        $errorMessage = 'Ce quiz n’est pas actuellement disponible.';
    } else {
        $stmt = $questionController->afficherQuestions($idQuiz);
        $questions = $stmt ? $stmt->fetchAll() : [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $answers = $_POST['answer'] ?? [];
            foreach ($questions as $question) {
                $questionPoints = intval($question['note']);
                if ($questionPoints <= 0) {
                    $questionPoints = 1;
                }
                $totalPoints += $questionPoints;
                $selected = trim($answers[$question['idQuestion']] ?? '');
                if ($selected !== '' && strtolower($selected) === strtolower(trim($question['bonneReponse']))) {
                    $correctPoints += $questionPoints;
                }
            }
            if ($totalPoints > 0) {
                $score = round(($correctPoints / $totalPoints) * 100);
                $seuil = intval($quizData['seuilReussite']);
                if ($score >= $seuil) {
                    $resultType = 'success';
                    $resultMessage = "Bravo ! Vous avez réussi le quiz avec $score% (" . $correctPoints . " / " . $totalPoints . " points).";
                } else {
                    $resultType = 'failure';
                    $resultMessage = "Dommage, vous n’avez pas atteint le seuil de réussite de $seuil%. Votre score est de $score% (" . $correctPoints . " / " . $totalPoints . " points).";
                }
            } else {
                $resultType = 'failure';
                $resultMessage = 'Impossible de calculer le score : vérifiez les questions et les notes du quiz.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz - <?= htmlspecialchars($quizData['titre'] ?? 'Évaluation') ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #050505; color: #f4f4f4; }
        .quiz-page { max-width: 900px; margin: 4rem auto 3rem; padding: 0 1.5rem; }
        .quiz-card { background: rgba(15,15,15,0.95); border: 1px solid rgba(255,255,255,0.08); border-radius: 24px; padding: 2rem; box-shadow: 0 20px 80px rgba(0,0,0,0.25); }
        .quiz-card h1 { margin-top: 0; font-size: 2rem; }
        .quiz-meta { display:flex; flex-wrap:wrap; gap:1rem; color:rgba(255,255,255,0.65); margin-bottom:1.5rem; }
        .quiz-status { background: rgba(234,179,8,0.15); color:#eab308; border-radius: 999px; padding: 0.5rem 0.9rem; font-weight:700; }
        .question-card { background: rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.08); border-radius: 18px; padding: 1.5rem; margin-bottom:1.25rem; }
        .question-card h3 { margin-top: 0; font-size: 1.05rem; }
        .option { display:block; margin:0.65rem 0; padding:0.9rem 1rem; border:1px solid rgba(255,255,255,0.12); border-radius:12px; cursor:pointer; transition: all 0.2s ease; }
        .option:hover { border-color: rgba(234,179,8,0.55); background: rgba(234,179,8,0.08); }
        .submit-btn { margin-top: 1rem; }
        .result-box { background: rgba(234,179,8,0.08); border:1px solid rgba(234,179,8,0.35); color:#fff; padding:1rem 1.25rem; border-radius:16px; margin-bottom:1.5rem; }
        .result-box.success-box { background: rgba(16,185,129,0.12); border-color: rgba(16,185,129,0.35); color: #d1fae5; }
        .error-box { background: rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.4); color:#fecaca; padding:1rem 1.25rem; border-radius:16px; margin-bottom:1.5rem; }
        .back-link { display:inline-flex; align-items:center; gap:0.5rem; color:#f3b82f; text-decoration:none; margin-bottom:1.5rem; }
    </style>
</head>
<body>
    <div class="quiz-page">
        <a href="index.php#evaluations" class="back-link"><i class="fas fa-arrow-left"></i> Retour aux évaluations</a>

        <?php if ($errorMessage): ?>
            <div class="error-box"><?= htmlspecialchars($errorMessage) ?></div>
        <?php else: ?>
            <div class="quiz-card">
                <h1><?= htmlspecialchars($quizData['titre']) ?></h1>
                <div class="quiz-meta">
                    <span><i class="fas fa-clock"></i> <?= htmlspecialchars($quizData['duree']) ?> min</span>
                    <span id="quizTimer"><i class="fas fa-hourglass-half"></i> <?= htmlspecialchars($quizData['duree']) ?>:00</span>
                    <span><i class="fas fa-percent"></i> Seuil <?= htmlspecialchars($quizData['seuilReussite']) ?>%</span>
                    <span><i class="fas fa-layer-group"></i> Niveau <?= htmlspecialchars($quizData['niveau']) ?></span>
                    <span class="quiz-status"><?= htmlspecialchars($quizData['statut']) ?></span>
                </div>

                <?php if ($resultMessage): ?>
                    <?php if ($resultType === 'success'): ?>
                        <div class="result-box success-box"><?= htmlspecialchars($resultMessage) ?></div>
                    <?php else: ?>
                        <div class="result-box error-box"><?= htmlspecialchars($resultMessage) ?></div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (empty($questions)): ?>
                    <p>Aucune question n’est encore disponible pour ce quiz. Revenez plus tard ou ajoutez des questions via le back office.</p>
                <?php else: ?>
                    <form method="POST">
                        <?php foreach ($questions as $index => $question): ?>
                            <div class="question-card">
                                <h3>Question <?= $index + 1 ?>: <?= htmlspecialchars($question['enonce']) ?></h3>
                                <?php foreach (['choixA', 'choixB', 'choixC', 'choixD'] as $choiceKey): ?>
                                    <?php if (!empty($question[$choiceKey])): ?>
                                        <label class="option">
                                            <input type="radio" name="answer[<?= $question['idQuestion'] ?>]" value="<?= substr($choiceKey, 5) ?>" style="margin-right:0.75rem;" required>
                                            <strong><?= substr($choiceKey, 5) ?>.</strong> <?= htmlspecialchars($question[$choiceKey]) ?>
                                        </label>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                        <button type="submit" class="submit-btn btn-primary">Envoyer mes réponses</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php if ($_SERVER['REQUEST_METHOD'] !== 'POST' && empty($resultMessage) && empty($errorMessage) && !empty($questions)): ?>
    <script>
        const quizForm = document.querySelector('form');
        const timerDisplay = document.getElementById('quizTimer');
        if (quizForm && timerDisplay) {
            const initialSeconds = <?= max(1, intval($quizData['duree'])) ?> * 60;
            const storeKey = 'quiz_timer_<?= $idQuiz ?>';
            let timeRemaining = initialSeconds;
            const stored = localStorage.getItem(storeKey);
            if (stored) {
                const parsed = parseInt(stored, 10);
                if (!isNaN(parsed) && parsed > 0) {
                    timeRemaining = parsed;
                }
            }
            timerDisplay.textContent = formatTime(timeRemaining);
            const interval = setInterval(() => {
                timeRemaining -= 1;
                if (timeRemaining <= 0) {
                    clearInterval(interval);
                    localStorage.removeItem(storeKey);
                    timerDisplay.textContent = '00:00';
                    const timeoutMessage = document.createElement('div');
                    timeoutMessage.className = 'result-box error-box';
                    timeoutMessage.textContent = 'Le temps imparti est écoulé. Vos réponses sont envoyées automatiquement.';
                    quizForm.parentNode.insertBefore(timeoutMessage, quizForm);
                    setTimeout(() => quizForm.submit(), 800);
                    return;
                }
                timerDisplay.textContent = formatTime(timeRemaining);
                localStorage.setItem(storeKey, timeRemaining);
            }, 1000);
            quizForm.addEventListener('submit', () => localStorage.removeItem(storeKey));
            function formatTime(seconds) {
                const minutes = Math.floor(seconds / 60);
                const secs = seconds % 60;
                return `${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
            }
        }
    </script>
    <?php endif; ?>
</body>
</html>
