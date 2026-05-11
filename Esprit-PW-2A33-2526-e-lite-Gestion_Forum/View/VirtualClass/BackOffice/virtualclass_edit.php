<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../../../login'); exit; }

require_once __DIR__ . '/../../../config.php';
require_once __DIR__ . '/../../../Controller/VirtualClass/VirtualClassController.php';
require_once __DIR__ . '/../../../Model/VirtualClass/VirtualClass.php';

$db = Config::getConnexion();
$controller = new VirtualClassController();

$_pr = realpath(__DIR__ . '/../../..'); $_dr = realpath($_SERVER['DOCUMENT_ROOT']);
$basePath = rtrim(str_replace('\\', '/', substr($_pr, strlen($_dr))), '/');

// Get ID from URL segment or GET param
$uriParts = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
$id = (int) end($uriParts);
if ($id <= 0 && isset($_GET['id'])) $id = (int) $_GET['id'];
if ($id <= 0) { header('Location: '.$basePath.'/virtualclass?error=ID+invalide'); exit; }

$courses = $db->query('SELECT idCourse, titre FROM course ORDER BY idCourse DESC')->fetchAll();
$error = null;
$vc = $controller->getVirtualClassById($id);
if (!$vc) { header('Location: '.$basePath.'/virtualclass?error=Classe+introuvable'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $updated = new VirtualClass($id, trim($_POST['titre']??''), trim($_POST['description']??''), trim($_POST['lienAcces']??''), trim($_POST['plateforme']??''), (int)($_POST['capacite']??30), (int)($_POST['idCourse']??0));
        $controller->updateVirtualClass($updated, $id);
        header('Location: '.$basePath.'/virtualclass?success=Classe+mise+à+jour'); exit;
    } catch (Throwable $e) { $error = $e->getMessage(); }
}

$titreV = $_POST['titre'] ?? $vc->getTitre();
$descV  = $_POST['description'] ?? $vc->getDescription();
$lienV  = $_POST['lienAcces'] ?? $vc->getLienAcces();
$platV  = $_POST['plateforme'] ?? $vc->getPlateforme();
$capV   = $_POST['capacite'] ?? $vc->getCapacite();
$crsV   = $_POST['idCourse'] ?? $vc->getIdCourse();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Classe Virtuelle #<?= $id ?></title>
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
        .main-content{flex:1;min-width:0;padding:2rem 2.5rem;overflow-y:auto;height:100vh}
        .alert{padding:.9rem 1.1rem;border-radius:10px;margin-bottom:1rem;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#ef4444;display:flex;align-items:center;gap:.7rem}
    </style>
</head>
<body>
<aside class="sidebar">
    <a href="<?= $basePath ?>/" class="logo">e-<span>lite</span></a>
    <nav class="nav-links">
        <a href="<?= $basePath ?>/virtualclass" class="active"><i class="fas fa-play-circle"></i> Classes Virtuelles</a>
        <a href="<?= $basePath ?>/virtualclass/sessions"><i class="fas fa-calendar-alt"></i> Séances</a>
        <a href="<?= $basePath ?>/forum/manage"><i class="fas fa-comments"></i> Forum</a>
    </nav>
</aside>
<main class="main-content">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
        <h1 style="margin:0;font-size:1.8rem;"><i class="fas fa-edit" style="color:var(--accent);"></i> Modifier Classe <span style="color:var(--accent);">#<?= $id ?></span></h1>
        <a href="<?= $basePath ?>/virtualclass" class="btn-outline" style="padding:.5rem 1rem;font-size:.9rem;"><i class="fas fa-arrow-left"></i> Retour</a>
    </div>
    <?php if ($error): ?><div class="alert"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>
    <div class="glass-card">
        <form id="virtualClassForm" method="POST" class="glass-form" novalidate>
            <div class="form-grid">
                <div class="form-group full-width">
                    <label>Titre *</label>
                    <input type="text" name="titre" id="vc_titre" value="<?= htmlspecialchars($titreV) ?>">
                </div>
                <div class="form-group full-width">
                    <label>Description</label>
                    <textarea name="description" id="vc_description" rows="3"><?= htmlspecialchars($descV) ?></textarea>
                </div>
                <div class="form-group full-width">
                    <label>Lien d'accès *</label>
                    <input type="text" name="lienAcces" id="vc_lienAcces" value="<?= htmlspecialchars($lienV) ?>">
                </div>
                <div class="form-group">
                    <label>Plateforme *</label>
                    <select name="plateforme" id="vc_plateforme">
                        <option value="">-- Choisir --</option>
                        <?php foreach(['Zoom','Meet','Teams','Autre'] as $p): ?>
                            <option value="<?= $p ?>" <?= $platV===$p?'selected':'' ?>><?= $p==='Meet'?'Google Meet':($p==='Teams'?'Microsoft Teams':$p) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Capacité *</label>
                    <input type="number" min="1" max="5000" name="capacite" id="vc_capacite" value="<?= htmlspecialchars((string)$capV) ?>">
                </div>
                <div class="form-group">
                    <label>Cours associé</label>
                    <select name="idCourse" id="vc_idCourse">
                        <option value="">-- Aucun --</option>
                        <?php foreach($courses as $c): ?>
                            <option value="<?= (int)$c['idCourse'] ?>" <?= (string)$crsV===(string)$c['idCourse']?'selected':'' ?>>#<?= (int)$c['idCourse'] ?> — <?= htmlspecialchars($c['titre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn-primary full-width" style="margin-top:1.4rem;"><i class="fas fa-save"></i> Mettre à jour</button>
        </form>
    </div>
</main>
<script src="<?= $basePath ?>/View/assets/Forum/virtualclass-validation.js"></script>
</body>
</html>
