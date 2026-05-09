<?php
require_once __DIR__ . '/../../../Controller/CourseController.php';
require_once __DIR__ . '/../../../Controller/SupportCourseController.php';
require_once __DIR__ . '/../../../Controller/ProgressController.php';
require_once __DIR__ . '/../../../Controller/RatingController.php';
require_once __DIR__ . '/../../../Controller/CertificateController.php';

$baseUrl = '/gestioncours';
$courseController   = new CourseController();
$supportController  = new SupportCourseController();
$progressController = new ProgressController();
$ratingController   = new RatingController();
$certController     = new CertificateController();

// --- Simulation utilisateur connecté (remplacer par session réelle plus tard) ---
$currentUserId = 1;

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$course = $courseController->getById($id);

if ($course === null) {
    http_response_code(404);
    echo 'Cours introuvable.';
    exit;
}

$pageTitle = 'FrontOffice - ' . $course['titre'];
$supports  = $supportController->listByCourse($id);

// --- Progress Tracking ---
// 1. Marque une leçon spécifique comme complétée
$progressMessage      = null;
$certificateGenerated = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_complete') {
    $lessonId             = isset($_POST['lesson_id']) ? (int)$_POST['lesson_id'] : 0;
    $result               = $progressController->markLessonComplete($currentUserId, $lessonId);
    $progressMessage      = $result['message'];
    $certificateGenerated = $result['certificate_generated'] ?? false;
}

// 2. Récupère la progression calculée + liste des leçons
$progressData    = $progressController->getProgress($currentUserId, $id);
$progressPercent = $progressData['progress_percent'];
$lastAccessed    = $progressData['last_accessed'];
$lessons         = $progressData['lessons'];
$completedIds    = $progressData['completed_ids'];

// 3. Récupère le certificat existant (pour afficher le lien même sans action POST)
$existingCert = $certController->getByUserAndCourse($currentUserId, $id);

// --- Rating System ---
// 1. Traitement du formulaire de notation
$ratingMessage = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_rating') {
    $ratingValue   = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $ratingResult  = $ratingController->addOrUpdateRating($currentUserId, $id, $ratingValue);
    $ratingMessage = $ratingResult['message'];
}

// 2. Récupère la note de l'utilisateur et la moyenne du cours
$userRating    = $ratingController->getUserRating($currentUserId, $id);
$averageData   = $ratingController->getAverageRating($id);

