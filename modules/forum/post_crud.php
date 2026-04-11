<?php
require_once __DIR__ . '/../../config/config.php';

class PostCrud
{
    public static function create($data) {
        $pdo = Config::getConnexion();
        // TODO : contenu, pieceJointe, idUser, idForum (datePost auto)
    }

    public static function getByForum($forumId) {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("
            SELECT p.*, u.nom, u.prenom, u.photo 
            FROM post p 
            JOIN user u ON p.idUser = u.idUser 
            WHERE p.idForum = ? 
            ORDER BY p.datePost ASC
        ");
        $stmt->execute([$forumId]);
        return $stmt->fetchAll();
    }

    // TODO : update, delete (modération)
}
?>