<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Post.php';

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
        } catch (Exception $e) {
            echo 'Erreur: ' . $e->getMessage();
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

    // Notation d'un post
    public function raterPost($idPost, $note) {
        $db = Config::getConnexion();
        $sql = "UPDATE post SET rating = :note WHERE idPost = :idPost";
        try {
            $q = $db->prepare($sql);
            $q->execute([
                'note'   => intval($note),
                'idPost' => intval($idPost)
            ]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
