<?php
require_once __DIR__ . '/../../Controller/QuizController.php';
$quizController = new QuizController();

if (isset($_GET['delete_quiz'])) {
    $quizController->deleteQuiz($_GET['delete_quiz']);
    header('Location: quizzes_list.php?deleted=1');
    exit;
}

$quizs = $quizController->afficherQuizs();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Quiz | e-lite BackOffice</title>
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
        .logout-btn { margin-top: auto; color: #ef4444 !important; }
        .logout-btn:hover { background: rgba(239,68,68,0.1) !important; box-shadow: inset 2px 0 0 #ef4444 !important; }
        .admin-content { margin-left: 280px; flex: 1; padding: 2.5rem 4rem; min-height: 100vh; background: radial-gradient(circle at top right, rgba(234,179,8,0.05) 0%, transparent 40%); }
        .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 3rem; position: relative; background: transparent; padding: 0; border: none; }
        .admin-header h1 { font-size: 2.5rem; margin: 0; }
        .admin-table { width: 100%; border-collapse: collapse; margin-top: 1.5rem; color: var(--text-main); }
        .admin-table th, .admin-table td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--glass-border); }
        .admin-table th { color: var(--light-gray); font-weight: 600; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px; }
        .admin-table tbody tr { transition: background 0.3s; }
        .admin-table tbody tr:hover { background: rgba(255,255,255,0.03); }
        .action-btn { background: none; border: none; color: var(--light-gray); cursor: pointer; transition: color 0.3s; font-size: 1.1rem; margin-right: 0.8rem; text-decoration: none; }
        .action-btn:hover { color: var(--accent); }
        .action-btn.delete:hover { color: #ef4444; }
        .add-btn { background: linear-gradient(135deg, #eab308, #d97706); color: #000; border: none; padding: 0.8rem 1.3rem; border-radius: 12px; font-weight: 700; font-size: 0.95rem; cursor: pointer; font-family: inherit; display: inline-flex; align-items: center; gap: 0.7rem; text-decoration: none; transition: transform 0.2s, box-shadow 0.3s; }
        .add-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(234,179,8,0.3); }
        .confirm-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.75); backdrop-filter: blur(6px); z-index: 9000; display: flex; align-items: center; justify-content: center; opacity: 0; pointer-events: none; transition: opacity 0.3s; }
        .confirm-overlay.active { opacity: 1; pointer-events: all; }
        .confirm-box { background: rgba(18,18,18,0.98); border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; padding: 2.5rem 2rem; max-width: 420px; width: 90%; text-align: center; transform: scale(0.92); transition: transform 0.3s; }
        .confirm-overlay.active .confirm-box { transform: scale(1); }
        .confirm-icon { font-size: 3rem; color: #ef4444; margin-bottom: 1rem; }
        .confirm-box h3 { margin: 0 0 0.5rem; font-size: 1.3rem; color: #fff; }
        .confirm-box p { color: rgba(255,255,255,0.5); font-size: 0.9rem; margin-bottom: 2rem; }
        .confirm-actions { display: flex; gap: 1rem; justify-content: center; }
        .c-btn-cancel { background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); color: rgba(255,255,255,0.7); padding: 0.7rem 1.6rem; border-radius: 10px; cursor: pointer; font-size: 0.9rem; font-family: inherit; transition: background 0.2s; }
        .c-btn-cancel:hover { background: rgba(255,255,255,0.1); }
        .c-btn-delete { background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.5); color: #ef4444; padding: 0.7rem 1.6rem; border-radius: 10px; cursor: pointer; font-size: 0.9rem; font-weight: 700; font-family: inherit; transition: background 0.2s; display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none; }
        .c-btn-delete:hover { background: rgba(239,68,68,0.25); }
        .toast { position: fixed; bottom: 2rem; right: 2rem; background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.4); border-radius: 12px; padding: 1rem 1.5rem; display: flex; align-items: center; gap: 0.8rem; color: #10b981; font-weight: 600; font-size: 0.9rem; transform: translateY(100px); opacity: 0; transition: all 0.4s cubic-bezier(0.4,0,0.2,1); z-index: 9999; pointer-events: none; }
        .toast.show { transform: translateY(0); opacity: 1; }
        .toast.error { background: rgba(239,68,68,0.15); border-color: rgba(239,68,68,0.4); color: #ef4444; }
    </style>
</head>
<body>

<div class="toast" id="toast"><i class="fas fa-check-circle"></i><span id="toastMsg"></span></div>

<div class="confirm-overlay" id="confirmOverlay">
    <div class="confirm-box">
        <div class="confirm-icon"><i class="fas fa-trash-alt"></i></div>
        <h3>Supprimer ce quiz ?</h3>
        <p>Cette action est irréversible. Toutes les questions liées seront également supprimées.</p>
        <div class="confirm-actions">
            <button class="c-btn-cancel" onclick="closeConfirm()">Annuler</button>
            <a class="c-btn-delete" id="confirmDeleteLink"><i class="fas fa-trash"></i> Supprimer</a>
        </div>
    </div>
</div>

    <aside class="admin-sidebar">
        <a href="dashboard.php" class="logo">e-lite<span>.</span><div style="font-size:0.8rem; letter-spacing:3px; color:rgba(255,255,255,0.4); font-family:inherit; text-transform:uppercase; font-weight:400;">BackOffice</div></a>
        <ul class="admin-nav">
            <li><a href="quizzes_list.php" class="active"><i class="fas fa-tasks"></i> Liste des Quiz</a></li>
            <li><a href="quiz_add.php"><i class="fas fa-plus-circle"></i> Ajouter un Quiz</a></li>
            <li><a href="questions_list.php"><i class="fas fa-question-circle"></i> Liste des Questions</a></li>
            <li><a href="question_add.php"><i class="fas fa-plus-square"></i> Ajouter une Question</a></li>
        </ul>
    </aside>

    <main class="admin-content">
        <header class="admin-header reveal">
            <div>
                <h1>Liste des <span class="text-gradient">Quiz</span></h1>
                <p style="color: var(--light-gray); margin-top: 0.5rem;">Affiche tous les quiz et leurs paramètres d’évaluation.</p>
            </div>
            <a href="quiz_add.php" class="add-btn"><i class="fas fa-plus"></i> Ajouter un Quiz</a>
        </header>

        <div class="glass-card reveal">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Titre</th>
                        <th>Durée</th>
                        <th>Seuil</th>
                        <th>Niveau</th>
                        <th>Course</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($quizs && $quizs->rowCount() > 0): ?>
                        <?php while ($q = $quizs->fetch()): ?>
                            <tr>
                                <td>#<?= htmlspecialchars($q['idQuiz']) ?></td>
                                <td><?= htmlspecialchars($q['titre']) ?></td>
                                <td><?= htmlspecialchars($q['duree']) ?> min</td>
                                <td><?= htmlspecialchars($q['seuilReussite']) ?>%</td>
                                <td><?= htmlspecialchars($q['niveau']) ?></td>
                                <td><?= htmlspecialchars($q['idCourse'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($q['statut']) ?></td>
                                <td>
                                    <a href="quiz_update.php?id=<?= $q['idQuiz'] ?>" class="action-btn" title="Modifier"><i class="fas fa-edit"></i></a>
                                    <a href="questions_list.php?idQuiz=<?= $q['idQuiz'] ?>" class="action-btn" title="Voir les questions"><i class="fas fa-question-circle"></i></a>
                                    <button class="action-btn delete" title="Supprimer" onclick="askDelete(<?= $q['idQuiz'] ?>)"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8" style="text-align:center; color:rgba(255,255,255,0.4); padding:2rem;">Aucun quiz trouvé.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

<script src="../assets/index.js"></script>
<script>
function askDelete(quizId) {
    document.getElementById('confirmDeleteLink').href = 'quizzes_list.php?delete_quiz=' + quizId;
    document.getElementById('confirmOverlay').classList.add('active');
}
function closeConfirm() {
    document.getElementById('confirmOverlay').classList.remove('active');
}
document.getElementById('confirmOverlay').addEventListener('click', function(e) {
    if (e.target === this) closeConfirm();
});
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeConfirm(); });
document.addEventListener('DOMContentLoaded', () => {
    const p = new URLSearchParams(window.location.search);
    if (p.get('added') === '1') showToast('Quiz créé avec succès !');
    if (p.get('updated') === '1') showToast('Quiz mis à jour avec succès !');
    if (p.get('deleted') === '1') showToast('Quiz supprimé avec succès !');
});
</script>
</body>
</html>
