<?php
/**
 * setup_certificates.php
 * Crée la table certificates dans la base e_lite.
 * À exécuter une seule fois via : http://localhost/gestioncours/setup_certificates.php
 */
require_once __DIR__ . '/config.php';

$db = Config::getInstance()->getConnexion();

// Vérifie dans quelle base on est
$currentDb = $db->query('SELECT DATABASE()')->fetchColumn();

$queries = [
    // Table certificates
    "CREATE TABLE IF NOT EXISTS `certificates` (
        `id`            INT      NOT NULL AUTO_INCREMENT,
        `user_id`       INT      NOT NULL,
        `course_id`     INT      NOT NULL,
        `date_obtained` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uq_cert_user_course` (`user_id`, `course_id`),
        CONSTRAINT `fk_cert_course`
            FOREIGN KEY (`course_id`) REFERENCES `course` (`idCourse`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
];

$results = [];
foreach ($queries as $sql) {
    try {
        $db->exec($sql);
        $results[] = ['status' => 'ok', 'msg' => 'Table certificates créée avec succès.'];
    } catch (PDOException $e) {
        // 1050 = table already exists — not an error
        if (str_contains($e->getMessage(), '1050')) {
            $results[] = ['status' => 'skip', 'msg' => 'Table certificates existe déjà.'];
        } else {
            $results[] = ['status' => 'error', 'msg' => $e->getMessage()];
        }
    }
}

// Vérifie que la table est bien accessible
$tableExists = false;
try {
    $db->query('SELECT 1 FROM certificates LIMIT 1');
    $tableExists = true;
} catch (PDOException $e) {
    $tableExists = false;
}

// Liste toutes les tables de la base
$tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Setup Certificates</title>
    <style>
        body  { font-family: monospace; background:#0d1117; color:#c9d1d9; padding:2rem; }
        h1    { color:#58a6ff; }
        .ok   { color:#3fb950; }
        .skip { color:#d29922; }
        .error{ color:#f85149; }
        ul    { line-height:2; }
        a     { color:#58a6ff; }
        .box  { background:#161b22; border:1px solid #30363d; border-radius:8px; padding:1.5rem; margin:1rem 0; }
    </style>
</head>
<body>
<h1>🔧 Setup — Table certificates</h1>

<div class="box">
    <strong>Base de données active :</strong>
    <span style="color:#f59e0b;"><?= htmlspecialchars((string)$currentDb) ?></span>
</div>

<div class="box">
    <strong>Résultat :</strong><br>
    <?php foreach ($results as $r): ?>
        <span class="<?= $r['status'] ?>">
            [<?= strtoupper($r['status']) ?>] <?= htmlspecialchars($r['msg']) ?>
        </span><br>
    <?php endforeach; ?>
</div>

<div class="box">
    <strong>Table certificates accessible :</strong>
    <?php if ($tableExists): ?>
        <span class="ok">✓ OUI — La table fonctionne correctement.</span>
    <?php else: ?>
        <span class="error">✗ NON — La table n'est pas accessible.</span>
    <?php endif; ?>
</div>

<div class="box">
    <strong>Toutes les tables dans <?= htmlspecialchars((string)$currentDb) ?> :</strong>
    <ul>
        <?php foreach ($tables as $t): ?>
            <li style="color:<?= $t === 'certificates' ? '#3fb950' : '#8b949e' ?>">
                <?= htmlspecialchars($t) ?>
                <?= $t === 'certificates' ? ' ✓' : '' ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<?php if ($tableExists): ?>
    <p>
        <a href="/gestioncours/View/FrontOffice/certificate/index.php">→ Mes certificats</a>
        &nbsp;|&nbsp;
        <a href="/gestioncours/View/BackOffice/certificate/list.php">→ BackOffice certificats</a>
        &nbsp;|&nbsp;
        <a href="/gestioncours/View/FrontOffice/course/show.php?id=1">→ Cours #1</a>
    </p>
<?php endif; ?>
</body>
</html>
