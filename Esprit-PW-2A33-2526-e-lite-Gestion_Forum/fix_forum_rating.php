<?php
/**
 * Script one-shot : crée les tables forum_rating et post_rating si elles n'existent pas.
 * Accéder une seule fois : http://localhost/7ashwa/Esprit-PW-2A33-2526-e-lite-Gestion_Forum/fix_forum_rating.php
 * Supprimer ce fichier après exécution.
 */
require_once __DIR__ . '/config.php';
$db = Config::getConnexion();

$sqls = [
    "forum_rating" => "CREATE TABLE IF NOT EXISTS forum_rating (
        id        INT AUTO_INCREMENT PRIMARY KEY,
        idForum   INT NOT NULL,
        idUser    INT NOT NULL,
        note      TINYINT NOT NULL CHECK (note BETWEEN 1 AND 5),
        createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_forum_user (idForum, idUser),
        FOREIGN KEY (idForum) REFERENCES forum(IdForum) ON DELETE CASCADE,
        FOREIGN KEY (idUser)  REFERENCES user(idUser)   ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "post_rating" => "CREATE TABLE IF NOT EXISTS post_rating (
        id        INT AUTO_INCREMENT PRIMARY KEY,
        idPost    INT NOT NULL,
        note      TINYINT NOT NULL CHECK (note BETWEEN 1 AND 5),
        createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (idPost) REFERENCES post(IdPost) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
];

echo "<h2>Fix Forum Rating Tables</h2><ul>";
foreach ($sqls as $table => $sql) {
    try {
        $db->exec($sql);
        echo "<li style='color:green'>✅ Table <strong>$table</strong> créée (ou déjà existante).</li>";
    } catch (Exception $e) {
        echo "<li style='color:red'>❌ Erreur pour <strong>$table</strong> : " . htmlspecialchars($e->getMessage()) . "</li>";
    }
}
echo "</ul>";
echo "<p><strong>✅ Terminé.</strong> Tu peux supprimer ce fichier et <a href='/7ashwa/Esprit-PW-2A33-2526-e-lite-Gestion_Forum/forum/manage'>retourner au BackOffice</a>.</p>";
?>
