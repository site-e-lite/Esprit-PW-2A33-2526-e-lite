<?php
require_once __DIR__ . '/../config.php';

class SessionModel
{
    private $db;

    public function __construct()
    {
        $this->db = Config::getConnexion();
    }

    public function getAll(): array
    {
        $stmt = $this->db->query(
            "SELECT s.*, vc.titre AS classTitre, vc.lienAcces, vc.plateforme, c.titre AS courseTitre
             FROM Session s
             LEFT JOIN VirtualClass vc ON s.idClass = vc.idClass
             LEFT JOIN Course c ON vc.idCourse = c.idCourse
             ORDER BY s.dateSession DESC, s.heureDebut ASC"
        );
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT s.*, vc.titre AS classTitre
             FROM Session s
             LEFT JOIN VirtualClass vc ON s.idClass = vc.idClass
             WHERE s.idSession = ?"
        );
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function getByClass(int $idClass): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM Session WHERE idClass = ? ORDER BY dateSession DESC, heureDebut ASC"
        );
        $stmt->execute([$idClass]);
        return $stmt->fetchAll();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO Session (dateSession, heureDebut, heureFin, statut, capacite, idClass)
             VALUES (:dateSession, :heureDebut, :heureFin, :statut, :capacite, :idClass)"
        );
        return $stmt->execute([
            ':dateSession' => $data['dateSession'],
            ':heureDebut'  => $data['heureDebut'],
            ':heureFin'    => $data['heureFin'],
            ':statut'      => $data['statut']   ?? 'planifiée',
            ':capacite'    => $data['capacite'],
            ':idClass'     => $data['idClass'],
        ]);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE Session SET dateSession=:dateSession, heureDebut=:heureDebut,
             heureFin=:heureFin, statut=:statut, capacite=:capacite, idClass=:idClass
             WHERE idSession=:id"
        );
        return $stmt->execute([
            ':dateSession' => $data['dateSession'],
            ':heureDebut'  => $data['heureDebut'],
            ':heureFin'    => $data['heureFin'],
            ':statut'      => $data['statut']   ?? 'planifiée',
            ':capacite'    => $data['capacite'],
            ':idClass'     => $data['idClass'],
            ':id'          => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM Session WHERE idSession = ?");
        return $stmt->execute([$id]);
    }
}
?>
