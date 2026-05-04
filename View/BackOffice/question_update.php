<?php
require_once __DIR__ . '/../../Controller/QuestionController.php';
require_once __DIR__ . '/../../Controller/Validator.php';
$questionController = new QuestionController();
$errors = [];

$questionData = null;
if (isset($_GET['id'])) {
    $questionData = $questionController->getQuestionById($_GET['id']);
}

$postedResponses = (isset($_POST['reponses']) && is_array($_POST['reponses'])) ? $_POST['reponses'] : [];
$currentType = $_POST['type'] ?? ($questionData['type'] ?? '');
$responsesForForm = !empty($postedResponses)
    ? $postedResponses
    : ($questionData ? $questionController->extractQuestionResponses($questionData) : []);
if ($currentType === '' && count($responsesForForm) >= 2) {
    $currentType = 'QCU';
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!$questionController->validateQuestion($_POST)) {
        $errors = Validator::getErrors();
    } else {
        $idQuestion = intval($_POST['idQuestion'] ?? 0);
        $enonce = trim($_POST['enonce']);
        $type = trim($_POST['type']);
        $bonneReponse = trim($_POST['bonneReponse']);
        $note = floatval($_POST['note']);
        $explication = trim($_POST['explication'] ?? '');
        $niveau = trim($_POST['niveau'] ?? 'Débutant');
        $idQuiz = $questionData['idQuiz'] ?? null;

        $question = new Question($enonce, $type, '', '', '', '', $bonneReponse, $note, $idQuiz, $explication, $idQuestion, $postedResponses, $niveau);
        $questionController->updateQuestion($question, $idQuestion);
        header('Location: questions_list.php?updated=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Question | e-lite BackOffice</title>
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
        .field-group select option { background: #1a1a1a; color: #ffffff; }
        .responses-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 0.9rem; flex-wrap: wrap; }
        .responses-hint { color: rgba(255,255,255,0.45); font-size: 0.82rem; }
        .responses-container { display: grid; gap: 0.75rem; }
        .response-row { display: flex; gap: 0.75rem; align-items: center; }
        .response-row input { flex: 1; }
        .add-response-btn, .remove-response-btn { border: 1px solid rgba(255,255,255,0.14); background: rgba(255,255,255,0.05); color: #fff; border-radius: 10px; font-family: inherit; cursor: pointer; transition: background 0.2s, border-color 0.2s, transform 0.2s; }
        .add-response-btn { padding: 0.7rem 1rem; color: #eab308; border-color: rgba(234,179,8,0.3); }
        .add-response-btn:hover, .remove-response-btn:hover { background: rgba(234,179,8,0.12); border-color: rgba(234,179,8,0.35); transform: translateY(-1px); }
        .remove-response-btn { width: 44px; height: 44px; flex: 0 0 44px; color: rgba(255,255,255,0.7); }
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
            <li><a href="questions_list.php" class="active"><i class="fas fa-question-circle"></i> Liste des Questions</a></li>
            <li><a href="question_add.php"><i class="fas fa-plus-square"></i> Ajouter une Question</a></li>
        </ul>
    </aside>

    <main class="admin-content">
        <a href="questions_list.php" class="back-link"><i class="fas fa-arrow-left"></i> Retour à la liste des questions</a>

        <div class="form-card">
            <div class="card-title"><i class="fas fa-edit"></i> Modifier une Question</div>
            <p class="card-subtitle">Mettez à jour le texte et les réponses de la question.</p>

            <?php if ($questionData): ?>
            <form id="questionUpdateForm" action="" method="POST">
                <input type="hidden" name="idQuestion" value="<?= htmlspecialchars($questionData['idQuestion']) ?>">

                <div class="field-group" id="fg-enonce">
                    <label for="enonce">Énoncé</label>
                    <textarea id="enonce" name="enonce" rows="4"><?= htmlspecialchars($questionData['enonce']) ?></textarea>
                    <div class="field-msg" id="enonce-msg"><?= htmlspecialchars($errors['enonce'] ?? '') ?></div>
                </div>

                <div class="field-group" id="fg-type">
                    <label for="type">Type</label>
                    <select id="type" name="type" onchange="toggleQCUHelp()">
                        <option value="">Sélectionnez</option>
                        <option value="QCU"<?= $currentType === 'QCU' ? ' selected' : '' ?>>QCU</option>
                        <option value="Ouverte"<?= $currentType === 'Ouverte' ? ' selected' : '' ?>>Ouverte</option>
                    </select>
                    <div class="field-msg" id="type-msg"><?= htmlspecialchars($errors['type'] ?? '') ?></div>
                </div>

                <div class="field-group" id="fg-responses" style="<?= ($currentType === 'QCU') ? '' : 'display:none;' ?>">
                    <label>Réponses proposées</label>
                    <div class="responses-toolbar">
                        <button type="button" class="add-response-btn" id="addResponseBtn"><i class="fas fa-plus"></i> Ajouter une réponse</button>
                        <span class="responses-hint">Ajoutez autant de réponses que nécessaire pour une QCU.</span>
                    </div>
                    <div class="responses-container" id="responsesContainer">
                        <?php foreach ($responsesForForm as $index => $response): ?>
                            <div class="response-row">
                                <input type="text" name="reponses[]" value="<?= htmlspecialchars($response) ?>" placeholder="Réponse <?= $index + 1 ?>">
                                <button type="button" class="remove-response-btn" aria-label="Supprimer cette réponse"><i class="fas fa-times"></i></button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="field-msg" id="responses-msg"></div>
                </div>

                <div class="field-group" id="fg-bonneReponse">
                    <label for="bonneReponseText">Bonne réponse</label>
                    <input type="hidden" id="bonneReponse" name="bonneReponse" value="<?= htmlspecialchars($questionData['bonneReponse'] ?? '') ?>">
                    <input type="text" id="bonneReponseText" placeholder="Réponse correcte" value="<?= htmlspecialchars($questionData['bonneReponse'] ?? '') ?>">
                    <div class="field-msg" id="bonneReponse-msg"><?= htmlspecialchars($errors['bonneReponse'] ?? '') ?></div>
                </div>

                <div class="field-group" id="fg-openResponses" style="display:none;">
                    <label>Réponse (Question ouverte)</label>
                    <div style="display:flex;gap:1rem;align-items:center;">
                        <label><input type="radio" name="openChoice" value="Vrai" <?= (isset($_POST['bonneReponse']) ? $_POST['bonneReponse'] : ($questionData['bonneReponse'] ?? '')) === 'Vrai' ? 'checked' : '' ?>> Vrai</label>
                        <label><input type="radio" name="openChoice" value="Faux" <?= (isset($_POST['bonneReponse']) ? $_POST['bonneReponse'] : ($questionData['bonneReponse'] ?? '')) === 'Faux' ? 'checked' : '' ?>> Faux</label>
                    </div>
                    <div class="field-msg">Pour une question ouverte, choisissez l'option vraie/faux/ni vrai ni faux.</div>
                </div>

                <div class="field-group" id="fg-note">
                    <label for="note">Note</label>
                    <input type="number" id="note" name="note" value="<?= htmlspecialchars($questionData['note']) ?>">
                    <div class="field-msg" id="note-msg"><?= htmlspecialchars($errors['note'] ?? '') ?></div>
                </div>

                <div class="field-group" id="fg-niveau">
                    <label for="niveau">Niveau</label>
                    <select id="niveau" name="niveau">
                        <option value="Débutant" <?= ($questionData['niveau'] ?? 'Débutant') === 'Débutant' ? 'selected' : '' ?>>Débutant</option>
                        <option value="Intermédiaire" <?= ($questionData['niveau'] ?? '') === 'Intermédiaire' ? 'selected' : '' ?>>Intermédiaire</option>
                        <option value="Avancé" <?= ($questionData['niveau'] ?? '') === 'Avancé' ? 'selected' : '' ?>>Avancé</option>
                    </select>
                    <div class="field-msg" id="niveau-msg"><?= htmlspecialchars($errors['niveau'] ?? '') ?></div>
                </div>

                <div class="field-group" id="fg-explication">
                    <label for="explication">Explication</label>
                    <textarea id="explication" name="explication" rows="3"><?= htmlspecialchars($questionData['explication']) ?></textarea>
                    <div class="field-msg" id="explication-msg"></div>
                </div>

                <div class="field-msg" style="margin-bottom: 1rem;">
                    L'association au quiz se gère dans le formulaire de quiz. Cette édition ne modifie pas le quiz lié.
                </div>

                <button type="submit" class="submit-btn"><i class="fas fa-save"></i> Enregistrer les modifications</button>
            </form>
            <?php else: ?>
                <div style="padding: 3rem 1rem; text-align:center; color:rgba(255,255,255,0.5);">
                    <i class="fas fa-exclamation-circle" style="font-size:3rem;margin-bottom:1rem;color:rgba(255,255,255,0.3);"></i>
                    <p>Question introuvable ou identifiant manquant.</p>
                    <a href="questions_list.php" class="submit-btn" style="width:auto; padding:0.75rem 1.5rem; text-decoration:none;">Retour à la liste</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="../assets/index.js?v=20260503d"></script>

    <script>
        function toggleQCUHelp() {
            const type = document.getElementById('type')?.value;
            const responsesGroup = document.getElementById('fg-responses');
            const openGroup = document.getElementById('fg-openResponses');
            const message = type === 'QCU'
                ? 'Pour une QCU, ajoutez autant de réponses que nécessaire puis saisissez la bonne réponse.'
                : 'Pour une question ouverte, sélectionnez Vrai / Faux / Ni vrai ni faux.';
            const msg = document.getElementById('type-msg');
            if (msg) {
                msg.textContent = message;
            }
            if (responsesGroup) {
                responsesGroup.style.display = type === 'QCU' ? 'block' : 'none';
            }
            if (openGroup) {
                openGroup.style.display = type === 'Ouverte' ? 'block' : 'none';
            }

            const bonneReponseText = document.getElementById('bonneReponseText');
            const bonneReponseHidden = document.getElementById('bonneReponse');
            if (bonneReponseText && bonneReponseHidden) {
                if (type === 'QCU') {
                    bonneReponseText.style.display = 'block';
                } else {
                    bonneReponseText.style.display = 'none';
                }
            }

            if (type === 'QCU' && window.ensureQuestionResponseRows) {
                window.ensureQuestionResponseRows(document.getElementById('questionUpdateForm'));
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            toggleQCUHelp();
            const txt = document.getElementById('bonneReponseText');
            const hidden = document.getElementById('bonneReponse');
            if (txt && hidden) {
                txt.addEventListener('input', function() { hidden.value = txt.value; });
            }

            // radios for open question
            const radios = document.querySelectorAll('input[name="openChoice"]');
            radios.forEach(r => {
                r.addEventListener('change', function() {
                    const h = document.getElementById('bonneReponse');
                    if (h) h.value = this.value;
                });
            });
            // check radio that matches hidden value
            if (hidden) {
                radios.forEach(r => { if (r.value === hidden.value) r.checked = true; });
            }
        });
    </script>
</body>
</html>