require_once __DIR__ . '/../../includes/header.php';
?>
<section>
    <h2><?= htmlspecialchars($course['titre']) ?></h2>
    <p><?= nl2br(htmlspecialchars($course['description'])) ?></p>
    <p><strong>Niveau:</strong> <?= htmlspecialchars($course['niveau']) ?></p>
    <p><strong>Durée:</strong> <?= (int)$course['duree'] ?> h</p>
    <p><strong>Langue:</strong> <?= htmlspecialchars($course['langue']) ?></p>
    <p><strong>Prix:</strong> <?= number_format((float)$course['prix'], 2, ',', ' ') ?> TND</p>

    <h3>Objectifs</h3>
    <p><?= nl2br(htmlspecialchars((string)($course['objectifs'] ?? 'Aucun objectif renseigné.'))) ?></p>

    <h3>Prérequis</h3>
    <p><?= nl2br(htmlspecialchars((string)($course['prerequis'] ?? 'Aucun prérequis renseigné.'))) ?></p>

    <h3>Supports associés</h3>
    <ul>
        <?php foreach ($supports as $support): ?>
            <li>
                <?= htmlspecialchars($support['titre']) ?> (<?= htmlspecialchars($support['type']) ?>)
                - <a href="<?= htmlspecialchars($support['url']) ?>" target="_blank">Ouvrir</a>
            </li>
        <?php endforeach; ?>
    </ul>

    <p>
        <a href="<?= $baseUrl ?>/View/FrontOffice/enrollment/add.php?idCourse=<?= (int)$course['idCourse'] ?>">S'inscrire à ce cours</a>
        |
        <a href="<?= $baseUrl ?>/View/FrontOffice/course/index.php">Retour</a>
    </p>

    <!-- ===== PROGRESS TRACKING ===== -->
    <div class="progress-block glass-card" style="margin-top:2rem; padding:1.5rem;">
        <h3><i class="fas fa-chart-line"></i> Ma progression</h3>

        <?php if ($lastAccessed): ?>
            <p style="color:#aaa; font-size:.9rem;">
                Dernière activité : <?= htmlspecialchars(date('d/m/Y à H:i', strtotime($lastAccessed))) ?>
            </p>
        <?php endif; ?>

        <!-- Barre de progression calculée -->
        <div style="background:#1e1e2e; border-radius:8px; height:22px; overflow:hidden; margin:1rem 0;">
            <div style="
                width: <?= $progressPercent ?>%;
                height: 100%;
                background: linear-gradient(90deg, #7c3aed, #a78bfa);
                border-radius: 8px;
                transition: width .4s ease;
                display: flex; align-items: center; justify-content: center;
                color: #fff; font-size: .8rem; font-weight: 600;
            ">
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
            <p style="color:#4ade80; font-size:1.1rem; font-weight:700;">
                <i class="fas fa-trophy"></i> Cours complété ! Félicitations.
            </p>
        <?php endif; ?>

        <!-- ===== BANDEAU CERTIFICAT ===== -->
        <?php if ($certificateGenerated): ?>
            <div style="
                margin-top:1.5rem; padding:1.5rem 2rem;
                background:linear-gradient(135deg,rgba(16,185,129,.15),rgba(5,150,105,.1));
                border:1px solid #10b981; border-radius:14px;
                display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:1rem;
            ">
                <div>
                    <p style="color:#4ade80; font-size:1.2rem; font-weight:700; margin:0;">
                        🎓 Certificat généré avec succès !
                    </p>
                    <p style="color:#aaa; font-size:.9rem; margin:.3rem 0 0;">
                        Votre certificat de réussite est disponible dans votre espace.
                    </p>
                </div>
                <a href="<?= $baseUrl ?>/View/FrontOffice/certificate/index.php"
                   style="background:#10b981; color:#fff; padding:.7rem 1.5rem; border-radius:8px;
                          font-weight:600; text-decoration:none; white-space:nowrap;">
                    <i class="fas fa-certificate"></i> Voir mon certificat
                </a>
            </div>
        <?php elseif ($existingCert !== null): ?>
            <!-- Certificat déjà obtenu lors d'une visite précédente -->
            <div style="
                margin-top:1.5rem; padding:1rem 1.5rem;
                background:rgba(16,185,129,.08); border:1px solid rgba(16,185,129,.4);
                border-radius:12px; display:flex; align-items:center; gap:1rem;
            ">
                <i class="fas fa-certificate" style="color:#10b981; font-size:1.4rem;"></i>
                <span style="color:#4ade80; font-weight:600;">Certificat obtenu le <?= htmlspecialchars(date('d/m/Y', strtotime($existingCert['date_obtained']))) ?></span>
                <a href="<?= $baseUrl ?>/View/FrontOffice/certificate/index.php"
                   style="margin-left:auto; color:#10b981; font-weight:600; text-decoration:none;">
                    Voir →
                </a>
            </div>
        <?php endif; ?>
        <!-- ===== FIN BANDEAU CERTIFICAT ===== -->

        <!-- Liste des leçons avec bouton de complétion individuel -->
        <?php if (!empty($lessons)): ?>
            <ul style="list-style:none; padding:0; margin-top:1.2rem;">
                <?php foreach ($lessons as $lesson): ?>
                    <?php $done = in_array((int)$lesson['idLesson'], $completedIds, true); ?>
                    <li style="
                        display:flex; align-items:center; justify-content:space-between;
                        padding:.6rem .8rem; margin-bottom:.5rem;
                        background:<?= $done ? 'rgba(74,222,128,.08)' : 'rgba(255,255,255,.04)' ?>;
                        border:1px solid <?= $done ? '#4ade80' : 'rgba(255,255,255,.1)' ?>;
                        border-radius:8px;
                    ">
                        <span>
                            <i class="fas <?= $done ? 'fa-check-circle' : 'fa-circle' ?>"
                               style="color:<?= $done ? '#4ade80' : '#6b7280' ?>; margin-right:.5rem;"></i>
                            <?= htmlspecialchars($lesson['titre']) ?>
                        </span>
                        <?php if (!$done): ?>
                            <form method="POST" action="" style="margin:0;">
                                <input type="hidden" name="action"    value="mark_complete">
                                <input type="hidden" name="lesson_id" value="<?= (int)$lesson['idLesson'] ?>">
                                <button type="submit" style="
                                    background:#7c3aed; color:#fff; border:none;
                                    border-radius:6px; padding:.3rem .8rem;
                                    font-size:.8rem; cursor:pointer;
                                ">
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
            <p style="color:#aaa; font-style:italic; margin-top:1rem;">
                Aucune leçon disponible pour ce cours.
            </p>
        <?php endif; ?>
    </div>
    <!-- ===== FIN PROGRESS TRACKING ===== -->

    <!-- ===== RATING SYSTEM ===== -->
    <div class="rating-block glass-card" style="margin-top:2rem; padding:1.5rem;">
        <h3><i class="fas fa-star"></i> Évaluation du cours</h3>

        <!-- Moyenne globale -->
        <p style="font-size:1.1rem; margin-bottom:1rem;">
            <?php if ($averageData['average'] !== null): ?>
                <?php
                    // Affiche les étoiles pleines/vides selon la moyenne
                    $avg = $averageData['average'];
                    for ($s = 1; $s <= 5; $s++):
                        $color = $s <= round($avg) ? '#f59e0b' : '#4b5563';
                ?>
                    <i class="fas fa-star" style="color:<?= $color ?>; font-size:1.2rem;"></i>
                <?php endfor; ?>
                <strong style="color:#f59e0b; margin-left:.5rem;"><?= $avg ?>/5</strong>
                <span style="color:#aaa; font-size:.9rem;">(<?= $averageData['count'] ?> avis)</span>
            <?php else: ?>
                <span style="color:#aaa;">Aucune note pour ce cours.</span>
            <?php endif; ?>
        </p>

        <!-- Message de confirmation après soumission -->
        <?php if ($ratingMessage): ?>
            <p style="color:#4ade80; font-weight:600;"><?= htmlspecialchars($ratingMessage) ?></p>
        <?php endif; ?>

        <!-- Formulaire de notation -->
        <form method="POST" action="" style="display:flex; align-items:center; gap:1rem; flex-wrap:wrap;">
            <input type="hidden" name="action" value="submit_rating">
            <label for="rating" style="font-weight:600;">
                <?= $userRating ? 'Modifier ma note :' : 'Noter ce cours :' ?>
            </label>
            <select name="rating" id="rating" style="
                background:#1e1e2e; color:#fff; border:1px solid #7c3aed;
                border-radius:6px; padding:.4rem .8rem; font-size:1rem;
            ">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <option value="<?= $i ?>"
                        <?= ($userRating && (int)$userRating['rating'] === $i) ? 'selected' : '' ?>>
                        <?= $i ?> étoile<?= $i > 1 ? 's' : '' ?>
                    </option>
                <?php endfor; ?>
            </select>
            <button type="submit" class="btn-primary">
                <i class="fas fa-paper-plane"></i>
                <?= $userRating ? 'Mettre à jour' : 'Envoyer ma note' ?>
            </button>
        </form>

        <?php if ($userRating): ?>
            <p style="color:#aaa; font-size:.85rem; margin-top:.5rem;">
                Votre note actuelle : <strong style="color:#f59e0b;"><?= (int)$userRating['rating'] ?> ⭐</strong>
            </p>
        <?php endif; ?>
    </div>
    <!-- ===== FIN RATING SYSTEM ===== -->
</section>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
