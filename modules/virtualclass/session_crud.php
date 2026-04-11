<?php
require_once __DIR__ . '/../../config/config.php';

class SessionCrud
{
    public static function create($data) {
        $pdo = Config::getConnexion();
        // TODO : dateSession, heureDebut, heureFin, statut, capacite, idClass
    }

    public static function getByClass($classId) {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("SELECT * FROM session WHERE idClass = ? ORDER BY dateSession, heureDebut");
        $stmt->execute([$classId]);
        return $stmt->fetchAll();
    }

    // TODO : update, delete
}
?>