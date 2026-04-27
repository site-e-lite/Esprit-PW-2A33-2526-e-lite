<?php
// forum.php - BackOffice Admin Panel for Forum Management
// Black Edition — with real Stats, Filtering & Rating
require_once __DIR__ . '/../../Controller/ForumController.php';
require_once __DIR__ . '/../../Controller/PostController.php';

$forumController = new ForumController();
$postController  = new PostController();

// ─── Handle Rating (AJAX or regular POST) ───────────────────
if (isset($_POST['action']) && $_POST['action'] === 'rate_forum') {
    $idForum = intval($_POST['idForum']  ?? 0);
    $note    = intval($_POST['note']     ?? 0);
    $idUser  = intval($_POST['idUser']   ?? 1); // guest user for demo
    if ($idForum > 0 && $note >= 1 && $note <= 5) {
        $newAvg = $forumController->raterForum($idForum, $note, $idUser);
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'avg' => $newAvg]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false]);
    }
    exit;
}

// ─── Handle Add / Update / Delete ───────────────────────────
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] == 'add_forum') {
        $titre       = trim($_POST['titre']       ?? '');
        $description = trim($_POST['description'] ?? '');
        $idCourse    = intval($_POST['idCourse']  ?? 0);
        $descVa      = preg_match('/^[a-zA-ZÀ-ÿ\s.,;:!\'"?()\-–—]+$/u', $description);
        if (strlen($titre) >= 3 && strlen($description) >= 10 && $descVa) {
            $forum = new Forum($titre, $description, $idCourse);
            $forumController->addForum($forum);
        }
        header('Location: forum.php');
        exit;
    }
    if (isset($_POST['action']) && $_POST['action'] == 'add_post') {
        $post = new Post($_POST['contenu'], $_POST['idUser'], $_POST['idForum'], $_POST['pieceJointe']);
        $postController->addPost($post);
        header('Location: forum.php');
        exit;
    }
    if (isset($_POST['action']) && $_POST['action'] == 'update_forum') {
        $titre       = trim($_POST['titre']       ?? '');
        $description = trim($_POST['description'] ?? '');
        $idCourse    = intval($_POST['idCourse']  ?? 0);
        $descVa      = preg_match('/^[a-zA-ZÀ-ÿ\s.,;:!\'"?()\-–—]+$/u', $description);
        if (strlen($titre) >= 3 && strlen($description) >= 10 && $descVa) {
            $forum = new Forum($titre, $description, $idCourse);
            $forumController->updateForum($forum, $_POST['idForum']);
        }
        header('Location: forum.php');
        exit;
    }
    if (isset($_POST['action']) && $_POST['action'] == 'update_post') {
        $post = new Post($_POST['contenu'], 0, 0, $_POST['pieceJointe']);
        $postController->updatePost($post, $_POST['idPost']);
        header('Location: forum.php');
        exit;
    }
}
if (isset($_GET['delete_forum'])) {
    $forumController->deleteForum($_GET['delete_forum']);
    header('Location: forum.php');
    exit;
}
if (isset($_GET['delete_post'])) {
    $postController->deletePost($_GET['delete_post']);
    header('Location: forum.php');
    exit;
}

// ─── Collect filter parameters ───────────────────────────────
$filters = [
    'search'    => trim($_GET['search']    ?? ''),
    'idCourse'  => trim($_GET['idCourse']  ?? ''),
    'dateFrom'  => trim($_GET['dateFrom']  ?? ''),
    'dateTo'    => trim($_GET['dateTo']    ?? ''),
    'minRating' => trim($_GET['minRating'] ?? ''),
];
$activeFilters = array_filter($filters);

