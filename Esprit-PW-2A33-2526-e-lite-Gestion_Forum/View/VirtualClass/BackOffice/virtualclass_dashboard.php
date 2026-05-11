<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_pr = realpath(__DIR__ . '/../../..');
$_dr = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
$basePath = ($_pr && $_dr && strpos($_pr, $_dr) === 0)
    ? rtrim(str_replace('\\', '/', substr($_pr, strlen($_dr))), '/')
    : '';
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . $basePath . '/login');
    exit;
}

require_once __DIR__ . '/../../../Controller/VirtualClass/VirtualClassController.php';
require_once __DIR__ . '/../../../Controller/VirtualClass/SessionController.php';

$db = Config::getConnexion();
$vcController = new VirtualClassController();

/* ── Handle inline CRUD for Classes Virtuelles ── */
$vcError   = null;
$vcSuccess = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* Delete */
    if (isset($_POST['action']) && $_POST['action'] === 'delete_virtualclass') {
        try {
            $vcController->deleteVirtualClass((int) $_POST['idClass']);
            $vcSuccess = 'Classe virtuelle supprimée avec succès.';
        } catch (Throwable $e) { $vcError = $e->getMessage(); }
    }

    /* Add */
    if (isset($_POST['action']) && $_POST['action'] === 'add_virtualclass') {
        try {
            $idCoursePost = (int) ($_POST['idCourse'] ?? 0);
            $idCoursePost = $idCoursePost > 0 ? $idCoursePost : null;
            $capPost = (int) ($_POST['capacite'] ?? 30);
            if ($capPost <= 0) {
                $capPost = 30;
            }
            $vc = new VirtualClass(
                null,
                trim($_POST['titre'] ?? ''),
                trim($_POST['description'] ?? ''),
                trim($_POST['lienAcces'] ?? ''),
                trim($_POST['plateforme'] ?? ''),
                $capPost,
                $idCoursePost
            );
            $vcController->addVirtualClass($vc);
            $vcSuccess = 'Classe virtuelle ajoutée avec succès.';
        } catch (Throwable $e) { $vcError = $e->getMessage(); }
    }

    /* Update */
    if (isset($_POST['action']) && $_POST['action'] === 'update_virtualclass') {
        try {
            $editId = (int) ($_POST['idClass'] ?? 0);
            $idCoursePost = (int) ($_POST['idCourse'] ?? 0);
            $idCoursePost = $idCoursePost > 0 ? $idCoursePost : null;
            $capPost = (int) ($_POST['capacite'] ?? 30);
            if ($capPost <= 0) {
                $capPost = 30;
            }
            $vc = new VirtualClass(
                $editId,
                trim($_POST['titre'] ?? ''),
                trim($_POST['description'] ?? ''),
                trim($_POST['lienAcces'] ?? ''),
                trim($_POST['plateforme'] ?? ''),
                $capPost,
                $idCoursePost
            );
            $vcController->updateVirtualClass($vc, $editId);
            $vcSuccess = 'Classe virtuelle mise à jour avec succès.';
        } catch (Throwable $e) { $vcError = $e->getMessage(); }
    }
}

/* Fetch list + courses for selects */
$virtualClasses = $vcController->afficherVirtualClasses();
$courseStmt = $db->prepare('SELECT idCourse, titre FROM course ORDER BY idCourse DESC');
$courseStmt->execute();
$courses = $courseStmt->fetchAll();

/* Fetch Séances for cours-en-ligne panel */
$sessionController = new SessionController();
$allSessions = $sessionController->afficherSessions();

/* Stats for Séances */
$sessionStats = ['total'=>0,'planifiee'=>0,'en_cours'=>0,'terminee'=>0,'annulee'=>0];
foreach ($allSessions as $sess) {
    $sessionStats['total']++;
    $k = str_replace(' ','_',strtolower($sess['statut']));
    if (isset($sessionStats[$k])) $sessionStats[$k]++;
}

/* ── Vue d'ensemble : statistiques réelles (projet + style boj) ── */
$userTotal = (int) $db->query('SELECT COUNT(*) FROM user')->fetchColumn();
$userActive = (int) $db->query("SELECT COUNT(*) FROM user WHERE LOWER(COALESCE(statut,'')) = 'actif'")->fetchColumn();
$userPct = $userTotal > 0 ? min(100, (int) round($userActive / $userTotal * 100)) : 0;
$userArc = (int) round(226 * $userPct / 100);

$courseTotal = (int) $db->query('SELECT COUNT(*) FROM course')->fetchColumn();
$coursePublished = (int) $db->query("SELECT COUNT(*) FROM course WHERE LOWER(COALESCE(statut,'')) = 'publie'")->fetchColumn();
$coursePct = $courseTotal > 0 ? min(100, (int) round($coursePublished / $courseTotal * 100)) : 0;
$courseArc = (int) round(226 * $coursePct / 100);

$postTotal = (int) $db->query('SELECT COUNT(*) FROM post')->fetchColumn();

$recentUsersStmt = $db->query(
    "SELECT u.idUser, u.nom, u.prenom, u.email, u.statut, u.last_login, r.nom AS roleNom
     FROM user u
     LEFT JOIN role r ON r.idRole = u.idRole
     ORDER BY u.idUser DESC
     LIMIT 8"
);
$recentUsers = $recentUsersStmt->fetchAll(PDO::FETCH_ASSOC);

