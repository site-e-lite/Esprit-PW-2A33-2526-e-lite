<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../Controller/Course/CourseController.php';

// Compute base path
$_projectRoot = realpath(__DIR__ . '/../../..');
$_docRoot     = realpath($_SERVER['DOCUMENT_ROOT']);
$_rel         = str_replace('\\', '/', substr($_projectRoot, strlen($_docRoot)));
$__bp         = rtrim($_rel, '/');
if ($__bp === '.' || $__bp === '') $__bp = '';

$courseController = new CourseController();

$page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit  = 12;
$result = $courseController->getCoursesPaginated($page, $limit);

$courses      = $result['courses'];
$totalPages   = $result['totalPages'];
$totalCourses = $result['totalCourses'];
$currentPage  = $result['currentPage'];

include __DIR__ . '/../../layout/header.php';
?>

<div style="max-width:1400px; margin:0 auto; padding:6rem 5% 3rem;">
    <div class="section-header">
        <h2>📚 Nos Cours</h2>
        <p>Découvrez nos <?= $totalCourses ?> cours disponibles et développez vos compétences</p>
    </div>

    <!-- Barre de recherche -->
    <div style="margin:2rem 0;">
        <input type="text" id="searchCourses" placeholder="Rechercher un cours..."
               style="width:100%; max-width:500px; background:rgba(0,0,0,0.5); border:1px solid rgba(255,255,255,0.1);
                      padding:0.9rem 1.2rem; border-radius:12px; color:#f4f4f5; font-size:1rem;">
    </div>

    <?php if (count($courses) > 0): ?>
        <div class="courses-grid">
            <?php foreach ($courses as $course): ?>
                <div class="glass-card course-card">
                    <?php if ($course['image']): ?>
                        <img src="<?= htmlspecialchars($course['image']) ?>"
                             alt="<?= htmlspecialchars($course['titre']) ?>"
                             class="course-img" style="width:100%; height:200px; object-fit:cover; border-radius:16px 16px 0 0;">
                    <?php else: ?>
                        <div class="course-img" style="height:200px; display:flex; align-items:center; justify-content:center; font-size:3.5rem; background:rgba(255,255,255,0.03); border-radius:16px 16px 0 0;">📚</div>
                    <?php endif; ?>

                    <div class="course-info">
                        <span class="level"><?= ucfirst(htmlspecialchars($course['niveau'])) ?></span>
                        <h3 class="course-title"><?= htmlspecialchars($course['titre']) ?></h3>
                        <p class="course-meta">⏱️ <?= (int)$course['duree'] ?> heures</p>
                        <div class="course-meta" style="margin-top:auto; padding-top:1rem;">
                            <strong><?= number_format((float)$course['prix'], 2, ',', ' ') ?> TND</strong>
                        </div>
                        <div style="display:flex; gap:0.5rem; margin-top:1rem; flex-wrap:wrap;">
                            <a href="<?= $__bp ?>/cours/detail?id=<?= (int)$course['idCourse'] ?>" class="btn-outline" style="flex:1; text-align:center;">Détails</a>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <a href="<?= $__bp ?>/cours/inscription?idCourse=<?= (int)$course['idCourse'] ?>" class="btn-primary" style="flex:1; text-align:center;">S'inscrire</a>
                            <?php else: ?>
                                <a href="<?= $__bp ?>/login" class="btn-primary" style="flex:1; text-align:center;">Connexion</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
        <div style="display:flex; justify-content:center; gap:0.5rem; flex-wrap:wrap; margin:3rem 0;">
            <?php if ($currentPage > 1): ?>
                <a href="<?= $__bp ?>/cours/liste?page=1" class="btn-outline">&laquo; Première</a>
                <a href="<?= $__bp ?>/cours/liste?page=<?= $currentPage - 1 ?>" class="btn-outline">← Précédent</a>
            <?php endif; ?>
            <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                <?php if ($i == $currentPage): ?>
                    <span class="btn-primary" style="padding:0.625rem 0.875rem; cursor:default;"><?= $i ?></span>
                <?php else: ?>
                    <a href="<?= $__bp ?>/cours/liste?page=<?= $i ?>" class="btn-outline"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            <?php if ($currentPage < $totalPages): ?>
                <a href="<?= $__bp ?>/cours/liste?page=<?= $currentPage + 1 ?>" class="btn-outline">Suivant →</a>
                <a href="<?= $__bp ?>/cours/liste?page=<?= $totalPages ?>" class="btn-outline">Dernière &raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="glass-card" style="text-align:center; padding:3rem;">
            <p style="color:#a1a1aa; font-size:1.1rem;">📚 Aucun cours disponible pour le moment</p>
        </div>
    <?php endif; ?>
</div>

<script>
const searchInput = document.getElementById('searchCourses');
if (searchInput) {
    searchInput.addEventListener('input', function() {
        const term = this.value.toLowerCase();
        document.querySelectorAll('.course-card').forEach(card => {
            const title = card.querySelector('.course-title')?.innerText.toLowerCase() || '';
            card.style.display = title.includes(term) ? '' : 'none';
        });
    });
}
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
