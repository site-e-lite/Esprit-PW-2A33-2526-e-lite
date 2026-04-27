<?php
require_once __DIR__ . '/../../Controller/PostController.php';
$postController = new PostController();

// Handle Delete
if (isset($_GET['delete_post'])) {
    $postController->deletePost($_GET['delete_post']);
    header('Location: posts_list.php?deleted=1');
    exit;
}

$idForumFilter = isset($_GET['IdForum']) ? $_GET['IdForum'] : null;
$posts = $postController->afficherPosts($idForumFilter);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Posts | e-lite BackOffice</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { display: flex; background-color: var(--black); margin: 0; overflow-x: hidden; }
        #front-header { display: none; }
        .admin-sidebar { width: 280px; height: 100vh; background: rgba(10,10,10,0.95); border-right: 1px solid var(--glass-border); position: fixed; top: 0; left: 0; display: flex; flex-direction: column; padding: 2rem 1.5rem; z-index: 100; }
        .admin-sidebar .logo { font-size: 2rem; margin-bottom: 3rem; text-align: center; text-decoration: none; color: inherit; }
        .admin-nav { display: flex; flex-direction: column; gap: 1rem; list-style: none; padding: 0; margin: 0; }
        .admin-nav li a { display: flex; align-items: center; gap: 1rem; color: var(--light-gray); text-decoration: none; padding: 1rem 1.5rem; border-radius: 12px; font-weight: 500; transition: all 0.3s; }
        .admin-nav li a i { font-size: 1.2rem; width: 20px; text-align: center; }
        .admin-nav li a:hover, .admin-nav li a.active { background: rgba(234,179,8,0.1); color: var(--accent); transform: translateX(5px); box-shadow: inset 2px 0 0 var(--accent); }
        .admin-content { margin-left: 280px; flex: 1; padding: 2.5rem 4rem; min-height: 100vh; background: radial-gradient(circle at top right, rgba(234,179,8,0.05) 0%, transparent 40%); }
        .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 3rem; position: relative; background: transparent; padding: 0; border: none; }
        .admin-header h1 { font-size: 2.5rem; margin: 0; }
        .admin-table { width: 100%; border-collapse: collapse; margin-top: 1.5rem; color: var(--text-main); }
        .admin-table th, .admin-table td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--glass-border); }
        .admin-table th { color: var(--light-gray); font-weight: 600; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px; }
        .admin-table tbody tr { transition: background 0.3s; }
        .admin-table tbody tr:hover { background: rgba(255,255,255,0.03); }
        .action-btn { background: none; border: none; color: var(--light-gray); cursor: pointer; transition: color 0.3s; font-size: 1.1rem; margin-right: 0.8rem; text-decoration: none; }
        .action-btn:hover { color: var(--accent); }
        .action-btn.delete:hover { color: #ef4444; }

        /* Filter bar */
        .filter-bar { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem; }
        .filter-form { display: flex; gap: 0.8rem; align-items: center; flex-wrap: wrap; }
        .filter-input {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            color: #fff; padding: 0.6rem 1rem;
            border-radius: 10px; font-size: 0.9rem;
            outline: none; transition: border-color 0.3s, box-shadow 0.3s;
            font-family: inherit; width: 120px;
        }
        .filter-input:focus { border-color: #eab308; box-shadow: 0 0 0 3px rgba(234,179,8,0.1); }
        .filter-input::placeholder { color: rgba(255,255,255,0.25); }
        .filter-btn { background: rgba(234,179,8,0.1); border: 1px solid rgba(234,179,8,0.3); color: #eab308; padding: 0.6rem 1.2rem; border-radius: 10px; cursor: pointer; font-size: 0.85rem; font-weight: 600; font-family: inherit; transition: background 0.3s; }
        .filter-btn:hover { background: rgba(234,179,8,0.2); }
        .reset-link { color: rgba(255,255,255,0.4); font-size: 0.85rem; text-decoration: none; transition: color 0.2s; display: inline-flex; align-items: center; gap: 0.3rem; }
        .reset-link:hover { color: #eab308; }
        .add-btn { background: linear-gradient(135deg, #eab308, #d97706); color: #000; border: none; padding: 0.6rem 1.4rem; border-radius: 10px; font-weight: 700; font-size: 0.85rem; cursor: pointer; font-family: inherit; display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none; transition: transform 0.2s, box-shadow 0.3s; }
        .add-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(234,179,8,0.3); }

        /* Confirm modal */
        .confirm-overlay {
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.75);
            backdrop-filter: blur(6px);
            z-index: 9000;
            display: flex; align-items: center; justify-content: center;
            opacity: 0; pointer-events: none;
            transition: opacity 0.3s;
        }
        .confirm-overlay.active { opacity: 1; pointer-events: all; }
        .confirm-box {
            background: rgba(18,18,18,0.98);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px;
            padding: 2.5rem 2rem;
            max-width: 420px;
            width: 90%;
            text-align: center;
            transform: scale(0.92);
            transition: transform 0.3s;
        }
        .confirm-overlay.active .confirm-box { transform: scale(1); }
        .confirm-icon { font-size: 3rem; color: #ef4444; margin-bottom: 1rem; }
        .confirm-box h3 { margin: 0 0 0.5rem; font-size: 1.3rem; color: #fff; }
        .confirm-box p { color: rgba(255,255,255,0.5); font-size: 0.9rem; margin-bottom: 2rem; }
        .confirm-actions { display: flex; gap: 1rem; justify-content: center; }
        .c-btn-cancel { background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); color: rgba(255,255,255,0.7); padding: 0.7rem 1.6rem; border-radius: 10px; cursor: pointer; font-size: 0.9rem; font-family: inherit; transition: background 0.2s; }
        .c-btn-cancel:hover { background: rgba(255,255,255,0.1); }
        .c-btn-delete { background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.5); color: #ef4444; padding: 0.7rem 1.6rem; border-radius: 10px; cursor: pointer; font-size: 0.9rem; font-weight: 700; font-family: inherit; transition: background 0.2s, box-shadow 0.2s; display: inline-flex; align-items: center; gap: 0.5rem; }
        .c-btn-delete:hover { background: rgba(239,68,68,0.25); box-shadow: 0 4px 14px rgba(239,68,68,0.2); }

        /* Toast */
        .toast {
            position: fixed; bottom: 2rem; right: 2rem;
            background: rgba(16,185,129,0.15);
            border: 1px solid rgba(16,185,129,0.4);
            border-radius: 12px; padding: 1rem 1.5rem;
            display: flex; align-items: center; gap: 0.8rem;
            color: #10b981; font-weight: 600; font-size: 0.9rem;
            transform: translateY(100px); opacity: 0;
            transition: all 0.4s cubic-bezier(0.4,0,0.2,1);
            z-index: 9999; pointer-events: none;
        }
        .toast.show { transform: translateY(0); opacity: 1; }
        .toast.error { background: rgba(239,68,68,0.15); border-color: rgba(239,68,68,0.4); color: #ef4444; }
    </style>
</head>
<body>

<!-- Toast notification -->
<div class="toast" id="toast"><i class="fas fa-check-circle"></i><span id="toastMsg"></span></div>

<!-- Confirm Delete Modal -->
<div class="confirm-overlay" id="confirmOverlay">
    <div class="confirm-box">
        <div class="confirm-icon"><i class="fas fa-trash-alt"></i></div>
        <h3>Supprimer ce message ?</h3>
        <p>Cette action est irréversible. Le message sera définitivement supprimé de la base de données.</p>
        <div class="confirm-actions">
            <button class="c-btn-cancel" onclick="closeConfirm()">Annuler</button>
            <a class="c-btn-delete" id="confirmDeleteLink"><i class="fas fa-trash"></i> Supprimer</a>
        </div>
    </div>
</div>

    <aside class="admin-sidebar">
        <a href="dashboard.php" class="logo">e-lite<span style="color:#eab308;">.</span>
            <div style="font-size:0.8rem; letter-spacing:3px; color:rgba(255,255,255,0.4); font-family:inherit; text-transform:uppercase; font-weight:400;">BackOffice</div>
        </a>
        <ul class="admin-nav">
            <li><a href="dashboard.php"><i class="fas fa-home"></i> Vue d'ensemble</a></li>
            <li><a href="forums_list.php"><i class="fas fa-list"></i> Liste Forums</a></li>
            <li><a href="posts_list.php" class="active"><i class="fas fa-comments"></i> Liste Posts</a></li>
        </ul>
    </aside>

    <main class="admin-content">
        <header class="admin-header reveal">
            <div>
                <h1>Liste des <span class="text-gradient">Posts</span></h1>
                <p style="color: var(--light-gray); margin-top: 0.5rem;">Modérez les messages et discussions de la communauté.</p>
            </div>
        </header>

        <div class="glass-card reveal">
            <div class="filter-bar">
                <form method="GET" class="filter-form">
                    <label style="color: rgba(255,255,255,0.5); font-weight: 600; font-size: 0.85rem; white-space:nowrap;">
                        <i class="fas fa-filter" style="color:#eab308; margin-right:0.4rem;"></i> Filtrer par Forum ID
                    </label>
                    <input type="number" name="IdForum" id="forumFilterInput"
                           value="<?= htmlspecialchars($idForumFilter ?? '') ?>"
                           placeholder="Ex: 3"
                           class="filter-input">
                    <button type="submit" class="filter-btn"><i class="fas fa-search"></i> Filtrer</button>
                    <?php if ($idForumFilter): ?>
                        <a href="posts_list.php" class="reset-link"><i class="fas fa-times"></i> Réinitialiser</a>
                    <?php endif; ?>
                </form>
                <a href="post_add.php" class="add-btn"><i class="fas fa-plus"></i> Nouveau Message</a>
            </div>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID Post</th>
                        <th>Auteur</th>
                        <th>Forum</th>
                        <th>Contenu</th>
                        <th>Pièce Jointe</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($posts && $posts->rowCount() > 0): ?>
                        <?php while ($row = $posts->fetch()): ?>
                        <?php $p = array_change_key_case($row, CASE_LOWER); ?>
                            <tr>
                                <td><span style="color:#eab308; font-weight:700;">#<?= htmlspecialchars($p['idpost']) ?></span></td>
                                <td><i class="fas fa-user" style="color:var(--accent); margin-right:0.4rem;"></i><?= htmlspecialchars($p['iduser']) ?></td>
                                <td><span style="background:rgba(255,255,255,0.08); padding:0.3rem 0.7rem; border-radius:10px; font-size:0.8rem; white-space:nowrap;">Forum #<?= htmlspecialchars($p['idforum']) ?></span></td>
                                <td style="max-width:220px; word-break:break-word; color:rgba(255,255,255,0.75);"><?= nl2br(htmlspecialchars(substr($p['contenu'], 0, 80))) ?>…</td>
                                <td style="color:rgba(255,255,255,0.4); font-size:0.85rem;"><?= $p['piecejointe'] ? htmlspecialchars($p['piecejointe']) : '<span style="color:rgba(255,255,255,0.2);">—</span>' ?></td>
                                <td style="color:rgba(255,255,255,0.4); font-size:0.82rem; white-space:nowrap;"><?= htmlspecialchars($p['datepost']) ?></td>
                                <td>
                                    <a href="post_update.php?id=<?= $p['idpost'] ?>" class="action-btn" title="Éditer"><i class="fas fa-edit"></i></a>
                                    <button class="action-btn delete" title="Supprimer" onclick="askDelete(<?= $p['idpost'] ?>)"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" style="text-align:center; color:rgba(255,255,255,0.3); padding:3rem;">
                            <i class="fas fa-inbox" style="font-size:2rem; display:block; margin-bottom:0.8rem;"></i>
                            Aucun message trouvé.
                        </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

<script>
function askDelete(postId) {
    document.getElementById('confirmDeleteLink').href = 'posts_list.php?delete_post=' + postId;
    document.getElementById('confirmOverlay').classList.add('active');
}
function closeConfirm() {
    document.getElementById('confirmOverlay').classList.remove('active');
}
// Close on overlay click
document.getElementById('confirmOverlay').addEventListener('click', function(e) {
    if (e.target === this) closeConfirm();
});
// Escape key close
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeConfirm(); });

// Toast notifications
function showToast(msg, type = 'success') {
    const t = document.getElementById('toast');
    const icon = t.querySelector('i');
    t.className = 'toast' + (type === 'error' ? ' error' : '');
    icon.className = type === 'error' ? 'fas fa-times-circle' : 'fas fa-check-circle';
    document.getElementById('toastMsg').textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 3500);
}

document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    if (params.get('updated') === '1') showToast('Message mis à jour avec succès !');
    if (params.get('deleted') === '1') showToast('Message supprimé avec succès !');
});
</script>
</body>
</html>
