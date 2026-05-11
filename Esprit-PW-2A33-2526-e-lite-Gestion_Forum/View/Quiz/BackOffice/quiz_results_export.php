<?php
$__bp = rtrim(str_replace('\\', '/', substr(realpath(__DIR__ . '/../../..'), strlen(realpath($_SERVER['DOCUMENT_ROOT'])))), '/');
if ($__bp === '.' || $__bp === '') $__bp = '';
require_once __DIR__ . '/../../../Controller/Quiz/QuizController.php';

$quizController = new QuizController();
$quizs = $quizController->afficherQuizs();
$courses = $quizController->getAllCourses();

$selectedQuizId = intval($_GET['idQuiz'] ?? 0);
$selectedCourseId = intval($_GET['idCourse'] ?? 0);
$action = $_GET['action'] ?? '';

if ($action === 'download') {
    $results = $quizController->getQuizResultsForExport($selectedQuizId ?: null, $selectedCourseId ?: null);

    if (empty($results)) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Aucun r\u00e9sultat disponible pour l\'export demand\u00e9.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $filename = 'quiz_results_export_' . date('Ymd_His') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');

    $output = fopen('php://output', 'w');
    fwrite($output, "\xEF\xBB\xBF");
    fputcsv($output, ['Nom', 'Prénom', 'Email', 'Titre du quiz', 'Cours associé', 'Score', 'Pourcentage', 'Statut', 'Statut anti-triche', 'Date de passage'], ';');

    foreach ($results as $row) {
        fputcsv($output, [
            $row['nom'] ?? '',
            $row['prenom'] ?? '',
            $row['email'] ?? '',
            $row['quizTitre'] ?? '',
            $row['coursTitre'] ?? '',
            $row['score'] ?? 0,
            $row['pourcentage'] ?? 0,
            $row['statut'] ?? '',
            $row['statutAntiTriche'] ?? '',
            $row['datePassage'] ?? ''
        ], ';');
    }

    fclose($output);
    exit;
}

$results = $quizController->getQuizResultsForExport($selectedQuizId ?: null, $selectedCourseId ?: null);

