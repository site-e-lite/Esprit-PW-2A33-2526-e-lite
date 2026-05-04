<?php
require_once __DIR__ . '/../../Controller/QuizController.php';
require_once __DIR__ . '/../../Controller/Validator.php';
$quizController = new QuizController();
// Charger toutes les questions, filtrage par niveau géré en JavaScript côté client
$questionsForAssignment = $quizController->getQuestionsForAssignment();
$courses = $quizController->getAllCourses();
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!$quizController->validateQuiz($_POST)) {
        $errors = Validator::getErrors();
    } else {
        $titre = trim($_POST['titre']);
        $duree = intval($_POST['duree']);
        $seuilReussite = intval($_POST['seuilReussite']);
        $niveau = trim($_POST['niveau']);
        $statut = trim($_POST['statut']);
        $idCourse = intval($_POST['idCourse']);
        $selectedQuestionIds = $_POST['questionIds'] ?? [];

        // Valider que toutes les questions sélectionnées correspondent au niveau du quiz
        if (!empty($selectedQuestionIds) && !$quizController->validateQuizQuestionMatch($niveau, $selectedQuestionIds)) {
            Validator::addError('questionIds', 'Toutes les questions doivent être du même niveau que le quiz.');
            $errors = Validator::getErrors();
        } elseif (empty($selectedQuestionIds) && $statut === 'Actif') {
            // Un quiz sans question ne peut pas être actif
            Validator::addError('statut', 'Un quiz sans question ne peut pas être Actif.');
            $errors = Validator::getErrors();
        } else {
            $quiz = new Quiz($titre, $duree, $seuilReussite, $niveau, $statut, $idCourse);
            $quizController->addQuiz($quiz, $selectedQuestionIds);
            header('Location: quizzes_list.php?added=1');
            exit;
        }
    }
}

    $selectedQuestionIds = isset($_POST['questionIds']) && is_array($_POST['questionIds']) ? array_map('intval', $_POST['questionIds']) : [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter Quiz | e-lite BackOffice</title>
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
        .question-picker { max-height: 280px; overflow-y: auto; padding: 0.8rem; border: 1px solid rgba(255,255,255,0.12); border-radius: 12px; background: rgba(255,255,255,0.03); }
        .question-item { display: flex; gap: 0.7rem; align-items: flex-start; padding: 0.65rem 0.5rem; border-bottom: 1px solid rgba(255,255,255,0.06); }
        .question-item:last-child { border-bottom: none; }
        .question-item input { width: auto; margin-top: 0.2rem; }
        .question-label { font-size: 0.9rem; color: rgba(255,255,255,0.9); line-height: 1.35; }
        .question-meta { display: block; margin-top: 0.2rem; font-size: 0.78rem; color: rgba(255,255,255,0.45); }
        .field-group label { display: block; margin-bottom: 0.6rem; font-size: 0.85rem; font-weight: 700; color: rgba(255,255,255,0.65); text-transform: uppercase; letter-spacing: 0.05em; }
        .field-group input, .field-group select { width: 100%; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.12); border-radius: 12px; color: #fff; font-family: inherit; font-size: 0.95rem; padding: 1rem 1.1rem; outline: none; transition: border-color 0.3s, background 0.3s, box-shadow 0.3s; }
        .field-group select option { background: #1a1a1a; color: #ffffff; }
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
            <li><a href="quiz_add.php" class="active"><i class="fas fa-plus-circle"></i> Ajouter un Quiz</a></li>
            <li><a href="questions_list.php"><i class="fas fa-question-circle"></i> Liste des Questions</a></li>
            <li><a href="question_add.php"><i class="fas fa-plus-square"></i> Ajouter une Question</a></li>
        </ul>
    </aside>

    <main class="admin-content">
        <a href="quizzes_list.php" class="back-link"><i class="fas fa-arrow-left"></i> Retour à la liste des quiz</a>

        <div class="form-card">
            <div class="card-title"><i class="fas fa-plus-circle"></i> Ajouter un Quiz</div>
            <p class="card-subtitle">Remplissez les informations du quiz pour l’ajouter à la plateforme.</p>

            <form id="quizAddForm" action="" method="POST">
                <div class="field-group" id="fg-titre">
                    <label for="titre">Titre</label>
                    <input type="text" id="titre" name="titre" placeholder="Titre du quiz" value="<?= isset($errors) ? htmlspecialchars($_POST['titre'] ?? '') : '' ?>">
                    <div class="field-msg" id="titre-msg"><?= htmlspecialchars($errors['titre'] ?? '') ?></div>
                </div>

                <div class="field-group" id="fg-duree">
                    <label for="duree">Durée (minutes)</label>
                    <input type="number" id="duree" name="duree" placeholder="Ex: 20" value="<?= isset($errors) ? htmlspecialchars($_POST['duree'] ?? '') : '' ?>">
                    <div class="field-msg" id="duree-msg"><?= htmlspecialchars($errors['duree'] ?? '') ?></div>
                </div>

                <div class="field-group" id="fg-seuilReussite">
                    <label for="seuilReussite">Seuil de réussite (%)</label>
                    <input type="number" id="seuilReussite" name="seuilReussite" placeholder="40 à 100" value="<?= isset($errors) ? htmlspecialchars($_POST['seuilReussite'] ?? '') : '' ?>">
                    <div class="field-msg" id="seuil-msg"><?= htmlspecialchars($errors['seuilReussite'] ?? '') ?></div>
                </div>

                <div class="field-group" id="fg-niveau">
                    <label for="niveau">Niveau</label>
                    <select id="niveau" name="niveau">
                        <option value="">Sélectionnez</option>
                        <option value="Débutant" <?= (isset($_POST['niveau']) && $_POST['niveau'] === 'Débutant') ? 'selected' : '' ?>>Débutant</option>
                        <option value="Intermédiaire" <?= (isset($_POST['niveau']) && $_POST['niveau'] === 'Intermédiaire') ? 'selected' : '' ?>>Intermédiaire</option>
                        <option value="Avancé" <?= (isset($_POST['niveau']) && $_POST['niveau'] === 'Avancé') ? 'selected' : '' ?>>Avancé</option>
                    </select>
                    <div class="field-msg" id="niveau-msg"><?= htmlspecialchars($errors['niveau'] ?? '') ?></div>
                </div>

                <div class="field-group" id="fg-idCourse">
                    <label for="idCourse">Cours associé</label>
                    <select id="idCourse" name="idCourse">
                        <option value="">Sélectionnez un cours</option>
                        <?php if ($courses): ?>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?= htmlspecialchars($course['idCourse']) ?>" <?= (isset($_POST['idCourse']) && intval($_POST['idCourse']) === intval($course['idCourse'])) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($course['titre']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="">Aucun cours disponible</option>
                        <?php endif; ?>
                    </select>
                    <div class="field-msg" id="idCourse-msg"><?= htmlspecialchars($errors['idCourse'] ?? '') ?></div>
                </div>

                <div class="field-group" id="fg-statut">
                    <label for="statut">Statut</label>
                    <select id="statut" name="statut">
                        <option value="Actif" <?= (isset($_POST['statut']) && $_POST['statut'] === 'Actif') ? 'selected' : 'selected' ?>>Actif</option>
                        <option value="Inactif" <?= (isset($_POST['statut']) && $_POST['statut'] === 'Inactif') ? 'selected' : '' ?>>Inactif</option>
                        <option value="Brouillon" <?= (isset($_POST['statut']) && $_POST['statut'] === 'Brouillon') ? 'selected' : '' ?>>Brouillon</option>
                    </select>
                    <div class="field-msg" id="statut-msg"><?= htmlspecialchars($errors['statut'] ?? '') ?></div>
                </div>

                <div class="field-group" id="fg-questionIds">
                    <label>Questions à associer (optionnel)</label>
                    <div class="question-picker">
                        <?php if ($questionsForAssignment && $questionsForAssignment->rowCount() > 0): ?>
                            <?php while ($question = $questionsForAssignment->fetch()): ?>
                                <?php
                                    $questionId = intval($question['idQuestion']);
                                    $isChecked = in_array($questionId, $selectedQuestionIds, true);
                                    $isLinked = !empty($question['idQuiz']);
                                    $questionNiveau = $question['niveau'] ?? '';
                                ?>
                                <label class="question-item" for="q-<?= $questionId ?>" data-niveau="<?= htmlspecialchars($questionNiveau) ?>">
                                    <input type="checkbox" id="q-<?= $questionId ?>" name="questionIds[]" value="<?= $questionId ?>" <?= $isChecked ? 'checked' : '' ?>>
                                    <span class="question-label">
                                        #<?= $questionId ?> - <?= htmlspecialchars(substr($question['enonce'], 0, 120)) ?><?= strlen($question['enonce']) > 120 ? '...' : '' ?>
                                        <span class="question-meta">
                                            <?= $isLinked ? 'Deja associee a un quiz (sera reaffectee si selectionnee).' : 'Non liee a un quiz.' ?>
                                            (Niveau: <?= htmlspecialchars($questionNiveau ?: 'Non défini') ?>)
                                        </span>
                                    </span>
                                </label>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="field-msg">Aucune question disponible.</div>
                        <?php endif; ?>
                    </div>
                    <div class="field-msg" id="questionIds-msg"><?= htmlspecialchars($errors['questionIds'] ?? '') ?></div>
                    <div class="field-msg">Vous pouvez selectionner plusieurs questions existantes.</div>
                </div>

                <button type="submit" class="submit-btn"><i class="fas fa-save"></i> Enregistrer le quiz</button>
            </form>
        </div>
    </main>

    <script src="../assets/index.js?v=20260503g"></script>
</body>
</html>
