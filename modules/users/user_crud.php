<?php
require_once __DIR__ . '/../../config/config.php';

class UserCrud
{
    /**
     * Crée un nouvel utilisateur
     * @param array $data (nom, prenom, email, motDePasse, role, telephone, dateNaissance, photo)
     * @return int|false ID de l'utilisateur créé ou false
     */
    public static function create($data) {
        $pdo = Config::getConnexion();
        // TODO : Hacher le mot de passe avec password_hash()
        // TODO : Préparer et exécuter l'insertion (statut par défaut 'actif')
        // TODO : Retourner l'ID généré
    }

    /**
     * Récupère un utilisateur par son ID
     * @param int $id
     * @return array|false
     */
    public static function getById($id) {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("SELECT * FROM user WHERE idUser = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Récupère un utilisateur par son email
     * @param string $email
     * @return array|false
     */
    public static function getByEmail($email) {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("SELECT * FROM user WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    /**
     * Met à jour les informations modifiables d'un utilisateur
     * @param int $id
     * @param array $data (nom, prenom, telephone, photo, bio)
     * @return bool
     */
    public static function update($id, $data) {
        $pdo = Config::getConnexion();
        // TODO : Construire dynamiquement la requête UPDATE selon les champs fournis
        // TODO : Exécuter
    }

    /**
     * Suppression logique (statut = 'inactif')
     * @param int $id
     * @return bool
     */
    public static function delete($id) {
        $pdo = Config::getConnexion();
        $stmt = $pdo->prepare("UPDATE user SET statut = 'inactif' WHERE idUser = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Liste tous les utilisateurs (avec filtres optionnels)
     * @param string|null $role
     * @return array
     */
    public static function getAll($role = null) {
        $pdo = Config::getConnexion();
        // TODO : Ajouter clause WHERE si $role fourni
        $stmt = $pdo->query("SELECT * FROM user WHERE statut = 'actif'");
        return $stmt->fetchAll();
    }
}
?>