<?php
require_once __DIR__ . '/../config.php';

class CourseModel
{
    private $db;

    public function __construct()
    {
        $this->db = Config::getConnexion();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM Course ORDER BY idCourse DESC");
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM Course WHERE idCourse = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO Course (titre, description, niveau, duree, statut, langue, prix, image)
             VALUES (:titre, :description, :niveau, :duree, :statut, :langue, :prix, :image)"
        );
        return $stmt->execute([
            ':titre'       => $data['titre'],
            ':description' => $data['description'] ?? null,
            ':niveau'      => $data['niveau']      ?? null,
            ':duree'       => $data['duree']        ?? null,
            ':statut'      => $data['statut']       ?? 'actif',
            ':langue'      => $data['langue']       ?? 'Français',
            ':prix'        => $data['prix']         ?? 0,
            ':image'       => $data['image']        ?? null,
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE Course SET titre=:titre, description=:description, niveau=:niveau,
             duree=:duree, statut=:statut, langue=:langue, prix=:prix, image=:image
             WHERE idCourse=:id"
        );
        return $stmt->execute([
            ':titre'       => $data['titre'],
            ':description' => $data['description'] ?? null,
            ':niveau'      => $data['niveau']      ?? null,
            ':duree'       => $data['duree']        ?? null,
            ':statut'      => $data['statut']       ?? 'actif',
            ':langue'      => $data['langue']       ?? 'Français',
            ':prix'        => $data['prix']         ?? 0,
            ':image'       => $data['image']        ?? null,
            ':id'          => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM Course WHERE idCourse = ?");
        return $stmt->execute([$id]);
    }

    public function getLastInsertId(): string
    {
        return $this->db->lastInsertId();
    }
}
?>
