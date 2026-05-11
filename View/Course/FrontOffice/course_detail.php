<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../Controller/Course/CourseController.php';
require_once __DIR__ . '/../../../Controller/Course/EnrollmentController.php';

$_projectRoot = realpath(__DIR__ . '/../../..');
$_docRoot     = realpath($_SERVER['DOCUMENT_ROOT']);
$_rel         = str_replace('\\', '/', substr($_projectRoot, strlen($_docRoot)));
$__bp         = rtrim($_rel, '/');
if ($__bp === '.' || $__bp === '') $__bp = '';

$idCourse = isset($_GET['id']) ? (int)$_GET['id'] : null;
if (!$idCourse) { header('Location: ' . $__bp . '/cours/liste'); exit; }

$courseController = new CourseController();
$course = $courseController->getById($idCourse);
if (!$course) { header('Location: ' . $__bp . '/cours/liste'); exit; }

$isEnrolled = false;
if (isset($_SESSION['user_id'])) {
    $enrollmentController = new EnrollmentController();
    $enrollments = $enrollmentController->getMyEnrollments((int)$_SESSION['user_id']);
    foreach ($enrollments as $enrollment) {
        if ((int)$enrollment['idCourse'] === $idCourse) { $isEnrolled = true; break; }
    }
}

include __DIR__ . '/../../layout/header.php';
?>

<div style="max-width:1200px; margin:0 auto; padding:6rem 5% 3rem;">
    <a href="<?= $__bp ?>/cours/liste" style="display:inline-block; margin-bottom:20px; color:#eab308; text-decoration:none; font-weight:600;">← Retour aux cours</a>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:40px; margin-bottom:40px;">
        <div class="glass-card" style="display:flex; align-items:center; justify-content:center; border-radius:24px; min-height:300px; overflow:hidden;">
            <?php if ($course['image']): ?>
                <img src="<?= htmlspecialchars($course['image']) ?>" alt="<?= htmlspecialchars($course['titre']) ?>" style="width:100%; height:100%; object-fit:cover; border-radius:24px;">
            <?php else: ?>
                <span style="font-size:5rem;">📚</span>
            <?php endif; ?>
        </div>

        <div class="glass-card">
            <h1 style="font-size:2rem; margin-bottom:20px;"><?= htmlspecialchars($course['titre']) ?></h1>
            <div style="display:flex; gap:20px; margin-bottom:20px; flex-wrap:wrap;">
                <span class="level">Niveau: <?= ucfirst(htmlspecialchars($course['niveau'])) ?></span>
                <span style="color:#a1a1aa;">📚 <?= (int)$course['duree'] ?> heures</span>
                <span style="color:#a1a1aa;">🌐 <?= strtoupper(htmlspecialchars($course['langue'])) ?></span>
            </div>
            <div class="level" style="margin-bottom:20px;">Statut: <?= ucfirst(htmlspecialchars($course['statut'])) ?></div>
            <div style="font-size:1.8rem; font-weight:700; color:#10b981; margin-bottom:20px;">
                <?php if ((float)$course['prix'] > 0): ?>
                    <?= number_format((float)$course['prix'], 2, ',', ' ') ?> TND
                <?php else: ?>
                    GRATUIT
                <?php endif; ?>
            </div>
            <div style="display:flex; gap:1rem; flex-wrap:wrap;">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($isEnrolled): ?>
                        <span style="display:inline-block; padding:1rem 2.2rem; background:#10b981; color:white; border-radius:50px; font-weight:600;">✓ Vous êtes inscrit</span>
                        <a href="<?= $__bp ?>/cours/mes-cours" class="btn-primary">Voir mon cours</a>
                    <?php else: ?>
                        <a href="<?= $__bp ?>/cours/inscription?idCourse=<?= (int)$course['idCourse'] ?>" class="btn-primary">S'inscrire au cours</a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="<?= $__bp ?>/login" class="btn-primary">Connexion pour s'inscrire</a>
                <?php endif; ?>
                <a href="<?= $__bp ?>/cours/show?id=<?= (int)$course['idCourse'] ?>" class="btn-outline">Voir le cours complet</a>
            </div>
        </div>
    </div>

    <div class="glass-card" style="margin-bottom:2rem;">
        <h2 style="font-size:1.3rem; margin-bottom:15px; color:#eab308; border-bottom:2px solid #eab308; padding-bottom:10px;">À propos du cours</h2>
        <p style="line-height:1.8; color:#a1a1aa;"><?= nl2br(htmlspecialchars($course['description'])) ?></p>
    </div>

    <?php if ($course['objectifs']): ?>
    <div class="glass-card" style="margin-bottom:2rem;">
        <h2 style="font-size:1.3rem; margin-bottom:15px; color:#eab308; border-bottom:2px solid #eab308; padding-bottom:10px;">Objectifs d'apprentissage</h2>
        <ul style="list-style-position:inside; margin-left:20px;">
            <?php foreach (explode("\n", $course['objectifs']) as $obj): ?>
                <?php if (trim($obj)): ?>
                    <li style="margin-bottom:10px; color:#a1a1aa;"><?= htmlspecialchars(trim($obj)) ?></li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <?php if ($course['prerequis']): ?>
    <div class="glass-card">
        <h2 style="font-size:1.3rem; margin-bottom:15px; color:#eab308; border-bottom:2px solid #eab308; padding-bottom:10px;">Prérequis</h2>
        <p style="line-height:1.8; color:#a1a1aa;"><?= nl2br(htmlspecialchars($course['prerequis'])) ?></p>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
