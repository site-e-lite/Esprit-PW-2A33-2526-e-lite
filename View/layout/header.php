<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Resolve base path relative to project root
$_projectRoot = realpath(__DIR__ . '/../..');
$_docRoot     = realpath($_SERVER['DOCUMENT_ROOT']);
$_rel         = str_replace('\\', '/', substr($_projectRoot, strlen($_docRoot)));
$basePath     = rtrim($_rel, '/');
if ($basePath === '.' || $basePath === '') $basePath = '';

// Current user info from session
$isLoggedIn  = isset($_SESSION['user_id']);
$userId      = $isLoggedIn ? (int)$_SESSION['user_id'] : 0;
$roleId      = (int)($_SESSION['user_role'] ?? 0);
$roleName    = strtolower(trim((string)($_SESSION['role_nom'] ?? '')));
$userNom     = htmlspecialchars((string)($_SESSION['user_nom']    ?? ''));
$userPrenom  = htmlspecialchars((string)($_SESSION['user_prenom'] ?? ''));

$isAdmin     = ($roleId === 1 || $roleName === 'admin');
$isTeacher   = ($roleName === 'enseignant');
$isStudent   = ($roleName === 'etudiant');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'e-lite | Éco-Digital Learning') ?></title>
    <link rel="stylesheet" href="<?= $basePath ?>/View/assets/index.css">
    <link rel="stylesheet" href="<?= $basePath ?>/View/assets/User/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<?php if ($isLoggedIn): ?>
<!-- ── SIDEBAR (logged-in users) ─────────────────────────── -->
<aside id="sidebar" class="sidebar">
    <div class="sidebar-header">
        <a href="<?= $basePath ?>/" class="sidebar-logo">e-<span>lite</span></a>
        <button id="sidebarClose" class="sidebar-close"><i class="fas fa-times"></i></button>
    </div>

    <div class="sidebar-user">
        <div class="sidebar-avatar">
            <i class="fas fa-user-circle"></i>
        </div>
        <div>
            <strong><?= $userPrenom . ' ' . $userNom ?></strong>
            <small><?= ucfirst($roleName) ?></small>
        </div>
    </div>

    <nav class="sidebar-nav">
        <ul>
            <li><a href="<?= $basePath ?>/View/FrontOffice/dashboard.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a></li>

            <?php if ($isStudent): ?>
                <li><a href="<?= $basePath ?>/View/FrontOffice/course/index.php">
                    <i class="fas fa-book-open"></i> Mes Cours
                </a></li>
                <li><a href="<?= $basePath ?>/View/FrontOffice/certificate/index.php">
                    <i class="fas fa-certificate"></i> Mes Certificats
                </a></li>
            <?php endif; ?>

            <?php if ($isTeacher): ?>
                <li><a href="<?= $basePath ?>/View/BackOffice/course/list.php">
                    <i class="fas fa-chalkboard-teacher"></i> Mes Cours
                </a></li>
                <li><a href="<?= $basePath ?>/View/BackOffice/course/add.php">
                    <i class="fas fa-plus-circle"></i> Ajouter un Cours
                </a></li>
            <?php endif; ?>

            <li><a href="<?= $basePath ?>/forum">
                <i class="fas fa-comments"></i> Forum
            </a></li>

            <?php if ($isAdmin || $isTeacher): ?>
                <li><a href="<?= $basePath ?>/forum/manage">
                    <i class="fas fa-cogs"></i> Gérer le Forum
                </a></li>
            <?php endif; ?>

            <?php if ($isAdmin): ?>
                <li class="sidebar-separator">Administration</li>
                <li><a href="<?= $basePath ?>/admin/dashboard">
                    <i class="fas fa-users-cog"></i> Utilisateurs
                </a></li>
                <li><a href="<?= $basePath ?>/View/BackOffice/course/list.php">
                    <i class="fas fa-graduation-cap"></i> Tous les Cours
                </a></li>
                <li><a href="<?= $basePath ?>/View/BackOffice/enrollment/list.php">
                    <i class="fas fa-user-graduate"></i> Inscriptions
                </a></li>
                <li><a href="<?= $basePath ?>/View/BackOffice/certificate/list.php">
                    <i class="fas fa-award"></i> Certificats
                </a></li>
            <?php endif; ?>

            <li class="sidebar-separator"></li>
            <li><a href="<?= $basePath ?>/profile">
                <i class="fas fa-user-edit"></i> Mon Profil
            </a></li>
            <li><a href="<?= $basePath ?>/logout" class="sidebar-logout">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a></li>
        </ul>
    </nav>
</aside>
<div id="sidebarOverlay" class="sidebar-overlay"></div>
<?php endif; ?>

