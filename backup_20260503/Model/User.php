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
        $stmt = $pdo->prepare("SELECT u.*, r.nom as role_nom FROM user u JOIN role r ON u.idRole = r.idRole WHERE idUser = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function findByEmail(string $email): array|false {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("SELECT u.*, r.nom as role_nom FROM user u JOIN role r ON u.idRole = r.idRole WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
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
        $allowed = ['nom', 'prenom', 'telephone', 'bio', 'statut', 'idRole', 'last_login', 'photo'];
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

    public static function updatePhoto(int $id, ?string $photoPath): bool {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("UPDATE user SET photo = :photo WHERE idUser = :id");
        return $stmt->execute([':photo' => $photoPath, ':id' => $id]);
    }

    public static function getAll(): array {
        $pdo = Config::getConnexion();
        $sql = "SELECT u.*, r.nom as role_nom FROM user u JOIN role r ON u.idRole = r.idRole WHERE u.statut = 'actif' ORDER BY u.idUser";
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function softDelete(int $id): bool {
        return self::update($id, ['statut' => 'inactif']);
    }

    public static function hardDelete(int $id): bool {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("DELETE FROM user WHERE idUser = :id");
        return $stmt->execute([':id' => $id]);
    }

  public static function changeRole(int $userId, int $newRoleId): bool {
    return self::update($userId, ['idRole' => $newRoleId]);
}
    public static function updateLastLogin(int $id): bool {
        return self::update($id, ['last_login' => date('Y-m-d H:i:s')]);
    }

    public static function getProfileCompletion(int $id): int {
        $user = self::findById($id);
        if (!$user) return 0;
        $fields = ['nom', 'prenom', 'telephone', 'bio', 'photo'];
        $filled = 0;
        foreach ($fields as $f) {
            if (!empty($user[$f])) $filled++;
        }
        return round(($filled / count($fields)) * 100);
    }

    public static function logLogin(int $userId, string $ip, string $userAgent): bool {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("INSERT INTO login_history (user_id, ip_address, user_agent) VALUES (?, ?, ?)");
        return $stmt->execute([$userId, $ip, $userAgent]);
    }

    public static function getLoginHistory(int $userId, int $limit = 10): array {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("SELECT ip_address, user_agent, login_time FROM login_history WHERE user_id = ? ORDER BY login_time DESC LIMIT ?");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function createResetToken(string $email): string|false {
        $pdo = Config::getConnexion();
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at, used) VALUES (?, ?, ?, 0)");
        if ($stmt->execute([$email, $token, $expires])) return $token;
        return false;
    }

    public static function validateResetToken(string $token): array|false {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND used = 0 AND expires_at > NOW()");
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function resetPassword(string $token, string $newPassword): bool {
        $pdo = Config::getConnexion();
        $reset = self::validateResetToken($token);
        if (!$reset) return false;
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE user SET motDePasse = ? WHERE email = ?");
        if ($stmt->execute([$hash, $reset['email']])) {
            $stmt2 = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
            $stmt2->execute([$token]);
            return true;
        }
        return false;
    }

    // ========== Simple 6-digit code reset methods ==========
    public static function generateResetCode($email) {
        $code = sprintf("%06d", mt_rand(0, 999999));
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at, used) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 15 MINUTE), 0)");
        $stmt->execute([$email, $code]);
        return $code;
    }

    public static function verifyResetCode($email, $code) {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE email = ? AND token = ? AND used = 0 AND expires_at > NOW()");
        $stmt->execute([$email, $code]);
        return $stmt->fetch() !== false;
    }

    public static function resetPasswordByEmail($email, $newPassword) {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("UPDATE user SET motDePasse = ? WHERE email = ?");
        return $stmt->execute([$hash, $email]);
    }

    public static function invalidateResetCode($email) {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE email = ? AND used = 0");
        $stmt->execute([$email]);
    }
}
