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

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . $__bp . '/login');
    exit;
}

$idCourse = isset($_GET['idCourse']) ? (int)$_GET['idCourse'] : null;
if (!$idCourse) { header('Location: ' . $__bp . '/cours/liste'); exit; }

$courseController = new CourseController();
$course = $courseController->getById($idCourse);
if (!$course) { header('Location: ' . $__bp . '/cours/liste'); exit; }

$message     = null;
$messageType = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enrollmentController = new EnrollmentController();
    $_POST['idCourse'] = $idCourse;
    $result      = $enrollmentController->enrollUser();
    $message     = $result['message'];
    $messageType = $result['success'] ? 'success' : 'error';

    if ($result['success']) {
        header('Location: ' . $__bp . '/cours/mes-cours');
        exit;
    }
}

include __DIR__ . '/../../layout/header.php';
?>

<div style="max-width:800px; margin:0 auto; padding:6rem 5% 3rem;">
    <a href="<?= $__bp ?>/cours/detail?id=<?= (int)$course['idCourse'] ?>" style="display:inline-block; margin-bottom:20px; color:#eab308; text-decoration:none; font-weight:600;">← Retour au cours</a>

    <div class="glass-card">
        <h1 style="font-size:1.8rem; margin-bottom:10px; color:#eab308;">S'inscrire au cours</h1>

        <div style="background:rgba(234,179,8,.05); border-left:4px solid #eab308; padding:15px; border-radius:0 12px 12px 0; margin-bottom:30px;">
            <h3 style="margin:0 0 10px 0;"><?= htmlspecialchars($course['titre']) ?></h3>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; font-size:.9rem; color:#a1a1aa;">
                <div><strong>Niveau:</strong> <?= ucfirst(htmlspecialchars($course['niveau'])) ?></div>
                <div><strong>Durée:</strong> <?= (int)$course['duree'] ?> heures</div>
                <div><strong>Prix:</strong> <?= number_format((float)$course['prix'], 2, ',', ' ') ?> TND</div>
                <div><strong>Langue:</strong> <?= strtoupper(htmlspecialchars($course['langue'])) ?></div>
            </div>
        </div>

        <?php if ($message): ?>
            <div style="padding:12px; margin-bottom:15px; border-radius:12px; background:<?= $messageType === 'success' ? 'rgba(16,185,129,.1)' : 'rgba(239,68,68,.1)' ?>; border-left:4px solid <?= $messageType === 'success' ? '#10b981' : '#ef4444' ?>; color:<?= $messageType === 'success' ? '#10b981' : '#ef4444' ?>;">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="niveauInitial">Votre niveau initial <span style="color:#ef4444;">*</span></label>
                <select id="niveauInitial" name="niveauInitial" required>
                    <option value="">-- Sélectionner --</option>
                    <option value="debutant">Débutant</option>
                    <option value="intermediaire">Intermédiaire</option>
                    <option value="avance">Avancé</option>
                </select>
            </div>

            <div class="form-group">
                <label for="objectifPersonnel">Votre objectif personnel <span style="color:#ef4444;">*</span></label>
                <textarea id="objectifPersonnel" name="objectifPersonnel" required placeholder="Qu'espérez-vous apprendre dans ce cours ?"></textarea>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                <div class="form-group">
                    <label for="engagement">Engagement (h/semaine) <span style="color:#ef4444;">*</span></label>
                    <select id="engagement" name="engagement" required>
                        <option value="">-- Sélectionner --</option>
                        <option value="5">5 heures</option>
                        <option value="10">10 heures</option>
                        <option value="15">15 heures</option>
                        <option value="20">20+ heures</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="modeAcces">Mode d'accès</label>
                    <select id="modeAcces" name="modeAcces">
                        <option value="gratuit">Gratuit</option>
                        <option value="payant">Payant</option>
                    </select>
                </div>
            </div>

            <div style="background:rgba(100,200,255,.05); border-left:4px solid #00a8ff; padding:15px; border-radius:0 12px 12px 0; margin:20px 0; color:#a1a1aa;">
                <strong>📋 Conditions:</strong> En vous inscrivant, vous acceptez les conditions d'utilisation. Vous pouvez quitter le cours à tout moment.
            </div>

            <div style="display:flex; gap:10px; margin-top:30px;">
                <button type="submit" class="btn-primary" style="flex:1;">Confirmer l'inscription</button>
                <a href="<?= $__bp ?>/cours/detail?id=<?= (int)$course['idCourse'] ?>" class="btn-outline" style="flex:1; text-align:center;">Annuler</a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
