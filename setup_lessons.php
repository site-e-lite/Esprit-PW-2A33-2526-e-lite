<?php
require_once __DIR__ . '/config.php';
$db = Config::getInstance()->getConnexion();

$queries = [
"SET FOREIGN_KEY_CHECKS = 0",

// Lessons table — each course has N lessons
"CREATE TABLE IF NOT EXISTS `lesson` (
  `idLesson`    INT          NOT NULL AUTO_INCREMENT,
  `idCourse`    INT          NOT NULL,
  `titre`       VARCHAR(150) NOT NULL,
  `ordre`       INT          NOT NULL DEFAULT 1,
  PRIMARY KEY (`idLesson`),
  KEY `fk_lesson_course` (`idCourse`),
  CONSTRAINT `fk_lesson_course` FOREIGN KEY (`idCourse`) REFERENCES `course` (`idCourse`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

// lesson_completion — one row per (user, lesson) when completed
"CREATE TABLE IF NOT EXISTS `lesson_completion` (
  `idCompletion`  INT      NOT NULL AUTO_INCREMENT,
  `user_id`       INT      NOT NULL,
  `idLesson`      INT      NOT NULL,
  `completed_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idCompletion`),
  UNIQUE KEY `uq_user_lesson` (`user_id`, `idLesson`),
  KEY `fk_lc_lesson` (`idLesson`),
  CONSTRAINT `fk_lc_lesson` FOREIGN KEY (`idLesson`) REFERENCES `lesson` (`idLesson`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

// Drop progress_percent from progress — it is now computed, not stored
"ALTER TABLE `progress` DROP COLUMN IF EXISTS `progress_percent`",

"SET FOREIGN_KEY_CHECKS = 1",
];

$errors = [];
foreach ($queries as $sql) {
    try { $db->exec($sql); }
    catch (PDOException $e) { $errors[] = $e->getMessage(); }
}

echo '<h3>Done. Tables:</h3><ul>';
foreach ($db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) as $t) echo "<li>$t</li>";
echo '</ul>';
if ($errors) {
    echo '<h3 style="color:orange">Notes:</h3><ul>';
    foreach ($errors as $e) echo "<li>$e</li>";
    echo '</ul>';
} else {
    echo '<p style="color:green"><strong>All good!</strong></p>';
}
