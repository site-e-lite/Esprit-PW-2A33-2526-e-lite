<?php
/**
 * View/includes/header.php
 * Unified role-based navigation header.
 * Used by all FrontOffice and BackOffice views.
 */
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($baseUrl)) {
    $baseUrl = '/gestioncours';
}

// Session state
$isLoggedIn = isset($_SESSION['user_id']);
$userId     = $isLoggedIn ? (int)$_SESSION['user_id'] : 0;
$roleName   = strtolower(trim((string)($_SESSION['role_nom'] ?? '')));
$userPrenom = htmlspecialchars((string)($_SESSION['user_prenom'] ?? ''));
$userNom    = htmlspecialchars((string)($_SESSION['user_nom']    ?? ''));

$isAdmin   = ($roleName === 'admin');
$isTeacher = ($roleName === 'enseignant');
$isStudent = ($roleName === 'etudiant');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'e-lite | Gestion Cours') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>/View/assets/index.css">
    <style>
        /* ── Sidebar ─────────────────────────────────────────── */
        .sidebar {
            position: fixed; top: 0; left: -290px; width: 270px; height: 100vh;
            background: rgba(9,9,11,.98); border-right: 1px solid rgba(255,255,255,.07);
            z-index: 2000; transition: left .28s ease; overflow-y: auto; padding: 1.4rem;
            display: flex; flex-direction: column; gap: 0;
        }
        .sidebar.open { left: 0; }
        .sidebar-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,.55); z-index: 1999;
        }
        .sidebar-overlay.active { display: block; }
        .sb-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.2rem; }
        .sb-logo { font-size: 1.5rem; font-weight: 800; color: #f4f4f5; text-decoration: none; }
        .sb-logo span { color: #eab308; }
        .sb-close { background: none; border: none; color: #888; font-size: 1.1rem; cursor: pointer; padding: .3rem; }
        .sb-user { display: flex; align-items: center; gap: .7rem; padding: .9rem; background: rgba(255,255,255,.04); border-radius: 10px; margin-bottom: 1.2rem; }
        .sb-user-icon { font-size: 1.8rem; color: #eab308; }
        .sb-user strong { display: block; color: #f4f4f5; font-size: .9rem; }
        .sb-user small { color: #888; font-size: .78rem; }
        .sb-nav { list-style: none; padding: 0; margin: 0; }
        .sb-nav li a {
            display: flex; align-items: center; gap: .75rem;
            padding: .65rem .9rem; color: #a1a1aa; text-decoration: none;
            border-radius: 8px; font-size: .88rem; transition: all .18s;
        }
        .sb-nav li a:hover { background: rgba(234,179,8,.1); color: #eab308; }
        .sb-nav li a i { width: 16px; text-align: center; flex-shrink: 0; }
        .sb-sep { padding: .5rem .9rem; color: #444; font-size: .72rem; text-transform: uppercase; letter-spacing: 1px; margin-top: .4rem; }
        .sb-logout { color: #ef4444 !important; }
        .sb-logout:hover { background: rgba(239,68,68,.1) !important; }
        .hamburger-btn {
            background: none; border: 1px solid rgba(255,255,255,.15);
            color: #f4f4f5; padding: .45rem .65rem; border-radius: 8px;
            cursor: pointer; font-size: .95rem; line-height: 1;
        }
    </style>
</head>
<body>

<?php if ($isLoggedIn): ?>
<!-- ── SIDEBAR ──────────────────────────────────────────────── -->
<aside id="sidebar" class="sidebar">
    <div class="sb-head">
        <a href="<?= $baseUrl ?>/View/FrontOffice/course/index.php" class="sb-logo">e-lite<span>.</span></a>
        <button id="sidebarClose" class="sb-close"><i class="fas fa-times"></i></button>
    </div>

    <div class="sb-user">
        <i class="fas fa-user-circle sb-user-icon"></i>
        <div>
            <strong><?= $userPrenom . ' ' . $userNom ?></strong>
            <small><?= ucfirst($roleName) ?></small>
        </div>
    </div>

    <ul class="sb-nav">
        <li><a href="<?= $baseUrl ?>/View/FrontOffice/dashboard.php">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a></li>

        <?php if ($isStudent): ?>
            <li class="sb-sep">Mes espaces</li>
            <li><a href="<?= $baseUrl ?>/View/FrontOffice/course/index.php">
                <i class="fas fa-book-open"></i> Mes Cours
            </a></li>
            <li><a href="<?= $baseUrl ?>/View/Forum/FrontOffice/index.php">
                <i class="fas fa-comments"></i> Forum (mes cours)
            </a></li>
            <li><a href="<?= $baseUrl ?>/View/FrontOffice/certificate/index.php">
                <i class="fas fa-certificate"></i> Mes Certificats
            </a></li>

        <?php elseif ($isTeacher): ?>
            <li class="sb-sep">Enseignement</li>
            <li><a href="<?= $baseUrl ?>/View/BackOffice/course/list.php">
                <i class="fas fa-chalkboard-teacher"></i> Mes Cours
            </a></li>
            <li><a href="<?= $baseUrl ?>/View/BackOffice/course/add.php">
                <i class="fas fa-plus-circle"></i> Ajouter un Cours
            </a></li>
            <li><a href="<?= $baseUrl ?>/View/Forum/BackOffice/index.php">
                <i class="fas fa-comments"></i> Forum (modération)
            </a></li>

        <?php elseif ($isAdmin): ?>
            <li class="sb-sep">Administration</li>
            <li><a href="<?= $baseUrl ?>/admin/dashboard">
                <i class="fas fa-users-cog"></i> Gestion Utilisateurs
            </a></li>
            <li><a href="<?= $baseUrl ?>/View/BackOffice/course/list.php">
                <i class="fas fa-graduation-cap"></i> Gestion Cours
            </a></li>
            <li><a href="<?= $baseUrl ?>/View/Forum/BackOffice/index.php">
                <i class="fas fa-comments"></i> Gestion Forum
            </a></li>
            <li><a href="<?= $baseUrl ?>/View/BackOffice/enrollment/list.php">
                <i class="fas fa-user-graduate"></i> Inscriptions
            </a></li>
            <li><a href="<?= $baseUrl ?>/View/BackOffice/certificate/list.php">
                <i class="fas fa-award"></i> Certificats
            </a></li>
        <?php endif; ?>

        <li class="sb-sep"></li>
        <li><a href="<?= $baseUrl ?>/profile">
            <i class="fas fa-user-edit"></i> Mon Profil
        </a></li>
        <li><a href="<?= $baseUrl ?>/logout" class="sb-logout">
            <i class="fas fa-sign-out-alt"></i> Déconnexion
        </a></li>
    </ul>
</aside>
<div id="sidebarOverlay" class="sidebar-overlay"></div>
<?php endif; ?>

<!-- ── TOP NAV ───────────────────────────────────────────────── -->
<header id="main-header">
    <nav>
        <div style="display:flex; align-items:center; gap:.8rem;">
            <?php if ($isLoggedIn): ?>
                <button id="sidebarToggle" class="hamburger-btn" aria-label="Menu">
                    <i class="fas fa-bars"></i>
                </button>
            <?php endif; ?>
            <a href="<?= $baseUrl ?>/View/FrontOffice/course/index.php" class="logo">
                e-lite<span>.</span>
            </a>
        </div>

        <ul class="nav-links">
            <li><a href="<?= $baseUrl ?>/View/FrontOffice/course/index.php">Accueil</a></li>

            <?php if ($isLoggedIn): ?>
                <li><a href="<?= $baseUrl ?>/View/FrontOffice/dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a></li>

                <?php if ($isStudent): ?>
                    <li><a href="<?= $baseUrl ?>/View/FrontOffice/course/index.php">
                        <i class="fas fa-book"></i> Mes Cours
                    </a></li>
                    <li><a href="<?= $baseUrl ?>/View/Forum/FrontOffice/index.php">
                        <i class="fas fa-comments"></i> Forum
                    </a></li>
                    <li><a href="<?= $baseUrl ?>/View/FrontOffice/certificate/index.php">
                        <i class="fas fa-certificate"></i> Certificats
                    </a></li>

                <?php elseif ($isTeacher): ?>
                    <li><a href="<?= $baseUrl ?>/View/BackOffice/course/list.php">
                        <i class="fas fa-chalkboard-teacher"></i> Mes Cours
                    </a></li>
                    <li><a href="<?= $baseUrl ?>/View/Forum/BackOffice/index.php">
                        <i class="fas fa-comments"></i> Forum
                    </a></li>
                    <li><a href="<?= $baseUrl ?>/View/BackOffice/course/add.php">
                        <i class="fas fa-plus"></i> Ajouter un cours
                    </a></li>

                <?php elseif ($isAdmin): ?>
                    <li><a href="<?= $baseUrl ?>/admin/dashboard">
                        <i class="fas fa-users-cog"></i> Utilisateurs
                    </a></li>
                    <li><a href="<?= $baseUrl ?>/View/BackOffice/course/list.php">
                        <i class="fas fa-graduation-cap"></i> Cours
                    </a></li>
                    <li><a href="<?= $baseUrl ?>/View/Forum/BackOffice/index.php">
                        <i class="fas fa-comments"></i> Forum
                    </a></li>
                <?php endif; ?>

            <?php else: ?>
                <li><a href="<?= $baseUrl ?>/View/FrontOffice/course/index.php#cours">Cours</a></li>
            <?php endif; ?>
        </ul>

        <div class="auth-buttons">
            <?php if ($isLoggedIn): ?>
                <span style="color:var(--light-gray); font-size:.88rem; margin-right:.3rem;">
                    <?= $userPrenom ?>
                </span>
                <a href="<?= $baseUrl ?>/profile" class="btn-outline" style="padding:.55rem .9rem;" title="Mon profil">
                    <i class="fas fa-user"></i>
                </a>
                <a href="<?= $baseUrl ?>/logout" class="btn-outline">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            <?php else: ?>
                <a href="<?= $baseUrl ?>/login"   class="btn-outline"><i class="fas fa-key"></i> Connexion</a>
                <a href="<?= $baseUrl ?>/register" class="btn-primary"><i class="fas fa-user-plus"></i> Inscription</a>
            <?php endif; ?>
        </div>
    </nav>
</header>

<main class="page-shell">

<script>
(function () {
    var toggle  = document.getElementById('sidebarToggle');
    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('sidebarOverlay');
    var close   = document.getElementById('sidebarClose');
    function open()  { sidebar && sidebar.classList.add('open');    overlay && overlay.classList.add('active'); }
    function shut()  { sidebar && sidebar.classList.remove('open'); overlay && overlay.classList.remove('active'); }
    if (toggle)  toggle.addEventListener('click', open);
    if (close)   close.addEventListener('click',  shut);
    if (overlay) overlay.addEventListener('click', shut);
})();
</script>