// ─── Fetch Data ───────────────────────────────────────────────
$forums = $forumController->afficherForums($filters);
$posts  = $postController->afficherPosts();
$stats  = $forumController->getStats();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion du Forum | e-lite BackOffice</title>
    <meta name="description" content="Tableau de bord administrateur pour la gestion des forums e-lite — statistiques, filtrage et notation.">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* ════════════════════════════════════
           LAYOUT
        ════════════════════════════════════ */
        body {
            display: flex;
            background-color: var(--black);
            margin: 0;
            overflow-x: hidden;
        }
        #front-header { display: none; }

        /* SIDEBAR */
        .admin-sidebar {
            width: 280px;
            height: 100vh;
            background: rgba(10, 10, 10, 0.95);
            border-right: 1px solid var(--glass-border);
            position: fixed;
            top: 0; left: 0;
            display: flex;
            flex-direction: column;
            padding: 2rem 1.5rem;
            z-index: 100;
        }
        .admin-sidebar .logo { font-size: 2rem; margin-bottom: 3rem; text-align: center; }
        .admin-nav { display: flex; flex-direction: column; gap: 1rem; list-style: none; }
        .admin-nav li a {
            display: flex; align-items: center; gap: 1rem;
            color: var(--light-gray); text-decoration: none;
            padding: 1rem 1.5rem; border-radius: 12px;
            font-weight: 500; transition: all 0.3s;
        }
        .admin-nav li a i { font-size: 1.2rem; width: 20px; text-align: center; }
        .admin-nav li a:hover, .admin-nav li a.active {
            background: rgba(234,179,8,0.1); color: var(--accent);
            transform: translateX(5px); box-shadow: inset 2px 0 0 var(--accent);
        }
        .logout-btn { margin-top: auto; color: #ef4444 !important; }
        .logout-btn:hover { background: rgba(239,68,68,0.1) !important; box-shadow: inset 2px 0 0 #ef4444 !important; }

        /* CONTENT */
        .admin-content {
            margin-left: 280px;
            flex: 1;
            padding: 2.5rem 4rem;
            min-height: 100vh;
            background: radial-gradient(circle at top right, rgba(234,179,8,0.05) 0%, transparent 40%);
        }
        .admin-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 3rem;
            background: transparent; backdrop-filter: none; -webkit-backdrop-filter: none;
            padding: 0; border: none;
        }
        .admin-header h1 { font-size: 2.5rem; margin: 0; }
        .admin-profile { display: flex; align-items: center; gap: 1rem; }
        .admin-profile img { width: 50px; height: 50px; border-radius: 50%; border: 2px solid var(--accent); }

        /* ════════════════════════════════════
           STATS GRID (real data)
        ════════════════════════════════════ */
        .stats-grid-6 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2rem;
        }
        .stat-item {
            padding: 1.5rem;
            display: flex; flex-direction: column; gap: 0.4rem;
        }
        .stat-item i { font-size: 1.8rem; }
        .stat-item h3 { font-size: 2rem; margin: 0.3rem 0 0; line-height: 1; }
        .stat-item p  { font-size: 0.85rem; margin: 0; color: var(--light-gray); }

        /* ════════════════════════════════════
           MINI BAR CHART
        ════════════════════════════════════ */
        .chart-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        @media(max-width:1100px){ .chart-section { grid-template-columns: 1fr; } }

        .bar-chart-wrap { display: flex; flex-direction: column; gap: 0.6rem; }
        .bar-row { display: flex; align-items: center; gap: 0.75rem; font-size: 0.82rem; }
        .bar-label { width: 130px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--light-gray); }
        .bar-track { flex: 1; background: rgba(255,255,255,0.06); border-radius: 6px; height: 10px; overflow: hidden; }
        .bar-fill {
            height: 100%; border-radius: 6px;
            background: linear-gradient(90deg, var(--accent), #f59e0b);
            transition: width 1s cubic-bezier(.4,0,.2,1);
        }
        .bar-count { min-width: 28px; text-align: right; color: var(--accent); font-weight: 700; font-size: 0.82rem; }

        /* Rating distribution */
        .rating-dist { display: flex; flex-direction: column; gap: 0.5rem; }
        .rating-dist-row { display: flex; align-items: center; gap: 0.7rem; font-size: 0.82rem; }
        .star-label { width: 50px; display: flex; align-items: center; gap: 3px; color: #f59e0b; }
        .rating-bar-track { flex: 1; background: rgba(255,255,255,0.06); border-radius: 6px; height: 10px; overflow:hidden; }
        .rating-bar-fill {
            height: 100%; border-radius: 6px;
            background: linear-gradient(90deg, #f59e0b, #d97706);
            transition: width 1s cubic-bezier(.4,0,.2,1);
        }
        .rating-dist-count { min-width: 24px; text-align: right; color: #f59e0b; font-weight: 700; }

        /* ════════════════════════════════════
           FILTER BAR
        ════════════════════════════════════ */
        .filter-bar {
            display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: flex-end;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.5rem;
        }
        .filter-group { display: flex; flex-direction: column; gap: 0.3rem; }
        .filter-group label { font-size: 0.72rem; text-transform: uppercase; letter-spacing: 1px; color: var(--light-gray); }
        .filter-group input,
        .filter-group select {
            background: rgba(255,255,255,0.06);
            border: 1px solid var(--glass-border);
            border-radius: 8px;
            padding: 0.5rem 0.85rem;
            color: var(--text-main);
            font-size: 0.85rem;
            font-family: var(--font-main);
            outline: none;
            min-width: 140px;
            transition: border-color 0.25s;
        }
        .filter-group input:focus, .filter-group select:focus {
            border-color: var(--accent);
        }
        .filter-group input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(0.7);
        }
        .filter-actions { display: flex; gap: 0.5rem; align-items: flex-end; margin-left: auto; }
        .filter-badge {
            display: inline-flex; align-items: center; gap: 0.35rem;
            background: rgba(234,179,8,0.12); border: 1px solid rgba(234,179,8,0.3);
            border-radius: 20px; padding: 0.25rem 0.7rem;
            font-size: 0.75rem; color: var(--accent); font-weight: 600;
            animation: fadeIn 0.3s ease;
        }

        /* ════════════════════════════════════
           TABLE
        ════════════════════════════════════ */
        .dashboard-grid { display: grid; grid-template-columns: 1fr; gap: 2rem; }
        .admin-table { width: 100%; border-collapse: collapse; margin-top: 1.5rem; color: var(--text-main); }
        .admin-table th, .admin-table td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--glass-border); }
        .admin-table th {
            color: var(--light-gray); font-weight: 600;
            text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px;
        }
        .admin-table tbody tr { transition: background 0.3s; }
        .admin-table tbody tr:hover { background: rgba(255,255,255,0.03); }

        .status-badge { padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.78rem; font-weight: 600; }
        .status-active  { background: rgba(16,185,129,0.2);  color: var(--green-eco); border: 1px solid var(--green-eco); }
        .status-danger  { background: rgba(239,68,68,0.2);   color: #ef4444;         border: 1px solid #ef4444; }
        .status-warning { background: rgba(245,158,11,0.2);  color: #f59e0b;         border: 1px solid #f59e0b; }

        .action-btn { background: none; border: none; color: var(--light-gray); cursor: pointer; transition: color 0.3s; font-size: 1.1rem; margin-right: 0.6rem; }
        .action-btn:hover       { color: var(--accent); }
        .action-btn.delete:hover{ color: #ef4444; }
        .action-btn.warn:hover  { color: #f59e0b; }

        /* ════════════════════════════════════
           STAR RATING WIDGET (inline)
        ════════════════════════════════════ */
        .star-widget { display: flex; align-items: center; gap: 0.3rem; }
        .star-widget .stars { display: flex; gap: 2px; }
        .star-widget .star {
            font-size: 1rem; cursor: pointer;
            color: rgba(255,255,255,0.15);
            transition: color 0.18s, transform 0.18s;
        }
        .star-widget .star.filled { color: #f59e0b; }
        .star-widget .star:hover  { transform: scale(1.25); }
        .star-widget .avg-label {
            font-size: 0.78rem; color: var(--light-gray);
            white-space: nowrap;
        }

        /* ════════════════════════════════════
           MODALS
        ════════════════════════════════════ */
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: none; } }
        .no-results-row td { text-align: center; color: var(--light-gray); padding: 2.5rem 1rem; }
        .no-results-row .no-results-icon { font-size: 2rem; opacity: 0.3; display: block; margin-bottom: 0.5rem; }
    </style>
</head>
<body>

    <!-- OVERLAYS & MODALS -->
    <div class="modal-overlay" id="modalOverlay"></div>

    <!-- Modale Action Forum -->
    <div class="modal" id="modalForumAction">
        <div class="modal-header">
            <h3><i class="fas fa-gavel"></i> Modération Automatique</h3>
            <button class="close-btn" onclick="closeModal('modalForumAction')">&times;</button>
        </div>
        <div class="modal-body">
            <p style="color: var(--light-gray); margin-bottom: 1.5rem;">Sanctionner un utilisateur ou agir sur un fil de discussion.</p>
            <form class="glass-form">
                <div class="form-group">
                    <label>Action Requise</label>
                    <select>
                        <option>Avertissement (Message Automatique)</option>
                        <option>Suppression du Message</option>
                        <option>Bannissement Temporaire (24h)</option>
                    </select>
                </div>
                <div class="form-group mt-3">
                    <label>Note Interne</label>
                    <textarea required placeholder="Détaillez la raison (Spam, Langage...)..."></textarea>
                </div>
                <button type="button" class="btn-primary w-100 mt-3" onclick="closeModal('modalForumAction')">Appliquer la Sanction</button>
            </form>
        </div>
    </div>

    <!-- Modale Ajout Forum -->
    <div class="modal" id="modalAddForum">
        <div class="modal-header">
            <h3><i class="fas fa-plus"></i> Ajouter un Forum</h3>
            <button class="close-btn" onclick="closeModal('modalAddForum')">&times;</button>
        </div>
        <div class="modal-body">
            <form action="forum.php" method="POST" class="glass-form" onsubmit="return validateForum(this)">
                <input type="hidden" name="action" value="add_forum">
                <div class="form-group mb-3"><label>Titre de la discussion</label><input type="text" name="titre" placeholder="Minimum 3 caractères"></div>
                <div class="form-group mb-3" style="margin-top:1rem;">
                    <label>Description</label>
                    <textarea name="description" placeholder="Minimum 10 caractères..." onkeydown="if(/[0-9]/.test(event.key)){event.preventDefault();}" oninput="if(window.blockDigits) window.blockDigits(this);"></textarea>
                </div>
                <div class="form-group mb-3" style="margin-top:1rem;"><label>ID du Cours lié (Optionnel)</label><input type="number" name="idCourse" min="1" oninput="if(this.value !== '' && this.value < 1) this.value = 1;"></div>
                <button type="submit" class="btn-primary w-100 mt-3">Créer le Forum</button>
            </form>
        </div>
    </div>

    <!-- Modale Ajout Post -->
    <div class="modal" id="modalAddPost">
        <div class="modal-header">
            <h3><i class="fas fa-plus"></i> Ajouter un Message (Admin)</h3>
            <button class="close-btn" onclick="closeModal('modalAddPost')">&times;</button>
        </div>
        <div class="modal-body">
            <form action="forum.php" method="POST" class="glass-form" onsubmit="return validateAddPost(this)">
                <input type="hidden" name="action" value="add_post">
                <div class="form-group mb-3"><label>Contenu du Message</label><textarea name="contenu" placeholder="Minimum 5 caractères..."></textarea></div>
                <div class="form-group mb-3" style="margin-top:1rem;"><label>Auteur (ID Utilisateur)</label><input type="number" name="idUser" placeholder="Ex: 1" min="1" oninput="if(this.value !== '' && this.value < 1) this.value = 1;"></div>
                <div class="form-group mb-3" style="margin-top:1rem;"><label>Forum cible (ID Forum)</label><input type="number" name="idForum" placeholder="Ex: 5" min="1" oninput="if(this.value !== '' && this.value < 1) this.value = 1;"></div>
                <div class="form-group mb-3" style="margin-top:1rem;"><label>Pièce Jointe (Optionnel)</label><input type="text" name="pieceJointe"></div>
                <button type="submit" class="btn-primary w-100 mt-3">Publier le Message</button>
            </form>
        </div>
    </div>

    <!-- Modale Edit Forum -->
    <div class="modal" id="modalEditForum">
        <div class="modal-header">
            <h3><i class="fas fa-edit"></i> Modifier le Forum</h3>
            <button class="close-btn" onclick="closeModal('modalEditForum')">&times;</button>
        </div>
        <div class="modal-body">
            <form action="forum.php" method="POST" class="glass-form" onsubmit="return validateForum(this)">
                <input type="hidden" name="action" value="update_forum">
                <input type="hidden" name="idForum" id="edit_forum_id">
                <div class="form-group mb-3"><label>Titre de la discussion</label><input type="text" name="titre" id="edit_forum_titre" placeholder="Min 3 caractères"></div>
                <div class="form-group mb-3" style="margin-top:1rem;">
                    <label>Description</label>
                    <textarea name="description" id="edit_forum_desc" placeholder="Min 10 caractères" onkeydown="if(/[0-9]/.test(event.key)){event.preventDefault();}" oninput="if(window.blockDigits) window.blockDigits(this);"></textarea>
                </div>
                <div class="form-group mb-3" style="margin-top:1rem;"><label>ID du Cours lié</label><input type="number" name="idCourse" id="edit_forum_course" min="1" oninput="if(this.value !== '' && this.value < 1) this.value = 1;"></div>
                <button type="submit" class="btn-primary w-100 mt-3">Enregistrer</button>
            </form>
        </div>
    </div>

    <!-- Modale Edit Post -->
    <div class="modal" id="modalEditPost">
        <div class="modal-header">
            <h3><i class="fas fa-edit"></i> Modifier le Message</h3>
            <button class="close-btn" onclick="closeModal('modalEditPost')">&times;</button>
        </div>
        <div class="modal-body">
            <form action="forum.php" method="POST" class="glass-form" onsubmit="return validateUpdatePost(this)">
                <input type="hidden" name="action" value="update_post">
                <input type="hidden" name="idPost" id="edit_post_id">
                <div class="form-group mb-3"><label>Contenu du Message</label><textarea name="contenu" id="edit_post_contenu" placeholder="Min 5 caractères..."></textarea></div>
                <div class="form-group mb-3" style="margin-top:1rem;"><label>Pièce Jointe</label><input type="text" name="pieceJointe" id="edit_post_pj"></div>
                <button type="submit" class="btn-primary w-100 mt-3">Enregistrer</button>
            </form>
        </div>
    </div>

    <!-- ════════════════════════════════════════════════
         SIDEBAR
    ════════════════════════════════════════════════ -->
    <aside class="admin-sidebar">
        <a href="dashboard.php" class="logo">e-lite<span>.</span><div style="font-size:0.8rem; letter-spacing:3px; color:var(--light-gray); font-family:var(--font-main);text-transform:uppercase;">BackOffice</div></a>
        <ul class="admin-nav">
            <li><a href="dashboard.php"><i class="fas fa-home"></i> Vue d'ensemble</a></li>
            <li><a href="#"><i class="fas fa-users"></i> Gest. Utilisateurs</a></li>
            <li><a href="#"><i class="fas fa-book-open"></i> Gest. Cours &amp; Inscr.</a></li>
            <li><a href="#"><i class="fas fa-tasks"></i> Gest. Évaluations</a></li>
            <li><a href="forum.php" class="active"><i class="fas fa-comments"></i> Gest. Forum</a></li>
            <li><a href="#"><i class="fas fa-video"></i> Classes Virtuelles</a></li>
            <li><a href="../FrontOffice/index.php" style="margin-top:2rem; border: 1px dashed var(--glass-border);"><i class="fas fa-external-link-alt"></i> Voir le Site</a></li>
            <li><a href="#" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
        </ul>
    </aside>

    <!-- ════════════════════════════════════════════════
         MAIN CONTENT
    ════════════════════════════════════════════════ -->
    <main class="admin-content">

        <!-- HEADER -->
        <header class="admin-header reveal">
            <div>
                <h1>Gestion du <span class="text-gradient">Forum</span></h1>
                <p style="color: var(--light-gray); margin-top: 0.5rem;">Statistiques en temps réel, filtrage avancé et notation par la communauté.</p>
            </div>
            <div class="admin-profile">
                <button class="btn-primary" style="margin-right:1rem; padding: 0.6rem 1.2rem; font-size: 0.85rem;" onclick="openModal('modalAddForum')"><i class="fas fa-plus"></i> Forum</button>
                <button class="btn-primary" style="margin-right:2rem; padding: 0.6rem 1.2rem; font-size: 0.85rem;" onclick="openModal('modalAddPost')"><i class="fas fa-plus"></i> Message</button>
                <div style="text-align: right;">
                    <strong style="display: block; color: var(--text-main);">Super Admin</strong>
                    <span style="font-size: 0.85rem; color: var(--accent);">Connecté</span>
                </div>
                <img src="https://ui-avatars.com/api/?name=Admin&background=d4af37&color=000" alt="Admin Avatar">
            </div>
        </header>

        <!-- ══════════════════════════════════════════
             REAL STATISTICS (6 KPI cards)
        ══════════════════════════════════════════ -->
        <section class="stats-grid-6 reveal">

            <div class="stat-item glass-card">
                <i class="fas fa-comments accent-icon"></i>
                <h3><?= number_format($stats['totalForums']) ?></h3>
                <p>Forums Totaux</p>
            </div>

            <div class="stat-item glass-card">
                <i class="fas fa-envelope-open-text accent-icon"></i>
                <h3><?= number_format($stats['totalPosts']) ?></h3>
                <p>Messages Totaux</p>
            </div>

            <div class="stat-item glass-card">
                <i class="fas fa-clock accent-icon"></i>
                <h3><?= number_format($stats['posts24h']) ?></h3>
                <p>Messages (24h)</p>
            </div>

            <div class="stat-item glass-card" style="background: rgba(245,158,11,0.05); border-color: rgba(245,158,11,0.3);">
                <i class="fas fa-star" style="color:#f59e0b; font-size:1.8rem;"></i>
                <h3 style="color:#f59e0b;"><?= $stats['avgRating'] > 0 ? $stats['avgRating'] : '—' ?></h3>
                <p style="color:#f59e0b;">Note Moyenne /5</p>
            </div>

            <div class="stat-item glass-card" style="background: rgba(16,185,129,0.05); border-color: rgba(16,185,129,0.3);">
                <i class="fas fa-poll" style="color: var(--green-eco); font-size:1.8rem;"></i>
                <h3 style="color: var(--green-eco);"><?= number_format($stats['totalRatings']) ?></h3>
                <p style="color: var(--green-eco);">Notations Reçues</p>
            </div>

            <div class="stat-item glass-card">
                <i class="fas fa-trophy accent-icon"></i>
                <h3 style="font-size: 1.1rem; line-height:1.3;"><?= htmlspecialchars($stats['topForum']) ?></h3>
                <p>Forum le + Actif (<?= $stats['topForumPosts'] ?> msg)</p>
            </div>
        </section>

        <!-- ══════════════════════════════════════════
             MINI CHARTS
        ══════════════════════════════════════════ -->
        <div class="chart-section reveal">

            <!-- Posts per forum bar chart -->
            <div class="glass-card">
                <h3 style="margin-bottom: 1.25rem;"><i class="fas fa-chart-bar accent-icon"></i> Posts par Forum</h3>
                <?php
                $maxPosts = 1;
                foreach ($stats['postsPerForum'] as $row) {
                    if ($row['c'] > $maxPosts) $maxPosts = $row['c'];
                }
                ?>
                <div class="bar-chart-wrap">
                    <?php foreach ($stats['postsPerForum'] as $row): ?>
                    <div class="bar-row">
                        <span class="bar-label" title="<?= htmlspecialchars($row['titre']) ?>"><?= htmlspecialchars(mb_strimwidth($row['titre'], 0, 22, '…')) ?></span>
                        <div class="bar-track">
                            <div class="bar-fill" style="width: <?= $maxPosts > 0 ? round($row['c'] / $maxPosts * 100) : 0 ?>%"></div>
                        </div>
                        <span class="bar-count"><?= $row['c'] ?></span>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($stats['postsPerForum'])): ?>
                    <p style="color:var(--light-gray); text-align:center; padding: 1rem 0;">Aucune donnée disponible.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Rating distribution -->
            <div class="glass-card">
                <h3 style="margin-bottom: 1.25rem;"><i class="fas fa-star accent-icon" style="color:#f59e0b;"></i> Répartition des Notes</h3>
                <?php
                $maxRating = max(array_values($stats['ratingDist']) ?: [1]);
                ?>
                <div class="rating-dist">
                    <?php for ($star = 5; $star >= 1; $star--): ?>
                    <div class="rating-dist-row">
                        <span class="star-label">
                            <?= $star ?> <i class="fas fa-star" style="font-size:0.75rem;"></i>
                        </span>
                        <div class="rating-bar-track">
                            <div class="rating-bar-fill" style="width: <?= $maxRating > 0 ? round($stats['ratingDist'][$star] / $maxRating * 100) : 0 ?>%"></div>
                        </div>
                        <span class="rating-dist-count"><?= $stats['ratingDist'][$star] ?></span>
                    </div>
                    <?php endfor; ?>
                </div>
                <p style="margin-top:1rem; font-size:0.8rem; color:var(--light-gray);">
                    <i class="fas fa-info-circle"></i>
                    <?= number_format($stats['totalRatings']) ?> notation(s) enregistrée(s) &mdash; 
                    Moyenne globale&nbsp;: <strong style="color:#f59e0b;"><?= $stats['avgRating'] > 0 ? $stats['avgRating'] . ' / 5' : 'Aucune' ?></strong>
                </p>
            </div>
        </div>

        <!-- ══════════════════════════════════════════
             FILTER BAR
        ══════════════════════════════════════════ -->
        <div class="glass-card reveal" style="margin-bottom: 1.5rem;">
            <form method="GET" action="forum.php" id="filterForm">
                <div class="filter-bar">
                    <div class="filter-group">
                        <label><i class="fas fa-search"></i> Mot-clé</label>
                        <input type="text" name="search" value="<?= htmlspecialchars($filters['search']) ?>" placeholder="Titre ou description…" id="filterSearch">
                    </div>
                    <div class="filter-group">
                        <label><i class="fas fa-book"></i> ID Cours</label>
                        <input type="number" name="idCourse" value="<?= htmlspecialchars($filters['idCourse']) ?>" placeholder="Ex: 3" min="1" id="filterCourse">
                    </div>
                    <div class="filter-group">
                        <label><i class="fas fa-calendar-alt"></i> Date début</label>
                        <input type="date" name="dateFrom" value="<?= htmlspecialchars($filters['dateFrom']) ?>" id="filterDateFrom">
                    </div>
                    <div class="filter-group">
                        <label><i class="fas fa-calendar-alt"></i> Date fin</label>
                        <input type="date" name="dateTo" value="<?= htmlspecialchars($filters['dateTo']) ?>" id="filterDateTo">
                    </div>
                    <div class="filter-group">
                        <label><i class="fas fa-star"></i> Note min.</label>
                        <select name="minRating" id="filterRating">
                            <option value="">Toutes</option>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <option value="<?= $i ?>" <?= $filters['minRating'] == $i ? 'selected' : '' ?>>≥ <?= $i ?> ★</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="btn-primary" style="padding:0.55rem 1.2rem; font-size:0.85rem;">
                            <i class="fas fa-filter"></i> Filtrer
                        </button>
                        <?php if ($activeFilters): ?>
                        <a href="forum.php" class="btn-outline" style="padding:0.55rem 1.2rem; font-size:0.85rem; text-decoration:none;">
                            <i class="fas fa-times"></i> Reset
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ($activeFilters): ?>
                <div style="padding: 0 1.5rem 1rem; display: flex; flex-wrap:wrap; gap: 0.4rem;">
                    <?php if ($filters['search']): ?><span class="filter-badge"><i class="fas fa-search"></i> "<?= htmlspecialchars($filters['search']) ?>"</span><?php endif; ?>
                    <?php if ($filters['idCourse']): ?><span class="filter-badge"><i class="fas fa-book"></i> Cours #<?= htmlspecialchars($filters['idCourse']) ?></span><?php endif; ?>
                    <?php if ($filters['dateFrom']): ?><span class="filter-badge"><i class="fas fa-calendar-alt"></i> Depuis <?= htmlspecialchars($filters['dateFrom']) ?></span><?php endif; ?>
                    <?php if ($filters['dateTo']): ?><span class="filter-badge"><i class="fas fa-calendar-alt"></i> Jusqu'au <?= htmlspecialchars($filters['dateTo']) ?></span><?php endif; ?>
                    <?php if ($filters['minRating']): ?><span class="filter-badge"><i class="fas fa-star"></i> ≥ <?= htmlspecialchars($filters['minRating']) ?> ★</span><?php endif; ?>
                </div>
                <?php endif; ?>
            </form>
        </div>

        <!-- ══════════════════════════════════════════
             DASHBOARD GRID
        ══════════════════════════════════════════ -->
        <div class="dashboard-grid">

            <!-- Posts Table -->
            <div class="glass-card reveal">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h3><i class="fas fa-shield-alt accent-icon"></i> Messages &amp; Signalements</h3>
                    <button class="btn-outline" style="padding: 0.5rem 1rem; font-size: 0.85rem;" onclick="openModal('modalForumAction')"><i class="fas fa-cog"></i> Auto-Modération</button>
                </div>
                <table class="admin-table" id="postsTable">
                    <thead>
                        <tr>
                            <th>Auteur</th>
                            <th>Aperçu du Message</th>
                            <th>Note du Post</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($posts && $posts->rowCount() > 0): ?>
                            <?php while ($p = $posts->fetch()): ?>
                            <tr>
                                <td><strong>#U-<?= htmlspecialchars($p['idUser']) ?></strong><br><span style="color:var(--light-gray); font-size:0.8rem;">Forum #<?= htmlspecialchars($p['idForum']) ?></span></td>
                                <td>"<?= htmlspecialchars(substr($p['contenu'], 0, 40)) ?>…"<br><a href="#" style="color:var(--accent); font-size:0.8rem; text-decoration:none;">ID Post: #<?= htmlspecialchars($p['idPost']) ?></a></td>
                                <td>
                                    <!-- Inline star rating for post -->
                                    <div class="star-widget" data-post-id="<?= $p['idPost'] ?>">
                                        <div class="stars" id="postStars_<?= $p['idPost'] ?>">
                                            <?php for ($s = 1; $s <= 5; $s++): ?>
                                            <span class="star <?= ($p['rating'] ?? 0) >= $s ? 'filled' : '' ?>"
                                                  onclick="ratePost(<?= $p['idPost'] ?>, <?= $s ?>)"
                                                  data-val="<?= $s ?>" title="<?= $s ?> étoile(s)">★</span>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="avg-label" id="postRatingLabel_<?= $p['idPost'] ?>">
                                            <?= $p['rating'] ? $p['rating'].'/5' : 'N/A' ?>
                                        </span>
                                    </div>
                                </td>
                                <td><span class="status-badge status-warning">En Attente</span></td>
                                <td>
                                    <button class="action-btn" title="Éditer" onclick="editPost(<?= $p['idPost'] ?>, '<?= htmlspecialchars(addslashes($p['contenu']), ENT_QUOTES) ?>', '<?= htmlspecialchars(addslashes($p['pieceJointe']), ENT_QUOTES) ?>')"><i class="fas fa-edit"></i></button>
                                    <button class="action-btn warn" title="Avertir" onclick="openModal('modalForumAction')"><i class="fas fa-exclamation-circle"></i></button>
                                    <a href="?delete_post=<?= $p['idPost'] ?>" onclick="return confirm('Confirmer la suppression du message ?');" class="action-btn delete" title="Supprimer"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr class="no-results-row"><td colspan="5"><span class="no-results-icon">💬</span>Aucun message trouvé.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Forums Table with Rating -->
            <div class="glass-card reveal">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h3><i class="fas fa-list accent-icon"></i> Liste des Forums
                        <?php if ($activeFilters): ?>
                        <span class="filter-badge" style="margin-left:0.75rem;"><i class="fas fa-filter"></i> Filtrés</span>
                        <?php endif; ?>
                    </h3>
                    <span style="color:var(--light-gray); font-size:0.85rem;" id="forumCount"></span>
                </div>
                <table class="admin-table" id="forumsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Titre &amp; Description</th>
                            <th>Date Création</th>
                            <th>Posts</th>
                            <th>Note ★</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $forumsData = $forums->fetchAll();
                        ?>
                        <?php if (count($forumsData) > 0): ?>
                            <?php foreach ($forumsData as $f): ?>
                            <tr>
                                <td><span style="color:var(--accent); font-weight:600;">#<?= htmlspecialchars($f['idForum']) ?></span></td>
                                <td>
                                    <strong style="display:block; font-size:1rem; margin-bottom:0.3rem;"><?= htmlspecialchars($f['titre']) ?></strong>
                                    <span style="color:var(--light-gray); font-size:0.82rem;"><?= htmlspecialchars(mb_strimwidth($f['description'] ?? '', 0, 90, '…')) ?></span>
                                </td>
                                <td><i class="far fa-calendar-alt" style="color:var(--light-gray);"></i> <?= htmlspecialchars(date('d/m/Y', strtotime($f['dateCreation']))) ?></td>
                                <td>
                                    <span style="background: rgba(234,179,8,0.12); border: 1px solid rgba(234,179,8,0.25); border-radius: 20px; padding: 0.25rem 0.65rem; font-size:0.82rem; color:var(--accent); font-weight:600;">
                                        <?= intval($f['postCount']) ?>
                                    </span>
                                </td>
                                <td>
                                    <!-- Star rating widget for forum -->
                                    <div class="star-widget" id="forumRating_<?= $f['idForum'] ?>">
                                        <div class="stars">
                                            <?php
                                            $avg = floatval($f['avgRating'] ?? 0);
                                            for ($s = 1; $s <= 5; $s++):
                                            ?>
                                            <span class="star <?= $avg >= $s ? 'filled' : '' ?>"
                                                  onclick="rateForum(<?= $f['idForum'] ?>, <?= $s ?>)"
                                                  data-forum="<?= $f['idForum'] ?>"
                                                  data-val="<?= $s ?>"
                                                  title="<?= $s ?> étoile(s)"
                                                  onmouseover="hoverStars(this)"
                                                  onmouseleave="resetStars(<?= $f['idForum'] ?>)">★</span>
                                            <?php endfor; ?>
                                        </div>
                                        <span class="avg-label" id="forumAvgLabel_<?= $f['idForum'] ?>">
                                            <?= $f['avgRating'] ? $f['avgRating'] . ' (' . $f['ratingCount'] . ')' : 'Pas encore noté' ?>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <button class="action-btn" title="Éditer" onclick="editForum(<?= $f['idForum'] ?>, '<?= htmlspecialchars(addslashes($f['titre']), ENT_QUOTES) ?>', '<?= htmlspecialchars(addslashes($f['description']), ENT_QUOTES) ?>', <?= intval($f['idCourse']) ?>)"><i class="fas fa-edit"></i></button>
                                    <a href="?delete_forum=<?= $f['idForum'] ?>" onclick="return confirm('Confirmer la suppression du forum \\'<?= htmlspecialchars(addslashes($f['titre'])) ?>\' ?');" class="action-btn delete" title="Supprimer"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr class="no-results-row">
                                <td colspan="6">
                                    <span class="no-results-icon">🔍</span>
                                    <?= $activeFilters ? 'Aucun forum ne correspond aux filtres sélectionnés.' : 'Aucun forum actif.' ?>
                                    <?php if ($activeFilters): ?><br><a href="forum.php" style="color:var(--accent); font-size:0.85rem;">Réinitialiser les filtres</a><?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <?php if (count($forumsData) > 0): ?>
                <p style="margin-top:1rem; padding-top:1rem; border-top:1px solid var(--glass-border); color:var(--light-gray); font-size:0.82rem; text-align:right;">
                    <i class="fas fa-list-ol"></i> <?= count($forumsData) ?> forum(s) affiché(s)
                    <?= $activeFilters ? ' — filtres actifs' : '' ?>
                </p>
                <?php endif; ?>
            </div>

        </div><!-- /.dashboard-grid -->

    </main>

    <script src="../assets/index.js?v=<?= time() ?>"></script>
    <script>
        /* ── Modal helpers ── */
        function openModal(id) {
            const overlay = document.getElementById('modalOverlay');
            const modal   = document.getElementById(id);
            if (overlay) overlay.classList.add('active');
            if (modal)   modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        function closeModal(id) {
            const overlay = document.getElementById('modalOverlay');
            const modal   = document.getElementById(id);
            if (overlay) overlay.classList.remove('active');
            if (modal)   modal.classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        /* ── Pre-fill helpers ── */
        function editForum(id, titre, desc, idCourse) {
            document.getElementById('edit_forum_id').value     = id;
            document.getElementById('edit_forum_titre').value  = titre;
            document.getElementById('edit_forum_desc').value   = desc;
            document.getElementById('edit_forum_course').value = idCourse;
            openModal('modalEditForum');
        }
        function editPost(id, contenu, pieceJointe) {
            document.getElementById('edit_post_id').value      = id;
            document.getElementById('edit_post_contenu').value = contenu;
            document.getElementById('edit_post_pj').value      = pieceJointe;
            openModal('modalEditPost');
        }

        /* ── Validation ── */
        function validateUpdatePost(form) {
            const contenu = form.contenu ? form.contenu.value.trim() : '';
            if (contenu.length < 5) { showInlineError(form, 'Le message doit contenir au minimum 5 caractères.'); return false; }
            return true;
        }
        function validateAddPost(form) {
            const contenu = form.contenu  ? form.contenu.value.trim()  : '';
            const idForum = form.idForum  ? form.idForum.value.trim()  : '';
            const idUser  = form.idUser   ? form.idUser.value.trim()   : '';
            if (!idForum || isNaN(idForum) || parseInt(idForum) <= 0) { showInlineError(form, "L'ID du forum est invalide ou manquant."); return false; }
            if (idUser && (isNaN(idUser) || parseInt(idUser) <= 0))   { showInlineError(form, "L'ID utilisateur doit être un nombre valide."); return false; }
            if (contenu.length < 5) { showInlineError(form, 'Le message doit contenir au minimum 5 caractères.'); return false; }
            return true;
        }
        function showInlineError(form, message) {
            const existing = form.querySelector('.modal-validation-error');
            if (existing) existing.remove();
            const banner = document.createElement('div');
            banner.className = 'modal-validation-error';
            banner.style.cssText = 'background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.45);border-radius:10px;padding:0.75rem 1rem;color:#ef4444;font-size:0.85rem;font-weight:600;margin-bottom:1rem;display:flex;align-items:center;gap:0.5rem';
            banner.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + message;
            form.insertBefore(banner, form.firstChild);
            setTimeout(() => banner.remove(), 4000);
        }

        /* ══════════════════════════════════════
           FORUM STAR RATING
        ══════════════════════════════════════ */
        function hoverStars(el) {
            const val  = parseInt(el.dataset.val);
            const fid  = el.dataset.forum;
            const wrap = document.getElementById('forumRating_' + fid);
            wrap.querySelectorAll('.star').forEach(s => {
                s.style.color = parseInt(s.dataset.val) <= val ? '#f59e0b' : 'rgba(255,255,255,0.15)';
            });
        }

        function resetStars(fid) {
            const wrap = document.getElementById('forumRating_' + fid);
            wrap.querySelectorAll('.star').forEach(s => {
                s.style.color = s.classList.contains('filled') ? '#f59e0b' : 'rgba(255,255,255,0.15)';
            });
        }

        function rateForum(idForum, note) {
            const fd = new FormData();
            fd.append('action', 'rate_forum');
            fd.append('idForum', idForum);
            fd.append('note', note);
            fd.append('idUser', 1); // demo: always user #1

            fetch('forum.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    if (!data.success) return;
                    const wrap = document.getElementById('forumRating_' + idForum);
                    const label = document.getElementById('forumAvgLabel_' + idForum);
                    // Update filled state
                    wrap.querySelectorAll('.star').forEach(s => {
                        const sv = parseInt(s.dataset.val);
                        if (sv <= note) s.classList.add('filled');
                        else            s.classList.remove('filled');
                        s.style.color = sv <= note ? '#f59e0b' : 'rgba(255,255,255,0.15)';
                    });
                    if (label) label.textContent = data.avg + ' ★';
                    showRatingToast('Note ' + note + '/5 enregistrée !');
                })
                .catch(() => {});
        }

        /* ══════════════════════════════════════
           POST STAR RATING (local only for demo)
        ══════════════════════════════════════ */
        function ratePost(idPost, note) {
            const stars = document.querySelectorAll('#postStars_' + idPost + ' .star');
            stars.forEach(s => {
                const sv = parseInt(s.dataset.val);
                if (sv <= note) s.classList.add('filled');
                else            s.classList.remove('filled');
            });
            const lbl = document.getElementById('postRatingLabel_' + idPost);
            if (lbl) lbl.textContent = note + '/5';
            showRatingToast('Post noté ' + note + '/5');
        }

        /* Toast notification for rating */
        function showRatingToast(msg) {
            let t = document.getElementById('ratingToast');
            if (!t) {
                t = document.createElement('div');
                t.id = 'ratingToast';
                t.style.cssText = [
                    'position:fixed', 'bottom:2rem', 'right:2rem',
                    'background:rgba(245,158,11,0.15)', 'border:1px solid rgba(245,158,11,0.4)',
                    'color:#f59e0b', 'border-radius:12px', 'padding:0.75rem 1.25rem',
                    'font-size:0.9rem', 'font-weight:600', 'z-index:9999',
                    'display:flex', 'align-items:center', 'gap:0.5rem',
                    'backdrop-filter:blur(10px)', 'transition:opacity 0.3s'
                ].join(';');
                document.body.appendChild(t);
            }
            t.innerHTML = '<i class="fas fa-star"></i> ' + msg;
            t.style.opacity = '1';
            clearTimeout(t._timer);
            t._timer = setTimeout(() => { t.style.opacity = '0'; }, 2800);
        }

        /* ── Live search / filter shortcut (Enter key) ── */
        document.getElementById('filterSearch').addEventListener('keypress', e => {
            if (e.key === 'Enter') document.getElementById('filterForm').submit();
        });
    </script>
</body>
</html>
