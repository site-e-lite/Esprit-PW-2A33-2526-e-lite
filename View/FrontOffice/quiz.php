<?php
require_once __DIR__ . '/../../Controller/QuizController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$quizController = new QuizController();
$idUser = $_SESSION['idUser'] ?? null;
$sessionKey = $quizController->getAttemptSessionKey();

$idQuiz = intval($_GET['id'] ?? 0);
$quizData = null;
$questions = [];
$errorMessage = '';
$resultMessage = '';
$resultType = '';
$pourcentage = null;
$totalPoints = 0.0;
$scorePoints = 0.0;
$tabSwitchCount = 0;
$inactivityTime = 0;
$fastAnswerFlag = 0;

// Anti-cheat: endpoint appelé par le JS pour verrouiller immédiatement une tentative.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'lock_quiz') {
    header('Content-Type: application/json; charset=utf-8');
    if ($idQuiz <= 0) {
        echo json_encode(['success' => false, 'message' => 'Quiz invalide.']);
        exit;
    }

    $reason = trim($_POST['reason'] ?? 'Tentative fermee pour comportement suspect.');
    $quizController->lockQuizForUser($idQuiz, $idUser, $sessionKey, $reason);
    echo json_encode(['success' => true, 'message' => 'Quiz verrouille.']);
    exit;
}

