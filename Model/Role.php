// ============================
// /home/hadesdivine/Music/user/Model/Role.php
// ============================
<?php
require_once __DIR__ . '/../config.php';

class Role {
    public static function getAll(): array {
        $pdo = Config::getConnexion();
        $stmt = $pdo->query("SELECT idRole, nom FROM role ORDER BY idRole");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}