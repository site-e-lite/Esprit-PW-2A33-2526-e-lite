<?php
/**
 * View/FrontOffice/dashboard.php
 * Role-based unified dashboard: Student / Teacher / Admin
 */
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Utils/PermissionHelper.php';

// Auth guard
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

$userId   = (int)$_SESSION['user_id'];
$roleName = strtolower(trim((string)($_SESSION['role_nom'] ?? '')));
$isAdmin   = ($roleName === 'admin');
$isTeacher = ($roleName === 'enseignant');
$isStudent = ($roleName === 'etudiant');

$db = Config::getInstance()->getConnexion();

// ── Fetch data based on role ──────────────────────────────────────
$data = [];

if ($isStudent) {
    // Enrolled courses with progress
    $stmt = $db->prepare(
        'SELECT e.*, c.titre, c.image, c.niveau, c.duree,
                (SELECT COUNT(*) FROM lesson WHERE idCourse = c.idCourse) AS totalLessons,
                (SELECT COUNT(*) FROM lesson_completion lc
                 JOIN lesson l ON l.idLesson = lc.idLesson
                 WHERE lc.user_id = :uid AND l.idCourse = c.idCourse) AS doneLessons
         FROM enrollment e
         JOIN course c ON c.idCourse = e.idCourse
         WHERE e.idUser = :uid AND e.statut = "actif"
         ORDER BY e.dateInscription DESC'
    );
    $stmt->execute([':uid' => $userId]);
    $data['courses'] = $stmt->fetchAll();

    // Certificates
    $stmt2 = $db->prepare(
        'SELECT cert.*, c.titre AS courseTitre
         FROM certificates cert
         JOIN course c ON c.idCourse = cert.course_id
         WHERE cert.user_id = :uid
         ORDER BY cert.date_obtained DESC LIMIT 5'
    );
    $stmt2->execute([':uid' => $userId]);
    $data['certificates'] = $stmt2->fetchAll();

    // Recent forum posts by this user
    $stmt3 = $db->prepare(
        'SELECT p.*, f.titre AS forumTitre, c.titre AS courseTitre
         FROM post p
         JOIN forum f ON f.idForum = p.idForum
         LEFT JOIN course c ON c.idCourse = f.idCourse
         WHERE p.idUser = :uid
         ORDER BY p.datePost DESC LIMIT 5'
    );
    $stmt3->execute([':uid' => $userId]);
    $data['recentPosts'] = $stmt3->fetchAll();

} elseif ($isTeacher) {
    // Courses taught
    $teacherCourseIds = PermissionHelper::getTeacherCourses($userId);
    $data['courses'] = [];

    if (!empty($teacherCourseIds)) {
        $placeholders = implode(',', array_fill(0, count($teacherCourseIds), '?'));
        $stmt = $db->prepare(
            "SELECT c.*,
                    COUNT(DISTINCT e.idEnrollment) AS totalEnrolled,
                    SUM(CASE WHEN e.statut='actif' THEN 1 ELSE 0 END) AS activeStudents,
                    ROUND(AVG(e.progression),0) AS avgProgress,
                    (SELECT COUNT(*) FROM forum WHERE idCourse = c.idCourse) AS forumCount
             FROM course c
             LEFT JOIN enrollment e ON e.idCourse = c.idCourse
             WHERE c.idCourse IN ($placeholders)
             GROUP BY c.idCourse
             ORDER BY c.idCourse DESC"
        );
        $stmt->execute($teacherCourseIds);
        $data['courses'] = $stmt->fetchAll();

        // Recent enrollments in teacher's courses
        $stmt2 = $db->prepare(
            "SELECT e.*, u.nom, u.prenom, c.titre AS courseTitre
             FROM enrollment e
             JOIN user u ON u.idUser = e.idUser
             JOIN course c ON c.idCourse = e.idCourse
             WHERE e.idCourse IN ($placeholders)
             ORDER BY e.dateInscription DESC LIMIT 10"
        );
        $stmt2->execute($teacherCourseIds);
        $data['recentEnrollments'] = $stmt2->fetchAll();

        // Recent forum posts in teacher's courses
        $stmt3 = $db->prepare(
            "SELECT p.*, f.titre AS forumTitre, c.titre AS courseTitre, u.nom, u.prenom
             FROM post p
             JOIN forum f ON f.idForum = p.idForum
             JOIN course c ON c.idCourse = f.idCourse
             JOIN user u ON u.idUser = p.idUser
             WHERE f.idCourse IN ($placeholders)
             ORDER BY p.datePost DESC LIMIT 8"
        );
        $stmt3->execute($teacherCourseIds);
        $data['recentPosts'] = $stmt3->fetchAll();
    }

} elseif ($isAdmin) {
    // Platform-wide stats
    $data['stats'] = [
        'totalUsers'       => $db->query("SELECT COUNT(*) FROM user WHERE statut='actif'")->fetchColumn(),
        'totalStudents'    => $db->query("SELECT COUNT(u.idUser) FROM user u JOIN role r ON u.idRole=r.idRole WHERE r.nom='etudiant' AND u.statut='actif'")->fetchColumn(),
        'totalTeachers'    => $db->query("SELECT COUNT(u.idUser) FROM user u JOIN role r ON u.idRole=r.idRole WHERE r.nom='enseignant' AND u.statut='actif'")->fetchColumn(),
        'totalCourses'     => $db->query("SELECT COUNT(*) FROM course")->fetchColumn(),
        'publishedCourses' => $db->query("SELECT COUNT(*) FROM course WHERE statut='publie'")->fetchColumn(),
        'totalEnrollments' => $db->query("SELECT COUNT(*) FROM enrollment")->fetchColumn(),
        'activeEnrollments'=> $db->query("SELECT COUNT(*) FROM enrollment WHERE statut='actif'")->fetchColumn(),
        'certificates'     => $db->query("SELECT COUNT(*) FROM certificates")->fetchColumn(),
        'totalForums'      => $db->query("SELECT COUNT(*) FROM forum")->fetchColumn(),
        'totalPosts'       => $db->query("SELECT COUNT(*) FROM post")->fetchColumn(),
    ];

    // Top courses
    $data['topCourses'] = $db->query(
        "SELECT c.idCourse, c.titre, COUNT(e.idEnrollment) AS enrolled
         FROM course c LEFT JOIN enrollment e ON e.idCourse = c.idCourse
         GROUP BY c.idCourse ORDER BY enrolled DESC LIMIT 5"
    )->fetchAll();

    // Recent activity
    $data['recentActivity'] = $db->query(
        "SELECT 'enrollment' AS type, e.dateInscription AS date,
                u.nom, u.prenom, c.titre AS label
         FROM enrollment e
         JOIN user u ON u.idUser = e.idUser
         JOIN course c ON c.idCourse = e.idCourse
         UNION ALL
         SELECT 'post' AS type, p.datePost AS date,
                u.nom, u.prenom, f.titre AS label
         FROM post p
         JOIN user u ON u.idUser = p.idUser
         JOIN forum f ON f.idForum = p.idForum
         ORDER BY date DESC LIMIT 12"
    )->fetchAll();
}

