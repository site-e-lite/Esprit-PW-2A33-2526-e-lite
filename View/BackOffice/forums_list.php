<?php
require_once __DIR__ . '/../../Controller/ForumController.php';
$forumController = new ForumController();

// Handle Delete
if (isset($_GET['delete_forum'])) {
    $forumController->deleteForum($_GET['delete_forum']);
    header('Location: forums_list.php?deleted=1');
    exit;
}

$forums = $forumController->afficherForums();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Forums | e-lite BackOffice</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { display: flex; background-color: var(--black); margin: 0; overflow-x: hidden; }
        #front-header { display: none; }
        .admin-sidebar { width: 280px; height: 100vh; background: rgba(10, 10, 10, 0.95); border-right: 1px solid var(--glass-border); position: fixed; top: 0; left: 0; display: flex; flex-direction: column; padding: 2rem 1.5rem; z-index: 100; }
        .admin-sidebar .logo { font-size: 2rem; margin-bottom: 3rem; text-align: center; }
        .admin-nav { display: flex; flex-direction: column; gap: 1rem; list-style: none; padding: 0; margin: 0;}
        .admin-nav li a { display: flex; align-items: center; gap: 1rem; color: var(--light-gray); text-decoration: none; padding: 1rem 1.5rem; border-radius: 12px; font-weight: 500; transition: all 0.3s; }
        .admin-nav li a i { font-size: 1.2rem; width: 20px; text-align: center;}
        .admin-nav li a:hover, .admin-nav li a.active { background: rgba(234, 179, 8, 0.1); color: var(--accent); transform: translateX(5px); box-shadow: inset 2px 0 0 var(--accent); }
        .logout-btn { margin-top: auto; color: #ef4444 !important; }
        .logout-btn:hover { background: rgba(239, 68, 68, 0.1) !important; box-shadow: inset 2px 0 0 #ef4444 !important;}
        .admin-content { margin-left: 280px; flex: 1; padding: 2.5rem 4rem; min-height: 100vh; background: radial-gradient(circle at top right, rgba(234, 179, 8, 0.05) 0%, transparent 40%); }
        .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 3rem; position: relative; background: transparent; backdrop-filter: none; padding: 0; border: none; }
        .admin-header h1 { font-size: 2.5rem; margin: 0; }
        .admin-profile { display: flex; align-items: center; gap: 1rem; }
        .admin-profile img { width: 50px; height: 50px; border-radius: 50%; border: 2px solid var(--accent); }
        .admin-table { width: 100%; border-collapse: collapse; margin-top: 1.5rem; color: var(--text-main); }
        .admin-table th, .admin-table td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--glass-border); }
        .admin-table th { color: var(--light-gray); font-weight: 600; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px; }
        .admin-table tbody tr { transition: background 0.3s; }
        .admin-table tbody tr:hover { background: rgba(255, 255, 255, 0.03); }
        .action-btn { background: none; border: none; color: var(--light-gray); cursor: pointer; transition: color 0.3s; font-size: 1.1rem; margin-right: 0.8rem; text-decoration: none;}
        .action-btn:hover { color: var(--accent); }
        .action-btn.delete:hover { color: #ef4444; }
        /* Confirm modal */
        .confirm-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.75); backdrop-filter: blur(6px); z-index: 9000; display: flex; align-items: center; justify-content: center; opacity: 0; pointer-events: none; transition: opacity 0.3s; }
        .confirm-overlay.active { opacity: 1; pointer-events: all; }
        .confirm-box { background: rgba(18,18,18,0.98); border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; padding: 2.5rem 2rem; max-width: 420px; width: 90%; text-align: center; transform: scale(0.92); transition: transform 0.3s; }
        .confirm-overlay.active .confirm-box { transform: scale(1); }
        .confirm-icon { font-size: 3rem; color: #ef4444; margin-bottom: 1rem; }
        .confirm-box h3 { margin: 0 0 0.5rem; font-size: 1.3rem; color: #fff; }
        .confirm-box p { color: rgba(255,255,255,0.5); font-size: 0.9rem; margin-bottom: 2rem; }
        .confirm-actions { display: flex; gap: 1rem; justify-content: center; }
        .c-btn-cancel { background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); color: rgba(255,255,255,0.7); padding: 0.7rem 1.6rem; border-radius: 10px; cursor: pointer; font-size: 0.9rem; font-family: inherit; transition: background 0.2s; }
        .c-btn-cancel:hover { background: rgba(255,255,255,0.1); }
        .c-btn-delete { background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.5); color: #ef4444; padding: 0.7rem 1.6rem; border-radius: 10px; cursor: pointer; font-size: 0.9rem; font-weight: 700; font-family: inherit; transition: background 0.2s; display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none; }
        .c-btn-delete:hover { background: rgba(239,68,68,0.25); }
        /* Toast */
        .toast { position: fixed; bottom: 2rem; right: 2rem; background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.4); border-radius: 12px; padding: 1rem 1.5rem; display: flex; align-items: center; gap: 0.8rem; color: #10b981; font-weight: 600; font-size: 0.9rem; transform: translateY(100px); opacity: 0; transition: all 0.4s cubic-bezier(0.4,0,0.2,1); z-index: 9999; pointer-events: none; }
        .toast.show { transform: translateY(0); opacity: 1; }
    </style>
</head>
<body>

<!-- Toast -->
<div class="toast" id="toast"><i class="fas fa-check-circle"></i><span id="toastMsg"></span></div>

<!-- Confirm Delete Modal -->
<div class="confirm-overlay" id="confirmOverlay">
    <div class="confirm-box">
        <div class="confirm-icon"><i class="fas fa-trash-alt"></i></div>
        <h3>Supprimer ce forum ?</h3>
        <p>Cette action est irréversible. Le forum et ses données seront définitivement supprimés.</p>
        <div class="confirm-actions">
            <button class="c-btn-cancel" onclick="closeConfirm()">Annuler</button>
            <a class="c-btn-delete" id="confirmDeleteLink"><i class="fas fa-trash"></i> Supprimer</a>
        </div>
    </div>
</div>

    <aside class="admin-sidebar">
        <a href="dashboard.php" class="logo">e-lite<span>.</span><div style="font-size:0.8rem; letter-spacing:3px; color:var(--light-gray); font-family:var(--font-main);text-transform:uppercase;">BackOffice</div></a>
        <ul class="admin-nav">
            <li><a href="dashboard.php"><i class="fas fa-home"></i> Vue d'ensemble</a></li>
            <li><a href="forums_list.php" class="active"><i class="fas fa-list"></i> Liste Forums</a></li>
            <li><a href="posts_list.php"><i class="fas fa-comments"></i> Liste Posts</a></li>
        </ul>
    </aside>

    <main class="admin-content">
        <header class="admin-header reveal">
            <div>
                <h1>Liste des <span class="text-gradient">Forums</span></h1>
                <p style="color: var(--light-gray); margin-top: 0.5rem;">Gérez tous les sujets de discussion et catégories de la plateforme.</p>
            </div>
            <div class="admin-profile">
                <a href="forum_add.php" class="btn-primary" style="padding: 0.8rem 1.5rem; font-size: 0.9rem;"><i class="fas fa-plus"></i> Ajouter un Forum</a>
            </div>
        </header>

        <div class="glass-card reveal">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Titre</th>
                        <th>Description</th>
                        <th>Date de Création</th>
                        <th>ID Cours</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($forums->rowCount() > 0): ?>
                        <?php while ($row = $forums->fetch()): ?>
                        <?php $f = array_change_key_case($row, CASE_LOWER); ?>
                            <tr>
                                <td>#<?= htmlspecialchars($f['idforum']) ?></td>
                                <td><strong><?= htmlspecialchars($f['titre']) ?></strong></td>
                                <td><?= htmlspecialchars($f['description']) ?></td>
                                <td><?= htmlspecialchars($f['datecreation']) ?></td>
                                <td><?= $f['idcourse'] ? htmlspecialchars($f['idcourse']) : '<span style="color:rgba(255,255,255,0.3);">—</span>' ?></td>
                                <td>
                                    <a href="forum_update.php?id=<?= $f['idforum'] ?>" class="action-btn" title="Éditer"><i class="fas fa-edit"></i></a>
                                    <button class="action-btn delete" title="Supprimer" onclick="askDelete(<?= $f['idforum'] ?>)"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align: center; color: var(--light-gray);">Aucun forum trouvé.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script src="../assets/index.js"></script>
    <script>
    function askDelete(forumId) {
        document.getElementById('confirmDeleteLink').href = 'forums_list.php?delete_forum=' + forumId;
        document.getElementById('confirmOverlay').classList.add('active');
    }
    function closeConfirm() {
        document.getElementById('confirmOverlay').classList.remove('active');
    }
    document.getElementById('confirmOverlay').addEventListener('click', function(e) {
        if (e.target === this) closeConfirm();
    });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeConfirm(); });

    function showToast(msg, type = 'success') {
        const t = document.getElementById('toast');
        t.className = 'toast' + (type === 'error' ? ' error' : '');
        t.querySelector('i').className = type === 'error' ? 'fas fa-times-circle' : 'fas fa-check-circle';
        document.getElementById('toastMsg').textContent = msg;
        t.classList.add('show');
        setTimeout(() => t.classList.remove('show'), 3500);
    }
    document.addEventListener('DOMContentLoaded', () => {
        const p = new URLSearchParams(window.location.search);
        if (p.get('added')   === '1') showToast('Forum créé avec succès !');
        if (p.get('updated') === '1') showToast('Forum mis à jour avec succès !');
        if (p.get('deleted') === '1') showToast('Forum supprimé avec succès !');
    });
    </script>
</body>
</html>