function export_e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function selected_attr($current, $candidate) {
    return intval($current) === intval($candidate) ? ' selected' : '';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Google Sheets | e-lite BackOffice</title>
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
        .admin-content { margin-left: 280px; flex: 1; padding: 2.5rem 4rem; min-height: 100vh; background: radial-gradient(circle at top right, rgba(34,197,94,0.06) 0%, transparent 40%); }
        .page-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 1.5rem; margin-bottom: 2rem; }
        .page-title { margin: 0; font-size: 2.35rem; line-height: 1.1; }
        .page-subtitle { color: var(--light-gray); margin-top: 0.5rem; max-width: 780px; }
        .toolbar { display: flex; gap: 0.75rem; flex-wrap: wrap; }
        .button { background: linear-gradient(135deg, #eab308, #d97706); color: #000; border: none; padding: 0.85rem 1.15rem; border-radius: 12px; font-weight: 700; font-size: 0.95rem; cursor: pointer; font-family: inherit; display: inline-flex; align-items: center; gap: 0.7rem; text-decoration: none; transition: transform 0.2s, box-shadow 0.3s; }
        .button:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(234,179,8,0.28); }
        .button.secondary { background: rgba(255,255,255,0.06); color: #fff; border: 1px solid rgba(255,255,255,0.12); }
        .button.secondary:hover { box-shadow: none; }
        .button.success { background: linear-gradient(135deg, #22c55e, #16a34a); color: #fff; }
        .card { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 20px; padding: 1.5rem; backdrop-filter: blur(12px); }
        .filters { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; margin-bottom: 1rem; }
        .field-group label { display: block; margin-bottom: 0.55rem; font-size: 0.84rem; font-weight: 700; color: rgba(255,255,255,0.65); text-transform: uppercase; letter-spacing: 0.05em; }
        .field-group select { width: 100%; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.12); border-radius: 12px; color: #fff; font-family: inherit; font-size: 0.95rem; padding: 0.95rem 1rem; outline: none; }
        .field-group select option { background: #1a1a1a; color: #fff; }
        .hint { color: rgba(255,255,255,0.52); font-size: 0.88rem; margin-top: 0.85rem; }
        .summary { display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 1rem; }
        .summary-item { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 14px; padding: 0.85rem 1rem; color: rgba(255,255,255,0.8); }
        .summary-item strong { display:block; color:#fff; margin-top:0.2rem; }
        .table-wrap { overflow-x: auto; margin-top: 1rem; }
        .admin-table { width: 100%; border-collapse: collapse; color: var(--text-main); min-width: 1100px; }
        .admin-table th, .admin-table td { padding: 0.95rem 0.9rem; text-align: left; border-bottom: 1px solid var(--glass-border); vertical-align: top; }
        .admin-table th { color: var(--light-gray); font-weight: 600; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px; }
        .admin-table tbody tr:hover { background: rgba(255,255,255,0.03); }
        .status-pill { display: inline-flex; align-items: center; gap: 0.4rem; border-radius: 999px; padding: 0.4rem 0.7rem; font-size: 0.82rem; font-weight: 700; }
        .status-pill.success { background: rgba(16,185,129,0.12); color: #10b981; }
        .status-pill.danger { background: rgba(239,68,68,0.12); color: #ef4444; }
        .status-pill.neutral { background: rgba(255,255,255,0.08); color: #d1d5db; }
        .status-pill.warning { background: rgba(245,158,11,0.12); color: #f59e0b; }
        .empty-state { padding: 2rem 1rem; text-align: center; color: rgba(255,255,255,0.55); }
        .toast { position: fixed; bottom: 2rem; right: 2rem; background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.4); border-radius: 12px; padding: 1rem 1.5rem; display: flex; align-items: center; gap: 0.8rem; color: #10b981; font-weight: 600; font-size: 0.9rem; transform: translateY(100px); opacity: 0; transition: all 0.4s cubic-bezier(0.4,0,0.2,1); z-index: 9999; pointer-events: none; }
        .toast.show { transform: translateY(0); opacity: 1; }
        .toast.error { background: rgba(239,68,68,0.15); border-color: rgba(239,68,68,0.4); color: #ef4444; }
        @media (max-width: 1100px) {
            .admin-content { margin-left: 0; padding: 1.5rem; }
            .admin-sidebar { position: static; width: auto; height: auto; }
            .page-header { flex-direction: column; }
            .filters { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="toast" id="toast"><i class="fas fa-check-circle"></i><span id="toastMsg"></span></div>

<aside class="admin-sidebar">
    <a href="<?= $__bp ?>/admin/dashboard" class="logo">e-lite<span>.</span><div style="font-size:0.8rem; letter-spacing:3px; color:rgba(255,255,255,0.4); font-family:inherit; text-transform:uppercase; font-weight:400;">BackOffice</div></a>
    <ul class="admin-nav">
        <li><a href="<?= $__bp ?>/quiz/admin"><i class="fas fa-tasks"></i> Liste des Quiz</a></li>
        <li><a href="<?= $__bp ?>/quiz/admin/export" class="active"><i class="fas fa-file-export"></i> Export Google Sheets</a></li>
        <li><a href="<?= $__bp ?>/quiz/admin/ajouter"><i class="fas fa-plus-circle"></i> Ajouter un Quiz</a></li>
        <li><a href="<?= $__bp ?>/quiz/admin/verrous"><i class="fas fa-lock"></i> Verrous Quiz</a></li>
        <li><a href="<?= $__bp ?>/quiz/admin/questions"><i class="fas fa-question-circle"></i> Liste des Questions</a></li>
    </ul>
</aside>

<main class="admin-content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Export <span class="text-gradient">Google Sheets</span></h1>
            <p class="page-subtitle">Filtrez les résultats par quiz ou par cours, puis exportez un fichier CSV propre, directement importable dans Google Sheets.</p>
        </div>
        <div class="toolbar">
            <a href="<?= $__bp ?>/quiz/admin" class="button secondary"><i class="fas fa-arrow-left"></i> Retour</a>
            <button type="button" id="exportButton" class="button success"><i class="fas fa-file-arrow-down"></i> Exporter vers Google Sheets</button>
        </div>
    </div>

    <section class="card">
        <form id="exportForm" method="get" action="<?= $__bp ?>/quiz/admin/export">
            <div class="filters">
                <div class="field-group">
                    <label for="idQuiz">Filtrer par quiz</label>
                    <select id="idQuiz" name="idQuiz">
                        <option value="">Tous les quiz</option>
                        <?php if ($quizs && $quizs->rowCount() > 0): ?>
                            <?php while ($quiz = $quizs->fetch()): ?>
                                <option value="<?= export_e($quiz['idQuiz']) ?>"<?= selected_attr($selectedQuizId, $quiz['idQuiz']) ?>>
                                    <?= export_e($quiz['titre']) ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="field-group">
                    <label for="idCourse">Filtrer par cours</label>
                    <select id="idCourse" name="idCourse">
                        <option value="">Tous les cours</option>
                        <?php if ($courses): ?>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?= export_e($course['idCourse']) ?>"<?= selected_attr($selectedCourseId, $course['idCourse']) ?>>
                                    <?= export_e($course['titre']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
            <div style="display:flex; gap:0.75rem; flex-wrap:wrap;">
                <button type="submit" class="button"><i class="fas fa-filter"></i> Appliquer les filtres</button>
                <a href="<?= $__bp ?>/quiz/admin/export" class="button secondary"><i class="fas fa-rotate-left"></i> Réinitialiser</a>
            </div>
        </form>

        <p class="hint">L'export contient le nom, le prénom, l'email, le quiz, le cours, le score, le pourcentage, le statut réussi/échoué, le statut anti-triche et la date de passage.</p>

        <div class="summary">
            <div class="summary-item">Résultats trouvés<strong><?= export_e(count($results)) ?></strong></div>
            <div class="summary-item">Quiz filtré<strong><?= export_e($selectedQuizId ?: 'Tous') ?></strong></div>
            <div class="summary-item">Cours filtré<strong><?= export_e($selectedCourseId ?: 'Tous') ?></strong></div>
        </div>

        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Quiz</th>
                        <th>Cours</th>
                        <th>Score</th>
                        <th>%</th>
                        <th>Statut</th>
                        <th>Anti-triche</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($results)): ?>
                        <?php foreach ($results as $row): ?>
                            <tr>
                                <td><?= export_e($row['nom'] ?: 'N/A') ?></td>
                                <td><?= export_e($row['prenom'] ?: 'N/A') ?></td>
                                <td><?= export_e($row['email'] ?: 'N/A') ?></td>
                                <td><?= export_e($row['quizTitre'] ?: 'N/A') ?></td>
                                <td><?= export_e($row['coursTitre'] ?: 'N/A') ?></td>
                                <td><?= export_e($row['score']) ?></td>
                                <td><?= export_e($row['pourcentage']) ?>%</td>
                                <td>
                                    <span class="status-pill <?= $row['statut'] === 'Réussi' ? 'success' : 'danger' ?>">
                                        <?= export_e($row['statut']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-pill <?= $row['statutAntiTriche'] === 'Suspect' ? 'warning' : 'neutral' ?>">
                                        <?= export_e($row['statutAntiTriche']) ?>
                                    </span>
                                </td>
                                <td><?= export_e($row['datePassage']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10">
                                <div class="empty-state">Aucun résultat trouvé pour ces filtres.</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<script>
function showToast(message, isError) {
    const toast = document.getElementById('toast');
    const toastMsg = document.getElementById('toastMsg');
    toast.classList.remove('error');
    if (isError) {
        toast.classList.add('error');
    }
    toastMsg.textContent = message;
    toast.classList.add('show');
    window.clearTimeout(window.__exportToastTimer);
    window.__exportToastTimer = window.setTimeout(() => toast.classList.remove('show'), 3200);
}

document.getElementById('exportButton').addEventListener('click', async function() {
    const form = document.getElementById('exportForm');
    const params = new URLSearchParams(new FormData(form));
    params.set('action', 'download');

    try {
        const response = await fetch('<?= $__bp ?>/quiz/admin/export?' + params.toString(), {
            method: 'GET'
        });

        if (!response.ok) {
            let message = "L'export a échoué.";
            try {
                const payload = await response.json();
                message = payload.message || message;
            } catch (error) {
                const text = await response.text();
                if (text) {
                    message = text;
                }
            }
            showToast(message, true);
            return;
        }

        const blob = await response.blob();
        const downloadUrl = window.URL.createObjectURL(blob);
        const downloadLink = document.createElement('a');
        downloadLink.href = downloadUrl;
        downloadLink.download = 'quiz_results_export.csv';
        document.body.appendChild(downloadLink);
        downloadLink.click();
        downloadLink.remove();
        window.URL.revokeObjectURL(downloadUrl);
        showToast('Export généré avec succès pour Google Sheets.');
    } catch (error) {
        showToast("Impossible de générer l'export. Vérifiez la connexion ou les filtres.", true);
    }
});
</script>
</body>
</html>
