<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../../config.php';
require_once __DIR__ . '/../../../../Controller/Course/CourseController.php';
require_once __DIR__ . '/../../../../Controller/Course/SupportCourseController.php';
require_once __DIR__ . '/../../../../Controller/Course/ProgressController.php';
require_once __DIR__ . '/../../../../Controller/Course/RatingController.php';
require_once __DIR__ . '/../../../../Controller/Course/CertificateController.php';

$_projectRoot = realpath(__DIR__ . '/../../../..');
$_docRoot     = realpath($_SERVER['DOCUMENT_ROOT']);
$_rel         = str_replace('\\', '/', substr($_projectRoot, strlen($_docRoot)));
$__bp         = rtrim($_rel, '/');
if ($__bp === '.' || $__bp === '') $__bp = '';

$courseController   = new CourseController();
$supportController  = new SupportCourseController();
$progressController = new ProgressController();
$ratingController   = new RatingController();
$certController     = new CertificateController();

$currentUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$course = $courseController->getById($id);

if ($course === null) {
    http_response_code(404);
    include __DIR__ . '/../../../layout/header.php';
    echo '<div style="padding:6rem 5%;"><p style="color:#ef4444;">Cours introuvable.</p><a href="' . $__bp . '/cours/liste">← Retour</a></div>';
    include __DIR__ . '/../../../layout/footer.php';
    exit;
}

$supports = $supportController->listByCourse($id);

// Progress Tracking
$progressMessage      = null;
$certificateGenerated = false;
if ($currentUserId > 0 && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_complete') {
    $lessonId             = isset($_POST['lesson_id']) ? (int)$_POST['lesson_id'] : 0;
    $result               = $progressController->markLessonComplete($currentUserId, $lessonId);
    $progressMessage      = $result['message'];
    $certificateGenerated = $result['certificate_generated'] ?? false;
}

$progressData    = $currentUserId > 0 ? $progressController->getProgress($currentUserId, $id) : ['progress_percent' => 0, 'last_accessed' => null, 'lessons' => $progressController->getLessonsByCourse($id), 'completed_ids' => [], 'total' => 0, 'done' => 0];
$progressPercent = $progressData['progress_percent'];
$lastAccessed    = $progressData['last_accessed'];
$lessons         = $progressData['lessons'];
$completedIds    = $progressData['completed_ids'];

$existingCert = ($currentUserId > 0) ? $certController->getByUserAndCourse($currentUserId, $id) : null;

// Rating System
$ratingMessage = null;
if ($currentUserId > 0 && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_rating') {
    $ratingValue   = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $ratingResult  = $ratingController->addOrUpdateRating($currentUserId, $id, $ratingValue);
    $ratingMessage = $ratingResult['message'];
}

$userRating  = ($currentUserId > 0) ? $ratingController->getUserRating($currentUserId, $id) : null;
$averageData = $ratingController->getAverageRating($id);

include __DIR__ . '/../../../layout/header.php';
?>

