<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/UserCrud.php';

class UserAdvanced
{
    /**
     * FONCTIONNALITÉ AVANCÉE 1 : Authentification sécurisée
     * @param string $email
     * @param string $password
     * @return array|false Retourne les données utilisateur (sans mot de passe) ou false
     */
    public static function authenticate($email, $password) {
        $user = UserCrud::getByEmail($email);
        if ($user && password_verify($password, $user['motDePasse']) && $user['statut'] === 'actif') {
            unset($user['motDePasse']);
            return $user;
        }
        return false;
    }

    /**
     * FONCTIONNALITÉ AVANCÉE 2 : Gestion des rôles et permissions
     * Vérifie si un utilisateur possède le rôle requis
     * @param int $userId
     * @param string|array $requiredRoles
     * @return bool
     */
    public static function hasPermission($userId, $requiredRoles) {
        $user = UserCrud::getById($userId);
        if (!$user) return false;
        if (is_array($requiredRoles)) {
            return in_array($user['role'], $requiredRoles);
        }
        return $user['role'] === $requiredRoles;
    }

    /**
     * Change le rôle d'un utilisateur (réservé admin)
     * @param int $userId
     * @param string $newRole
     * @return bool
     */
    public static function changeRole($userId, $newRole) {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("UPDATE user SET role = ? WHERE idUser = ?");
        return $stmt->execute([$newRole, $userId]);
    }
}
?>