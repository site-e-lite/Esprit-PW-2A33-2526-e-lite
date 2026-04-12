<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Forum.php';

class ForumController {
    // Afficher tous les forums
    public function afficherForums() {
        $sql = "SELECT * FROM forum";
        $db = Config::getConnexion();
        try {
            $liste = $db->query($sql);
            return $liste;
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // Ajouter un forum
    public function addForum($forum) {
        $sql = "INSERT INTO forum (titre, description, idCourse) VALUES (:titre, :description, :idCourse)";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'titre' => $forum->getTitre(),
                'description' => $forum->getDescription(),
                'idCourse' => $forum->getIdCourse()
            ]);
        } catch (Exception $e) {
            echo 'Erreur: ' . $e->getMessage();
        }
    }

    // Modifier un forum
    public function updateForum($forum, $id) {
        try {
            $db = Config::getConnexion();
            $query = $db->prepare(
                'UPDATE forum SET 
                    titre = :titre, 
                    description = :description, 
                    idCourse = :idCourse 
                WHERE idForum = :idForum'
            );
            $query->execute([
                'titre' => $forum->getTitre(),
                'description' => $forum->getDescription(),
                'idCourse' => $forum->getIdCourse(),
                'idForum' => $id
            ]);
            // echo $query->rowCount() . " records UPDATED successfully <br>";
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
    }

    // Supprimer un forum
    public function deleteForum($id) {
        $sql = "DELETE FROM forum WHERE idForum = :id";
        $db = Config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);
        try {
            $req->execute();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // Récupérer un forum par son ID
    public function getForumById($id) {
        $sql = "SELECT * FROM forum WHERE idForum = :id";
        $db = Config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }
}
?>
