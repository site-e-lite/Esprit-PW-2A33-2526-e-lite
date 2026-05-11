<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../../../login'); exit; }

require_once __DIR__ . '/../../../Controller/VirtualClass/VirtualClassController.php';

$controller = new VirtualClassController();
$error = null; $success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_virtualclass') {
    try { $controller->deleteVirtualClass((int) $_POST['idClass']); $success = 'Classe virtuelle supprimée.'; }
    catch (Throwable $e) { $error = $e->getMessage(); }
}
if (isset($_GET['error']))   $error   = $_GET['error'];
if (isset($_GET['success'])) $success = $_GET['success'];

$virtualClasses = $controller->afficherVirtualClasses();

$bp = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
// Compute basePath to project root (3 levels up from View/VirtualClass/BackOffice)
$_pr = realpath(__DIR__ . '/../../..'); $_dr = realpath($_SERVER['DOCUMENT_ROOT']);
$basePath = rtrim(str_replace('\\', '/', substr($_pr, strlen($_dr))), '/');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BackOffice | Classes Virtuelles</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $basePath ?>/View/assets/User/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Reset global header interference */
        header { display: none !important; }
        main   { padding-top: 0 !important; margin-top: 0 !important; }

        html, body { height: 100%; margin: 0; padding: 0; }
        body {
            display: flex;
            flex-direction: row !important;
            height: 100vh;
            overflow: hidden;
            background: var(--dark-bg);
        }

        /* ── Sidebar (LEFT) ── */
        .sidebar {
            width: 240px;
            min-width: 240px;
            flex-shrink: 0;
            background: rgba(5,5,5,0.9);
            border-right: 1px solid var(--glass-border);
            padding: 1.5rem 1rem;
            display: flex;
            flex-direction: column;
            gap: .8rem;
            height: 100vh;
            overflow-y: auto;
            position: relative !important;
            top: auto !important;
            left: auto !important;
            z-index: 10;
        }
        .sidebar .logo { font-size: 1.6rem; font-weight: 800; color: var(--text-main); text-decoration: none; display: block; margin-bottom: .5rem; }
        .sidebar .logo span { color: var(--accent); }
        .sidebar .nav-label { font-size: .7rem; text-transform: uppercase; letter-spacing: 1px; color: var(--light-gray); padding: .5rem 1rem 0; }
        .sidebar .nav-links { display: flex; flex-direction: column; gap: .4rem; }
        .sidebar .nav-links a {
            display: flex; align-items: center; gap: .75rem;
            padding: .75rem 1rem; border-radius: 10px;
            color: var(--light-gray); text-decoration: none;
            font-size: .9rem; font-weight: 500;
            transition: all .2s;
        }
        .sidebar .nav-links a:hover,
        .sidebar .nav-links a.active { background: rgba(234,179,8,.12); color: var(--accent); }
        .sidebar .nav-links a i { width: 18px; text-align: center; }
        .sidebar .nav-bottom { margin-top: auto; border-top: 1px solid var(--glass-border); padding-top: 1rem; display: flex; flex-direction: column; gap: .4rem; }

        /* ── Main content (RIGHT of sidebar) ── */
        .main-content {
            flex: 1;
            min-width: 0;
            padding: 2rem 2.5rem;
            overflow-y: auto;
            height: 100vh;
        }

        /* ── Table ── */
        .data-table { width: 100%; border-collapse: collapse; table-layout: auto; }
        .data-table th, .data-table td { padding: .85rem 1rem; border-bottom: 1px solid var(--glass-border); text-align: left; white-space: nowrap; }
        .data-table th { color: var(--light-gray); font-size: .78rem; text-transform: uppercase; letter-spacing: .5px; }
        .data-table tbody tr:hover { background: rgba(255,255,255,.03); }
        .data-table td:nth-child(2) { white-space: normal; max-width: 220px; } /* titre wraps */

        .badge { padding: .25rem .7rem; border-radius: 20px; background: rgba(234,179,8,.14); color: var(--accent); font-size: .8rem; }
        .alert { padding: .9rem 1.1rem; border-radius: 10px; margin-bottom: 1rem; display: flex; align-items: center; gap: .7rem; }
        .alert-ok { background: rgba(16,185,129,.1); border: 1px solid rgba(16,185,129,.3); color: #10b981; }
        .alert-ko { background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.3); color: #ef4444; }

        .actions { display: flex; gap: .4rem; justify-content: flex-end; flex-wrap: nowrap; }
        .action-btn {
            width: 32px; height: 32px; border-radius: 8px;
            border: 1px solid var(--glass-border);
            display: inline-flex; align-items: center; justify-content: center;
            background: transparent; color: var(--light-gray);
            cursor: pointer; transition: all .2s; text-decoration: none;
            font-size: .82rem; flex-shrink: 0;
        }
        .action-btn:hover { border-color: var(--accent); color: var(--accent); }
        .action-btn.del:hover { border-color: #ef4444; color: #ef4444; }
        .action-btn.qr:hover  { border-color: #a78bfa; color: #a78bfa; }

        .toolbar { display: flex; gap: 1rem; align-items: center; margin-bottom: 1.2rem; flex-wrap: wrap; }
        .toolbar input {
            flex: 1; min-width: 200px;
            padding: .6rem .6rem .6rem 2.2rem;
            background: rgba(255,255,255,.04);
            border: 1px solid var(--glass-border);
            border-radius: 10px; color: inherit; font-size: .9rem; outline: none;
        }
        .toolbar input:focus { border-color: var(--accent); }
        .search-counter { font-size: .8rem; color: var(--light-gray); }
        .search-wrapper { position: relative; flex: 1; min-width: 200px; }
        .search-wrapper .search-icon { position: absolute; left: .7rem; top: 50%; transform: translateY(-50%); color: var(--light-gray); pointer-events: none; }
    </style>
</head>
<body>
<aside class="sidebar">
    <a href="<?= $basePath ?>/" class="logo">e-<span>lite</span></a>
    <div class="nav-label">BackOffice</div>
    <nav class="nav-links">
        <a href="<?= $basePath ?>/forum/manage"><i class="fas fa-comments"></i> Forum</a>
        <a href="<?= $basePath ?>/virtualclass" class="active"><i class="fas fa-play-circle"></i> Classes Virtuelles</a>
        <a href="<?= $basePath ?>/virtualclass/dashboard"><i class="fas fa-chart-line"></i> Dashboard</a>
        <a href="<?= $basePath ?>/virtualclass/sessions"><i class="fas fa-calendar-alt"></i> Séances</a>
        <a href="<?= $basePath ?>/admin/dashboard"><i class="fas fa-users"></i> Utilisateurs</a>
    </nav>
    <div class="nav-bottom">
        <a href="<?= $basePath ?>/forum" class="nav-links" style="display:flex;align-items:center;gap:.75rem;padding:.75rem 1rem;border-radius:10px;color:var(--light-gray);text-decoration:none;font-size:.9rem;"><i class="fas fa-globe"></i> Front Office</a>
        <a href="<?= $basePath ?>/logout" style="display:flex;align-items:center;gap:.75rem;padding:.75rem 1rem;border-radius:10px;color:var(--light-gray);text-decoration:none;font-size:.9rem;"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
    </div>
</aside>
<main class="main-content">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem;">
        <h1 style="margin:0;font-size:1.8rem;"><i class="fas fa-play-circle" style="color:var(--accent);"></i> Classes Virtuelles</h1>
        <div style="display:flex;flex-wrap:wrap;gap:.5rem;align-items:center;">
            <a href="<?= $basePath ?>/virtualclass/dashboard" class="btn-outline" style="padding:.6rem 1.1rem;font-size:.88rem;text-decoration:none;"><i class="fas fa-chart-line"></i> Stats &amp; export PDF</a>
            <a href="<?= $basePath ?>/virtualclass/add" class="btn-primary" style="padding:.6rem 1.2rem;font-size:.9rem;text-decoration:none;"><i class="fas fa-plus"></i> Ajouter</a>
        </div>
    </div>

    <?php if ($success): ?><div class="alert alert-ok"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="alert alert-ko"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div id="vc-stats" data-mode="virtualclass"></div>
    <div class="toolbar">
        <div class="search-wrapper" style="flex:1;min-width:200px;position:relative;">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="vc-search" placeholder="Rechercher par titre, plateforme…">
        </div>
        <span class="search-counter" id="vc-search-counter"></span>
    </div>

    <div class="glass-card" style="padding:0;overflow:hidden;">
        <table class="data-table" id="vc-table">
            <thead><tr>
                <th data-sort="0">#</th>
                <th data-sort="1">Titre</th>
                <th data-sort="2">Plateforme</th>
                <th data-sort="3">Capacité</th>
                <th data-sort="4">Lien</th>
                <th data-sort="5">Cours</th>
                <th style="text-align:right;">Actions</th>
            </tr></thead>
            <tbody>
            <?php if (empty($virtualClasses)): ?>
                <tr><td colspan="7" style="text-align:center;color:var(--light-gray);padding:2rem;">Aucune classe virtuelle.</td></tr>
            <?php else: foreach ($virtualClasses as $vc): ?>
                <tr>
                    <td>#<?= (int)$vc['idClass'] ?></td>
                    <td>
                        <strong><?= htmlspecialchars($vc['titre']) ?></strong>
                        <?php if (!empty($vc['description'])): ?>
                            <div style="font-size:.82rem;color:var(--light-gray);"><?= htmlspecialchars(mb_substr($vc['description'],0,70)) ?><?= mb_strlen($vc['description'])>70?'…':'' ?></div>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($vc['plateforme']) ?></td>
                    <td><span style="background:rgba(16,185,129,.1);color:#10b981;padding:.2rem .7rem;border-radius:20px;font-size:.82rem;font-weight:600;"><?= (int)$vc['capacite'] ?> places</span></td>
                    <td><a href="<?= htmlspecialchars($vc['lienAcces']) ?>" target="_blank" style="color:var(--accent);" rel="noopener">ouvrir <i class="fas fa-external-link-alt" style="font-size:.7rem;"></i></a></td>
                    <td><span class="badge"><?= htmlspecialchars($vc['courseTitre'] ?? '#'.(int)$vc['idCourse']) ?></span></td>
                    <td>
                        <div class="actions">
                            <a class="action-btn qr" href="<?= $basePath ?>/virtualclass/qr/<?= (int)$vc['idClass'] ?>" title="QR Code &amp; Mailing"><i class="fas fa-qrcode"></i></a>
                            <a class="action-btn" href="<?= $basePath ?>/virtualclass/edit/<?= (int)$vc['idClass'] ?>" title="Modifier"><i class="fas fa-edit"></i></a>
                            <form method="POST" data-confirm="Supprimer cette classe ? Les séances liées seront aussi supprimées." style="display:inline;">
                                <input type="hidden" name="action" value="delete_virtualclass">
                                <input type="hidden" name="idClass" value="<?= (int)$vc['idClass'] ?>">
                                <button type="submit" class="action-btn del" title="Supprimer"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</main>
<script src="<?= $basePath ?>/View/assets/Forum/virtualclass-validation.js"></script>
</body>
</html>
