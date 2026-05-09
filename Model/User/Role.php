<?php
require_once __DIR__ . '/../../config.php';
class Role {
    public static function getAll(): array {
        $pdo = Config::getConnexion();
        return $pdo->query("SELECT idRole, nom FROM role ORDER BY idRole")->fetchAll();
    }
}
