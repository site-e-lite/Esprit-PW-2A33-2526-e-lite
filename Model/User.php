<?php
require_once __DIR__ . '/../config.php';

class User {
    public static function create(array $data): int|false {
        $pdo = Config::getConnexion();
        $sql = "INSERT INTO user (nom, prenom, email, motDePasse, idRole, telephone, dateNaissance, photo, statut, bio)
                VALUES (:nom, :prenom, :email, :motDePasse, :idRole, :telephone, :dateNaissance, :photo, :statut, :bio)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nom' => $data['nom'],
            ':prenom' => $data['prenom'],
            ':email' => $data['email'],
            ':motDePasse' => $data['motDePasse'],
            ':idRole' => $data['idRole'],
            ':telephone' => $data['telephone'] ?? null,
            ':dateNaissance' => $data['dateNaissance'] ?? null,
            ':photo' => $data['photo'] ?? null,
            ':statut' => $data['statut'] ?? 'actif',
            ':bio' => $data['bio'] ?? null
        ]);
        return $pdo->lastInsertId() ?: false;
    }

    public static function findById(int $id): array|false {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("SELECT u.*, r.nom as role_nom FROM user u JOIN role r ON u.idRole = r.idRole WHERE u.idUser = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public static function findByEmail(string $email): array|false {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("SELECT u.*, r.nom as role_nom FROM user u JOIN role r ON u.idRole = r.idRole WHERE u.email = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    public static function authenticate(string $email, string $password): array|false {
        $user = self::findByEmail($email);
        if ($user && password_verify($password, $user['motDePasse'])) {
            return $user;
        }
        return false;
    }

    public static function update(int $id, array $data): bool {
        $pdo = Config::getConnexion();
        $fields = [];
        $params = [':id' => $id];
        $allowed = ['nom', 'prenom', 'telephone', 'bio', 'statut', 'idRole', 'last_login', 'photo', 'motDePasse'];
        foreach ($data as $key => $value) {
            if (in_array($key, $allowed)) {
                $fields[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }
        if (empty($fields)) return false;
        $sql = "UPDATE user SET " . implode(', ', $fields) . " WHERE idUser = :id";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public static function getAll(): array {
        $pdo = Config::getConnexion();
        $sql = "SELECT u.*, r.nom as role_nom FROM user u JOIN role r ON u.idRole = r.idRole WHERE u.statut = 'actif' ORDER BY u.idUser";
        return $pdo->query($sql)->fetchAll();
    }

    public static function softDelete(int $id): bool { return self::update($id, ['statut' => 'inactif']); }
    public static function hardDelete(int $id): bool { $pdo = Config::getConnexion(); $stmt = $pdo->prepare("DELETE FROM user WHERE idUser = :id"); return $stmt->execute([':id' => $id]); }
    public static function changeRole(int $userId, int $newRoleId): bool { return self::update($userId, ['idRole' => $newRoleId]); }
    public static function updateLastLogin(int $id): bool { return self::update($id, ['last_login' => date('Y-m-d H:i:s')]); }
    public static function getProfileCompletion(int $id): int { return 0; }
    public static function logLogin(int $userId, string $ip, string $userAgent): bool { return true; }
    public static function getLoginHistory(int $userId, int $limit = 10): array { return []; }
    public static function createResetToken(string $email): string|false { return bin2hex(random_bytes(16)); }
    public static function validateResetToken(string $token): array|false { return false; }
    public static function resetPassword(string $token, string $newPassword): bool { return false; }
}