$pageTitle = 'Dashboard — ' . ucfirst($roleName);
require_once __DIR__ . '/../layout/header.php';
?>

<style>
.dash-grid   { display:grid; grid-template-columns:repeat(auto-fit,minmax(320px,1fr)); gap:1.5rem; margin-top:1.5rem; }
.dash-card   { background:var(--glass-bg); border:1px solid var(--glass-border); border-radius:16px; padding:1.5rem; }
.dash-card h2{ font-size:1.2rem; margin-bottom:1rem; color:#eab308; }
.kpi-grid    { display:grid; grid-template-columns:repeat(auto-fit,minmax(130px,1fr)); gap:1rem; margin-bottom:1.5rem; }
.kpi         { background:rgba(255,255,255,.04); border-radius:12px; padding:1rem; text-align:center; }
.kpi .num    { font-size:2rem; font-weight:800; color:#f4f4f5; }
.kpi .lbl    { font-size:.8rem; color:#aaa; margin-top:.2rem; }
.course-row  { display:flex; align-items:center; gap:1rem; padding:.75rem 0; border-bottom:1px solid rgba(255,255,255,.06); }
.course-row:last-child { border-bottom:none; }
.course-thumb{ width:48px; height:48px; border-radius:8px; object-fit:cover; background:#1e1e2e; flex-shrink:0; }
.course-info h4 { margin:0; font-size:.95rem; color:#f4f4f5; }
.course-info small { color:#aaa; font-size:.8rem; }
.prog-bar    { height:6px; background:#1e1e2e; border-radius:3px; margin-top:.4rem; overflow:hidden; }
.prog-fill   { height:100%; background:linear-gradient(90deg,#7c3aed,#a78bfa); border-radius:3px; }
.activity-item { padding:.6rem 0; border-bottom:1px solid rgba(255,255,255,.05); font-size:.88rem; color:#d1d5db; }
.activity-item:last-child { border-bottom:none; }
.badge       { display:inline-block; padding:.2rem .6rem; border-radius:20px; font-size:.75rem; font-weight:600; }
.badge-enroll{ background:rgba(16,185,129,.15); color:#4ade80; }
.badge-post  { background:rgba(124,58,237,.15); color:#a78bfa; }
.badge-cert  { background:rgba(234,179,8,.15); color:#fde047; }
</style>

<section>
    <h1 style="font-size:2rem; margin-bottom:.5rem;">
        Bonjour, <?= htmlspecialchars((string)($_SESSION['user_prenom'] ?? '')) ?> 👋
    </h1>
    <p style="color:#aaa;">
        <?php
        if ($isStudent)  echo 'Votre espace étudiant — suivez votre progression et participez aux forums.';
        if ($isTeacher)  echo 'Votre espace enseignant — gérez vos cours et suivez vos étudiants.';
        if ($isAdmin)    echo 'Tableau de bord administrateur — vue globale de la plateforme.';
        ?>
    </p>

    <?php /* ── STUDENT ─────────────────────────────────────────── */ ?>
    <?php if ($isStudent): ?>

        <div class="kpi-grid" style="margin-top:1.5rem;">
            <div class="kpi">
                <div class="num"><?= count($data['courses']) ?></div>
                <div class="lbl">Cours actifs</div>
            </div>
            <div class="kpi">
                <div class="num"><?= count($data['certificates']) ?></div>
                <div class="lbl">Certificats</div>
            </div>
            <div class="kpi">
                <div class="num"><?= count($data['recentPosts']) ?></div>
                <div class="lbl">Posts forum</div>
            </div>
        </div>

        <div class="dash-grid">
            <div class="dash-card">
                <h2><i class="fas fa-book-open"></i> Mes cours</h2>
                <?php if (empty($data['courses'])): ?>
                    <p style="color:#aaa;">Aucun cours en cours.
                        <a href="/gestioncours/View/FrontOffice/course/index.php">Explorer les cours →</a>
                    </p>
                <?php else: ?>
                    <?php foreach ($data['courses'] as $c):
                        $total   = max(1, (int)$c['totalLessons']);
                        $done    = (int)$c['doneLessons'];
                        $pct     = min(100, (int)round($done / $total * 100));
                    ?>
                        <div class="course-row">
                            <img class="course-thumb"
                                 src="<?= htmlspecialchars((string)($c['image'] ?: 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=100&q=60')) ?>"
                                 alt="">
                            <div class="course-info" style="flex:1;">
                                <h4><?= htmlspecialchars($c['titre']) ?></h4>
                                <small><?= $done ?>/<?= $total ?> leçons — <?= $pct ?>%</small>
                                <div class="prog-bar"><div class="prog-fill" style="width:<?= $pct ?>%"></div></div>
                            </div>
                            <a href="/gestioncours/View/FrontOffice/course/show.php?id=<?= (int)$c['idCourse'] ?>"
                               style="color:#a78bfa; font-size:.85rem;">Continuer →</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="dash-card">
                <h2><i class="fas fa-certificate"></i> Mes certificats</h2>
                <?php if (empty($data['certificates'])): ?>
                    <p style="color:#aaa;">Complétez un cours à 100% pour obtenir votre certificat.</p>
                <?php else: ?>
                    <?php foreach ($data['certificates'] as $cert): ?>
                        <div class="course-row">
                            <div style="width:40px; height:40px; border-radius:50%; background:linear-gradient(135deg,#10b981,#059669); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                <i class="fas fa-certificate" style="color:#fff;"></i>
                            </div>
                            <div class="course-info" style="flex:1;">
                                <h4><?= htmlspecialchars($cert['courseTitre']) ?></h4>
                                <small><?= date('d/m/Y', strtotime($cert['date_obtained'])) ?></small>
                            </div>
                            <a href="/gestioncours/View/FrontOffice/certificate/view.php?id=<?= (int)$cert['id'] ?>"
                               style="color:#10b981; font-size:.85rem;">Voir →</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <a href="/gestioncours/View/FrontOffice/certificate/index.php"
                   style="display:block; margin-top:1rem; color:#eab308; font-size:.85rem;">
                    Tous mes certificats →
                </a>
            </div>

            <div class="dash-card">
                <h2><i class="fas fa-comments"></i> Activité forum</h2>
                <?php if (empty($data['recentPosts'])): ?>
                    <p style="color:#aaa;">Aucune activité forum récente.</p>
                <?php else: ?>
                    <?php foreach ($data['recentPosts'] as $p): ?>
                        <div class="activity-item">
                            <strong style="color:#a78bfa;"><?= htmlspecialchars($p['forumTitre']) ?></strong>
                            <br><?= htmlspecialchars(mb_substr($p['contenu'], 0, 80)) ?>…
                            <br><small style="color:#555;"><?= date('d/m/Y H:i', strtotime($p['datePost'])) ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    <?php /* ── TEACHER ─────────────────────────────────────────── */ ?>
    <?php elseif ($isTeacher): ?>

        <div class="kpi-grid" style="margin-top:1.5rem;">
            <div class="kpi">
                <div class="num"><?= count($data['courses']) ?></div>
                <div class="lbl">Mes cours</div>
            </div>
            <div class="kpi">
                <div class="num"><?= array_sum(array_column($data['courses'], 'activeStudents')) ?></div>
                <div class="lbl">Étudiants actifs</div>
            </div>
            <div class="kpi">
                <div class="num"><?= count($data['recentPosts'] ?? []) ?></div>
                <div class="lbl">Posts récents</div>
            </div>
        </div>

        <div class="dash-grid">
            <div class="dash-card">
                <h2><i class="fas fa-chalkboard-teacher"></i> Mes cours</h2>
                <?php if (empty($data['courses'])): ?>
                    <p style="color:#aaa;">Aucun cours assigné.</p>
                <?php else: ?>
                    <?php foreach ($data['courses'] as $c): ?>
                        <div class="course-row">
                            <div class="course-info" style="flex:1;">
                                <h4><?= htmlspecialchars($c['titre']) ?></h4>
                                <small>
                                    <?= (int)$c['activeStudents'] ?> étudiants actifs •
                                    <?= (int)$c['avgProgress'] ?>% progression moy. •
                                    <?= (int)$c['forumCount'] ?> forum(s)
                                </small>
                            </div>
                            <div style="display:flex; gap:.5rem;">
                                <a href="/gestioncours/View/BackOffice/course/edit.php?id=<?= (int)$c['idCourse'] ?>"
                                   style="color:#eab308; font-size:.85rem;">Éditer</a>
                                <a href="/forum?courseId=<?= (int)$c['idCourse'] ?>"
                                   style="color:#a78bfa; font-size:.85rem;">Forum</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <a href="/gestioncours/View/BackOffice/course/add.php"
                   style="display:block; margin-top:1rem; color:#eab308; font-size:.85rem;">
                    + Ajouter un cours
                </a>
            </div>

            <div class="dash-card">
                <h2><i class="fas fa-user-graduate"></i> Inscriptions récentes</h2>
                <?php if (empty($data['recentEnrollments'])): ?>
                    <p style="color:#aaa;">Aucune inscription récente.</p>
                <?php else: ?>
                    <?php foreach (array_slice($data['recentEnrollments'], 0, 6) as $e): ?>
                        <div class="activity-item">
                            <strong><?= htmlspecialchars($e['prenom'] . ' ' . $e['nom']) ?></strong>
                            → <?= htmlspecialchars($e['courseTitre']) ?>
                            <br><small style="color:#555;"><?= date('d/m/Y', strtotime($e['dateInscription'])) ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="dash-card">
                <h2><i class="fas fa-comments"></i> Forum — activité récente</h2>
                <?php if (empty($data['recentPosts'])): ?>
                    <p style="color:#aaa;">Aucune activité forum.</p>
                <?php else: ?>
                    <?php foreach ($data['recentPosts'] as $p): ?>
                        <div class="activity-item">
                            <strong style="color:#a78bfa;"><?= htmlspecialchars($p['prenom'] . ' ' . $p['nom']) ?></strong>
                            dans <em><?= htmlspecialchars($p['forumTitre']) ?></em>
                            <br><?= htmlspecialchars(mb_substr($p['contenu'], 0, 70)) ?>…
                            <br><small style="color:#555;"><?= date('d/m/Y H:i', strtotime($p['datePost'])) ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    <?php /* ── ADMIN ───────────────────────────────────────────── */ ?>
    <?php elseif ($isAdmin): ?>

        <div class="kpi-grid" style="margin-top:1.5rem;">
            <?php
            $kpis = [
                ['num' => $data['stats']['totalStudents'],    'lbl' => 'Étudiants'],
                ['num' => $data['stats']['totalTeachers'],    'lbl' => 'Enseignants'],
                ['num' => $data['stats']['publishedCourses'], 'lbl' => 'Cours publiés'],
                ['num' => $data['stats']['activeEnrollments'],'lbl' => 'Inscriptions actives'],
                ['num' => $data['stats']['certificates'],     'lbl' => 'Certificats'],
                ['num' => $data['stats']['totalPosts'],       'lbl' => 'Posts forum'],
            ];
            foreach ($kpis as $k): ?>
                <div class="kpi">
                    <div class="num"><?= $k['num'] ?></div>
                    <div class="lbl"><?= $k['lbl'] ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="dash-grid">
            <div class="dash-card">
                <h2><i class="fas fa-trophy"></i> Top cours</h2>
                <?php foreach ($data['topCourses'] as $i => $c): ?>
                    <div class="course-row">
                        <div style="width:28px; height:28px; border-radius:50%; background:#eab308; display:flex; align-items:center; justify-content:center; font-weight:800; color:#000; flex-shrink:0;">
                            <?= $i + 1 ?>
                        </div>
                        <div class="course-info" style="flex:1;">
                            <h4><?= htmlspecialchars($c['titre']) ?></h4>
                            <small><?= (int)$c['enrolled'] ?> inscriptions</small>
                        </div>
                    </div>
                <?php endforeach; ?>
                <a href="/gestioncours/View/BackOffice/course/list.php"
                   style="display:block; margin-top:1rem; color:#eab308; font-size:.85rem;">
                    Gérer tous les cours →
                </a>
            </div>

            <div class="dash-card">
                <h2><i class="fas fa-stream"></i> Activité récente</h2>
                <?php foreach ($data['recentActivity'] as $a): ?>
                    <div class="activity-item">
                        <span class="badge <?= $a['type'] === 'enrollment' ? 'badge-enroll' : 'badge-post' ?>">
                            <?= $a['type'] === 'enrollment' ? 'Inscription' : 'Post' ?>
                        </span>
                        <strong><?= htmlspecialchars($a['prenom'] . ' ' . $a['nom']) ?></strong>
                        — <?= htmlspecialchars(mb_substr($a['label'], 0, 50)) ?>
                        <br><small style="color:#555;"><?= date('d/m/Y H:i', strtotime($a['date'])) ?></small>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="dash-card">
                <h2><i class="fas fa-links"></i> Accès rapides</h2>
                <div style="display:flex; flex-direction:column; gap:.8rem;">
                    <a href="/gestioncours/View/BackOffice/course/list.php"    class="btn-outline" style="justify-content:flex-start; gap:.8rem;"><i class="fas fa-graduation-cap"></i> Gérer les cours</a>
                    <a href="/gestioncours/View/BackOffice/enrollment/list.php" class="btn-outline" style="justify-content:flex-start; gap:.8rem;"><i class="fas fa-user-graduate"></i> Inscriptions</a>
                    <a href="/gestioncours/View/BackOffice/certificate/list.php" class="btn-outline" style="justify-content:flex-start; gap:.8rem;"><i class="fas fa-certificate"></i> Certificats</a>
                    <a href="/admin/dashboard"                                  class="btn-outline" style="justify-content:flex-start; gap:.8rem;"><i class="fas fa-users-cog"></i> Utilisateurs</a>
                    <a href="/forum/manage"                                     class="btn-outline" style="justify-content:flex-start; gap:.8rem;"><i class="fas fa-comments"></i> Forum</a>
                </div>
            </div>
        </div>

    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
