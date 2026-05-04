<?php
require_once __DIR__ . '/../../../Controller/QuizController.php';
$quizController = new QuizController();
$formData = $quizController->generateForm();
$courses = $quizController->getAllCourses();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $quizController->generateQuiz($_POST);
    if (!empty($result['success'])) {
        header('Location: ../quizzes_list.php?generated=1');
        exit;
    }
    $errors = $result['errors'] ?? ['global' => 'Une erreur est survenue pendant la generation.'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generer un Quiz | e-lite BackOffice</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/index.css">
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
        .form-card { max-width: 760px; margin: 0 auto; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 20px; padding: 2.5rem; backdrop-filter: blur(12px); position: relative; overflow: hidden; }
        .form-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, #eab308, #f59e0b, #d97706); border-radius: 20px 20px 0 0; }
        .card-title { display: flex; align-items: center; gap: 0.8rem; font-size: 1.5rem; font-weight: 700; margin-bottom: 0.4rem; color: #fff; }
        .card-title i { color: #eab308; }
        .card-subtitle { color: rgba(255,255,255,0.4); font-size: 0.9rem; margin-bottom: 2rem; }
        .back-link { display: inline-flex; align-items: center; gap: 0.5rem; color: rgba(255,255,255,0.4); text-decoration: none; font-size: 0.85rem; margin-bottom: 2rem; transition: color 0.2s; }
        .back-link:hover { color: #eab308; }
        .field-group { position: relative; margin-bottom: 1.6rem; }
        .field-group label { display: block; margin-bottom: 0.6rem; font-size: 0.85rem; font-weight: 700; color: rgba(255,255,255,0.65); text-transform: uppercase; letter-spacing: 0.05em; }
        .field-group input, .field-group select { width: 100%; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.12); border-radius: 12px; color: #fff; font-family: inherit; font-size: 0.95rem; padding: 1rem 1.1rem; outline: none; transition: border-color 0.3s, background 0.3s, box-shadow 0.3s; }
        .field-group select option { background: #1a1a1a; color: #ffffff; }
        .field-group input:focus, .field-group select:focus { border-color: #eab308; background: rgba(234,179,8,0.05); box-shadow: 0 0 0 3px rgba(234,179,8,0.1); }
        .field-msg { margin-top: 0.4rem; font-size: 0.82rem; min-height: 1rem; color: rgba(255,255,255,0.55); }
        .field-error .field-msg { color: #ef4444; }
        .global-error { margin-bottom: 1rem; padding: 0.9rem 1rem; border-radius: 10px; border: 1px solid rgba(239,68,68,0.45); background: rgba(239,68,68,0.1); color: #fecaca; }
        .submit-btn { width: 100%; padding: 1rem; border: none; border-radius: 12px; background: linear-gradient(135deg, #eab308, #d97706); color: #000; font-weight: 700; font-size: 1rem; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 0.6rem; transition: transform 0.2s, box-shadow 0.3s; margin-top: 0.8rem; font-family: inherit; }
        .submit-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(234,179,8,0.35); }
    </style>
</head>
<body>
    <aside class="admin-sidebar">
        <a href="../dashboard.php" class="logo">e-lite<span>.</span><div style="font-size:0.8rem; letter-spacing:3px; color:rgba(255,255,255,0.4); font-family:inherit; text-transform:uppercase; font-weight:400;">BackOffice</div></a>
        <ul class="admin-nav">
            <li><a href="../quizzes_list.php"><i class="fas fa-tasks"></i> Liste des Quiz</a></li>
            <li><a href="../quiz_add.php"><i class="fas fa-plus-circle"></i> Ajouter un Quiz</a></li>
            <li><a href="generate.php" class="active"><i class="fas fa-wand-magic-sparkles"></i> Generer un Quiz</a></li>
            <li><a href="../questions_list.php"><i class="fas fa-question-circle"></i> Liste des Questions</a></li>
            <li><a href="../question_add.php"><i class="fas fa-plus-square"></i> Ajouter une Question</a></li>
        </ul>
    </aside>

    <main class="admin-content">
        <a href="../quizzes_list.php" class="back-link"><i class="fas fa-arrow-left"></i> Retour a la liste des quiz</a>

        <div class="form-card">
            <div class="card-title"><i class="fas fa-wand-magic-sparkles"></i> Generer un Quiz</div>
            <p class="card-subtitle">Le systeme selectionne automatiquement des questions disponibles et les associe au quiz cree.</p>

            <?php if (!empty($errors['global'])): ?>
                <div class="global-error"><?= htmlspecialchars($errors['global']) ?></div>
            <?php endif; ?>

            <form method="POST" action="" id="quizGenerateForm">
                <div class="field-group">
                    <label for="titre">Titre du quiz</label>
                    <input type="text" id="titre" name="titre" value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>" autocomplete="off">
                    <div class="field-msg"><?= htmlspecialchars($errors['titre'] ?? '') ?></div>
                </div>

                <div class="field-group">
                    <label for="duree">Duree (minutes)</label>
                    <input type="number" id="duree" name="duree" value="<?= htmlspecialchars($_POST['duree'] ?? '') ?>">
                    <div class="field-msg"><?= htmlspecialchars($errors['duree'] ?? '') ?></div>
                </div>

                <div class="field-group">
                    <label for="seuilReussite">Seuil de reussite (%)</label>
                    <input type="number" id="seuilReussite" name="seuilReussite" value="<?= htmlspecialchars($_POST['seuilReussite'] ?? '') ?>">
                    <div class="field-msg"><?= htmlspecialchars($errors['seuilReussite'] ?? 'Minimum 40%, maximum 100%') ?></div>
                </div>

                <div class="field-group">
                    <label for="niveau">Niveau</label>
                    <select id="niveau" name="niveau">
                        <option value="">Selectionnez</option>
                        <?php foreach ($formData['niveaux'] as $niveau): ?>
                            <option value="<?= htmlspecialchars($niveau) ?>" <?= (($_POST['niveau'] ?? '') === $niveau) ? 'selected' : '' ?>><?= htmlspecialchars(ucfirst($niveau)) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="field-msg"><?= htmlspecialchars($errors['niveau'] ?? '') ?></div>
                </div>

                <div class="field-group">
                    <label for="statut">Statut</label>
                    <select id="statut" name="statut">
                        <option value="">Selectionnez</option>
                        <?php foreach ($formData['statuts'] as $statut): ?>
                            <option value="<?= htmlspecialchars($statut) ?>" <?= (($_POST['statut'] ?? '') === $statut) ? 'selected' : '' ?>><?= htmlspecialchars(ucfirst($statut)) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="field-msg"><?= htmlspecialchars($errors['statut'] ?? '') ?></div>
                </div>

                <div class="field-group">
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
                    <div class="field-msg"><?= htmlspecialchars($errors['idCourse'] ?? '') ?></div>
                </div>

                <div class="field-group">
                    <label for="nombreQuestions">Nombre de questions souhaitees</label>
                    <input type="number" id="nombreQuestions" name="nombreQuestions" value="<?= htmlspecialchars($_POST['nombreQuestions'] ?? '') ?>">
                    <div class="field-msg"><?= htmlspecialchars($errors['nombreQuestions'] ?? '') ?></div>
                </div>

                <button type="submit" class="submit-btn"><i class="fas fa-bolt"></i> Generer automatiquement le quiz</button>
            </form>
        </div>
    </main>
</body>
</html>