<div style="max-width:1100px; margin:0 auto; padding:6rem 5% 3rem;">
    <a href="<?= $__bp ?>/cours/liste" style="color:#eab308; text-decoration:none; font-weight:600;">← Retour aux cours</a>

    <div class="glass-card" style="margin-top:2rem; padding:2rem;">
        <h2 style="font-size:2rem; margin-bottom:1rem;"><?= htmlspecialchars($course['titre']) ?></h2>
        <p style="color:#a1a1aa; line-height:1.7;"><?= nl2br(htmlspecialchars($course['description'])) ?></p>
        <div style="display:flex; gap:2rem; margin-top:1.5rem; flex-wrap:wrap; color:#a1a1aa;">
            <span><strong>Niveau:</strong> <?= htmlspecialchars($course['niveau']) ?></span>
            <span><strong>Durée:</strong> <?= (int)$course['duree'] ?> h</span>
            <span><strong>Langue:</strong> <?= htmlspecialchars($course['langue']) ?></span>
            <span><strong>Prix:</strong> <?= number_format((float)$course['prix'], 2, ',', ' ') ?> TND</span>
        </div>

        <?php if ($course['objectifs']): ?>
            <h3 style="margin-top:1.5rem; color:#eab308;">Objectifs</h3>
            <p style="color:#a1a1aa;"><?= nl2br(htmlspecialchars($course['objectifs'])) ?></p>
        <?php endif; ?>

        <?php if ($course['prerequis']): ?>
            <h3 style="margin-top:1.5rem; color:#eab308;">Prérequis</h3>
            <p style="color:#a1a1aa;"><?= nl2br(htmlspecialchars($course['prerequis'])) ?></p>
        <?php endif; ?>

        <?php if (!empty($supports)): ?>
            <h3 style="margin-top:1.5rem; color:#eab308;">Supports associés</h3>
            <ul style="color:#a1a1aa; padding-left:1.5rem;">
                <?php foreach ($supports as $support): ?>
                    <li style="margin-bottom:0.5rem;">
                        <?= htmlspecialchars($support['titre']) ?> (<?= htmlspecialchars($support['type']) ?>)
                        — <a href="<?= htmlspecialchars($support['url']) ?>" target="_blank" style="color:#eab308;">Ouvrir</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if ($currentUserId > 0): ?>
            <div style="margin-top:1.5rem; display:flex; gap:1rem; flex-wrap:wrap;">
                <a href="<?= $__bp ?>/cours/inscription?idCourse=<?= (int)$course['idCourse'] ?>" class="btn-primary">S'inscrire à ce cours</a>
            </div>
        <?php else: ?>
            <div style="margin-top:1.5rem;">
                <a href="<?= $__bp ?>/login" class="btn-primary">Connexion pour s'inscrire</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- PROGRESS TRACKING -->
    <?php if ($currentUserId > 0): ?>
    <div class="glass-card" style="margin-top:2rem; padding:1.5rem;">
        <h3><i class="fas fa-chart-line"></i> Ma progression</h3>

        <?php if ($lastAccessed): ?>
            <p style="color:#a1a1aa; font-size:.9rem;">Dernière activité : <?= htmlspecialchars(date('d/m/Y à H:i', strtotime($lastAccessed))) ?></p>
        <?php endif; ?>

        <div style="background:#1e1e2e; border-radius:8px; height:22px; overflow:hidden; margin:1rem 0;">
            <div style="width:<?= $progressPercent ?>%; height:100%; background:linear-gradient(90deg,#7c3aed,#a78bfa); border-radius:8px; display:flex; align-items:center; justify-content:center; color:#fff; font-size:.8rem; font-weight:600; transition:width .4s ease;">
                <?php if ($progressPercent > 10): ?><?= $progressPercent ?>%<?php endif; ?>
            </div>
        </div>
        <p style="text-align:right; font-weight:600; color:#a78bfa;">
            <?= $progressData['done'] ?>/<?= $progressData['total'] ?> leçons — <?= $progressPercent ?>% complété
        </p>

        <?php if ($progressMessage): ?>
            <p style="color:#4ade80; font-weight:600;"><?= htmlspecialchars($progressMessage) ?></p>
        <?php endif; ?>

        <?php if ($progressPercent >= 100): ?>
            <p style="color:#4ade80; font-size:1.1rem; font-weight:700;"><i class="fas fa-trophy"></i> Cours complété ! Félicitations.</p>
        <?php endif; ?>

        <?php if ($certificateGenerated): ?>
            <div style="margin-top:1.5rem; padding:1.5rem 2rem; background:linear-gradient(135deg,rgba(16,185,129,.15),rgba(5,150,105,.1)); border:1px solid #10b981; border-radius:14px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;">
                <div>
                    <p style="color:#4ade80; font-size:1.2rem; font-weight:700; margin:0;">🎓 Certificat généré avec succès !</p>
                    <p style="color:#a1a1aa; font-size:.9rem; margin:.3rem 0 0;">Votre certificat de réussite est disponible dans votre espace.</p>
                </div>
                <a href="<?= $__bp ?>/cours/certificats" style="background:#10b981; color:#fff; padding:.7rem 1.5rem; border-radius:8px; font-weight:600; text-decoration:none;">
                    <i class="fas fa-certificate"></i> Voir mon certificat
                </a>
            </div>
        <?php elseif ($existingCert !== null): ?>
            <div style="margin-top:1.5rem; padding:1rem 1.5rem; background:rgba(16,185,129,.08); border:1px solid rgba(16,185,129,.4); border-radius:12px; display:flex; align-items:center; gap:1rem;">
                <i class="fas fa-certificate" style="color:#10b981; font-size:1.4rem;"></i>
                <span style="color:#4ade80; font-weight:600;">Certificat obtenu le <?= htmlspecialchars(date('d/m/Y', strtotime($existingCert['date_obtained']))) ?></span>
                <a href="<?= $__bp ?>/cours/certificats" style="margin-left:auto; color:#10b981; font-weight:600; text-decoration:none;">Voir →</a>
            </div>
        <?php endif; ?>

        <?php if (!empty($lessons)): ?>
            <ul style="list-style:none; padding:0; margin-top:1.2rem;">
                <?php foreach ($lessons as $lesson): ?>
                    <?php $done = in_array((int)$lesson['idLesson'], $completedIds, true); ?>
                    <li style="display:flex; align-items:center; justify-content:space-between; padding:.6rem .8rem; margin-bottom:.5rem; background:<?= $done ? 'rgba(74,222,128,.08)' : 'rgba(255,255,255,.04)' ?>; border:1px solid <?= $done ? '#4ade80' : 'rgba(255,255,255,.1)' ?>; border-radius:8px;">
                        <span>
                            <i class="fas <?= $done ? 'fa-check-circle' : 'fa-circle' ?>" style="color:<?= $done ? '#4ade80' : '#6b7280' ?>; margin-right:.5rem;"></i>
                            <?= htmlspecialchars($lesson['titre']) ?>
                        </span>
                        <?php if (!$done): ?>
                            <form method="POST" style="margin:0;">
                                <input type="hidden" name="action" value="mark_complete">
                                <input type="hidden" name="lesson_id" value="<?= (int)$lesson['idLesson'] ?>">
                                <button type="submit" style="background:#7c3aed; color:#fff; border:none; border-radius:6px; padding:.3rem .8rem; font-size:.8rem; cursor:pointer;">
                                    <i class="fas fa-check"></i> Terminer
                                </button>
                            </form>
                        <?php else: ?>
                            <span style="color:#4ade80; font-size:.85rem; font-weight:600;">Complétée</span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p style="color:#a1a1aa; font-style:italic; margin-top:1rem;">Aucune leçon disponible pour ce cours.</p>
        <?php endif; ?>
    </div>

    <!-- RATING SYSTEM -->
    <div class="glass-card" style="margin-top:2rem; padding:1.5rem;">
        <h3><i class="fas fa-star"></i> Évaluation du cours</h3>
        <p style="font-size:1.1rem; margin-bottom:1rem;">
            <?php if ($averageData['average'] !== null): ?>
                <?php $avg = $averageData['average']; for ($s = 1; $s <= 5; $s++): ?>
                    <i class="fas fa-star" style="color:<?= $s <= round($avg) ? '#f59e0b' : '#4b5563' ?>; font-size:1.2rem;"></i>
                <?php endfor; ?>
                <strong style="color:#f59e0b; margin-left:.5rem;"><?= $avg ?>/5</strong>
                <span style="color:#a1a1aa; font-size:.9rem;">(<?= $averageData['count'] ?> avis)</span>
            <?php else: ?>
                <span style="color:#a1a1aa;">Aucune note pour ce cours.</span>
            <?php endif; ?>
        </p>

        <?php if ($ratingMessage): ?>
            <p style="color:#4ade80; font-weight:600;"><?= htmlspecialchars($ratingMessage) ?></p>
        <?php endif; ?>

        <form method="POST" style="display:flex; align-items:center; gap:1rem; flex-wrap:wrap;">
            <input type="hidden" name="action" value="submit_rating">
            <label for="rating" style="font-weight:600;"><?= $userRating ? 'Modifier ma note :' : 'Noter ce cours :' ?></label>
            <select name="rating" id="rating" style="background:#1e1e2e; color:#fff; border:1px solid #7c3aed; border-radius:6px; padding:.4rem .8rem; font-size:1rem;">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <option value="<?= $i ?>" <?= ($userRating && (int)$userRating['rating'] === $i) ? 'selected' : '' ?>>
                        <?= $i ?> étoile<?= $i > 1 ? 's' : '' ?>
                    </option>
                <?php endfor; ?>
            </select>
            <button type="submit" class="btn-primary">
                <i class="fas fa-paper-plane"></i> <?= $userRating ? 'Mettre à jour' : 'Envoyer ma note' ?>
            </button>
        </form>

        <?php if ($userRating): ?>
            <p style="color:#a1a1aa; font-size:.85rem; margin-top:.5rem;">Votre note actuelle : <strong style="color:#f59e0b;"><?= (int)$userRating['rating'] ?> ⭐</strong></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../../layout/footer.php'; ?>