<!-- ── TOP NAVIGATION ────────────────────────────────────── -->
<header id="main-header">
    <nav>
        <div style="display:flex; align-items:center; gap:1rem;">
            <?php if ($isLoggedIn): ?>
                <button id="sidebarToggle" class="hamburger-btn" aria-label="Menu">
                    <i class="fas fa-bars"></i>
                </button>
            <?php endif; ?>
            <a href="<?= $basePath ?>/View/FrontOffice/course/index.php" class="logo">
                e-lite<span>.</span>
            </a>
        </div>

        <ul class="nav-links">
            <li><a href="<?= $basePath ?>/View/FrontOffice/course/index.php">
                <i class="fas fa-home"></i> Accueil
            </a></li>

            <?php if ($isLoggedIn): ?>
                <li><a href="<?= $basePath ?>/View/FrontOffice/dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a></li>

                <?php if ($isStudent): ?>
                    <li><a href="<?= $basePath ?>/View/FrontOffice/course/index.php">
                        <i class="fas fa-book"></i> Mes Cours
                    </a></li>
                    <li><a href="<?= $basePath ?>/View/FrontOffice/certificate/index.php">
                        <i class="fas fa-certificate"></i> Certificats
                    </a></li>
                <?php elseif ($isTeacher): ?>
                    <li><a href="<?= $basePath ?>/View/BackOffice/course/list.php">
                        <i class="fas fa-chalkboard-teacher"></i> Mes Cours
                    </a></li>
                <?php elseif ($isAdmin): ?>
                    <li><a href="<?= $basePath ?>/View/BackOffice/course/list.php">
                        <i class="fas fa-graduation-cap"></i> Cours
                    </a></li>
                    <li><a href="<?= $basePath ?>/admin/dashboard">
                        <i class="fas fa-users-cog"></i> Admin
                    </a></li>
                <?php endif; ?>

                <li><a href="<?= $basePath ?>/forum">
                    <i class="fas fa-comments"></i> Forum
                </a></li>
            <?php endif; ?>
        </ul>

        <div class="auth-buttons">
            <?php if ($isLoggedIn): ?>
                <span style="color:var(--light-gray); font-size:.9rem; margin-right:.5rem;">
                    <?= $userPrenom ?>
                </span>
                <a href="<?= $basePath ?>/profile" class="btn-outline" style="padding:.6rem 1rem;">
                    <i class="fas fa-user"></i>
                </a>
                <a href="<?= $basePath ?>/logout" class="btn-outline">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            <?php else: ?>
                <a href="<?= $basePath ?>/login"    class="btn-outline"><i class="fas fa-key"></i> Connexion</a>
                <a href="<?= $basePath ?>/register"  class="btn-primary"><i class="fas fa-user-plus"></i> Inscription</a>
            <?php endif; ?>
        </div>
    </nav>
</header>

<main class="page-shell">

<script>
// Sidebar toggle
(function() {
    var toggle  = document.getElementById('sidebarToggle');
    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('sidebarOverlay');
    var close   = document.getElementById('sidebarClose');

    function openSidebar()  { if(sidebar) sidebar.classList.add('open');    if(overlay) overlay.classList.add('active'); }
    function closeSidebar() { if(sidebar) sidebar.classList.remove('open'); if(overlay) overlay.classList.remove('active'); }

    if (toggle)  toggle.addEventListener('click', openSidebar);
    if (close)   close.addEventListener('click',  closeSidebar);
    if (overlay) overlay.addEventListener('click', closeSidebar);
})();
</script>

<style>
/* ── Sidebar ─────────────────────────────────────────────── */
.sidebar {
    position: fixed; top: 0; left: -300px; width: 280px; height: 100vh;
    background: rgba(10,10,10,.97); border-right: 1px solid rgba(255,255,255,.08);
    z-index: 2000; transition: left .3s ease; overflow-y: auto; padding: 1.5rem;
}
.sidebar.open { left: 0; }
.sidebar-overlay {
    display: none; position: fixed; inset: 0; background: rgba(0,0,0,.6);
    z-index: 1999;
}
.sidebar-overlay.active { display: block; }
.sidebar-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.sidebar-logo { font-size: 1.6rem; font-weight: 800; color: #f4f4f5; text-decoration: none; }
.sidebar-logo span { color: #eab308; }
.sidebar-close { background: none; border: none; color: #aaa; font-size: 1.2rem; cursor: pointer; }
.sidebar-user { display: flex; align-items: center; gap: .8rem; padding: 1rem; background: rgba(255,255,255,.04); border-radius: 10px; margin-bottom: 1.5rem; }
.sidebar-avatar { font-size: 2rem; color: #eab308; }
.sidebar-user strong { display: block; color: #f4f4f5; font-size: .95rem; }
.sidebar-user small  { color: #aaa; font-size: .8rem; }
.sidebar-nav ul { list-style: none; padding: 0; margin: 0; }
.sidebar-nav li a { display: flex; align-items: center; gap: .8rem; padding: .75rem 1rem; color: #a1a1aa; text-decoration: none; border-radius: 8px; font-size: .9rem; transition: all .2s; }
.sidebar-nav li a:hover { background: rgba(234,179,8,.1); color: #eab308; }
.sidebar-nav li a i { width: 18px; text-align: center; }
.sidebar-separator { padding: .5rem 1rem; color: #555; font-size: .75rem; text-transform: uppercase; letter-spacing: 1px; margin-top: .5rem; }
.sidebar-logout { color: #ef4444 !important; }
.sidebar-logout:hover { background: rgba(239,68,68,.1) !important; }
.hamburger-btn { background: none; border: 1px solid rgba(255,255,255,.15); color: #f4f4f5; padding: .5rem .7rem; border-radius: 8px; cursor: pointer; font-size: 1rem; }
</style>
