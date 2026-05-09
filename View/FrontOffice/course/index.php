<?php
require_once __DIR__ . '/../../../Controller/CourseController.php';

$baseUrl = '/gestioncours';
$pageTitle = 'FrontOffice - Cours disponibles';
$controller = new CourseController();
$courses = $controller->listPublished();

// IDs des cours récents (ajoutés après le lancement initial) — badge "Nouveau"
// Mis à jour automatiquement : les 4 derniers cours insérés sont marqués "Nouveau"
$allIds      = array_column($courses, 'idCourse');
$recentIds   = array_slice($allIds, 0, 4); // Les 4 plus récents (ORDER BY idCourse DESC)

// Cours "Tendance" : le cours avec le plus de leçons (premier de la liste)
$trendingId  = $allIds[0] ?? null;

$totalCourses = count($courses);
$avgPrice = 0;
if ($totalCourses > 0) {
    $sum = 0;
    foreach ($courses as $courseItem) {
        $sum += (float)$courseItem['prix'];
    }
    $avgPrice = $sum / $totalCourses;
}

require_once __DIR__ . '/../../includes/header.php';
?>
<section id="accueil" class="hero reveal">
    <div class="hero-bg-anim"></div>
    <div class="hero-content">
        <div class="eco-badge"><i class="fas fa-leaf"></i> 100% Digital Learning</div>
        <h1>Gestion <span class="text-gradient">Cours</span></h1>
        <p>Même design et même navigation globale que gestion evaluation, adapté au module des cours.</p>
        <div class="hero-actions">
            <a href="#cours" class="btn-primary"><i class="fas fa-book-open"></i> Explorer les cours</a>
            <a href="<?= $baseUrl ?>/View/BackOffice/course/list.php" class="btn-outline"><i class="fas fa-gear"></i> Ouvrir BackOffice</a>
        </div>
    </div>
</section>

<section class="stats-section reveal">
    <div class="stats-grid">
        <div class="stat-item glass-card">
            <i class="fas fa-graduation-cap accent-icon"></i>
            <h3><?= (int)$totalCourses ?></h3>
            <p>Cours publiés</p>
        </div>
        <div class="stat-item glass-card">
            <i class="fas fa-tags accent-icon"></i>
            <h3><?= number_format((float)$avgPrice, 2, ',', ' ') ?></h3>
            <p>Prix moyen (TND)</p>
        </div>
    </div>
</section>

<section id="cours" class="gestion-section reveal">
    <div class="section-header">
        <h2>Nos <span class="text-gradient">Cours</span></h2>
        <p>Parcourez nos formations et inscrivez-vous directement.</p>
    </div>

    <div class="courses-grid">
        <?php if (!empty($courses)): ?>
            <?php foreach ($courses as $index => $course): ?>
                <?php
                    $isNew      = in_array((int)$course['idCourse'], $recentIds, true);
                    $isTrending = (int)$course['idCourse'] === (int)$trendingId;
                ?>
                <article class="course-card glass-card <?= $index === 0 ? 'top-course' : '' ?>">
                    <?php if ($isTrending): ?>
                        <div class="ai-badge"><i class="fas fa-fire"></i> 🔥 Tendance</div>
                    <?php elseif ($isNew): ?>
                        <div class="ai-badge" style="background:linear-gradient(135deg,#10b981,#059669);">
                            <i class="fas fa-sparkles"></i> Nouveau
                        </div>
                    <?php endif; ?>
                    <div class="course-img">
                        <img
                            src="<?= htmlspecialchars((string)($course['image'] ?: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=900&q=80')) ?>"
                            alt="<?= htmlspecialchars($course['titre']) ?>"
                            loading="lazy"
                            style="width:100%; height:100%; object-fit:cover;"
                        >
                    </div>
                    <div class="course-info">
                        <span class="level"><?= htmlspecialchars($course['niveau']) ?></span>
                        <h3><?= htmlspecialchars($course['titre']) ?></h3>
                        <p><?= nl2br(htmlspecialchars(mb_substr((string)$course['description'], 0, 130))) ?>...</p>
                        <div class="course-meta">
                            <span><i class="far fa-clock"></i> <?= (int)$course['duree'] ?> h</span>
                            <span><i class="fas fa-money-bill-wave"></i> <?= number_format((float)$course['prix'], 2, ',', ' ') ?> TND</span>
                        </div>
                        <a class="btn-outline" href="<?= $baseUrl ?>/View/FrontOffice/course/show.php?id=<?= (int)$course['idCourse'] ?>">Voir détails</a>
                        <a class="btn-primary" href="<?= $baseUrl ?>/View/FrontOffice/enrollment/add.php?idCourse=<?= (int)$course['idCourse'] ?>">S'inscrire</a>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="glass-card">Aucun cours publié actuellement.</div>
        <?php endif; ?>
    </div>
</section>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
