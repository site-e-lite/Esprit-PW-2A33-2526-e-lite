<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Model/Forum/Post.php';

class PostController {
    // Afficher tous les posts (optionnellement filtrés par forum)
    public function afficherPosts($idForum = null) {
        $sql = "SELECT * FROM post";
        if ($idForum != null) {
            $sql .= " WHERE idForum = " . intval($idForum);
        }
        $db = Config::getConnexion();
        try {
            $liste = $db->query($sql);
            return $liste;
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // Ajouter un post
    public function addPost($post) {
        $sql = "INSERT INTO post (contenu, pieceJointe, idUser, idForum) VALUES (:contenu, :pieceJointe, :idUser, :idForum)";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'contenu'     => $post->getContenu(),
                'pieceJointe' => $post->getPieceJointe() ?: null,
                'idUser'      => $post->getIdUser()  ?: null,
                'idForum'     => $post->getIdForum() ?: null
            ]);
            return true;
        } catch (Exception $e) {
            error_log('addPost: ' . $e->getMessage());
            return false;
        }
    }

    // Modifier un post
    public function updatePost($post, $id) {
        try {
            $db = Config::getConnexion();
            $query = $db->prepare(
                'UPDATE post SET 
                    contenu = :contenu, 
                    pieceJointe = :pieceJointe 
                WHERE idPost = :idPost'
            );
            $query->execute([
                'contenu' => $post->getContenu(),
                'pieceJointe' => $post->getPieceJointe(),
                'idPost' => $id
            ]);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    // Supprimer un post
    public function deletePost($id) {
        $sql = "DELETE FROM post WHERE idPost = :id";
        $db = Config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);
        try {
            $req->execute();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // Récupérer un post par son ID
    public function getPostById($id) {
        $sql = "SELECT * FROM post WHERE idPost = :id";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // Notation d'un post (stockée dans post_rating)
    public function raterPost($idPost, $note) {
        $db = Config::getConnexion();
        try {
            // Créer la table si elle n'existe pas encore
            $db->exec("CREATE TABLE IF NOT EXISTS post_rating (
                id      INT AUTO_INCREMENT PRIMARY KEY,
                idPost  INT NOT NULL,
                note    TINYINT NOT NULL,
                createdAt DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (idPost) REFERENCES post(IdPost) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            $stmt = $db->prepare(
                'INSERT INTO post_rating (idPost, note) VALUES (:idPost, :note)'
            );
            $stmt->execute([
                'idPost' => intval($idPost),
                'note'   => intval($note),
            ]);
            return true;
        } catch (Exception $e) {
            error_log('raterPost: ' . $e->getMessage());
            return false;
        }
    }

    // Récupérer la note moyenne d'un post
    public function getPostAvgRating($idPost) {
        $db = Config::getConnexion();
        try {
            $stmt = $db->prepare(
                'SELECT ROUND(AVG(note), 1) FROM post_rating WHERE idPost = :idPost'
            );
            $stmt->execute(['idPost' => intval($idPost)]);
            return (float) $stmt->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }
}
?>
