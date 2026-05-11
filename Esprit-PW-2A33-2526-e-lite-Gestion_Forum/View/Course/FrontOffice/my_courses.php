<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../config.php';
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

$enrollmentController = new EnrollmentController();
$idUser = (int)$_SESSION['user_id'];

// Handle unenroll
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'unenroll' && isset($_POST['idEnrollment'])) {
    $enrollmentController->delete((int)$_POST['idEnrollment']);
    header('Location: ' . $__bp . '/cours/mes-cours');
    exit;
}

$enrollments = $enrollmentController->getMyEnrollments($idUser);

include __DIR__ . '/../../layout/header.php';
?>

<div style="max-width:1200px; margin:0 auto; padding:6rem 5% 3rem;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px; flex-wrap:wrap; gap:1rem;">
        <h1 style="font-size:2rem;">📚 Mes Cours</h1>
        <a href="<?= $__bp ?>/cours/liste" class="btn-primary">+ Découvrir d'autres cours</a>
    </div>

    <div style="display:flex; gap:10px; margin-bottom:20px; flex-wrap:wrap;">
        <button class="btn-outline" onclick="filterCourses('all')" data-filter="all" style="border:2px solid #eab308;">Tous</button>
        <button class="btn-outline" onclick="filterCourses('actif')" data-filter="actif">En cours</button>
        <button class="btn-outline" onclick="filterCourses('termine')" data-filter="termine">Terminés</button>
        <button class="btn-outline" onclick="filterCourses('abandonne')" data-filter="abandonne">Abandonnés</button>
    </div>

    <?php if (count($enrollments) > 0): ?>
        <div class="courses-grid">
            <?php foreach ($enrollments as $enrollment): ?>
                <div class="glass-card" data-status="<?= htmlspecialchars($enrollment['statut']) ?>">
                    <div style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%); padding:20px; border-radius:24px 24px 0 0; color:white;">
                        <div style="font-size:1.2rem; font-weight:bold; margin-bottom:10px;"><?= htmlspecialchars($enrollment['titre']) ?></div>
                        <div style="font-size:.85rem; opacity:.9;">
                            Niveau: <?= ucfirst(htmlspecialchars($enrollment['niveau'])) ?> | <?= (int)$enrollment['duree'] ?>h
                        </div>
                    </div>

                    <div style="padding:20px;">
                        <?php
                        $statusColors = ['actif' => '#10b981', 'termine' => '#00a8ff', 'abandonne' => '#ffc107'];
                        $statusBg     = ['actif' => 'rgba(16,185,129,.1)', 'termine' => 'rgba(0,168,255,.1)', 'abandonne' => 'rgba(255,193,7,.1)'];
                        $sc = $enrollment['statut'];
                        ?>
                        <span style="display:inline-block; padding:.5rem 1rem; border-radius:50px; font-weight:600; margin-bottom:10px; background:<?= $statusBg[$sc] ?? 'rgba(255,255,255,.05)' ?>; color:<?= $statusColors[$sc] ?? '#a1a1aa' ?>;">
                            <?= ucfirst(htmlspecialchars($sc)) ?>
                        </span>

                        <div style="margin-bottom:15px;">
                            <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:.9rem;">
                                <span>Progression</span>
                                <span><strong><?= round($enrollment['progression']) ?>%</strong></span>
                            </div>
                            <div style="width:100%; height:8px; background:rgba(255,255,255,.1); border-radius:4px; overflow:hidden;">
                                <div style="height:100%; background:linear-gradient(90deg,#10b981,#20c997); width:<?= (int)$enrollment['progression'] ?>%; transition:width .3s ease;"></div>
                            </div>
                        </div>

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:15px; font-size:.85rem;">
                            <div style="background:rgba(255,255,255,.05); padding:10px; border-radius:12px;">
                                <div style="color:#a1a1aa; margin-bottom:3px;">Inscrit depuis</div>
                                <div style="font-weight:bold;"><?= date('d/m/Y', strtotime($enrollment['dateInscription'])) ?></div>
                            </div>
                            <div style="background:rgba(255,255,255,.05); padding:10px; border-radius:12px;">
                                <div style="color:#a1a1aa; margin-bottom:3px;">Temps passé</div>
                                <div style="font-weight:bold;">
                                    <?php
                                    $h = floor($enrollment['tempsTotalPasse'] / 3600);
                                    $m = floor(($enrollment['tempsTotalPasse'] % 3600) / 60);
                                    echo $h . 'h ' . $m . 'm';
                                    ?>
                                </div>
                            </div>
                            <div style="background:rgba(255,255,255,.05); padding:10px; border-radius:12px;">
                                <div style="color:#a1a1aa; margin-bottom:3px;">Dernière activité</div>
                                <div style="font-weight:bold;">
                                    <?php
                                    if ($enrollment['derniereActivite']) {
                                        $diff = (new DateTime())->diff(new DateTime($enrollment['derniereActivite']));
                                        if ($diff->days > 0) echo 'il y a ' . $diff->days . 'j';
                                        elseif ($diff->h > 0) echo 'il y a ' . $diff->h . 'h';
                                        else echo 'à l\'instant';
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </div>
                            </div>
                            <div style="background:rgba(255,255,255,.05); padding:10px; border-radius:12px;">
                                <div style="color:#a1a1aa; margin-bottom:3px;">Note finale</div>
                                <div style="font-weight:bold;">
                                    <?= $enrollment['noteFinale'] !== null ? round($enrollment['noteFinale'], 1) . '/20' : 'En attente' ?>
                                </div>
                            </div>
                        </div>

                        <div style="display:flex; gap:10px; margin-top:15px;">
                            <a href="<?= $__bp ?>/cours/show?id=<?= (int)$enrollment['idCourse'] ?>" class="btn-outline" style="flex:1; text-align:center;">Continuer</a>
                            <form method="POST" style="flex:1;" onsubmit="return confirm('Êtes-vous sûr de vouloir quitter ce cours ?');">
                                <input type="hidden" name="action" value="unenroll">
                                <input type="hidden" name="idEnrollment" value="<?= (int)$enrollment['idEnrollment'] ?>">
                                <button type="submit" class="btn-outline" style="width:100%; background:rgba(239,68,68,.1); border-color:#ef4444; color:#ef4444; cursor:pointer;">Quitter</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="glass-card" style="text-align:center; padding:3rem;">
            <h2 style="color:#a1a1aa; margin-bottom:15px;">Aucun cours pour le moment</h2>
            <p style="color:#a1a1aa; margin-bottom:20px;">Vous n'êtes inscrit à aucun cours. Découvrez nos cours disponibles !</p>
            <a href="<?= $__bp ?>/cours/liste" class="btn-primary" style="display:inline-block;">Parcourir nos cours</a>
        </div>
    <?php endif; ?>
</div>

<script>
function filterCourses(status) {
    document.querySelectorAll('[data-filter]').forEach(btn => {
        btn.style.borderColor = btn.dataset.filter === status ? '#eab308' : 'rgba(255,255,255,.2)';
    });
    document.querySelectorAll('[data-status]').forEach(card => {
        card.style.display = (status === 'all' || card.dataset.status === status) ? '' : 'none';
    });
}
</script>

<?php include __DIR__ . '/../../layout/footer.php'; ?>
