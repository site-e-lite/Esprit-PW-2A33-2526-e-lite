<?php
// forum.php - BackOffice Admin Panel for Forum Management
// Réutilise l'esthétique "Black Edition & Eco-Digital"
require_once __DIR__ . '/../../Controller/ForumController.php';
require_once __DIR__ . '/../../Controller/PostController.php';

$forumController = new ForumController();
$postController = new PostController();

// Handle Add Forms
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && $_POST['action'] == 'add_forum') {
        $forum = new Forum($_POST['titre'], $_POST['description'], $_POST['idCourse']);
        $forumController->addForum($forum);
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
        $forum = new Forum($_POST['titre'], $_POST['description'], $_POST['idCourse']);
        $forumController->updateForum($forum, $_POST['idForum']);
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

// Handle Delete Actions
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

// Fetch Data
$forums = $forumController->afficherForums();
$posts = $postController->afficherPosts();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion du Forum | e-lite BackOffice</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Inclure le même CSS esthétique -->
    <link rel="stylesheet" href="../assets/index.css">
    <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS Supplémentaire Spécifique au layout du Dashboard Admin -->
    <style>
        body {
            display: flex; /* Layout Sidebar + Main */
            background-color: var(--black);
            margin: 0;
            overflow-x: hidden;
        }

        /* Désactiver le header fixe du front-office s'il gène, ou le cacher */
        #front-header { display: none; }

        /* Sidebar Administrative */
        .admin-sidebar {
            width: 280px;
            height: 100vh;
            background: rgba(10, 10, 10, 0.95);
            border-right: 1px solid var(--glass-border);
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            padding: 2rem 1.5rem;
            z-index: 100;
        }

        .admin-sidebar .logo {
            font-size: 2rem;
            margin-bottom: 3rem;
            text-align: center;
        }

        .admin-nav {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            list-style: none;
        }

        .admin-nav li a {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: var(--light-gray);
            text-decoration: none;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .admin-nav li a i { font-size: 1.2rem; width: 20px; text-align: center;}

        .admin-nav li a:hover, .admin-nav li a.active {
            background: rgba(234, 179, 8, 0.1);
            color: var(--accent);
            transform: translateX(5px);
            box-shadow: inset 2px 0 0 var(--accent);
        }

        .logout-btn {
            margin-top: auto;
            color: #ef4444 !important;
        }
        .logout-btn:hover { background: rgba(239, 68, 68, 0.1) !important; box-shadow: inset 2px 0 0 #ef4444 !important;}

        /* Contenu Principal */
        .admin-content {
            margin-left: 280px; /* Largeur de la sidebar */
            flex: 1;
            padding: 2.5rem 4rem;
            min-height: 100vh;
            background: radial-gradient(circle at top right, rgba(234, 179, 8, 0.05) 0%, transparent 40%);
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
            position: relative;
            background: transparent;
            backdrop-filter: none;
            -webkit-backdrop-filter: none;
            padding: 0;
            border: none;
        }

        .admin-header h1 {
            font-size: 2.5rem;
            margin: 0;
        }

        .admin-profile {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .admin-profile img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 2px solid var(--accent);
        }

        /* Dashboard Sections */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
            margin-top: 2rem;
        }

        @media (max-width: 1100px) {
            .dashboard-grid { grid-template-columns: 1fr; }
        }

        /* Tables Admin */
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
            color: var(--text-main);
        }

        .admin-table th, .admin-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--glass-border);
        }

        .admin-table th {
            color: var(--light-gray);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
        }

        .admin-table tbody tr {
            transition: background 0.3s;
        }

        .admin-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.03);
        }

        .status-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .status-active { background: rgba(16, 185, 129, 0.2); color: var(--green-eco); border: 1px solid var(--green-eco);}
        .status-danger { background: rgba(239, 68, 68, 0.2); color: #ef4444; border: 1px solid #ef4444;}
        .status-warning { background: rgba(245, 158, 11, 0.2); color: #f59e0b; border: 1px solid #f59e0b;}
        
        .action-btn { background: none; border: none; color: var(--light-gray); cursor: pointer; transition: color 0.3s; font-size: 1.1rem; margin-right: 0.8rem;}
        .action-btn:hover { color: var(--accent); }
        .action-btn.delete:hover { color: #ef4444; }
        .action-btn.warn:hover { color: #f59e0b; }

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
            <p style="color: var(--light-gray); margin-bottom: 1.5rem;">Ceci permet de sanctionner un utilisateur ou agir sur un fil de discussion.</p>
            <form class="glass-form">
                <div class="form-group">
                    <label>Action Requise</label>
                    <select>
                        <option>Avertissement (Message Automatique)</option>
                        <option>Suppression du Message</option>
                        <option>Bannissement Temporaire du Forum (24h)</option>
                    </select>
                </div>
                <div class="form-group mt-3">
                    <label>Note Interne</label>
                    <textarea required placeholder="Détaillez la raison (ex: Spam, Langage...)..."></textarea>
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
                <div class="form-group mb-3" style="margin-top:1rem;"><label>Description</label><textarea name="description" placeholder="Minimum 10 caractères..."></textarea></div>
                <div class="form-group mb-3" style="margin-top:1rem;"><label>ID du Cours lié (Optionnel)</label><input type="number" name="idCourse" value="0"></div>
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
                <div class="form-group mb-3" style="margin-top:1rem;"><label>Auteur (ID Utilisateur)</label><input type="number" name="idUser" placeholder="Ex: 1"></div>
                <div class="form-group mb-3" style="margin-top:1rem;"><label>Forum cible (ID Forum)</label><input type="number" name="idForum" placeholder="Ex: 5"></div>
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
                <div class="form-group mb-3" style="margin-top:1rem;"><label>Description</label><textarea name="description" id="edit_forum_desc" placeholder="Min 10 caractères"></textarea></div>
                <div class="form-group mb-3" style="margin-top:1rem;"><label>ID du Cours lié</label><input type="number" name="idCourse" id="edit_forum_course"></div>
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


    <!-- SIDEBAR NAVIGATION -->
    <aside class="admin-sidebar">
        <a href="dashboard.php" class="logo">e-lite<span>.</span><div style="font-size:0.8rem; letter-spacing:3px; color:var(--light-gray); font-family:var(--font-main);text-transform:uppercase;">BackOffice</div></a>
        
        <ul class="admin-nav">
            <li><a href="dashboard.php"><i class="fas fa-home"></i> Vue d'ensemble</a></li>
            <li><a href="#"><i class="fas fa-users"></i> Gest. Utilisateurs</a></li>
            <li><a href="#"><i class="fas fa-book-open"></i> Gest. Cours & Inscr.</a></li>
            <li><a href="#"><i class="fas fa-tasks"></i> Gest. Évaluations</a></li>
            <li><a href="forum.php" class="active"><i class="fas fa-comments"></i> Gest. Forum</a></li>
            <li><a href="#"><i class="fas fa-video"></i> Classes Virtuelles</a></li>
            
            <li><a href="../FrontOffice/index.html" style="margin-top:2rem; border: 1px dashed var(--glass-border);"><i class="fas fa-external-link-alt"></i> Voir le Site</a></li>
            <li><a href="#" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
        </ul>
    </aside>

    <!-- CONTENT -->
    <main class="admin-content">
        
        <header class="admin-header reveal">
            <div>
                <h1>Gestion du <span class="text-gradient">Forum</span></h1>
                <p style="color: var(--light-gray); margin-top: 0.5rem;">Supervisez les échanges, les alertes de l'IA et la modération communautaire.</p>
            </div>
            <div class="admin-profile">
                <!-- Nouveaux boutons d'ajout via modales -->
                <button class="btn-primary" style="margin-right:1rem; padding: 0.6rem 1.2rem; font-size: 0.85rem;" onclick="openModal('modalAddForum')"><i class="fas fa-plus"></i> Forum</button>
                <button class="btn-primary" style="margin-right:2rem; padding: 0.6rem 1.2rem; font-size: 0.85rem;" onclick="openModal('modalAddPost')"><i class="fas fa-plus"></i> Message</button>
                
                <div style="text-align: right;">
                    <strong style="display: block; color: var(--text-main);">Super Admin</strong>
                    <span style="font-size: 0.85rem; color: var(--accent);">Connecté</span>
                </div>
                <!-- Vous pouvez remplacer par le lien exact de l'image si besoin -->
                <img src="https://ui-avatars.com/api/?name=Admin&background=d4af37&color=000" alt="Admin Avatar">
            </div>
        </header>

        <!-- KPI STATS GRID FOR FORUM -->
        <section class="stats-grid reveal" style="margin-top: 0;">
            <div class="stat-item glass-card" style="padding: 1.5rem;">
                <i class="fas fa-comments accent-icon" style="font-size: 2rem;"></i>
                <h3 style="font-size: 2rem; margin: 0.5rem 0;">3,204</h3>
                <p style="font-size: 0.9rem;">Discussions Actives</p>
            </div>
            <div class="stat-item glass-card" style="padding: 1.5rem;">
                <i class="fas fa-envelope-open-text accent-icon" style="font-size: 2rem;"></i>
                <h3 style="font-size: 2rem; margin: 0.5rem 0;">89</h3>
                <p style="font-size: 0.9rem;">Nouveaux Messages (24h)</p>
            </div>
            <div class="stat-item glass-card" style="padding: 1.5rem; background: rgba(239, 68, 68, 0.05); border-color: rgba(239,68,68,0.3);">
                <i class="fas fa-exclamation-triangle" style="color: #ef4444; font-size: 2rem;"></i>
                <h3 style="font-size: 2rem; margin: 0.5rem 0; color: #ef4444;">12</h3>
                <p style="font-size: 0.9rem; color: #ef4444;">Signalements à traiter</p>
            </div>
            <div class="stat-item glass-card" style="padding: 1.5rem; background: rgba(16, 185, 129, 0.05); border-color: rgba(16,185,129,0.3);">
                <i class="fas fa-robot" style="color: var(--green-eco); font-size: 2rem;"></i>
                <h3 style="font-size: 2rem; margin: 0.5rem 0; color: var(--green-eco);">98%</h3>
                <p style="font-size: 0.9rem; color: var(--green-eco);">Modération gérée par l'IA</p>
            </div>
        </section>

        <!-- DASHBOARD GRID : Tables & Activités -->
        <div class="dashboard-grid">
            <!-- Table des Signalements -->
            <div class="glass-card reveal">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h3><i class="fas fa-shield-alt accent-icon"></i> Messages Signalés</h3>
                    <button class="btn-outline" style="padding: 0.5rem 1rem; font-size: 0.85rem;" onclick="openModal('modalForumAction')"><i class="fas fa-cog"></i> Auto-Modération</button>
                </div>
                
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Auteur</th>
                            <th>Aperçu du Message</th>
                            <th>Analyse IA</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($posts && $posts->rowCount() > 0): ?>
                            <?php while ($p = $posts->fetch()): ?>
                            <tr>
                                <td><strong>#U-<?= htmlspecialchars($p['idUser']) ?></strong><br><span style="color:var(--light-gray); font-size:0.8rem;">Forum #<?= htmlspecialchars($p['idForum']) ?></span></td>
                                <td>"<?= htmlspecialchars(substr($p['contenu'], 0, 40)) ?>..."<br><a href="#" style="color:var(--accent); font-size:0.8rem; text-decoration:none;">ID Post: #<?= htmlspecialchars($p['idPost']) ?></a></td>
                                <td><i class="fas fa-robot text-warning" style="color: #f59e0b;"></i> Analyse Auto...</td>
                                <td><span class="status-badge status-warning">Signalé</span></td>
                                <td>
                                    <button class="action-btn" title="Éditer" onclick="editPost(<?= $p['idPost'] ?>, '<?= htmlspecialchars(addslashes($p['contenu']), ENT_QUOTES) ?>', '<?= htmlspecialchars(addslashes($p['pieceJointe']), ENT_QUOTES) ?>')"><i class="fas fa-edit"></i></button>
                                    <button class="action-btn warn" title="Avertir" onclick="openModal('modalForumAction')"><i class="fas fa-exclamation-circle"></i></button>
                                    <a href="?delete_post=<?= $p['idPost'] ?>" onclick="return confirm('Confirmer la suppression du message ?');" class="action-btn delete" title="Supprimer le message"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align: center; color: var(--light-gray); padding: 2rem;">Aucun message trouvé.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Table des Forums -->
            <div class="glass-card reveal">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h3><i class="fas fa-list accent-icon"></i> Liste des Forums (Catégories)</h3>
                </div>
                
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Titre & Description</th>
                            <th>Date Création</th>
                            <th>ID Cours</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($forums && $forums->rowCount() > 0): ?>
                            <?php while ($f = $forums->fetch()): ?>
                            <tr>
                                <td><span style="color:var(--accent); font-weight:600;">#<?= htmlspecialchars($f['idForum']) ?></span></td>
                                <td>
                                    <strong style="display:block; font-size:1rem; margin-bottom:0.3rem;"><?= htmlspecialchars($f['titre']) ?></strong>
                                    <span style="color:var(--light-gray); font-size:0.85rem;"><?= htmlspecialchars(substr($f['description'], 0, 80)) ?>...</span>
                                </td>
                                <td><i class="far fa-calendar-alt" style="color:var(--light-gray);"></i> <?= htmlspecialchars($f['dateCreation']) ?></td>
                                <td><?= htmlspecialchars($f['idCourse']) ?></td>
                                <td>
                                    <button class="action-btn" title="Éditer" onclick="editForum(<?= $f['idForum'] ?>, '<?= htmlspecialchars(addslashes($f['titre']), ENT_QUOTES) ?>', '<?= htmlspecialchars(addslashes($f['description']), ENT_QUOTES) ?>', <?= htmlspecialchars($f['idCourse']) ?>)"><i class="fas fa-edit"></i></button>
                                    <a href="?delete_forum=<?= $f['idForum'] ?>" onclick="return confirm('Confirmer la suppression du forum ?');" class="action-btn delete" title="Supprimer"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align: center; color: var(--light-gray); padding: 2rem;">Aucun forum actif.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Inclusion du JS (cache-busted) -->
    <script src="../assets/index.js?v=<?= time() ?>"></script>
    <script>
        /* ── Modal helpers (override shared ones to guarantee they work here) ── */
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

        /* ── Validation: Update Post (modal) — ONLY checks content length ── */
        function validateUpdatePost(form) {
            const contenu = form.contenu ? form.contenu.value.trim() : '';
            if (contenu.length < 5) {
                showInlineError(form, 'Le message doit contenir au minimum 5 caractères.');
                return false;
            }
            return true;
        }

        /* ── Validation: Add Post (modal) — checks content + idForum + idUser ── */
        function validateAddPost(form) {
            const contenu  = form.contenu  ? form.contenu.value.trim()  : '';
            const idForum  = form.idForum  ? form.idForum.value.trim()  : '';
            const idUser   = form.idUser   ? form.idUser.value.trim()   : '';

            if (!idForum || isNaN(idForum) || parseInt(idForum) <= 0) {
                showInlineError(form, "L'ID du forum est invalide ou manquant.");
                return false;
            }
            if (idUser && (isNaN(idUser) || parseInt(idUser) <= 0)) {
                showInlineError(form, "L'ID utilisateur doit être un nombre valide.");
                return false;
            }
            if (contenu.length < 5) {
                showInlineError(form, 'Le message doit contenir au minimum 5 caractères.');
                return false;
            }
            return true;
        }

        /* ── Shared inline error banner (no alert!) ── */
        function showInlineError(form, message) {
            const existing = form.querySelector('.modal-validation-error');
            if (existing) existing.remove();
            const banner = document.createElement('div');
            banner.className = 'modal-validation-error';
            banner.style.cssText = [
                'background:rgba(239,68,68,0.12)',
                'border:1px solid rgba(239,68,68,0.45)',
                'border-radius:10px',
                'padding:0.75rem 1rem',
                'color:#ef4444',
                'font-size:0.85rem',
                'font-weight:600',
                'margin-bottom:1rem',
                'display:flex',
                'align-items:center',
                'gap:0.5rem'
            ].join(';');
            banner.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + message;
            form.insertBefore(banner, form.firstChild);
            setTimeout(() => banner.remove(), 4000);
        }
    </script>
</body>
</html>
