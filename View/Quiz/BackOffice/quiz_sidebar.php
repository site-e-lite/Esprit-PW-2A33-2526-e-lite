<?php
// Helper: calcule le basePath pour les vues BackOffice Quiz
if (!isset($basePath)) {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $basePath = rtrim(str_replace('\\', '/', dirname(dirname(dirname(dirname($scriptName))))), '/');
    if ($basePath === '.' || $basePath === '/') $basePath = '';
}
?>
<aside class="admin-sidebar">
    <a href="<?= $basePath ?>/" class="logo" style="text-decoration:none;">
        e-lite<span>.</span>
        <div style="font-size:0.8rem; letter-spacing:3px; color:rgba(255,255,255,0.4); font-family:inherit; text-transform:uppercase; font-weight:400;">BackOffice Quiz</div>
    </a>
    <ul class="admin-nav">
        <li><a href="<?= $basePath ?>/quiz/admin" <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/quiz/admin') !== false && strpos($_SERVER['REQUEST_URI'] ?? '', '/quiz/admin/') === false) ? 'class="active"' : '' ?>><i class="fas fa-tasks"></i> Liste des Quiz</a></li>
        <li><a href="<?= $basePath ?>/quiz/admin/ajouter" <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/quiz/admin/ajouter') !== false) ? 'class="active"' : '' ?>><i class="fas fa-plus-circle"></i> Ajouter un Quiz</a></li>
        <li><a href="<?= $basePath ?>/quiz/admin/generer" <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/quiz/admin/generer') !== false) ? 'class="active"' : '' ?>><i class="fas fa-wand-magic-sparkles"></i> Générer un Quiz</a></li>
        <li><a href="<?= $basePath ?>/quiz/admin/verrous" <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/quiz/admin/verrous') !== false) ? 'class="active"' : '' ?>><i class="fas fa-lock"></i> Verrous Quiz</a></li>
        <li><a href="<?= $basePath ?>/quiz/admin/export" <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/quiz/admin/export') !== false) ? 'class="active"' : '' ?>><i class="fas fa-file-export"></i> Exporter Résultats</a></li>
        <li><a href="<?= $basePath ?>/quiz/admin/questions" <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/quiz/admin/questions') !== false && strpos($_SERVER['REQUEST_URI'] ?? '', '/quiz/admin/question') === false) ? 'class="active"' : '' ?>><i class="fas fa-question-circle"></i> Liste des Questions</a></li>
        <li><a href="<?= $basePath ?>/quiz/admin/question/ajouter" <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/quiz/admin/question/ajouter') !== false || strpos($_SERVER['REQUEST_URI'] ?? '', '/quiz/admin/questions/ajouter') !== false) ? 'class="active"' : '' ?>><i class="fas fa-plus-square"></i> Ajouter une Question</a></li>
        <li style="margin-top:auto;"><a href="<?= $basePath ?>/" style="color:rgba(255,255,255,0.5);"><i class="fas fa-arrow-left"></i> Retour au site</a></li>
    </ul>
</aside>
