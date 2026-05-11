<?php
$__bp = rtrim(str_replace('\\', '/', substr(realpath(__DIR__ . '/../../..'), strlen(realpath($_SERVER['DOCUMENT_ROOT'])))), '/');
if ($__bp === '.' || $__bp === '') $__bp = '';
require_once __DIR__ . '/../../../Controller/Quiz/QuizController.php';
$quizController = new QuizController();

if (isset($_GET['unlock_lock'])) {
    $quizController->unlockLockById($_GET['unlock_lock'], 'admin_backoffice');
    header('Location: ' . $__bp . '/quiz/admin/verrous?unlocked=1');
    exit;
}

$locks = $quizController->getLockedAttempts();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verrous Quiz | e-lite BackOffice</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $__bp ?>/View/assets/Quiz/index.css">
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
        .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .admin-table { width: 100%; border-collapse: collapse; margin-top: 1.5rem; color: var(--text-main); }
        .admin-table th, .admin-table td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--glass-border); }
        .admin-table th { color: var(--light-gray); font-weight: 600; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px; }
        .admin-table tbody tr:hover { background: rgba(255,255,255,0.03); }
        .unlock-btn { background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.45); color: #10b981; padding: 0.45rem 0.8rem; border-radius: 8px; text-decoration: none; font-weight: 700; }
        .unlock-btn:hover { background: rgba(16,185,129,0.25); }
        .toast { position: fixed; bottom: 2rem; right: 2rem; background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.4); border-radius: 12px; padding: 1rem 1.5rem; color: #10b981; font-weight: 600; transform: translateY(100px); opacity: 0; transition: all 0.3s; }
        .toast.show { transform: translateY(0); opacity: 1; }
    </style>
</head>
<body>
<div class="toast" id="toast">Tentative deverrouillee avec succes.</div>

<aside class="admin-sidebar">
    <a href="<?= $__bp ?>/admin/dashboard" class="logo">e-lite<span>.</span><div style="font-size:0.8rem; letter-spacing:3px; color:rgba(255,255,255,0.4); font-family:inherit; text-transform:uppercase; font-weight:400;">BackOffice</div></a>
    <ul class="admin-nav">
        <li><a href="<?= $__bp ?>/quiz/admin"><i class="fas fa-tasks"></i> Liste des Quiz</a></li>
        <li><a href="<?= $__bp ?>/quiz/admin/ajouter"><i class="fas fa-plus-circle"></i> Ajouter un Quiz</a></li>
        <li><a href="<?= $__bp ?>/quiz/admin/generer"><i class="fas fa-wand-magic-sparkles"></i> Generer un Quiz</a></li>
        <li><a href="<?= $__bp ?>/quiz/admin/verrous" class="active"><i class="fas fa-lock"></i> Verrous Quiz</a></li>
        <li><a href="<?= $__bp ?>/quiz/admin/questions"><i class="fas fa-question-circle"></i> Liste des Questions</a></li>
    </ul>
</aside>

<main class="admin-content">
    <header class="admin-header">
        <div>
            <h1>Verrous <span class="text-gradient">Anti-triche</span></h1>
            <p style="color: var(--light-gray); margin-top: 0.5rem;">Un etudiant bloque ne peut reouvrir le quiz qu'apres deverrouillage.</p>
        </div>
    </header>

    <div class="glass-card">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID verrou</th>
                    <th>Quiz</th>
                    <th>User</th>
                    <th>Session</th>
                    <th>Raison</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($locks && $locks->rowCount() > 0): ?>
                    <?php while ($lock = $locks->fetch()): ?>
                        <tr>
                            <td>#<?= htmlspecialchars($lock['idLock']) ?></td>
                            <td>#<?= htmlspecialchars($lock['idQuiz']) ?> - <?= htmlspecialchars($lock['quizTitre']) ?></td>
                            <td><?= htmlspecialchars($lock['idUser'] ?? 'Anonyme') ?></td>
                            <td><?= htmlspecialchars(substr($lock['sessionKey'], 0, 12)) ?>...</td>
                            <td><?= htmlspecialchars($lock['reason'] ?? 'Comportement suspect') ?></td>
                            <td><?= htmlspecialchars($lock['lockedAt']) ?></td>
                            <td><a class="unlock-btn" href="<?= $__bp ?>/quiz/admin/verrous?unlock_lock=<?= htmlspecialchars($lock['idLock']) ?>">Deverrouiller</a></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align:center; color:rgba(255,255,255,0.5);">Aucun verrou actif.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const p = new URLSearchParams(window.location.search);
    if (p.get('unlocked') === '1') {
        const toast = document.getElementById('toast');
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 2500);
    }
});
</script>
</body>
</html>
