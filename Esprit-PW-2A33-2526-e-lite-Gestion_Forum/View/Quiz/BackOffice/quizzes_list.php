<?php
// Calcul du basePath
$__bp = rtrim(str_replace('\\', '/', substr(realpath(__DIR__ . '/../../..'), strlen(realpath($_SERVER['DOCUMENT_ROOT'])))), '/');
if ($__bp === '.' || $__bp === '') $__bp = '';

require_once __DIR__ . '/../../../Controller/Quiz/QuizController.php';
$quizController = new QuizController();

if (isset($_GET['delete_quiz'])) {
    $quizController->deleteQuiz($_GET['delete_quiz']);
    header('Location: ' . $__bp . '/quiz/admin?deleted=1');
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
    <link rel="stylesheet" href="<?= $__bp ?>/View/assets/Quiz/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ── Reset & Layout ─────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; }
        body {
            display: flex;
            background-color: var(--black);
            margin: 0;
            overflow-x: hidden;
        }

        /* ── Sidebar ─────────────────────────────────────────── */
        .admin-sidebar {
            width: 240px;
            height: 100vh;
            background: rgba(10,10,10,0.95);
            border-right: 1px solid var(--glass-border);
            position: fixed;
            top: 0; left: 0;
            display: flex;
            flex-direction: column;
            padding: 1.5rem 1rem;
            z-index: 100;
            flex-shrink: 0;
        }
        .admin-sidebar .logo {
            font-size: 1.7rem;
            margin-bottom: 2rem;
            text-align: center;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .admin-nav {
            display: flex; flex-direction: column;
            gap: 0.4rem; list-style: none;
            padding: 0; margin: 0; flex: 1;
        }
        .admin-nav li a {
            display: flex; align-items: center; gap: 0.8rem;
            color: var(--light-gray); text-decoration: none;
            padding: 0.75rem 1rem; border-radius: 10px;
            font-weight: 500; font-size: 0.9rem;
            transition: all 0.25s;
        }
        .admin-nav li a i { font-size: 1rem; width: 18px; text-align: center; }
        .admin-nav li a:hover,
        .admin-nav li a.active {
            background: rgba(234,179,8,0.1);
            color: var(--accent);
            transform: translateX(4px);
            box-shadow: inset 2px 0 0 var(--accent);
        }

        /* ── Main content ────────────────────────────────────── */
        .admin-content {
            margin-left: 240px;
            flex: 1;
            min-width: 0;                /* empêche le flex item de déborder */
            padding: 2rem 1.5rem;
            min-height: 100vh;
            background: radial-gradient(circle at top right, rgba(234,179,8,0.05) 0%, transparent 40%);
        }

        /* ── Header ──────────────────────────────────────────── */
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        .admin-header h1 { font-size: 2rem; margin: 0; }
        .header-actions {
            display: flex;
            gap: 0.6rem;
            flex-wrap: wrap;
            align-items: center;
        }

        /* ── Buttons ─────────────────────────────────────────── */
        .add-btn {
            background: linear-gradient(135deg, #eab308, #d97706);
            color: #000; border: none;
            padding: 0.6rem 1rem;
            border-radius: 10px;
            font-weight: 700; font-size: 0.82rem;
            cursor: pointer; font-family: inherit;
            display: inline-flex; align-items: center; gap: 0.45rem;
            text-decoration: none;
            transition: transform 0.2s, box-shadow 0.25s;
            white-space: nowrap;
        }
        .add-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(234,179,8,0.3); }

        /* ── Table wrapper (scroll horizontal si besoin) ─────── */
        .table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border-radius: 12px;
        }

        /* ── Table ───────────────────────────────────────────── */
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            color: var(--text-main);
            font-size: 0.88rem;
            /* table-layout auto : les colonnes s'adaptent au contenu */
        }
        .admin-table th,
        .admin-table td {
            padding: 0.75rem 0.8rem;
            text-align: left;
            border-bottom: 1px solid var(--glass-border);
            vertical-align: middle;
        }
        .admin-table th {
            color: var(--light-gray);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.8px;
            white-space: nowrap;
        }
        .admin-table tbody tr:hover { background: rgba(255,255,255,0.03); }

        /* Colonnes à largeur fixe */
        .col-id      { width: 44px;  }
        .col-duree   { width: 64px;  white-space: nowrap; }
        .col-seuil   { width: 56px;  white-space: nowrap; }
        .col-niveau  { width: 96px;  white-space: nowrap; }
        .col-statut  { width: 64px;  white-space: nowrap; }
        .col-actions { width: 100px; white-space: nowrap; }

        /* Colonnes à contenu tronqué */
        .col-titre,
        .col-course {
            max-width: 160px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* ── Action buttons ──────────────────────────────────── */
        .action-btn {
            background: none; border: none;
            color: var(--light-gray);
            cursor: pointer;
            transition: color 0.25s, transform 0.15s;
            font-size: 1rem;
            margin-right: 0.4rem;
            text-decoration: none;
            display: inline-flex; align-items: center;
            padding: 0.2rem;
        }
        .action-btn:last-child { margin-right: 0; }
        .action-btn:hover { color: var(--accent); transform: scale(1.15); }
        .action-btn.delete:hover { color: #ef4444; }

        /* ── Confirm modal ───────────────────────────────────── */
        .confirm-overlay {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.75);
            backdrop-filter: blur(6px);
            z-index: 9000;
            display: flex; align-items: center; justify-content: center;
            opacity: 0; pointer-events: none;
            transition: opacity 0.3s;
        }
        .confirm-overlay.active { opacity: 1; pointer-events: all; }
        .confirm-box {
            background: rgba(18,18,18,0.98);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px;
            padding: 2.5rem 2rem;
            max-width: 420px; width: 90%;
            text-align: center;
            transform: scale(0.92);
            transition: transform 0.3s;
        }
        .confirm-overlay.active .confirm-box { transform: scale(1); }
        .confirm-icon { font-size: 3rem; color: #ef4444; margin-bottom: 1rem; }
        .confirm-box h3 { margin: 0 0 0.5rem; font-size: 1.3rem; color: #fff; }
        .confirm-box p  { color: rgba(255,255,255,0.5); font-size: 0.9rem; margin-bottom: 2rem; }
        .confirm-actions { display: flex; gap: 1rem; justify-content: center; }
        .c-btn-cancel {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.12);
            color: rgba(255,255,255,0.7);
            padding: 0.7rem 1.6rem; border-radius: 10px;
            cursor: pointer; font-size: 0.9rem; font-family: inherit;
        }
        .c-btn-delete {
            background: rgba(239,68,68,0.15);
            border: 1px solid rgba(239,68,68,0.5);
            color: #ef4444;
            padding: 0.7rem 1.6rem; border-radius: 10px;
            cursor: pointer; font-size: 0.9rem; font-weight: 700;
            font-family: inherit;
            display: inline-flex; align-items: center; gap: 0.5rem;
            text-decoration: none;
        }
        .c-btn-delete:hover { background: rgba(239,68,68,0.25); }

        /* ── Toast ───────────────────────────────────────────── */
        .toast {
            position: fixed; bottom: 2rem; right: 2rem;
            background: rgba(16,185,129,0.15);
            border: 1px solid rgba(16,185,129,0.4);
            border-radius: 12px;
            padding: 1rem 1.5rem;
            display: flex; align-items: center; gap: 0.8rem;
            color: #10b981; font-weight: 600; font-size: 0.9rem;
            transform: translateY(100px); opacity: 0;
            transition: all 0.4s; z-index: 9999; pointer-events: none;
        }
        .toast.show { transform: translateY(0); opacity: 1; }
    </style>
</head>
<body>

<div class="toast" id="toast"><i class="fas fa-check-circle"></i><span id="toastMsg"></span></div>

<div class="confirm-overlay" id="confirmOverlay">
    <div class="confirm-box">
        <div class="confirm-icon"><i class="fas fa-trash-alt"></i></div>
        <h3>Supprimer ce quiz ?</h3>
        <p>Cette action est irréversible. Les questions liées seront conservées et simplement détachées du quiz.</p>
        <div class="confirm-actions">
            <button class="c-btn-cancel" onclick="closeConfirm()">Annuler</button>
            <a class="c-btn-delete" id="confirmDeleteLink"><i class="fas fa-trash"></i> Supprimer</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/quiz_sidebar.php'; ?>

<main class="admin-content">
    <header class="admin-header">
        <div>
            <h1>Liste des <span class="text-gradient">Quiz</span></h1>
            <p style="color: var(--light-gray); margin-top: 0.4rem; font-size:0.9rem;">Tous les quiz et leurs paramètres d'évaluation.</p>
        </div>
        <div class="header-actions">
            <a href="<?= $__bp ?>/quiz/admin/generer" class="add-btn" style="background:linear-gradient(135deg,#22c55e,#16a34a);"><i class="fas fa-wand-magic-sparkles"></i> Générer</a>
            <a href="<?= $__bp ?>/quiz/admin/export"  class="add-btn" style="background:linear-gradient(135deg,#22c55e,#15803d);"><i class="fas fa-file-export"></i> Exporter</a>
            <a href="<?= $__bp ?>/quiz/admin/verrous" class="add-btn" style="background:linear-gradient(135deg,#f59e0b,#d97706);"><i class="fas fa-lock"></i> Verrous</a>
            <a href="<?= $__bp ?>/quiz/admin/ajouter" class="add-btn"><i class="fas fa-plus"></i> Ajouter</a>
        </div>
    </header>

    <div class="glass-card">
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th class="col-id">ID</th>
                        <th class="col-titre">Titre</th>
                        <th class="col-duree">Durée</th>
                        <th class="col-seuil">Seuil</th>
                        <th class="col-niveau">Niveau</th>
                        <th class="col-course">Cours</th>
                        <th class="col-statut">Statut</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($quizs && $quizs->rowCount() > 0): ?>
                        <?php while ($q = $quizs->fetch()):
                            $courseTitre = htmlspecialchars($quizController->getCourseTitleById($q['idCourse']));
                        ?>
                            <tr>
                                <td class="col-id">#<?= htmlspecialchars($q['idQuiz']) ?></td>
                                <td class="col-titre" title="<?= htmlspecialchars($q['titre']) ?>"><?= htmlspecialchars($q['titre']) ?></td>
                                <td class="col-duree"><?= htmlspecialchars($q['duree']) ?> min</td>
                                <td class="col-seuil"><?= htmlspecialchars($q['seuilReussite']) ?>%</td>
                                <td class="col-niveau"><?= htmlspecialchars($q['niveau']) ?></td>
                                <td class="col-course" title="<?= $courseTitre ?>"><?= $courseTitre ?></td>
                                <td class="col-statut"><?= htmlspecialchars($q['statut']) ?></td>
                                <td class="col-actions">
                                    <a href="<?= $__bp ?>/quiz/admin/modifier?id=<?= $q['idQuiz'] ?>" class="action-btn" title="Modifier"><i class="fas fa-edit"></i></a>
                                    <a href="<?= $__bp ?>/quiz/admin/questions" class="action-btn" title="Questions"><i class="fas fa-question-circle"></i></a>
                                    <button class="action-btn delete" title="Supprimer" onclick="askDelete(<?= $q['idQuiz'] ?>)"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8" style="text-align:center;color:rgba(255,255,255,0.4);padding:2rem;">Aucun quiz trouvé.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script src="<?= $__bp ?>/View/assets/Quiz/index.js?v=20260511"></script>
<script>
const __bp = '<?= $__bp ?>';
function askDelete(quizId) {
    document.getElementById('confirmDeleteLink').href = __bp + '/quiz/admin?delete_quiz=' + quizId;
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
    if (p.get('generated') === '1') showToast('Quiz généré automatiquement avec succès !');
    if (p.get('deleted') === '1') showToast('Quiz supprimé avec succès !');
});
</script>
</body>
</html>
