<?php
require 'config.php';
$db = Config::getConnexion();
$r = $db->query('SELECT COUNT(*) as c FROM question');
echo 'Questions count: ' . $r->fetch()['c'] . PHP_EOL;
$r2 = $db->query('SELECT COUNT(*) as c FROM quiz');
echo 'Quiz count: ' . $r2->fetch()['c'] . PHP_EOL;
// Test the exact query used in QuestionController
$sql = "SELECT q.idQuestion, q.enonce, q.type AS type, q.choixA, q.choixB, q.choixC, q.choixD, q.reponses_json, q.bonneReponse, q.note, q.explication, q.idQuiz, q.niveau, quiz.titre AS quizTitre FROM question q LEFT JOIN quiz ON q.idQuiz = quiz.idQuiz ORDER BY q.idQuestion DESC";
$r3 = $db->query($sql);
echo 'Query OK, rows: ' . $r3->rowCount() . PHP_EOL;
echo 'ALL OK!' . PHP_EOL;
