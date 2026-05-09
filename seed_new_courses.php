<?php
/**
 * seed_new_courses.php
 * Insère les 4 nouveaux cours (JS, Java, C, C++) avec leurs leçons.
 * À exécuter UNE SEULE FOIS via : http://localhost/gestioncours/seed_new_courses.php
 *
 * Idempotent : utilise INSERT IGNORE sur le titre pour éviter les doublons.
 */
require_once __DIR__ . '/config.php';

$db = Config::getInstance()->getConnexion();

// ─────────────────────────────────────────────────────────────
// Données des 4 cours
// Colonnes réelles de la table `course` :
//   idCourse, titre, description, niveau, duree, statut, langue, prix, image, objectifs, prerequis
// ─────────────────────────────────────────────────────────────
$courses = [
    [
        'titre'       => 'JavaScript Moderne (ES6+)',
        'description' => 'Maîtrisez JavaScript avec ES6+, Promises, Async/Await, et manipulation du DOM. Ce cours complet vous apprendra JavaScript des bases aux concepts modernes pour créer des applications web dynamiques et réactives.',
        'niveau'      => 'intermediaire',
        'duree'       => 25,
        'statut'      => 'publie',
        'langue'      => 'Français',
        'prix'        => 59.90,
        'image'       => '/gestioncours/View/assets/images/courses/js.svg',
        'objectifs'   => 'Maîtriser ES6+, async/await, manipulation du DOM et créer une application météo complète',
        'prerequis'   => 'Bases de HTML et CSS',
        'lessons'     => [
            'Les bases : variables, fonctions et objets',
            'ES6+ : let/const, arrow functions, template literals, destructuring',
            'Asynchrone : Promises, Async/Await et Fetch API',
            'Manipulation du DOM',
            'Projet final : Application météo',
        ],
    ],
    [
        'titre'       => 'Java - POO & Collections',
        'description' => 'Apprenez Java de zéro : POO, Collections Framework, Gestion d\'exceptions. Un parcours complet du débutant à l\'intermédiaire pour maîtriser la programmation orientée objet avec Java.',
        'niveau'      => 'debutant',
        'duree'       => 30,
        'statut'      => 'publie',
        'langue'      => 'Français',
        'prix'        => 69.90,
        'image'       => '/gestioncours/View/assets/images/courses/java.svg',
        'objectifs'   => 'Maîtriser la POO Java, les Collections Framework et créer un système de gestion de bibliothèque',
        'prerequis'   => 'Aucun prérequis — cours pour débutants',
        'lessons'     => [
            'Syntaxe de base et types de données',
            'Classes et Objets : héritage, polymorphisme, encapsulation',
            'Collections : List, Set, Map',
            'Gestion des exceptions',
            'Fichiers et I/O',
            'Projet : Système de gestion de bibliothèque',
        ],
    ],
    [
        'titre'       => 'Langage C - Fondamentaux',
        'description' => 'Pointeurs, gestion mémoire, et programmation système avec le langage C. Maîtrisez les fondamentaux du C, langage incontournable pour comprendre le fonctionnement des systèmes informatiques.',
        'niveau'      => 'debutant',
        'duree'       => 20,
        'statut'      => 'publie',
        'langue'      => 'Français',
        'prix'        => 49.90,
        'image'       => '/gestioncours/View/assets/images/courses/c_lang.svg',
        'objectifs'   => 'Maîtriser les pointeurs, la gestion mémoire dynamique et la programmation système en C',
        'prerequis'   => 'Aucun prérequis — cours pour débutants',
        'lessons'     => [
            'Introduction et environnement de développement',
            'Variables, opérateurs et structures de contrôle',
            'Tableaux et chaînes de caractères',
            'Pointeurs : la base essentielle',
            'Allocation mémoire dynamique : malloc et free',
            'Structures et unions',
            'Fichiers en C',
        ],
    ],
    [
        'titre'       => 'C++ Moderne (C++11/14/17)',
        'description' => 'POO en C++, STL, Smart pointers, et programmation générique avec templates. Passez du C au C++ moderne et maîtrisez les fonctionnalités avancées du standard C++11/14/17.',
        'niveau'      => 'intermediaire',
        'duree'       => 28,
        'statut'      => 'publie',
        'langue'      => 'Français',
        'prix'        => 74.90,
        'image'       => '/gestioncours/View/assets/images/courses/cpp.svg',
        'objectifs'   => 'Maîtriser la POO C++, la STL, les smart pointers et créer un système de gestion de compte bancaire',
        'prerequis'   => 'Maîtrise du langage C (ou équivalent)',
        'lessons'     => [
            'De C à C++ : cin/cout, références, paramètres par défaut',
            'Classes et objets en C++ : constructeurs et destructeurs',
            'Héritage et polymorphisme : virtual functions',
            'Surcharge d\'opérateurs',
            'STL : vector, map, string, algorithm',
            'Smart pointers : unique_ptr et shared_ptr',
            'Templates et programmation générique',
            'Projet : Gestion de compte bancaire',
        ],
    ],
];

