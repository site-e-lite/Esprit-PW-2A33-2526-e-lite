<?php
/**
 * Script de correction automatique de la base de données
 * Accéder via: http://localhost/7ashwa/Esprit-PW-2A33-2526-e-lite-Gestion_Forum/fix_db.php
 */
require_once __DIR__ . '/config.php';

$db = Config::getConnexion();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$results = [];

function runSQL($db, $label, $sql) {
    global $results;
    try {
        $db->exec($sql);
        $results[] = ['ok', $label];
    } catch (Exception $e) {
        $results[] = ['err', $label . ' → ' . $e->getMessage()];
    }
}

function columnExists($db, $table, $column) {
    try {
        $q = $db->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
        return $q && $q->fetch() !== false;
    } catch (Exception $e) {
        return false;
    }
}

function tableExists($db, $table) {
    try {
        $q = $db->query("SHOW TABLES LIKE '$table'");
        return $q && $q->fetch() !== false;
    } catch (Exception $e) {
        return false;
    }
}

// ── 1. Créer la table quiz si elle n'existe pas ──────────────────────────────
runSQL($db, 'Créer table quiz', "
    CREATE TABLE IF NOT EXISTS quiz (
        idQuiz INT AUTO_INCREMENT PRIMARY KEY,
        titre VARCHAR(255) NOT NULL,
        duree INT NOT NULL DEFAULT 20,
        seuilReussite INT NOT NULL DEFAULT 60,
        niveau VARCHAR(50) NOT NULL DEFAULT 'Débutant',
        statut VARCHAR(20) NOT NULL DEFAULT 'Actif',
        idCourse INT NULL,
        CONSTRAINT fk_quiz_course
            FOREIGN KEY (idCourse) REFERENCES course(idCourse)
            ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

// ── 2. Créer la table question si elle n'existe pas ──────────────────────────
runSQL($db, 'Créer table question', "
    CREATE TABLE IF NOT EXISTS question (
        idQuestion INT AUTO_INCREMENT PRIMARY KEY,
        enonce TEXT NOT NULL,
        type VARCHAR(20) NOT NULL DEFAULT 'QCU',
        choixA VARCHAR(500) NULL,
        choixB VARCHAR(500) NULL,
        choixC VARCHAR(500) NULL,
        choixD VARCHAR(500) NULL,
        reponses_json LONGTEXT NULL,
        bonneReponse VARCHAR(500) NOT NULL,
        note INT NOT NULL DEFAULT 1,
        explication TEXT NULL,
        idQuiz INT NULL,
        niveau VARCHAR(50) NOT NULL DEFAULT 'Débutant',
        CONSTRAINT fk_question_quiz
            FOREIGN KEY (idQuiz) REFERENCES quiz(idQuiz)
            ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

// ── 3. Ajouter les colonnes manquantes à question si la table existait déjà ──
$questionCols = [
    'choixA'       => "ALTER TABLE question ADD COLUMN choixA VARCHAR(500) NULL AFTER type",
    'choixB'       => "ALTER TABLE question ADD COLUMN choixB VARCHAR(500) NULL AFTER choixA",
    'choixC'       => "ALTER TABLE question ADD COLUMN choixC VARCHAR(500) NULL AFTER choixB",
    'choixD'       => "ALTER TABLE question ADD COLUMN choixD VARCHAR(500) NULL AFTER choixC",
    'reponses_json'=> "ALTER TABLE question ADD COLUMN reponses_json LONGTEXT NULL AFTER choixD",
    'explication'  => "ALTER TABLE question ADD COLUMN explication TEXT NULL AFTER note",
    'idQuiz'       => "ALTER TABLE question ADD COLUMN idQuiz INT NULL AFTER explication",
    'niveau'       => "ALTER TABLE question ADD COLUMN niveau VARCHAR(50) NOT NULL DEFAULT 'Débutant' AFTER idQuiz",
    'bonneReponse' => "ALTER TABLE question ADD COLUMN bonneReponse VARCHAR(500) NOT NULL DEFAULT '' AFTER note",
    'note'         => "ALTER TABLE question ADD COLUMN note INT NOT NULL DEFAULT 1 AFTER enonce",
    'type'         => "ALTER TABLE question ADD COLUMN type VARCHAR(20) NOT NULL DEFAULT 'QCU' AFTER enonce",
];

foreach ($questionCols as $col => $sql) {
    if (!columnExists($db, 'question', $col)) {
        runSQL($db, "Ajouter colonne question.$col", $sql);
    } else {
        $results[] = ['ok', "Colonne question.$col déjà présente"];
    }
}

// ── 4. Ajouter FK idQuiz sur question si elle n'existe pas ───────────────────
try {
    $fkCheck = $db->query("
        SELECT COUNT(*) as cnt FROM information_schema.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'question'
          AND COLUMN_NAME = 'idQuiz'
          AND REFERENCED_TABLE_NAME = 'quiz'
    ")->fetch();
    if (intval($fkCheck['cnt']) === 0) {
        // Vérifier si la colonne idQuiz existe avant d'ajouter la FK
        if (columnExists($db, 'question', 'idQuiz')) {
            runSQL($db, 'Ajouter FK question.idQuiz → quiz', "
                ALTER TABLE question
                ADD CONSTRAINT fk_question_quiz
                FOREIGN KEY (idQuiz) REFERENCES quiz(idQuiz)
                ON DELETE SET NULL ON UPDATE CASCADE
            ");
        }
    } else {
        $results[] = ['ok', 'FK question.idQuiz déjà présente'];
    }
} catch (Exception $e) {
    $results[] = ['warn', 'Vérification FK question: ' . $e->getMessage()];
}

// ── 5. Créer quiz_result ─────────────────────────────────────────────────────
runSQL($db, 'Créer table quiz_result', "
    CREATE TABLE IF NOT EXISTS quiz_result (
        idResult INT AUTO_INCREMENT PRIMARY KEY,
        idQuiz INT NOT NULL,
        idUser INT NULL,
        score DECIMAL(10,2) NOT NULL DEFAULT 0,
        totalPoints DECIMAL(10,2) NOT NULL DEFAULT 0,
        pourcentage DECIMAL(5,2) NOT NULL DEFAULT 0,
        statut VARCHAR(20) NOT NULL DEFAULT 'echoue',
        tabSwitchCount INT NOT NULL DEFAULT 0,
        inactivityTime INT NOT NULL DEFAULT 0,
        fastAnswerFlag TINYINT(1) NOT NULL DEFAULT 0,
        datePassage DATETIME DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_result_quiz
            FOREIGN KEY (idQuiz) REFERENCES quiz(idQuiz)
            ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT fk_result_user
            FOREIGN KEY (idUser) REFERENCES user(idUser)
            ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

// ── 6. Créer quiz_lock ───────────────────────────────────────────────────────
runSQL($db, 'Créer table quiz_lock', "
    CREATE TABLE IF NOT EXISTS quiz_lock (
        idLock INT AUTO_INCREMENT PRIMARY KEY,
        idQuiz INT NOT NULL,
        idUser INT NULL,
        sessionKey VARCHAR(128) NOT NULL,
        reason VARCHAR(255) NULL,
        isLocked TINYINT(1) NOT NULL DEFAULT 1,
        lockedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
        unlockedAt DATETIME NULL,
        unlockedBy VARCHAR(100) NULL,
        CONSTRAINT fk_lock_quiz
            FOREIGN KEY (idQuiz) REFERENCES quiz(idQuiz)
            ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

// ── 7. Vérification finale ───────────────────────────────────────────────────
$tables = ['quiz', 'question', 'quiz_result', 'quiz_lock'];
foreach ($tables as $t) {
    if (tableExists($db, $t)) {
        $results[] = ['ok', "Table '$t' ✓ présente dans la base"];
    } else {
        $results[] = ['err', "Table '$t' MANQUANTE !"];
    }
}

$hasErrors = array_filter($results, fn($r) => $r[0] === 'err');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fix DB | e-lite</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #050505; color: #f4f4f4; padding: 2rem; max-width: 900px; margin: 0 auto; }
        h1 { color: #eab308; margin-bottom: 0.5rem; }
        p.sub { color: rgba(255,255,255,0.5); margin-bottom: 2rem; }
        .item { display: flex; align-items: center; gap: 0.8rem; padding: 0.6rem 1rem; border-radius: 8px; margin-bottom: 0.4rem; font-size: 0.95rem; }
        .item.ok  { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); color: #10b981; }
        .item.err { background: rgba(239,68,68,0.1);  border: 1px solid rgba(239,68,68,0.3);  color: #ef4444; }
        .item.warn{ background: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.3); color: #f59e0b; }
        .done { background: rgba(16,185,129,0.12); border: 1px solid #10b981; padding: 1.5rem; border-radius: 12px; margin-top: 2rem; }
        .fail { background: rgba(239,68,68,0.12);  border: 1px solid #ef4444;  padding: 1.5rem; border-radius: 12px; margin-top: 2rem; }
        a { color: #eab308; font-weight: 600; }
        .icon { font-size: 1.2rem; }
    </style>
</head>
<body>
    <h1>🔧 Correction automatique de la base de données</h1>
    <p class="sub">Création et mise à jour des tables quiz, question, quiz_result, quiz_lock</p>

    <?php foreach ($results as [$type, $msg]): ?>
        <div class="item <?= $type ?>">
            <span class="icon"><?= $type === 'ok' ? '✅' : ($type === 'err' ? '❌' : '⚠️') ?></span>
            <?= htmlspecialchars($msg) ?>
        </div>
    <?php endforeach; ?>

    <?php if (empty($hasErrors)): ?>
        <div class="done">
            <strong>✅ Base de données corrigée avec succès !</strong><br><br>
            Vous pouvez maintenant accéder à :
            <ul style="margin-top:0.8rem; line-height:2;">
                <li><a href="/7ashwa/Esprit-PW-2A33-2526-e-lite-Gestion_Forum/quiz">🎯 Front-office Quiz</a></li>
                <li><a href="/7ashwa/Esprit-PW-2A33-2526-e-lite-Gestion_Forum/quiz/admin">⚙️ Back-office Quiz (admin)</a></li>
                <li><a href="/7ashwa/Esprit-PW-2A33-2526-e-lite-Gestion_Forum/quiz/admin/questions">📋 Liste des Questions</a></li>
            </ul>
        </div>
    <?php else: ?>
        <div class="fail">
            <strong>❌ Des erreurs sont survenues.</strong> Vérifiez les messages ci-dessus.
        </div>
    <?php endif; ?>
</body>
</html>