/* Fetch record to edit if requested */
$editVC = null;
if (isset($_GET['edit_vc'])) {
    $editVC = $vcController->getVirtualClassById((int) $_GET['edit_vc']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>e-lite | Dashboard BackOffice</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars($basePath) ?>/View/assets/User/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        header { display: none !important; }
        main.layout-main { padding-top: 0 !important; margin-top: 0 !important; }

        /*
         * Isolation : index.css définit .sidebar (fixed, droite) pour le panneau profil.
         * Sans ces règles, le menu du dashboard est déplacé / masqué et les stats ne sont plus visibles.
         */
        body.vc-boj-dashboard {
            display: flex !important;
            flex-direction: row !important;
            align-items: stretch !important;
            min-height: 100vh !important;
            background: var(--dark-bg) !important;
            overflow-x: hidden !important;
        }
        body.vc-boj-dashboard > aside.sidebar {
            position: relative !important;
            inset: auto !important;
            left: auto !important;
            right: auto !important;
            top: auto !important;
            bottom: auto !important;
            width: 280px !important;
            min-width: 280px !important;
            max-width: none !important;
            max-height: none !important;
            height: 100vh !important;
            transform: none !important;
            opacity: 1 !important;
            pointer-events: auto !important;
            z-index: 20 !important;
            flex-shrink: 0 !important;
            margin: 0 !important;
        }
        body.vc-boj-dashboard > main.main-content {
            flex: 1 !important;
            min-width: 0 !important;
        }

        /* ── Sidebar ── */
        .sidebar {
            width:280px; background:rgba(5,5,5,0.8); border-right:1px solid var(--glass-border);
            padding:2rem; display:flex; flex-direction:column; gap:2rem; height:100vh; flex-shrink:0;
        }
        .sidebar .nav-links { display:flex; flex-direction:column; gap:0.6rem; list-style:none; margin:0; padding:0; }
        .sidebar .nav-links a {
            display:flex; align-items:center; gap:1rem; padding:0.9rem 1rem; border-radius:12px;
            color:var(--light-gray); text-decoration:none; font-weight:500; transition:all 0.25s; cursor:pointer;
        }
        .sidebar .nav-links a:hover, .sidebar .nav-links a.active {
            background:rgba(234,179,8,0.1); color:var(--accent);
        }
        .sidebar .nav-links a::after { display:none; }

        /* ── Main ── */
        .main-content {
            flex:1; padding:2.5rem; overflow-y:auto; height:100vh; height:100vh;
            background:radial-gradient(circle at top right, rgba(234,179,8,0.05), transparent 40%);
        }

        /* ── Section panels ── */
        .dash-section { display:none; animation:fadePanel 0.3s ease; }
        .dash-section.active { display:block; }
        @keyframes fadePanel { from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:translateY(0)} }

        /* ── Header (override global fixed header from index.css) ── */
        .header-dashboard {
            position:static !important;
            width:auto !important;
            backdrop-filter:none !important;
            -webkit-backdrop-filter:none !important;
            background:transparent !important;
            border-bottom:1px solid var(--glass-border);
            display:flex; justify-content:space-between; align-items:center;
            margin-bottom:2.5rem; padding-bottom:1.5rem;
            padding:0 0 1.5rem 0;
            z-index:auto !important;
        }
        .user-profile { display:flex; align-items:center; gap:1rem; }
        .user-profile img { width:45px; height:45px; border-radius:50%; border:2px solid var(--accent); }

        /* ── Tables ── */
        .dashboard-table { width:100%; border-collapse:collapse; text-align:left; }
        .dashboard-table th { padding:1rem 1.2rem; color:var(--light-gray); font-size:0.82rem; text-transform:uppercase; letter-spacing:1px; border-bottom:1px solid var(--glass-border); }
        .dashboard-table td { padding:1.1rem 1.2rem; border-bottom:1px solid rgba(255,255,255,0.02); font-size:0.92rem; }
        .dashboard-table tr { transition:background 0.2s; }
        .dashboard-table tbody tr:hover { background:rgba(255,255,255,0.025); }

        /* ── Status badges ── */
        .status-badge { padding:0.35rem 0.8rem; border-radius:20px; font-size:0.8rem; font-weight:600; }
        .status-success { background:rgba(16,185,129,0.1); color:var(--green-eco); }
        .status-warning { background:rgba(234,179,8,0.1); color:var(--accent); }
        .badge-vc { padding:0.25rem 0.7rem; border-radius:20px; background:rgba(234,179,8,0.14); color:var(--accent); font-size:0.8rem; }

        /* ── Action buttons ── */
        .action-btn { width:34px; height:34px; border-radius:8px; border:1px solid var(--glass-border); display:inline-flex; align-items:center; justify-content:center; background:transparent; color:var(--light-gray); cursor:pointer; transition:all 0.2s; text-decoration:none; font-size:0.85rem; }
        .action-btn:hover { border-color:var(--accent); color:var(--accent); }
        .action-btn.del:hover { border-color:#ef4444; color:#ef4444; }

        /* ── Sortable th ── */
        th[data-sort] { cursor:pointer; user-select:none; }
        th[data-sort]:hover { color:var(--accent); }
        th[data-sort] .sort-icon { margin-left:5px; font-size:0.7rem; opacity:0.45; }
        th[data-sort].asc  .sort-icon::before { content:"▲"; opacity:1; }
        th[data-sort].desc .sort-icon::before { content:"▼"; opacity:1; }
        th[data-sort]:not(.asc):not(.desc) .sort-icon::before { content:"⇅"; }

        /* ── Search bar ── */
        .search-bar { position:relative; }
        .search-bar input { width:100%; padding:0.65rem 0.65rem 0.65rem 2.3rem; background:rgba(255,255,255,0.04); border:1px solid var(--glass-border); border-radius:10px; color:inherit; font-size:0.9rem; outline:none; transition:border-color 0.2s; box-sizing:border-box; }
        .search-bar input:focus { border-color:var(--accent); }
        .search-bar .si { position:absolute; left:0.75rem; top:50%; transform:translateY(-50%); color:var(--light-gray); pointer-events:none; font-size:0.85rem; }

        /* ── Stats chips ── */
        .stats-chips { display:flex; gap:0.8rem; flex-wrap:wrap; margin-bottom:0.8rem; }
        .chip { background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); border-radius:10px; padding:0.5rem 1rem; font-size:0.82rem; display:flex; align-items:center; gap:0.5rem; }
        .chip .cv { font-size:1.1rem; font-weight:700; color:var(--accent); }

        /* ── VC Toolbar ── */
        .vc-toolbar { display:flex; justify-content:flex-end; align-items:center; gap:0.8rem; flex-wrap:wrap; margin-bottom:1rem; }
        .vc-toolbar .search-bar { width:240px; min-width:160px; }

        /* ── Circular stats (donut) ── */
        .donut-ring { position:relative; display:inline-flex; align-items:center; justify-content:center; }
        .donut-ring svg { transform:rotate(-90deg); }
        .donut-ring .donut-label { position:absolute; text-align:center; }
        .donut-ring .donut-num { font-size:1.4rem; font-weight:800; color:var(--accent); line-height:1; }
        .donut-ring .donut-sub { font-size:0.68rem; color:var(--light-gray); text-transform:uppercase; letter-spacing:0.5px; margin-top:2px; }

        /* ── Séance status badges ── */
        .sbadge { padding:0.2rem 0.6rem; border-radius:20px; font-size:0.75rem; font-weight:600; }
        .sbadge-planifiee { background:rgba(59,130,246,0.15); color:#60a5fa; }
        .sbadge-en_cours  { background:rgba(234,179,8,0.15);  color:var(--accent); }
        .sbadge-terminee  { background:rgba(16,185,129,0.15); color:#10b981; }
        .sbadge-annulee   { background:rgba(239,68,68,0.15);  color:#ef4444; }

        /* ── PDF print-only isolation ── */
        body.vc-boj-dashboard.pdf-export-mode .sidebar,
        .pdf-export-mode .header-dashboard,
        .pdf-export-mode .vc-toolbar,
        .pdf-export-mode .stats-chips,
        .pdf-export-mode #vc-chips,
        .pdf-export-mode #vc-form-container,
        .pdf-export-mode .form-actions,
        .pdf-export-mode .action-btn,
        .pdf-export-mode #panel-overview,
        .pdf-export-mode .sessions-panel-header-actions { display:none !important; }
        .pdf-export-mode #panel-classes-virtuelles { display:block !important; }
        .pdf-export-mode .glass-card { background:#fff !important; color:#000 !important; border:1px solid #ccc !important; box-shadow:none !important; }
        .pdf-export-mode .dashboard-table th,
        .pdf-export-mode .dashboard-table td { color:#000 !important; border-bottom:1px solid #ddd !important; }
        .pdf-export-mode .badge-vc { background:#eee !important; color:#333 !important; }
        .pdf-export-mode h2.pdf-title { display:block !important; }
        @media print {
            body.vc-boj-dashboard > aside.sidebar { display:none !important; }
            .main-content { padding:0 !important; }
            .dash-section.active { display:block !important; }
            .header-dashboard, .vc-toolbar, .stats-chips, #vc-chips, #vc-form-container, .form-actions, .action-btn, .sessions-panel-header-actions { display:none !important; }
            .glass-card { background:#fff !important; color:#000 !important; border:1px solid #ccc !important; box-shadow:none !important; }
            .dashboard-table th, .dashboard-table td { color:#000 !important; border-bottom:1px solid #ddd !important; }
            .badge-vc { background:#eee !important; color:#333 !important; }
            h2.pdf-title { display:block !important; }
        }
        h2.pdf-title { display:none; }

        /* ── Inline form panel ── */
        .vc-form-panel { background:rgba(255,255,255,0.03); border:1px solid var(--glass-border); border-radius:16px; padding:1.8rem; margin-top:1.5rem; animation:fadePanel 0.3s ease; }
        .vc-form-panel .form-grid { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
        .vc-form-panel .full { grid-column:1/-1; }
        .vc-form-panel label { display:block; font-size:0.82rem; color:var(--light-gray); margin-bottom:0.4rem; text-transform:uppercase; letter-spacing:0.5px; }
        .vc-form-panel input, .vc-form-panel select, .vc-form-panel textarea {
            width:100%; padding:0.7rem 0.9rem; background:rgba(255,255,255,0.05); border:1px solid var(--glass-border);
            border-radius:10px; color:inherit; font-size:0.9rem; outline:none; transition:border-color 0.2s; box-sizing:border-box;
            font-family:inherit;
        }
        .vc-form-panel input:focus, .vc-form-panel select:focus, .vc-form-panel textarea:focus { border-color:var(--accent); }
        .vc-form-panel textarea { resize:vertical; min-height:80px; }
        .form-actions { display:flex; gap:0.8rem; margin-top:1.2rem; }

        /* ── Alert inline ── */
        .inline-alert { padding:0.8rem 1rem; border-radius:10px; margin-bottom:1rem; display:flex; align-items:center; gap:0.7rem; font-size:0.9rem; }
        .alert-ok { background:rgba(16,185,129,0.1); border:1px solid rgba(16,185,129,0.3); color:#10b981; }
        .alert-ko { background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3); color:#ef4444; }

        /* ── Highlight search ── */
        .hl { background:rgba(234,179,8,0.28); border-radius:3px; padding:0 1px; }

        /* ── Tooltip confirm modal ── */
        #del-modal { position:fixed; inset:0; z-index:9999; display:none; align-items:center; justify-content:center; background:rgba(0,0,0,0.55); backdrop-filter:blur(6px); }
        #del-modal.show { display:flex; }
        .del-box { background:#111; border:1px solid rgba(255,255,255,0.1); border-radius:18px; padding:2rem 2.5rem; max-width:380px; width:90%; box-shadow:0 20px 60px rgba(0,0,0,0.6); animation:fadePanel 0.25s ease; }
    </style>
</head>
<body class="vc-boj-dashboard">

<!-- ══ Sidebar ══════════════════════════════════════════ -->
<aside class="sidebar">
    <a href="<?= htmlspecialchars($basePath) ?>/" class="logo">e-lite<span>.</span></a>
    <div style="font-size:0.8rem; text-transform:uppercase; letter-spacing:1px; color:var(--light-gray); margin-top:-1rem;">BackOffice Admin</div>
    <nav class="nav-links">
        <a href="#" data-panel="overview" class="active"><i class="fas fa-chart-line"></i> Vue d'ensemble</a>
        <a href="<?= htmlspecialchars($basePath) ?>/virtualclass"><i class="fas fa-list"></i> Liste des classes</a>
        <a href="<?= htmlspecialchars($basePath) ?>/virtualclass/sessions"><i class="fas fa-calendar-alt"></i> Séances</a>
        <a href="<?= htmlspecialchars($basePath) ?>/forum/manage"><i class="fas fa-comments"></i> Forum</a>
        <a href="#" data-panel="classes-virtuelles" id="nav-cel"><i class="fas fa-chart-pie"></i> Stats, PDF &amp; classes</a>
        <a href="<?= htmlspecialchars($basePath) ?>/forum" style="margin-top:auto; border-top:1px solid var(--glass-border); border-radius:0; padding-top:1.5rem;"><i class="fas fa-globe"></i> Front Office</a>
    </nav>
</aside>

<!-- ══ Main content ════════════════════════════════════ -->
<main class="main-content">

    <!-- Shared header -->
    <div class="header-dashboard">
        <div>
            <h1 id="page-title" style="font-size:2rem; margin:0;">Tableau de Bord</h1>
            <p id="page-sub" style="color:var(--light-gray); margin-top:0.4rem;">Bienvenue dans l'espace administrateur.</p>
        </div>
        <div class="user-profile">
            <button class="btn-icon" title="Notifications" style="width:40px;height:40px;font-size:1rem;"><i class="fas fa-bell"></i></button>
            <img src="https://ui-avatars.com/api/?name=Admin&background=d4af37&color=000" alt="Admin">
            <div>
                <strong style="display:block;line-height:1;">Admin Base</strong>
                <span style="font-size:0.8rem;color:var(--accent);">Superviseur</span>
            </div>
        </div>
    </div>

    <!-- ══════════════ PANEL: Overview ══════════════════ -->
    <section id="panel-overview" class="dash-section active">

        <div style="display:flex;flex-wrap:wrap;gap:0.65rem;margin-bottom:1.75rem;">
            <button type="button" class="btn-primary" style="padding:0.55rem 1.1rem;font-size:0.88rem;" onclick="exportPDF();">
                <i class="fas fa-file-pdf"></i> Export PDF — liste des classes
            </button>
            <button type="button" class="btn-outline" style="padding:0.55rem 1.1rem;font-size:0.88rem;" onclick="switchPanel('classes-virtuelles'); var b=document.getElementById('btn-export-pdf'); if(b) b.scrollIntoView({behavior:'smooth',block:'nearest'});">
                <i class="fas fa-table"></i> Ouvrir le panneau détaillé
            </button>
            <a href="<?= htmlspecialchars($basePath) ?>/virtualclass" class="btn-outline" style="padding:0.55rem 1.1rem;font-size:0.88rem;text-decoration:none;display:inline-flex;align-items:center;gap:0.45rem;">
                <i class="fas fa-list"></i> Liste simple (CRUD)
            </a>
        </div>

        <!-- Stats Grid with donut circles -->
        <div style="margin-bottom:3rem; display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:1.5rem;">

            <!-- Utilisateurs (données réelles) -->
            <div class="glass-card" style="padding:1.8rem;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <p style="font-size:0.9rem;text-transform:uppercase;letter-spacing:1px;color:var(--light-gray);margin:0 0 0.4rem;">Utilisateurs</p>
                        <h3 style="font-size:2rem;margin:0;"><?= number_format($userTotal, 0, ',', ' ') ?></h3>
                        <div style="color:var(--green-eco);font-size:0.83rem;margin-top:0.6rem;display:flex;align-items:center;gap:0.4rem;">
                            <i class="fas fa-user-check"></i> <?= (int) $userActive ?> compte(s) actif(s)
                        </div>
                    </div>
                    <div class="donut-ring">
                        <svg width="90" height="90" viewBox="0 0 90 90">
                            <circle cx="45" cy="45" r="36" fill="none" stroke="rgba(16,185,129,0.12)" stroke-width="9"/>
                            <circle cx="45" cy="45" r="36" fill="none" stroke="#10b981" stroke-width="9"
                                stroke-dasharray="<?= $userArc ?> 226" stroke-linecap="round"/>
                        </svg>
                        <div class="donut-label">
                            <div class="donut-num" style="color:#10b981;"><?= $userPct ?>%</div>
                            <div class="donut-sub">actifs</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cours (données réelles) -->
            <div class="glass-card" style="padding:1.8rem;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <p style="font-size:0.9rem;text-transform:uppercase;letter-spacing:1px;color:var(--light-gray);margin:0 0 0.4rem;">Cours</p>
                        <h3 style="font-size:2rem;margin:0;"><?= (int) $courseTotal ?></h3>
                        <div style="color:var(--accent);font-size:0.83rem;margin-top:0.6rem;display:flex;align-items:center;gap:0.4rem;">
                            <i class="fas fa-book"></i> <?= (int) $coursePublished ?> publié(s)
                        </div>
                    </div>
                    <div class="donut-ring">
                        <svg width="90" height="90" viewBox="0 0 90 90">
                            <circle cx="45" cy="45" r="36" fill="none" stroke="rgba(234,179,8,0.12)" stroke-width="9"/>
                            <circle cx="45" cy="45" r="36" fill="none" stroke="#eab308" stroke-width="9"
                                stroke-dasharray="<?= $courseArc ?> 226" stroke-linecap="round"/>
                        </svg>
                        <div class="donut-label">
                            <div class="donut-num"><?= $coursePct ?>%</div>
                            <div class="donut-sub">publiés</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activité forum -->
            <div class="glass-card" style="padding:1.8rem;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <p style="font-size:0.9rem;text-transform:uppercase;letter-spacing:1px;color:var(--light-gray);margin:0 0 0.4rem;">Forum</p>
                        <h3 style="font-size:2rem;margin:0;"><?= number_format($postTotal, 0, ',', ' ') ?></h3>
                        <div style="color:#a78bfa;font-size:0.83rem;margin-top:0.6rem;display:flex;align-items:center;gap:0.4rem;">
                            <i class="fas fa-comments"></i> messages postés
                        </div>
                    </div>
                    <div class="donut-ring">
                        <svg width="90" height="90" viewBox="0 0 90 90">
                            <circle cx="45" cy="45" r="36" fill="none" stroke="rgba(167,139,250,0.12)" stroke-width="9"/>
                            <?php $forumArc = $postTotal > 0 ? min(226, (int) round(40 + min(186, sqrt((float) $postTotal) * 18))) : 0; ?>
                            <circle cx="45" cy="45" r="36" fill="none" stroke="#a78bfa" stroke-width="9"
                                stroke-dasharray="<?= $forumArc ?> 226" stroke-linecap="round"/>
                        </svg>
                        <div class="donut-label">
                            <div class="donut-num" style="color:#a78bfa;font-size:1.1rem;"><?= $postTotal > 99 ? '99+' : (string)(int)$postTotal ?></div>
                            <div class="donut-sub">posts</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Classes Virtuelles donut — clickable -->
            <?php
            $vcTotal = count($virtualClasses);
            $sesTotal = $sessionStats['total'];
            $sesPct   = $sesTotal > 0 ? min(100, round($sessionStats['en_cours'] / $sesTotal * 100)) : 0;
            $arcLen   = round(226 * $sesPct / 100);
            ?>
            <div class="glass-card" style="padding:1.8rem;cursor:pointer;transition:border-color 0.2s;" onclick="switchPanel('classes-virtuelles')" title="Gérer les classes virtuelles">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <p style="font-size:0.9rem;text-transform:uppercase;letter-spacing:1px;color:var(--light-gray);margin:0 0 0.4rem;">Classes Virtuelles</p>
                        <h3 style="font-size:2rem;margin:0;"><?= $vcTotal ?></h3>
                        <div style="color:#3b82f6;font-size:0.83rem;margin-top:0.6rem;display:flex;align-items:center;gap:0.4rem;">
                            <i class="fas fa-calendar-alt"></i> <?= $sesTotal ?> séances
                        </div>
                    </div>
                    <div class="donut-ring">
                        <svg width="90" height="90" viewBox="0 0 90 90">
                            <circle cx="45" cy="45" r="36" fill="none" stroke="rgba(59,130,246,0.12)" stroke-width="9"/>
                            <circle cx="45" cy="45" r="36" fill="none" stroke="#3b82f6" stroke-width="9"
                                stroke-dasharray="<?= $arcLen ?> 226" stroke-linecap="round"/>
                        </svg>
                        <div class="donut-label">
                            <div class="donut-num" style="color:#3b82f6;"><?= $sesPct ?>%</div>
                            <div class="donut-sub">en cours</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Utilisateurs récents -->
        <div class="glass-card" style="padding:0;overflow:hidden;">
            <div style="display:flex;justify-content:space-between;align-items:center;padding:1.8rem 2rem 1.3rem;">
                <h3 style="margin:0;"><i class="fas fa-users" style="color:var(--accent);margin-right:0.8rem;"></i> Utilisateurs récents</h3>
                <a href="<?= htmlspecialchars($basePath) ?>/admin/dashboard" class="btn-outline" style="padding:0.5rem 1.5rem;font-size:0.9rem;text-decoration:none;">Gestion utilisateurs</a>
            </div>
            <table class="dashboard-table">
                <thead><tr>
                    <th>Membre</th><th>Rôle</th><th>Dernière connexion</th><th>Statut</th>
                </tr></thead>
                <tbody>
                <?php if (empty($recentUsers)): ?>
                    <tr><td colspan="4" style="text-align:center;color:var(--light-gray);padding:2rem;">Aucun utilisateur.</td></tr>
                <?php else: foreach ($recentUsers as $ru):
                    $fullName = trim(($ru['prenom'] ?? '') . ' ' . ($ru['nom'] ?? ''));
                    $avName = rawurlencode($fullName !== '' ? $fullName : ($ru['email'] ?? 'User'));
                    $st = strtolower((string)($ru['statut'] ?? ''));
                    $isActif = ($st === 'actif');
                    $last = $ru['last_login'] ?? null;
                    $lastStr = $last ? date('d/m/Y H:i', strtotime((string) $last)) : '—';
                ?>
                    <tr>
                        <td>
                            <img src="https://ui-avatars.com/api/?name=<?= $avName ?>&background=333&color=fff" alt="" style="width:34px;height:34px;border-radius:50%;vertical-align:middle;margin-right:0.7rem;">
                            <strong><?= htmlspecialchars($fullName !== '' ? $fullName : '—') ?></strong>
                            <div style="font-size:0.78rem;color:var(--light-gray);"><?= htmlspecialchars($ru['email'] ?? '') ?></div>
                        </td>
                        <td><?= htmlspecialchars($ru['roleNom'] ?? '—') ?></td>
                        <td><i class="far fa-clock" style="color:var(--light-gray);margin-right:0.4rem;"></i><?= htmlspecialchars($lastStr) ?></td>
                        <td>
                            <?php if ($isActif): ?>
                                <span class="status-badge status-success">Actif</span>
                            <?php else: ?>
                                <span class="status-badge status-warning"><?= htmlspecialchars($ru['statut'] ?? '—') ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>

    </section>

    <!-- ══════════════ PANEL: Classes Virtuelles ════════════ -->
    <section id="panel-classes-virtuelles" class="dash-section">

        <?php if ($vcSuccess): ?>
            <div class="inline-alert alert-ok"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($vcSuccess) ?></div>
        <?php endif; ?>
        <?php if ($vcError): ?>
            <div class="inline-alert alert-ko"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($vcError) ?></div>
        <?php endif; ?>

        <!-- Toolbar: circular stats row -->
        <?php
        /* Platform stats computation */
        $platforms = [];
        foreach ($virtualClasses as $v) {
            $p = $v['plateforme'] ?? '?';
            $platforms[$p] = ($platforms[$p] ?? 0) + 1;
        }
        arsort($platforms);
        $topPlat      = array_key_first($platforms);
        $vcTotal2     = count($virtualClasses);
        $topPlatCount = $topPlat ? ($platforms[$topPlat] ?? 0) : 0;
        $topPlatPct   = $vcTotal2 > 0 ? round($topPlatCount / $vcTotal2 * 100) : 0;
        $topPlatArc   = round(226 * $topPlatPct / 100);
        $platCount    = count($platforms);
        /* arc for "platforms diversity": filled 90° per platform, max 4 */
        $platArc      = round(226 * min(100, $platCount * 25) / 100);
        ?>
        <div id="vc-chips" style="display:flex;gap:2rem;flex-wrap:wrap;align-items:center;margin-bottom:1.4rem;padding:1.2rem 1.4rem;background:rgba(255,255,255,0.025);border:1px solid var(--glass-border);border-radius:16px;">

            <!-- Total circle -->
            <div style="display:flex;align-items:center;gap:1rem;">
                <div class="donut-ring">
                    <svg width="82" height="82" viewBox="0 0 90 90">
                        <circle cx="45" cy="45" r="36" fill="none" stroke="rgba(234,179,8,0.12)" stroke-width="9"/>
                        <circle cx="45" cy="45" r="36" fill="none" stroke="#eab308" stroke-width="9"
                            stroke-dasharray="226 226" stroke-linecap="round"/>
                    </svg>
                    <div class="donut-label">
                        <div class="donut-num" id="chip-total" style="font-size:1.3rem;"><?= $vcTotal2 ?></div>
                    </div>
                </div>
                <div>
                    <div style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.8px;color:var(--light-gray);margin-bottom:0.2rem;">Total</div>
                    <div style="font-size:0.95rem;font-weight:700;">Classes virtuelles</div>
                </div>
            </div>

            <div style="width:1px;height:60px;background:var(--glass-border);"></div>

            <?php if ($topPlat): ?>
            <!-- Top platform circle -->
            <div style="display:flex;align-items:center;gap:1rem;">
                <div class="donut-ring">
                    <svg width="82" height="82" viewBox="0 0 90 90">
                        <circle cx="45" cy="45" r="36" fill="none" stroke="rgba(167,139,250,0.12)" stroke-width="9"/>
                        <circle cx="45" cy="45" r="36" fill="none" stroke="#a78bfa" stroke-width="9"
                            stroke-dasharray="<?= $topPlatArc ?> 226" stroke-linecap="round"/>
                    </svg>
                    <div class="donut-label">
                        <div class="donut-num" style="color:#a78bfa;font-size:1.15rem;"><?= $topPlatPct ?>%</div>
                    </div>
                </div>
                <div>
                    <div style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.8px;color:var(--light-gray);margin-bottom:0.2rem;">Top plateforme</div>
                    <div style="font-size:0.95rem;font-weight:700;color:#a78bfa;"><?= htmlspecialchars($topPlat) ?></div>
                    <div style="font-size:0.78rem;color:var(--light-gray);"><?= $topPlatCount ?> cours sur <?= $vcTotal2 ?></div>
                </div>
            </div>

            <div style="width:1px;height:60px;background:var(--glass-border);"></div>

            <!-- Platforms diversity circle -->
            <div style="display:flex;align-items:center;gap:1rem;">
                <div class="donut-ring">
                    <svg width="82" height="82" viewBox="0 0 90 90">
                        <circle cx="45" cy="45" r="36" fill="none" stroke="rgba(59,130,246,0.12)" stroke-width="9"/>
                        <circle cx="45" cy="45" r="36" fill="none" stroke="#3b82f6" stroke-width="9"
                            stroke-dasharray="<?= $platArc ?> 226" stroke-linecap="round"/>
                    </svg>
                    <div class="donut-label">
                        <div class="donut-num" style="color:#3b82f6;font-size:1.3rem;"><?= $platCount ?></div>
                    </div>
                </div>
                <div>
                    <div style="font-size:0.72rem;text-transform:uppercase;letter-spacing:0.8px;color:var(--light-gray);margin-bottom:0.2rem;">Séances</div>
                    <div style="font-size:0.95rem;font-weight:700;">utilisées</div>
                    <?php foreach ($platforms as $pName => $pCnt): ?>
                        <div style="font-size:0.75rem;color:var(--light-gray);"><?= htmlspecialchars($pName) ?>: <?= $pCnt ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- Toolbar: search + buttons (always visible, separate row) -->
        <div class="vc-toolbar">
            <div class="search-bar">
                <i class="fas fa-search si"></i>
                <input type="text" id="vc-search" placeholder="Rechercher…">
            </div>
            <button class="btn-outline" id="btn-export-pdf" style="padding:0.6rem 1.1rem; font-size:0.9rem;" onclick="exportPDF()">
                <i class="fas fa-file-pdf"></i> Export PDF
            </button>
            <button class="btn-primary" id="btn-show-add" style="padding:0.6rem 1.1rem; font-size:0.9rem; white-space:nowrap;" onclick="showVcForm('add')">
                <i class="fas fa-plus"></i> Ajouter
            </button>
        </div>

        <!-- Inline Add / Edit Form -->
        <div id="vc-form-container" style="display:none;">
            <div class="vc-form-panel">
                <h3 id="vc-form-title" style="margin:0 0 1.2rem; font-size:1.1rem;"><i class="fas fa-plus-circle" style="color:var(--accent);margin-right:0.5rem;"></i> Ajouter une Classe Virtuelle</h3>
                <form id="vcInlineForm" method="POST" action="<?= htmlspecialchars($basePath) ?>/virtualclass/dashboard#panel-classes-virtuelles" novalidate>
                    <input type="hidden" name="action" id="vc-action-field" value="add_virtualclass">
                    <input type="hidden" name="idClass" id="vc-edit-id" value="">
                    <div class="form-grid">
                        <div class="full">
                            <label>Titre *</label>
                            <input type="text" name="titre" id="fi-titre" placeholder="Titre de la classe virtuelle" autocomplete="off">
                            <span class="ferr" id="err-titre"></span>
                        </div>
                        <div class="full">
                            <label>Description <small style="color:var(--light-gray);">(optionnel, max 500 car.)</small></label>
                            <textarea name="description" id="fi-description" placeholder="Décrivez le cours…"></textarea>
                            <span class="ferr" id="err-desc"></span>
                        </div>
                        <div class="full">
                            <label>Lien d'accès * <small style="color:var(--light-gray);">(https://…)</small></label>
                            <input type="text" name="lienAcces" id="fi-lien" placeholder="https://zoom.us/j/...">
                            <span class="ferr" id="err-lien"></span>
                        </div>
                        <div>
                            <label>Plateforme *</label>
                            <select name="plateforme" id="fi-plateforme">
                                <option value="">-- Choisir --</option>
                                <option value="Zoom">Zoom</option>
                                <option value="Meet">Google Meet</option>
                                <option value="Teams">Microsoft Teams</option>
                                <option value="Autre">Autre</option>
                            </select>
                            <span class="ferr" id="err-plat"></span>
                        </div>
                        <div>
                            <label>Capacité *</label>
                            <input type="number" name="capacite" id="fi-capacite" min="1" max="5000" value="30" placeholder="Nombre de places">
                            <span class="ferr" id="err-capacite"></span>
                        </div>
                        <div>
                            <label>Cours associé *</label>
                            <select name="idCourse" id="fi-idcourse">
                                <option value="">-- Choisir un cours --</option>
                                <?php foreach ($courses as $c): ?>
                                    <option value="<?= (int)$c['idCourse'] ?>">#<?= (int)$c['idCourse'] ?> — <?= htmlspecialchars($c['titre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="ferr" id="err-course"></span>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-primary" id="vc-submit-btn" style="padding:0.7rem 1.5rem;"><i class="fas fa-save"></i> Enregistrer</button>
                        <button type="button" class="btn-outline" onclick="hideVcForm()" style="padding:0.7rem 1.2rem;"><i class="fas fa-times"></i> Annuler</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- PDF print title -->
        <h2 class="pdf-title">Classes Virtuelles — Export <?= date('d/m/Y') ?></h2>

        <!-- Data table -->
        <div class="glass-card" style="padding:0;overflow:visible;margin-top:1rem;">
            <table class="dashboard-table" id="vc-table">
                <thead><tr>
                    <th data-sort="0">#<span class="sort-icon"></span></th>
                    <th data-sort="1">Titre<span class="sort-icon"></span></th>
                    <th data-sort="2">Capacité<span class="sort-icon"></span></th>
                    <th data-sort="3">Plateforme<span class="sort-icon"></span></th>
                    <th>Lien</th>
                    <th data-sort="5">Cours<span class="sort-icon"></span></th>
                    <th style="text-align:right;">Actions</th>
                </tr></thead>
                <tbody id="vc-tbody">
                <?php if (empty($virtualClasses)): ?>
                    <tr id="vc-empty"><td colspan="7" style="text-align:center;color:var(--light-gray);padding:2rem;">Aucune classe virtuelle trouvée.</td></tr>
                <?php else: ?>
                    <?php foreach ($virtualClasses as $vc): ?>
                    <tr
                        data-id="<?= (int)$vc['idClass'] ?>"
                        data-titre="<?= htmlspecialchars($vc['titre'],ENT_QUOTES) ?>"
                        data-desc="<?= htmlspecialchars($vc['description'] ?? '',ENT_QUOTES) ?>"
                        data-lien="<?= htmlspecialchars($vc['lienAcces'],ENT_QUOTES) ?>"
                        data-plat="<?= htmlspecialchars($vc['plateforme'],ENT_QUOTES) ?>"
                        data-capacite="<?= (int)($vc['capacite'] ?? 30) ?>"
                        data-course="<?= (int)($vc['idCourse'] ?? 0) ?>">
                        <td>#<?= (int)$vc['idClass'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($vc['titre']) ?></strong>
                            <?php if (!empty($vc['description'])): ?>
                                <div style="font-size:0.8rem;color:var(--light-gray);"><?= htmlspecialchars(mb_substr($vc['description'],0,70)) ?><?= mb_strlen($vc['description'])>70?'…':'' ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span style="background:rgba(16,185,129,0.12);color:#10b981;padding:0.25rem 0.7rem;border-radius:20px;font-size:0.8rem;font-weight:600;"><?= (int)($vc['capacite'] ?? 30) ?> places</span>
                        </td>
                        <td><?= htmlspecialchars($vc['plateforme']) ?></td>
                        <td><a href="<?= htmlspecialchars($vc['lienAcces']) ?>" target="_blank" rel="noopener" style="color:var(--accent);">ouvrir <i class="fas fa-external-link-alt" style="font-size:0.65rem;"></i></a></td>
                        <td><span class="badge-vc"><?= htmlspecialchars($vc['courseTitre'] ?? ('#'.(int)$vc['idCourse'])) ?></span></td>
                        <td>
                            <div style="display:flex;gap:0.5rem;justify-content:flex-end;">
                                <button class="action-btn" title="Modifier" onclick="fillEditForm(this)" data-row="true"><i class="fas fa-edit"></i></button>
                                <button class="action-btn del" title="Supprimer" onclick="askDelete(<?= (int)$vc['idClass'] ?>)"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div id="vc-search-info" style="font-size:0.82rem;color:var(--light-gray);margin-top:0.6rem;"></div>
        <!-- Séances sub-section -->
        <div style="margin-top:2.5rem;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                <h3 style="margin:0;font-size:1.1rem;"><i class="fas fa-calendar-alt" style="color:var(--accent);margin-right:0.6rem;"></i>Séances associées
                    <span style="font-size:0.82rem;font-weight:400;color:var(--light-gray);margin-left:0.5rem;">(<?= $sessionStats['total'] ?> au total)</span>
                </h3>
                <div class="sessions-panel-header-actions" style="display:flex;gap:0.8rem;align-items:center;">
                    <!-- Quick stats badges -->
                    <?php if ($sessionStats['planifiee'] > 0): ?>
                    <span class="sbadge sbadge-planifiee"><i class="fas fa-clock"></i> <?= $sessionStats['planifiee'] ?> planif.</span>
                    <?php endif; ?>
                    <?php if ($sessionStats['en_cours'] > 0): ?>
                    <span class="sbadge sbadge-en_cours"><i class="fas fa-play"></i> <?= $sessionStats['en_cours'] ?> en cours</span>
                    <?php endif; ?>
                    <?php if ($sessionStats['terminee'] > 0): ?>
                    <span class="sbadge sbadge-terminee"><i class="fas fa-check"></i> <?= $sessionStats['terminee'] ?> terminée(s)</span>
                    <?php endif; ?>
                    <a href="<?= htmlspecialchars($basePath) ?>/virtualclass/sessions/add" class="btn-primary" style="padding:0.5rem 1rem;font-size:0.85rem;text-decoration:none;"><i class="fas fa-plus"></i> Ajouter séance</a>
                </div>
            </div>

            <!-- Séance donut summary -->
            <?php if ($sessionStats['total'] > 0): ?>
            <div style="display:flex;gap:1.2rem;flex-wrap:wrap;margin-bottom:1.2rem;align-items:center;">
                <?php
                $sColors = ['planifiee'=>['#60a5fa','rgba(59,130,246,0.12)'],'en_cours'=>['#eab308','rgba(234,179,8,0.12)'],'terminee'=>['#10b981','rgba(16,185,129,0.12)'],'annulee'=>['#ef4444','rgba(239,68,68,0.12)']];
                $sLabels = ['planifiee'=>'Planif.','en_cours'=>'En cours','terminee'=>'Terminée','annulee'=>'Annulée'];
                foreach ($sColors as $sk => $sc):
                    $cnt = $sessionStats[$sk];
                    if ($cnt === 0) continue;
                    $pct = round($cnt / $sessionStats['total'] * 100);
                    $arc = round(226 * $pct / 100);
                ?>
                <div style="display:flex;align-items:center;gap:0.6rem;">
                    <div class="donut-ring">
                        <svg width="68" height="68" viewBox="0 0 90 90">
                            <circle cx="45" cy="45" r="36" fill="none" stroke="<?= $sc[1] ?>" stroke-width="9"/>
                            <circle cx="45" cy="45" r="36" fill="none" stroke="<?= $sc[0] ?>" stroke-width="9"
                                stroke-dasharray="<?= $arc ?> 226" stroke-linecap="round"/>
                        </svg>
                        <div class="donut-label">
                            <div class="donut-num" style="font-size:1rem;color:<?= $sc[0] ?>;"><?= $cnt ?></div>
                        </div>
                    </div>
                    <span style="font-size:0.82rem;color:var(--light-gray);"><?= $sLabels[$sk] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="glass-card" style="padding:0;overflow:hidden;">
                <table class="dashboard-table" id="sessions-table">
                    <thead><tr>
                        <th>#</th>
                        <th>Classe Virtuelle</th>
                        <th>Date</th>
                        <th>Début</th>
                        <th>Fin</th>
                        <th>Statut</th>
                        <th class="sessions-panel-header-actions" style="text-align:right;">Actions</th>
                    </tr></thead>
                    <tbody>
                    <?php if (empty($allSessions)): ?>
                        <tr><td colspan="7" style="text-align:center;color:var(--light-gray);padding:2rem;">Aucune séance trouvée.</td></tr>
                    <?php else: ?>
                        <?php foreach ($allSessions as $sess):
                            $sk = str_replace(' ','_',strtolower($sess['statut']));
                            $allowedSk = ['planifiee','en_cours','terminee','annulee'];
                            if (!in_array($sk,$allowedSk,true)) $sk='planifiee';
                        ?>
                        <tr>
                            <td>#<?= (int)$sess['idSession'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($sess['classTitre'] ?? '—') ?></strong>
                                <div style="font-size:0.78rem;color:var(--light-gray);margin-top:0.2rem;">
                                    <span style="background:rgba(59,130,246,0.1);color:#60a5fa;padding:0.15rem 0.6rem;border-radius:12px;font-size:0.75rem;">
                                        <i class="fas fa-laptop"></i> <?= htmlspecialchars($sess['plateforme'] ?? '—') ?>
                                    </span>
                                    <?php if (isset($sess['classCapacite'])): ?>
                                    &nbsp;<span><?= (int) $sess['classCapacite'] ?> places (classe)</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($sess['dateSession']) ?></td>
                            <td><?= htmlspecialchars(substr($sess['heureDebut'],0,5)) ?></td>
                            <td><?= htmlspecialchars(substr($sess['heureFin'],0,5)) ?></td>
                            <td><span class="sbadge sbadge-<?= $sk ?>"><?= htmlspecialchars($sess['statut']) ?></span></td>
                            <td class="sessions-panel-header-actions">
                                <div style="display:flex;gap:0.5rem;justify-content:flex-end;">
                                    <a class="action-btn" href="<?= htmlspecialchars($basePath) ?>/virtualclass/sessions/edit/<?= (int)$sess['idSession'] ?>" title="Modifier"><i class="fas fa-edit"></i></a>
                                    <form method="POST" action="<?= htmlspecialchars($basePath) ?>/virtualclass/sessions" style="display:inline;" onsubmit="return confirm('Supprimer cette session ?')">
                                        <input type="hidden" name="action" value="delete_session">
                                        <input type="hidden" name="idSession" value="<?= (int)$sess['idSession'] ?>">
                                        <button type="submit" class="action-btn del" title="Supprimer"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

</main>

<!-- ══ Delete confirm modal ════════════════════════════ -->
<div id="del-modal">
    <div class="del-box">
        <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1rem;">
            <div style="width:44px;height:44px;border-radius:12px;background:rgba(239,68,68,0.12);display:flex;align-items:center;justify-content:center;color:#ef4444;font-size:1.3rem;">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 style="margin:0;">Confirmer la suppression</h3>
        </div>
        <p style="color:#9ca3af;margin:0 0 1.5rem;line-height:1.6;">Voulez-vous vraiment supprimer cette classe virtuelle ? Cette action est irréversible.</p>
        <form method="POST" action="<?= htmlspecialchars($basePath) ?>/virtualclass/dashboard#panel-classes-virtuelles" id="del-form">
            <input type="hidden" name="action" value="delete_virtualclass">
            <input type="hidden" name="idClass" id="del-id" value="">
            <div style="display:flex;gap:0.8rem;justify-content:flex-end;">
                <button type="button" onclick="closeDelModal()" style="padding:0.6rem 1.3rem;border-radius:10px;border:1px solid rgba(255,255,255,0.1);background:transparent;color:#9ca3af;cursor:pointer;">Annuler</button>
                <button type="submit" style="padding:0.6rem 1.3rem;border-radius:10px;border:none;background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff;cursor:pointer;font-weight:600;"><i class="fas fa-trash"></i> Supprimer</button>
            </div>
        </form>
    </div>
</div>

<!-- Error message style -->
<style>
    .ferr { display:block; font-size:0.78rem; color:#ef4444; margin-top:0.3rem; min-height:1rem; }
    .fi-error { border-color:#ef4444 !important; box-shadow:0 0 0 3px rgba(239,68,68,0.15) !important; }
    .fi-ok    { border-color:#10b981 !important; }
</style>

<script>
/* ══════════════════════════════════════════════════════
   Panel switching
══════════════════════════════════════════════════════ */
function switchPanel(panelId) {
    document.querySelectorAll('.dash-section').forEach(s => s.classList.remove('active'));
    const panel = document.getElementById('panel-' + panelId);
    if (panel) panel.classList.add('active');

    document.querySelectorAll('.sidebar .nav-links a[data-panel]').forEach(a => {
        a.classList.toggle('active', a.dataset.panel === panelId);
    });

    // Update header title
    const titles = {
        'overview':           ['Tableau de Bord',    "Bienvenue dans l'espace administrateur."],
        'classes-virtuelles': ['Classes Virtuelles', 'Gérez vos classes virtuelles, séances et statistiques.'],
        'placeholder':        ['Module',             'Ce module est en cours de développement.'],
    };
    const t = titles[panelId] || titles['overview'];
    document.getElementById('page-title').textContent = t[0];
    document.getElementById('page-sub').textContent   = t[1];
}

// Wire nav links
document.querySelectorAll('.sidebar .nav-links a[data-panel]').forEach(a => {
    a.addEventListener('click', function(e) {
        e.preventDefault();
        switchPanel(this.dataset.panel);
    });
});

// Auto-open if redirected back after form POST (detect hash or param)
(function() {
    <?php if ($vcSuccess || $vcError): ?>
    switchPanel('classes-virtuelles');
    <?php else: ?>
    if (window.location.hash === '#panel-classes-virtuelles' || window.location.hash === '#classes-virtuelles') {
        switchPanel('classes-virtuelles');
    }
    <?php endif; ?>
})();

/* ══════════════════════════════════════════════════════
   Inline form: Add / Edit
══════════════════════════════════════════════════════ */
function showVcForm(mode, data) {
    const container = document.getElementById('vc-form-container');
    const title     = document.getElementById('vc-form-title');
    const actionFld = document.getElementById('vc-action-field');
    const editId    = document.getElementById('vc-edit-id');
    const submitBtn = document.getElementById('vc-submit-btn');

    clearFormErrors();

    if (mode === 'add') {
        title.innerHTML = '<i class="fas fa-plus-circle" style="color:var(--accent);margin-right:0.5rem;"></i> Ajouter une Classe Virtuelle';
        actionFld.value = 'add_virtualclass';
        editId.value    = '';
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Enregistrer';
        document.getElementById('fi-titre').value       = '';
        document.getElementById('fi-description').value = '';
        document.getElementById('fi-lien').value        = '';
        document.getElementById('fi-plateforme').value  = '';
        document.getElementById('fi-capacite').value    = '30';
        document.getElementById('fi-idcourse').value    = '';
    } else if (mode === 'edit' && data) {
        title.innerHTML = '<i class="fas fa-edit" style="color:var(--accent);margin-right:0.5rem;"></i> Modifier la Classe Virtuelle #' + data.id;
        actionFld.value = 'update_virtualclass';
        editId.value    = data.id;
        submitBtn.innerHTML = '<i class="fas fa-save"></i> Mettre à jour';
        document.getElementById('fi-titre').value       = data.titre;
        document.getElementById('fi-description').value = data.desc;
        document.getElementById('fi-lien').value        = data.lien;
        document.getElementById('fi-plateforme').value  = data.plat;
        document.getElementById('fi-capacite').value    = String(data.capacite || '30');
        document.getElementById('fi-idcourse').value    = data.course ? String(data.course) : '';
    }

    container.style.display = 'block';
    container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function hideVcForm() {
    document.getElementById('vc-form-container').style.display = 'none';
    clearFormErrors();
}

function fillEditForm(btn) {
    const row = btn.closest('tr');
    showVcForm('edit', {
        id:     row.dataset.id,
        titre:  row.dataset.titre,
        desc:   row.dataset.desc,
        lien:   row.dataset.lien,
        plat:   row.dataset.plat,
        capacite: row.dataset.capacite || '30',
        course: row.dataset.course,
    });
}

/* ══════════════════════════════════════════════════════
   PDF Export (simple print-based)
══════════════════════════════════════════════════════ */
function exportPDF() {
    switchPanel('classes-virtuelles');

    document.body.classList.add('pdf-export-mode');
    var pdfTitle = document.querySelector('h2.pdf-title');
    if (pdfTitle) pdfTitle.style.display = 'block';

    setTimeout(function () {
        window.print();
        setTimeout(function () {
            document.body.classList.remove('pdf-export-mode');
            if (pdfTitle) pdfTitle.style.display = 'none';
        }, 800);
    }, 200);
}

/* ══════════════════════════════════════════════════════
   Form validation
══════════════════════════════════════════════════════ */
function setErr(id, msg) {
    const el = document.getElementById(id);
    el.textContent = msg;
}
function clearFormErrors() {
    ['err-titre','err-desc','err-lien','err-plat','err-course','err-capacite'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.textContent = '';
    });
    ['fi-titre','fi-description','fi-lien','fi-plateforme','fi-idcourse','fi-capacite'].forEach(id => {
        const el = document.getElementById(id);
        if (el) { el.classList.remove('fi-error','fi-ok'); }
    });
}
function markField(id, ok, errId, msg) {
    const f = document.getElementById(id);
    if (!f) return true;
    if (ok) { f.classList.add('fi-ok'); f.classList.remove('fi-error'); setErr(errId,''); return true; }
    else     { f.classList.add('fi-error'); f.classList.remove('fi-ok'); setErr(errId, msg); return false; }
}

document.getElementById('vcInlineForm').addEventListener('submit', function(e) {
    let valid = true;
    const titre = document.getElementById('fi-titre').value.trim();
    const lien  = document.getElementById('fi-lien').value.trim();
    const plat  = document.getElementById('fi-plateforme').value;
    const cours = document.getElementById('fi-idcourse').value;
    const desc  = document.getElementById('fi-description').value.trim();
    const capEl = document.getElementById('fi-capacite');
    const cap   = capEl ? parseInt(capEl.value, 10) : 0;

    if (!titre || titre.length < 3)      valid = markField('fi-titre','',       'err-titre',  'Titre obligatoire (min 3 car.)') && valid; else markField('fi-titre', true, 'err-titre','');
    if (desc.length > 500)               valid = markField('fi-description','', 'err-desc',   'Max 500 caractères.') && valid; else markField('fi-description', true, 'err-desc','');
    if (!lien || !/^https?:\/\/.+/.test(lien)) valid = markField('fi-lien','','err-lien','Lien obligatoire (http:// ou https://)') && valid; else markField('fi-lien', true,'err-lien','');
    if (!plat)                           valid = markField('fi-plateforme','',  'err-plat',   'Plateforme obligatoire.') && valid; else markField('fi-plateforme', true, 'err-plat','');
    if (!capEl || !cap || cap < 1)       valid = markField('fi-capacite','',    'err-capacite', 'Capacité obligatoire (≥ 1).') && valid; else markField('fi-capacite', true, 'err-capacite','');
    if (!cours || parseInt(cours) <= 0)  valid = markField('fi-idcourse','',    'err-course', 'Cours associé obligatoire.') && valid; else markField('fi-idcourse', true, 'err-course','');

    if (!valid) { e.preventDefault(); }
});

// Live validation on blur
['fi-titre','fi-lien','fi-description','fi-plateforme','fi-idcourse','fi-capacite'].forEach(id => {
    const el = document.getElementById(id);
    if (!el) return;
    el.addEventListener('blur', function() {
        const v = el.value.trim();
        if (id === 'fi-titre')       markField(id, v.length>=3, 'err-titre',  'Titre obligatoire (min 3 car.)');
        if (id === 'fi-lien')        markField(id, /^https?:\/\/.+/.test(v), 'err-lien', 'Lien obligatoire (http:// ou https://)');
        if (id === 'fi-description') markField(id, v.length<=500, 'err-desc', 'Max 500 caractères.');
        if (id === 'fi-plateforme')  markField(id, !!el.value, 'err-plat', 'Plateforme obligatoire.');
        if (id === 'fi-idcourse')    markField(id, parseInt(el.value)>0, 'err-course', 'Cours associé obligatoire.');
        if (id === 'fi-capacite')    markField(id, parseInt(el.value,10)>=1, 'err-capacite', 'Capacité obligatoire (≥ 1).');
    });
});

/* ══════════════════════════════════════════════════════
   Delete modal
══════════════════════════════════════════════════════ */
function askDelete(id) {
    document.getElementById('del-id').value = id;
    document.getElementById('del-modal').classList.add('show');
}
function closeDelModal() {
    document.getElementById('del-modal').classList.remove('show');
}
document.getElementById('del-modal').addEventListener('click', function(e) {
    if (e.target === this) closeDelModal();
});

/* ══════════════════════════════════════════════════════
   Search
══════════════════════════════════════════════════════ */
document.getElementById('vc-search').addEventListener('input', function() {
    const q     = this.value.trim().toLowerCase();
    const rows  = document.querySelectorAll('#vc-tbody tr[data-id]');
    let visible = 0;

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const match = !q || text.includes(q);
        row.style.display = match ? '' : 'none';
        if (match) visible++;

        // Highlight
        row.querySelectorAll('td').forEach(td => {
            if (!td.dataset.orig) td.dataset.orig = td.innerHTML;
            if (q) {
                const re = new RegExp('(' + q.replace(/[.*+?^${}()|[\]\\]/g,'\\$&') + ')', 'gi');
                td.innerHTML = (td.dataset.orig || td.textContent).replace(re, '<span class="hl">$1</span>');
            } else {
                if (td.dataset.orig) td.innerHTML = td.dataset.orig;
            }
        });
    });

    document.getElementById('chip-total').textContent = visible;
    const info = document.getElementById('vc-search-info');
    info.textContent = q ? visible + ' résultat(s) pour "' + this.value.trim() + '"' : '';
});

/* ══════════════════════════════════════════════════════
   Sort
══════════════════════════════════════════════════════ */
document.querySelectorAll('#vc-table thead th[data-sort]').forEach(th => {
    th.addEventListener('click', function() {
        const col = parseInt(this.dataset.sort);
        const asc = !this.classList.contains('asc');
        document.querySelectorAll('#vc-table thead th').forEach(h => h.classList.remove('asc','desc'));
        this.classList.add(asc ? 'asc' : 'desc');

        const tbody = document.getElementById('vc-tbody');
        const rows  = Array.from(tbody.querySelectorAll('tr[data-id]'));
        rows.sort((a, b) => {
            const ta = (a.cells[col]?.textContent || '').trim();
            const tb = (b.cells[col]?.textContent || '').trim();
            const na = parseFloat(ta), nb = parseFloat(tb);
            if (!isNaN(na) && !isNaN(nb)) return asc ? na - nb : nb - na;
            return asc ? ta.localeCompare(tb,'fr') : tb.localeCompare(ta,'fr');
        });
        rows.forEach(r => tbody.appendChild(r));
    });
});
</script>
</body>
</html>

