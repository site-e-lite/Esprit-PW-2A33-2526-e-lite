<?php
require_once __DIR__ . '/../../Controller/QuestionController.php';
require_once __DIR__ . '/../../Controller/QuizController.php';
$questionController = new QuestionController();
$quizController = new QuizController();
$quizList = $quizController->afficherQuizs();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $enonce = trim($_POST['enonce'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $choixA = trim($_POST['choixA'] ?? '');
    $choixB = trim($_POST['choixB'] ?? '');
    $choixC = trim($_POST['choixC'] ?? '');
    $choixD = trim($_POST['choixD'] ?? '');
    $bonneReponse = trim($_POST['bonneReponse'] ?? '');
    $note = floatval($_POST['note'] ?? 0);
    $explication = trim($_POST['explication'] ?? '');
    $idQuiz = intval($_POST['idQuiz'] ?? 0);

    $validQCM = true;
    if ($type === 'QCM') {
        $filled = 0;
        foreach ([$choixA, $choixB, $choixC, $choixD] as $choice) {
            if ($choice !== '') { $filled++; }
        }
        $validQCM = $filled >= 2;
    }

    if ($enonce !== '' && $type !== '' && $bonneReponse !== '' && $note > 0 && $idQuiz > 0 && $validQCM) {
        $question = new Question($enonce, $type, $choixA, $choixB, $choixC, $choixD, $bonneReponse, $note, $idQuiz, $explication);
        $questionController->addQuestion($question);
        header('Location: questions_list.php?added=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter Question | e-lite BackOffice</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { display: flex; background-color: var(--black); margin: 0; overflow-x: hidden; }
        #front-header { display: none; }
        .admin-sidebar { width: 280px; height: 100vh; background: rgba(10,10,10,0.95); border-right: 1px solid var(--glass-border); position: fixed; top: 0; left: 0; display: flex; flex-direction: column; padding: 2rem 1.5rem; z-index: 100; }
        .admin-sidebar .logo { font-size: 2rem; margin-bottom: 3rem; text-align: center; text-decoration: none; color: inherit; }
        .admin-nav { display: flex; flex-direction: column; gap: 1rem; list-style: none; padding: 0; margin: 0; }
        .admin-nav li a { display: flex; align-items: center; gap: 1rem; color: var(--light-gray); text-decoration: none; padding: 1rem 1.5rem; border-radius: 12px; font-weight: 500; transition: all 0.3s; }
        .admin-nav li a i { font-size: 1.2rem; width: 20px; text-align: center; }
        .admin-nav li a:hover, .admin-nav li a.active { background: rgba(234,179,8,0.1); color: var(--accent); transform: translateX(5px); box-shadow: inset 2px 0 0 var(--accent); }
        .admin-content { margin-left: 280px; flex: 1; padding: 2.5rem 4rem; min-height: 100vh; background: radial-gradient(circle at top right, rgba(234,179,8,0.05) 0%, transparent 40%); }
        .form-card { max-width: 720px; margin: 0 auto; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 20px; padding: 2.5rem; backdrop-filter: blur(12px); position: relative; overflow: hidden; }
        .form-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, #eab308, #f59e0b, #d97706); border-radius: 20px 20px 0 0; }
        .card-title { display: flex; align-items: center; gap: 0.8rem; font-size: 1.5rem; font-weight: 700; margin-bottom: 0.4rem; color: #fff; }
        .card-title i { color: #eab308; }
        .card-subtitle { color: rgba(255,255,255,0.4); font-size: 0.9rem; margin-bottom: 2rem; }
        .back-link { display: inline-flex; align-items: center; gap: 0.5rem; color: rgba(255,255,255,0.4); text-decoration: none; font-size: 0.85rem; margin-bottom: 2rem; transition: color 0.2s; }
        .back-link:hover { color: #eab308; }
        .field-group { position: relative; margin-bottom: 1.8rem; }
        .field-group label { display: block; margin-bottom: 0.6rem; font-size: 0.85rem; font-weight: 700; color: rgba(255,255,255,0.65); text-transform: uppercase; letter-spacing: 0.05em; }
        .field-group input, .field-group select, .field-group textarea { width: 100%; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.12); border-radius: 12px; color: #fff; font-family: inherit; font-size: 0.95rem; padding: 1rem 1.1rem; outline: none; transition: border-color 0.3s, background 0.3s, box-shadow 0.3s; resize: vertical; }
        .field-group input:focus, .field-group select:focus, .field-group textarea:focus { border-color: #eab308; background: rgba(234,179,8,0.05); box-shadow: 0 0 0 3px rgba(234,179,8,0.1); }
        .field-msg { margin-top: 0.4rem; font-size: 0.82rem; min-height: 1rem; color: rgba(255,255,255,0.55); }
        .field-error input, .field-error select, .field-error textarea { border-color: #ef4444; }
        .field-error .field-msg { color: #ef4444; }
        .field-success input, .field-success select, .field-success textarea { border-color: #10b981; }
        .field-success .field-msg { color: #10b981; }
        .submit-btn { width: 100%; padding: 1rem; border: none; border-radius: 12px; background: linear-gradient(135deg, #eab308, #d97706); color: #000; font-weight: 700; font-size: 1rem; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 0.6rem; transition: transform 0.2s, box-shadow 0.3s; margin-top: 1rem; font-family: inherit; }
        .submit-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(234,179,8,0.35); }
        .submit-btn:disabled { opacity: 0.5; cursor: not-allowed; }
    </style>
</head>
<body>
    <aside class="admin-sidebar">
        <a href="dashboard.php" class="logo">e-lite<span>.</span><div style="font-size:0.8rem; letter-spacing:3px; color:rgba(255,255,255,0.4); font-family:inherit; text-transform:uppercase; font-weight:400;">BackOffice</div></a>
        <ul class="admin-nav">
            <li><a href="quizzes_list.php"><i class="fas fa-tasks"></i> Liste des Quiz</a></li>
            <li><a href="quiz_add.php"><i class="fas fa-plus-circle"></i> Ajouter un Quiz</a></li>
            <li><a href="questions_list.php"><i class="fas fa-question-circle"></i> Liste des Questions</a></li>
            <li><a href="question_add.php" class="active"><i class="fas fa-plus-square"></i> Ajouter une Question</a></li>
        </ul>
    </aside>

    <main class="admin-content">
        <a href="questions_list.php" class="back-link"><i class="fas fa-arrow-left"></i> Retour à la liste des questions</a>

        <div class="form-card">
            <div class="card-title"><i class="fas fa-plus-circle"></i> Ajouter une Question</div>
            <p class="card-subtitle">Saisissez la question avec ses choix et les valeurs correctes.</p>

            <form id="questionAddForm" action="" method="POST" onsubmit="return validateQuestionForm(this)">
                <div class="field-group" id="fg-enonce">
                    <label for="enonce">Énoncé</label>
                    <textarea id="enonce" name="enonce" rows="4" placeholder="Texte de la question"></textarea>
                    <div class="field-msg" id="enonce-msg"></div>
                </div>

                <div class="field-group" id="fg-type">
                    <label for="type">Type</label>
                    <select id="type" name="type" onchange="toggleQCMHelp()">
                        <option value="">Sélectionnez</option>
                        <option value="QCM">QCM</option>
                        <option value="Ouverte">Ouverte</option>
                    </select>
                    <div class="field-msg" id="type-msg"></div>
                </div>

                <div class="field-group" id="fg-choixA">
                    <label for="choixA">Choix A</label>
                    <input type="text" id="choixA" name="choixA" placeholder="Option A">
                    <div class="field-msg" id="choixA-msg"></div>
                </div>
                <div class="field-group" id="fg-choixB">
                    <label for="choixB">Choix B</label>
                    <input type="text" id="choixB" name="choixB" placeholder="Option B">
                    <div class="field-msg" id="choixB-msg"></div>
                </div>
                <div class="field-group" id="fg-choixC">
                    <label for="choixC">Choix C</label>
                    <input type="text" id="choixC" name="choixC" placeholder="Option C">
                    <div class="field-msg" id="choixC-msg"></div>
                </div>
                <div class="field-group" id="fg-choixD">
                    <label for="choixD">Choix D</label>
                    <input type="text" id="choixD" name="choixD" placeholder="Option D">
                    <div class="field-msg" id="choixD-msg"></div>
                </div>

                <div class="field-group" id="fg-bonneReponse">
                    <label for="bonneReponse">Bonne réponse</label>
                    <input type="text" id="bonneReponse" name="bonneReponse" placeholder="Ex: A, B, C, D ou réponse texte">
                    <div class="field-msg" id="bonneReponse-msg"></div>
                </div>

                <div class="field-group" id="fg-note">
                    <label for="note">Note</label>
                    <input type="number" step="0.1" id="note" name="note" min="0.1" placeholder="Ex: 2.5">
                    <div class="field-msg" id="note-msg"></div>
                </div>

                <div class="field-group" id="fg-explication">
                    <label for="explication">Explication</label>
                    <textarea id="explication" name="explication" rows="3" placeholder="Optionnel, explication de la réponse"></textarea>
                    <div class="field-msg" id="explication-msg"></div>
                </div>

                <div class="field-group" id="fg-idQuiz">
                    <label for="idQuiz">ID Quiz</label>
                    <input type="number" id="idQuiz" name="idQuiz" min="1" value="<?= htmlspecialchars($_GET['idQuiz'] ?? '') ?>" placeholder="Ex: 5">
                    <div class="field-msg" id="idQuiz-msg"></div>
                </div>

                <button type="submit" class="submit-btn"><i class="fas fa-save"></i> Enregistrer la question</button>
            </form>
        </div>
    </main>

<script src="../assets/index.js"></script>
<script>
function toggleQCMHelp() {
    const type = document.getElementById('type').value;
    const message = type === 'QCM' ? 'Pour un QCM, remplissez au moins deux choix et indiquez la réponse correspondante.' : 'Pour une question ouverte, la bonne réponse peut être du texte libre.';
    document.getElementById('type-msg').textContent = message;
}
</script>
</body>
</html>
