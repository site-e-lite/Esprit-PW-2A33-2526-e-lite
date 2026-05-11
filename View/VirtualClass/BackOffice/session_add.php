<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../../../login'); exit; }

require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../Controller/VirtualClass/SessionController.php';
require_once __DIR__ . '/../../../Model/VirtualClass/SessionClass.php';

$db = Config::getConnexion();
$controller = new SessionController();
$classes = $db->query('SELECT idClass, titre FROM virtualclass ORDER BY idClass DESC')->fetchAll();
$error = null;

$_pr = realpath(__DIR__ . '/../../..'); $_dr = realpath($_SERVER['DOCUMENT_ROOT']);
$basePath = rtrim(str_replace('\\', '/', substr($_pr, strlen($_dr))), '/');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $s = new SessionClass(null, trim($_POST['dateSession']??''), trim($_POST['heureDebut']??''), trim($_POST['heureFin']??''), trim($_POST['statut']??''), (int)($_POST['idClass']??0));
        $controller->addSession($s);
        header('Location: '.$basePath.'/virtualclass/sessions?success=Séance+ajoutée'); exit;
    } catch (Throwable $e) { $error = $e->getMessage(); }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter Séance</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $basePath ?>/View/assets/User/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        header{display:none!important}
        main{padding-top:0!important;margin-top:0!important}
        html,body{height:100%;margin:0;padding:0}
        body{display:flex;flex-direction:row!important;min-height:100vh;background:var(--dark-bg)}
        .sidebar{width:240px;min-width:240px;flex-shrink:0;background:rgba(5,5,5,0.9);border-right:1px solid var(--glass-border);padding:1.5rem 1rem;display:flex;flex-direction:column;gap:.8rem;height:100vh;overflow-y:auto;position:sticky;top:0;z-index:10}
        .sidebar .logo{font-size:1.6rem;font-weight:800;color:var(--text-main);text-decoration:none;display:block;margin-bottom:.5rem}
        .sidebar .logo span{color:var(--accent)}
        .sidebar .nav-links{display:flex;flex-direction:column;gap:.4rem}
        .sidebar .nav-links a{display:flex;align-items:center;gap:.75rem;padding:.75rem 1rem;border-radius:10px;color:var(--light-gray);text-decoration:none;font-size:.9rem;font-weight:500;transition:all .2s}
        .sidebar .nav-links a:hover,.sidebar .nav-links a.active{background:rgba(234,179,8,.12);color:var(--accent)}
        .main-content{flex:1;min-width:0;padding:2rem 2.5rem}
        .alert{padding:.9rem 1.1rem;border-radius:10px;margin-bottom:1rem;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#ef4444;display:flex;align-items:center;gap:.7rem}
    </style>
</head>
<body>
<aside class="sidebar">
    <a href="<?= $basePath ?>/" class="logo">e-<span>lite</span></a>
    <nav class="nav-links">
        <a href="<?= $basePath ?>/virtualclass"><i class="fas fa-play-circle"></i> Classes Virtuelles</a>
        <a href="<?= $basePath ?>/virtualclass/sessions" class="active"><i class="fas fa-calendar-alt"></i> Séances</a>
        <a href="<?= $basePath ?>/forum/manage"><i class="fas fa-comments"></i> Forum</a>
    </nav>
</aside>
<main class="main-content">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
        <h1 style="margin:0;font-size:1.8rem;"><i class="fas fa-plus-circle" style="color:var(--accent);"></i> Ajouter une Séance</h1>
        <a href="<?= $basePath ?>/virtualclass/sessions" class="btn-outline" style="padding:.5rem 1rem;font-size:.9rem;"><i class="fas fa-arrow-left"></i> Retour</a>
    </div>
    <?php if ($error): ?><div class="alert"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>
    <div class="glass-card">
        <form id="sessionForm" method="POST" class="glass-form" novalidate>
            <div class="form-grid">
                <div class="form-group full-width">
                    <label>Classe Virtuelle *</label>
                    <select name="idClass" id="se_idClass">
                        <option value="">-- Choisir --</option>
                        <?php foreach($classes as $c): ?>
                            <option value="<?= (int)$c['idClass'] ?>" <?= (($_POST['idClass']??'')==(string)$c['idClass'])?'selected':'' ?>>#<?= (int)$c['idClass'] ?> — <?= htmlspecialchars($c['titre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Date *</label>
                    <input type="date" name="dateSession" id="se_dateSession" value="<?= htmlspecialchars($_POST['dateSession']??'') ?>">
                </div>
                <div class="form-group">
                    <label>Statut *</label>
                    <select name="statut" id="se_statut">
                        <option value="">-- Choisir --</option>
                        <?php foreach(['planifiee','en_cours','terminee','annulee'] as $st): ?>
                            <option value="<?= $st ?>" <?= (($_POST['statut']??'')===$st)?'selected':'' ?>><?= $st ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Heure début *</label>
                    <input type="time" name="heureDebut" id="se_heureDebut" value="<?= htmlspecialchars($_POST['heureDebut']??'') ?>">
                </div>
                <div class="form-group">
                    <label>Heure fin *</label>
                    <input type="time" name="heureFin" id="se_heureFin" value="<?= htmlspecialchars($_POST['heureFin']??'') ?>">
                </div>
            </div>
            <button type="submit" class="btn-primary full-width" style="margin-top:1.2rem;"><i class="fas fa-save"></i> Enregistrer</button>
        </form>
    </div>
</main>
<script src="<?= $basePath ?>/View/assets/Forum/virtualclass-validation.js"></script>
</body>
</html>
