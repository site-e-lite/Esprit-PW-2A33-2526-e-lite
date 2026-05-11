<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../Controller/Quiz/QuizController.php';

$quizController = new QuizController();
$quizResult = $quizController->afficherQuizsActifs();
$quizList = [];
if ($quizResult) {
    $quizList = $quizResult->fetchAll();
}

// Calcul du basePath pour les liens
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
$basePath = rtrim(str_replace('\\', '/', dirname(dirname(dirname(dirname($scriptName))))), '/');
if ($basePath === '.' || $basePath === '/') $basePath = '';
?>
<?php include __DIR__ . '/../../layout/header.php'; ?>

<div class="quiz-front-page" style="max-width:1100px; margin:2rem auto; padding:0 1.5rem;">

    <div style="margin-bottom:2.5rem;">
        <h1 style="font-size:2.2rem; margin-bottom:0.5rem;">
            Évaluations & <span class="text-gradient">Quiz Adaptatifs</span>
        </h1>
        <p style="color:rgba(255,255,255,0.55);">Testez vos connaissances avec nos quiz adaptatifs. Calcul automatique des scores.</p>
    </div>

    <?php if (!empty($quizList)): ?>
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:1.5rem;">
            <?php foreach ($quizList as $quiz): ?>
                <div class="glass-card" style="padding:1.8rem; border-radius:20px; position:relative; overflow:hidden;">
                    <div style="position:absolute; top:0; left:0; right:0; height:3px; background:linear-gradient(90deg,#eab308,#d97706);"></div>
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:1rem;">
                        <h3 style="margin:0; font-size:1.15rem; flex:1; padding-right:1rem;">
                            <?= htmlspecialchars($quiz['titre']) ?>
                        </h3>
                        <span style="background:rgba(234,179,8,0.15); color:#eab308; border-radius:999px; padding:0.3rem 0.8rem; font-size:0.8rem; font-weight:700; white-space:nowrap;">
                            <?= htmlspecialchars($quiz['statut']) ?>
                        </span>
                    </div>
                    <div style="display:flex; flex-wrap:wrap; gap:0.8rem; color:rgba(255,255,255,0.6); font-size:0.88rem; margin-bottom:1.5rem;">
                        <span><i class="fas fa-clock"></i> <?= htmlspecialchars($quiz['duree']) ?> min</span>
                        <span><i class="fas fa-percent"></i> Seuil <?= htmlspecialchars($quiz['seuilReussite']) ?>%</span>
                        <span><i class="fas fa-layer-group"></i> <?= htmlspecialchars($quiz['niveau']) ?></span>
                    </div>
                    <a href="<?= $basePath ?>/quiz/passer?id=<?= htmlspecialchars($quiz['idQuiz']) ?>"
                       style="display:inline-flex; align-items:center; gap:0.5rem; background:linear-gradient(135deg,#eab308,#d97706); color:#000; font-weight:700; padding:0.75rem 1.5rem; border-radius:12px; text-decoration:none; transition:transform 0.2s, box-shadow 0.3s;"
                       onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 24px rgba(234,179,8,0.35)'"
                       onmouseout="this.style.transform=''; this.style.boxShadow=''">
                        <i class="fas fa-play-circle"></i> Commencer le quiz
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="glass-card" style="padding:3rem; text-align:center; border-radius:20px;">
            <i class="fas fa-clipboard-list" style="font-size:3rem; color:rgba(255,255,255,0.2); margin-bottom:1rem; display:block;"></i>
            <p style="color:rgba(255,255,255,0.5); font-size:1.1rem;">Aucun quiz disponible pour le moment.</p>
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <a href="<?= $basePath ?>/quiz/admin" style="display:inline-flex; align-items:center; gap:0.5rem; margin-top:1rem; background:linear-gradient(135deg,#eab308,#d97706); color:#000; font-weight:700; padding:0.75rem 1.5rem; border-radius:12px; text-decoration:none;">
                    <i class="fas fa-plus"></i> Créer un quiz
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