if ($idQuiz <= 0) {
    $errorMessage = 'Quiz invalide ou introuvable.';
} else {
    $activeLock = $quizController->isQuizLockedForUser($idQuiz, $idUser, $sessionKey);
    if ($activeLock) {
        $errorMessage = 'Votre tentative est verrouillee pour ce quiz. Contactez l\'administrateur pour la reouvrir.';
        if (!empty($activeLock['reason'])) {
            $errorMessage .= ' Raison: ' . $activeLock['reason'];
        }
    } else {
        $quizData = $quizController->getQuizById($idQuiz);
        if (!$quizData) {
            $errorMessage = 'Quiz introuvable.';
        } elseif (strtolower(trim($quizData['statut'])) !== 'actif') {
            $errorMessage = 'Ce quiz n’est pas actuellement disponible.';
        } else {
            $questions = $quizController->getQuizQuestionsForPassage($idQuiz);

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $answers = $_POST['answer'] ?? [];

                $antiCheatData = [
                    'tabSwitchCount' => intval($_POST['tabSwitchCount'] ?? 0),
                    'inactivityTime' => intval($_POST['inactivityTime'] ?? 0),
                    'fastAnswerFlag' => intval($_POST['fastAnswerFlag'] ?? 0)
                ];

                if ($antiCheatData['fastAnswerFlag'] === 1) {
                    $quizController->lockQuizForUser($idQuiz, $idUser, $sessionKey, 'Fermeture automatique: reponses trop rapides detectees.');
                    $errorMessage = 'Quiz ferme: reponses trop rapides detectees. Vous devez demander le deverrouillage a l\'administrateur.';
                } else {
                    $evaluation = $quizController->evaluateAndSaveQuizResult($idQuiz, $answers, $antiCheatData, $idUser);

                    if (!empty($evaluation['success'])) {
                        $pourcentage = floatval($evaluation['pourcentage']);
                        $scorePoints = floatval($evaluation['scorePoints']);
                        $totalPoints = floatval($evaluation['totalPoints']);
                        $tabSwitchCount = intval($evaluation['tabSwitchCount']);
                        $inactivityTime = intval($evaluation['inactivityTime']);
                        $fastAnswerFlag = intval($evaluation['fastAnswerFlag']);

                        if ($evaluation['statut'] === 'reussi') {
                            $resultType = 'success';
                            $resultMessage = "Bravo ! Vous avez réussi le quiz avec " . $pourcentage . "% (" . $scorePoints . " / " . $totalPoints . " points).";
                        } else {
                            $resultType = 'failure';
                            $resultMessage = "Dommage, vous n’avez pas atteint le seuil de réussite de " . intval($quizData['seuilReussite']) . "%. Votre score est de " . $pourcentage . "% (" . $scorePoints . " / " . $totalPoints . " points).";
                        }
                    } else {
                        $resultType = 'failure';
                        $resultMessage = $evaluation['message'] ?? 'Impossible de corriger le quiz.';
                    }
                }
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
        .question-card.field-error { border-color: rgba(239,68,68,0.45); background: rgba(239,68,68,0.06); }
        .question-card h3 { margin-top: 0; font-size: 1.05rem; }
        .option { display:block; margin:0.65rem 0; padding:0.9rem 1rem; border:1px solid rgba(255,255,255,0.12); border-radius:12px; cursor:pointer; transition: all 0.2s ease; }
        .option:hover { border-color: rgba(234,179,8,0.55); background: rgba(234,179,8,0.08); }
        .submit-btn { margin-top: 1rem; }
        .result-box { background: rgba(234,179,8,0.08); border:1px solid rgba(234,179,8,0.35); color:#fff; padding:1rem 1.25rem; border-radius:16px; margin-bottom:1.5rem; }
        .result-box.success-box { background: rgba(16,185,129,0.12); border-color: rgba(16,185,129,0.35); color: #d1fae5; }
        .error-box { background: rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.4); color:#fecaca; padding:1rem 1.25rem; border-radius:16px; margin-bottom:1.5rem; }
        .warning-box { background: rgba(245,158,11,0.1); border:1px solid rgba(245,158,11,0.45); color:#fcd34d; padding:0.85rem 1rem; border-radius:12px; margin-bottom:1rem; display:none; }
        .anti-cheat-box { margin-top: 1rem; padding: 1rem 1.15rem; border-radius: 12px; border:1px solid rgba(255,255,255,0.14); background: rgba(255,255,255,0.04); }
        .lock-warning-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.82); display: none; align-items: center; justify-content: center; z-index: 9999; }
        .lock-warning-box { width: min(520px, 92vw); background: rgba(17,17,17,0.98); border: 1px solid rgba(245,158,11,0.5); border-radius: 16px; padding: 1.5rem; }
        .lock-warning-title { color: #fcd34d; font-weight: 800; font-size: 1.15rem; margin-bottom: 0.7rem; }
        .lock-warning-text { color: rgba(255,255,255,0.84); margin-bottom: 1rem; }
        .lock-warning-count { color: #f59e0b; font-weight: 800; }
        .lock-warning-actions { display: flex; justify-content: flex-end; }
        .lock-warning-btn { border: none; border-radius: 10px; background: linear-gradient(135deg,#22c55e,#16a34a); color: #fff; font-weight: 700; padding: 0.65rem 1rem; cursor: pointer; }
        .back-link { display:inline-flex; align-items:center; gap:0.5rem; color:#f3b82f; text-decoration:none; margin-bottom:1.5rem; }
    </style>
</head>
<body>
    <div class="quiz-page">
        <div id="lockWarningOverlay" class="lock-warning-overlay">
            <div class="lock-warning-box">
                <div class="lock-warning-title">Avertissement anti-triche</div>
                <div id="lockWarningText" class="lock-warning-text">Comportement suspect detecte.</div>
                <div class="lock-warning-text">Le quiz sera ferme dans <span id="lockWarningCount" class="lock-warning-count">10</span> secondes.</div>
                <div class="lock-warning-actions">
                    <button type="button" id="continueQuizBtn" class="lock-warning-btn">Je reprends le quiz</button>
                </div>
            </div>
        </div>

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
                    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && $resultType !== ''): ?>
                        <div class="anti-cheat-box">
                            <strong>Changements d'onglet :</strong> <?= htmlspecialchars($tabSwitchCount) ?><br>
                            <strong>Inactivite totale :</strong> <?= htmlspecialchars($inactivityTime) ?> s<br>
                            <strong>Reponses trop rapides :</strong> <?= $fastAnswerFlag ? 'oui' : 'non' ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (empty($questions)): ?>
                    <p>Aucune question n’est encore disponible pour ce quiz. Revenez plus tard ou ajoutez des questions via le back office.</p>
                <?php else: ?>
                    <div id="antiCheatWarning" class="warning-box"></div>
                    <form method="POST" id="quizPassageForm">
                        <!-- Anti-cheat: ces champs cachés transportent l'état de la tentative vers le serveur. -->
                        <input type="hidden" name="tabSwitchCount" id="tabSwitchCount" value="0">
                        <input type="hidden" name="inactivityTime" id="inactivityTime" value="0">
                        <input type="hidden" name="fastAnswerFlag" id="fastAnswerFlag" value="0">
                        <?php foreach ($questions as $index => $question): ?>
                            <?php $responses = $quizController->extractQuestionResponses($question); ?>
                            <?php
                                $qType = isset($question['type']) ? strtolower(trim($question['type'])) : '';
                                $bonne = isset($question['bonneReponse']) ? strtolower(trim($question['bonneReponse'])) : '';
                                $isOuverte = ($qType === 'ouverte' || strpos($qType, 'ouvre') !== false) || in_array($bonne, ['vrai', 'faux'], true);
                            ?>
                            <div class="question-card">
                                <h3>Question <?= $index + 1 ?>: <?= htmlspecialchars($question['enonce']) ?></h3>
                                <?php if ($isOuverte): ?>
                                    <label class="option">
                                        <input type="radio" name="answer[<?= $question['idQuestion'] ?>]" value="Vrai" style="margin-right:0.75rem;">
                                        Vrai
                                    </label>
                                    <label class="option">
                                        <input type="radio" name="answer[<?= $question['idQuestion'] ?>]" value="Faux" style="margin-right:0.75rem;">
                                        Faux
                                    </label>
                                <?php elseif (!empty($responses)): ?>
                                    <?php foreach ($responses as $responseIndex => $responseText): ?>
                                        <label class="option">
                                            <input type="radio" name="answer[<?= $question['idQuestion'] ?>]" value="<?= htmlspecialchars($responseText) ?>" style="margin-right:0.75rem;">
                                            <strong><?= $responseIndex + 1 ?>.</strong> <?= htmlspecialchars($responseText) ?>
                                        </label>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <input type="text" name="answer[<?= $question['idQuestion'] ?>]" placeholder="Votre réponse" style="width:100%; padding:0.95rem 1rem; border-radius:12px; border:1px solid rgba(255,255,255,0.12); background: rgba(255,255,255,0.03); color:#fff; font-family:inherit;">
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <button type="submit" class="submit-btn btn-primary">Envoyer mes réponses</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php if ($_SERVER['REQUEST_METHOD'] !== 'POST' && empty($resultMessage) && empty($errorMessage) && !empty($questions)): ?>
    <script src="../assets/index.js?v=20260503c"></script>
    <script>
        const quizForm = document.getElementById('quizPassageForm');
        const timerDisplay = document.getElementById('quizTimer');
        const warningBox = document.getElementById('antiCheatWarning');
        const tabSwitchInput = document.getElementById('tabSwitchCount');
        const inactivityInput = document.getElementById('inactivityTime');
        const fastAnswerInput = document.getElementById('fastAnswerFlag');
        const lockWarningOverlay = document.getElementById('lockWarningOverlay');
        const lockWarningText = document.getElementById('lockWarningText');
        const lockWarningCount = document.getElementById('lockWarningCount');
        const continueQuizBtn = document.getElementById('continueQuizBtn');
        const MAX_TAB_SWITCH = 2;
        const INACTIVITY_LIMIT_SECONDS = 120;
        const WARNING_DURATION_SECONDS = 10;
        let tabSwitchCount = 0;
        let inactivitySeconds = 0;
        let idleSeconds = 0;
        let antiCheatAlerts = 0;
        let lockCountdownInterval = null;
        let warningRunning = false;
        let forcedCloseInProgress = false;
        let allowPageLeave = false;
        const quizStartTs = Date.now();
        const questionCount = <?= count($questions) ?>;

        function showInlineWarning(message) {
            if (!warningBox) return;
            warningBox.textContent = message;
            warningBox.style.display = 'block';
        }

        function updateAntiCheatFields() {
            // Anti-cheat: calcule les indicateurs à partir du comportement réel pendant le passage.
            const elapsedSeconds = Math.floor((Date.now() - quizStartTs) / 1000);
            const fastThreshold = Math.max(10, questionCount * 4);
            const fastFlag = elapsedSeconds <= fastThreshold ? 1 : 0;

            tabSwitchInput.value = String(tabSwitchCount);
            inactivityInput.value = String(inactivitySeconds);
            fastAnswerInput.value = String(fastFlag);

            return { elapsedSeconds, fastFlag };
        }

        async function terminateQuiz(reason) {
            // Anti-cheat: ferme localement la tentative et demande le verrouillage côté serveur.
            if (forcedCloseInProgress) {
                return;
            }
            forcedCloseInProgress = true;
            warningRunning = false;
            if (lockCountdownInterval) {
                clearInterval(lockCountdownInterval);
                lockCountdownInterval = null;
            }

            updateAntiCheatFields();
            showInlineWarning('Quiz ferme automatiquement: ' + reason + ' Vous devez demander le deverrouillage a l\'administrateur.');

            try {
                const payload = new URLSearchParams();
                payload.set('action', 'lock_quiz');
                payload.set('reason', reason);
                payload.set('tabSwitchCount', tabSwitchInput.value);
                payload.set('inactivityTime', inactivityInput.value);
                payload.set('fastAnswerFlag', fastAnswerInput.value);

                await fetch(window.location.href, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                    body: payload.toString()
                });
            } catch (error) {
            }

            if (quizForm) {
                const controls = quizForm.querySelectorAll('input, button, select, textarea');
                controls.forEach((control) => {
                    control.disabled = true;
                });
            }

            allowPageLeave = true;
            setTimeout(() => {
                window.location.href = window.location.pathname + '?id=<?= intval($idQuiz) ?>';
            }, 800);
        }

        function startTenSecondWarning(message, lockReason) {
            if (warningRunning || forcedCloseInProgress) {
                return;
            }

            warningRunning = true;
            antiCheatAlerts += 1;
            let remaining = WARNING_DURATION_SECONDS;
            lockWarningText.textContent = message;
            lockWarningCount.textContent = String(remaining);
            lockWarningOverlay.style.display = 'flex';

            lockCountdownInterval = setInterval(() => {
                remaining -= 1;
                lockWarningCount.textContent = String(Math.max(remaining, 0));
                if (remaining <= 0) {
                    clearInterval(lockCountdownInterval);
                    lockCountdownInterval = null;
                    terminateQuiz(lockReason);
                }
            }, 1000);
        }

        function startProfessionalWarning(message) {
            // Anti-cheat: premier niveau de réaction visuelle avant blocage définitif.
            if (warningRunning || forcedCloseInProgress) {
                return;
            }

            warningRunning = true;
            antiCheatAlerts += 1;
            lockWarningText.textContent = message;
            if (lockWarningCount && lockWarningCount.parentNode) {
                lockWarningCount.parentNode.style.display = 'none';
            }
            lockWarningOverlay.style.display = 'flex';
        }

        if (continueQuizBtn) {
            continueQuizBtn.addEventListener('click', () => {
                if (!warningRunning) {
                    return;
                }
                warningRunning = false;
                idleSeconds = 0;
                if (lockCountdownInterval) {
                    clearInterval(lockCountdownInterval);
                    lockCountdownInterval = null;
                }
                lockWarningOverlay.style.display = 'none';
                showInlineWarning('Avertissement pris en compte. Reprenez votre quiz sans quitter la page.');
            });
        }

        document.addEventListener('visibilitychange', () => {
            // Anti-cheat: chaque perte de visibilité de l'onglet est comptée comme tentative de sortie.
            if (document.hidden && !forcedCloseInProgress) {
                tabSwitchCount += 1;
                updateAntiCheatFields();
                if (tabSwitchCount <= MAX_TAB_SWITCH) {
                    startProfessionalWarning('Avertissement anti-triche : Vous avez changé d\'onglet. Tentative ' + tabSwitchCount + '/' + MAX_TAB_SWITCH + '. Après ' + MAX_TAB_SWITCH + ' tentatives, le quiz sera bloqué.');
                } else {
                    terminateQuiz('Fermeture automatique: plus de 2 changements d\'onglet.');
                }
            }
        });

        window.addEventListener('beforeunload', (event) => {
            if (allowPageLeave) {
                return;
            }
            event.preventDefault();
            event.returnValue = 'Quitter la page mettra fin a la tentative en cours.';
        });

        ['mousemove', 'keydown', 'scroll', 'click', 'touchstart'].forEach((eventName) => {
            document.addEventListener(eventName, () => {
                idleSeconds = 0;
            }, { passive: true });
        });

        setInterval(() => {
            if (forcedCloseInProgress) {
                return;
            }
            idleSeconds += 1;
            if (idleSeconds >= 3) {
                inactivitySeconds += 1;
            }
            updateAntiCheatFields();
        }, 1000);

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
                if (forcedCloseInProgress) {
                    clearInterval(interval);
                    return;
                }
                timeRemaining -= 1;
                if (timeRemaining <= 0) {
                    clearInterval(interval);
                    localStorage.removeItem(storeKey);
                    timerDisplay.textContent = '00:00';
                    updateAntiCheatFields();
                    const timeoutMessage = document.createElement('div');
                    timeoutMessage.className = 'result-box error-box';
                    timeoutMessage.textContent = 'Le temps imparti est ecoule. Vos reponses sont envoyees automatiquement.';
                    quizForm.parentNode.insertBefore(timeoutMessage, quizForm);
                    allowPageLeave = true;
                    setTimeout(() => quizForm.submit(), 800);
                    return;
                }
                timerDisplay.textContent = formatTime(timeRemaining);
                localStorage.setItem(storeKey, timeRemaining);
            }, 1000);

            quizForm.addEventListener('submit', (event) => {
                // Anti-cheat: blocage immédiat si passage trop rapide ou trop de changements d'onglet.
                if (forcedCloseInProgress) {
                    event.preventDefault();
                    return;
                }

                const antiData = updateAntiCheatFields();
                if (antiData.fastFlag === 1) {
                    event.preventDefault();
                    terminateQuiz('Fermeture automatique: reponses trop rapides detectees.');
                    return;
                }

                if (tabSwitchCount > MAX_TAB_SWITCH) {
                    event.preventDefault();
                    terminateQuiz('Fermeture automatique: comportement suspect persistant (changement d\'onglet).');
                    return;
                }

                allowPageLeave = true;
                localStorage.removeItem(storeKey);
            });

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
