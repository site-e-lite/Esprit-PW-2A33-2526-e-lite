<?php
require_once __DIR__ . '/../config.php';

class VirtualClassModel
{
    private $db;

    public function __construct()
    {
        $this->db = Config::getConnexion();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query(
            "SELECT vc.*, c.titre AS courseTitre
             FROM VirtualClass vc
             LEFT JOIN Course c ON vc.idCourse = c.idCourse
             ORDER BY vc.idClass DESC"
        );
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT vc.*, c.titre AS courseTitre
             FROM VirtualClass vc
             LEFT JOIN Course c ON vc.idCourse = c.idCourse
             WHERE vc.idClass = ?"
        );
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function getByCourse(int $idCourse): array
    {
        $stmt = $this->db->prepare("SELECT * FROM VirtualClass WHERE idCourse = ? ORDER BY idClass DESC");
        $stmt->execute([$idCourse]);
        return $stmt->fetchAll();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO VirtualClass (titre, description, lienAcces, plateforme, idCourse)
             VALUES (:titre, :description, :lienAcces, :plateforme, :idCourse)"
        );
        return $stmt->execute([
            ':titre'       => $data['titre'],
            ':description' => $data['description'] ?? null,
            ':lienAcces'   => $data['lienAcces'],
            ':plateforme'  => $data['plateforme']  ?? null,
            ':idCourse'    => $data['idCourse'],
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE VirtualClass SET titre=:titre, description=:description,
             lienAcces=:lienAcces, plateforme=:plateforme, idCourse=:idCourse
             WHERE idClass=:id"
        );
        return $stmt->execute([
            ':titre'       => $data['titre'],
            ':description' => $data['description'] ?? null,
            ':lienAcces'   => $data['lienAcces'],
            ':plateforme'  => $data['plateforme']  ?? null,
            ':idCourse'    => $data['idCourse'],
            ':id'          => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM VirtualClass WHERE idClass = ?");
        return $stmt->execute([$id]);
    }

    public function getLastInsertId(): string
    {
        return $this->db->lastInsertId();
    }
}
?>
