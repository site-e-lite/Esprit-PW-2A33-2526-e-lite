<?php
$dsn = 'mysql:host=localhost;dbname=e_lite';
try {
    $pdo = new PDO($dsn, 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    $stmt = $pdo->query('SELECT idQuiz, titre, duree, seuilReussite, niveau, statut, idCourse FROM quiz ORDER BY idQuiz DESC LIMIT 10');
    foreach ($stmt as $r) {
        echo $r['idQuiz'] . '|' . $r['titre'] . '|' . $r['statut'] . '|' . $r['idCourse'] . "\n";
    }
} catch (Exception $e) {
    echo 'ERR:' . $e->getMessage();
}
