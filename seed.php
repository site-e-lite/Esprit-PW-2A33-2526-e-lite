<?php
require_once __DIR__ . '/config.php';
$db = Config::getInstance()->getConnexion();

$queries = [
    "INSERT INTO course (titre, description, niveau, duree, statut, langue, prix)
     VALUES ('PHP MVC Avancé', 'Maîtrisez le pattern MVC avec PHP et PDO.', 'avance', 20, 'publie', 'Français', 49.90)",

    "INSERT INTO lesson (idCourse, titre, ordre)
     SELECT c.idCourse, l.titre, l.ordre
     FROM course c
     CROSS JOIN (
         SELECT 'Introduction au MVC'       AS titre, 1 AS ordre UNION ALL
         SELECT 'Connexion PDO',                              2   UNION ALL
         SELECT 'Controllers et routing',                    3   UNION ALL
         SELECT 'Vues et templates',                         4   UNION ALL
         SELECT 'Projet final',                              5
     ) l
     WHERE c.titre = 'PHP MVC Avancé'
     LIMIT 5",
];

foreach ($queries as $sql) {
    try { $db->exec($sql); echo "<p style='color:green'>OK: " . htmlspecialchars(substr($sql, 0, 60)) . "...</p>"; }
    catch (PDOException $e) { echo "<p style='color:orange'>Skip: " . $e->getMessage() . "</p>"; }
}

$courses = $db->query('SELECT idCourse, titre, statut FROM course')->fetchAll();
echo '<h3>Courses:</h3><ul>';
foreach ($courses as $c) echo "<li>[{$c['idCourse']}] {$c['titre']} ({$c['statut']})</li>";
echo '</ul>';

$lessons = $db->query('SELECT l.idLesson, l.titre, l.idCourse FROM lesson l')->fetchAll();
echo '<h3>Lessons:</h3><ul>';
foreach ($lessons as $l) echo "<li>[course {$l['idCourse']}] {$l['titre']}</li>";
echo '</ul>';

echo '<br><a href="/gestioncours/View/FrontOffice/course/show.php?id=1">→ Open course page</a>';