// ─────────────────────────────────────────────────────────────
// Insertion
// ─────────────────────────────────────────────────────────────
$insertCourse = $db->prepare(
    'INSERT INTO course (titre, description, niveau, duree, statut, langue, prix, image, objectifs, prerequis)
     VALUES (:titre, :description, :niveau, :duree, :statut, :langue, :prix, :image, :objectifs, :prerequis)'
);

$insertLesson = $db->prepare(
    'INSERT INTO lesson (idCourse, titre, ordre)
     VALUES (:idCourse, :titre, :ordre)'
);

$checkCourse = $db->prepare('SELECT idCourse FROM course WHERE titre = :titre');

$results = [];

foreach ($courses as $course) {
    // Vérifie si le cours existe déjà (idempotence)
    $checkCourse->execute(['titre' => $course['titre']]);
    $existing = $checkCourse->fetch();

    if ($existing) {
        $results[] = [
            'status'  => 'skip',
            'titre'   => $course['titre'],
            'message' => 'Cours déjà existant (id=' . $existing['idCourse'] . ')',
        ];
        continue;
    }

    try {
        $db->beginTransaction();

        // Insère le cours
        $insertCourse->execute([
            'titre'       => $course['titre'],
            'description' => $course['description'],
            'niveau'      => $course['niveau'],
            'duree'       => $course['duree'],
            'statut'      => $course['statut'],
            'langue'      => $course['langue'],
            'prix'        => $course['prix'],
            'image'       => $course['image'],
            'objectifs'   => $course['objectifs'],
            'prerequis'   => $course['prerequis'],
        ]);

        $courseId = (int)$db->lastInsertId();

        // Insère les leçons
        foreach ($course['lessons'] as $ordre => $titrelecon) {
            $insertLesson->execute([
                'idCourse' => $courseId,
                'titre'    => $titrelecon,
                'ordre'    => $ordre + 1,
            ]);
        }

        $db->commit();

        $results[] = [
            'status'   => 'ok',
            'titre'    => $course['titre'],
            'courseId' => $courseId,
            'lessons'  => count($course['lessons']),
            'message'  => 'Inséré avec succès',
        ];

    } catch (PDOException $e) {
        $db->rollBack();
        $results[] = [
            'status'  => 'error',
            'titre'   => $course['titre'],
            'message' => $e->getMessage(),
        ];
    }
}

// ─────────────────────────────────────────────────────────────
// Rapport HTML
// ─────────────────────────────────────────────────────────────
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Seed — Nouveaux cours</title>
    <style>
        body { font-family: monospace; background: #0d1117; color: #c9d1d9; padding: 2rem; }
        h1   { color: #58a6ff; }
        .ok    { color: #3fb950; }
        .skip  { color: #d29922; }
        .error { color: #f85149; }
        table { border-collapse: collapse; width: 100%; margin-top: 1rem; }
        th, td { border: 1px solid #30363d; padding: .6rem 1rem; text-align: left; }
        th { background: #161b22; color: #58a6ff; }
        a  { color: #58a6ff; }
    </style>
</head>
<body>
<h1>🌱 Seed — Nouveaux cours</h1>
<table>
    <thead>
        <tr><th>Statut</th><th>Cours</th><th>ID</th><th>Leçons</th><th>Message</th></tr>
    </thead>
    <tbody>
    <?php foreach ($results as $r): ?>
        <tr>
            <td class="<?= $r['status'] ?>"><?= strtoupper($r['status']) ?></td>
            <td><?= htmlspecialchars($r['titre']) ?></td>
            <td><?= htmlspecialchars((string)($r['courseId'] ?? '—')) ?></td>
            <td><?= htmlspecialchars((string)($r['lessons'] ?? '—')) ?></td>
            <td><?= htmlspecialchars($r['message']) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<h2 style="margin-top:2rem;">📚 Tous les cours en base</h2>
<table>
    <thead><tr><th>ID</th><th>Titre</th><th>Niveau</th><th>Prix</th><th>Statut</th><th>Leçons</th></tr></thead>
    <tbody>
    <?php
    $allCourses = $db->query(
        'SELECT c.idCourse, c.titre, c.niveau, c.prix, c.statut,
                COUNT(l.idLesson) AS nb_lessons
         FROM course c
         LEFT JOIN lesson l ON l.idCourse = c.idCourse
         GROUP BY c.idCourse
         ORDER BY c.idCourse DESC'
    )->fetchAll();
    foreach ($allCourses as $c):
    ?>
        <tr>
            <td><?= (int)$c['idCourse'] ?></td>
            <td><?= htmlspecialchars($c['titre']) ?></td>
            <td><?= htmlspecialchars($c['niveau']) ?></td>
            <td><?= number_format((float)$c['prix'], 2, ',', ' ') ?> TND</td>
            <td><?= htmlspecialchars($c['statut']) ?></td>
            <td><?= (int)$c['nb_lessons'] ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<p style="margin-top:2rem;">
    <a href="/gestioncours/View/FrontOffice/course/index.php">→ Voir les cours (FrontOffice)</a>
    &nbsp;|&nbsp;
    <a href="/gestioncours/View/BackOffice/course/list.php">→ BackOffice cours</a>
</p>
</body>
</html>
