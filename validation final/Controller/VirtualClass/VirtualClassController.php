<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Model/VirtualClass/VirtualClass.php';

class VirtualClassController
{
    private $db;

    public function __construct()
    {
        $this->db = Config::getConnexion();
    }

    public function afficherVirtualClasses(): array
    {
        $stmt = $this->db->prepare(
            "SELECT vc.idClass, vc.titre, vc.description, vc.lienAcces,
                    vc.plateforme, vc.capacite, vc.idCourse,
                    c.titre AS courseTitre
             FROM virtualclass vc
             LEFT JOIN course c ON c.idCourse = vc.idCourse
             ORDER BY vc.idClass DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function afficherVirtualClassesParCours($idCourse): array
    {
        $stmt = $this->db->prepare(
            "SELECT vc.idClass, vc.titre, vc.description, vc.lienAcces,
                    vc.plateforme, vc.capacite, vc.idCourse,
                    c.titre AS courseTitre
             FROM virtualclass vc
             LEFT JOIN course c ON c.idCourse = vc.idCourse
             WHERE vc.idCourse = :idCourse
             ORDER BY vc.idClass DESC"
        );
        $stmt->execute([':idCourse' => (int) $idCourse]);
        return $stmt->fetchAll();
    }

    public function addVirtualClass(VirtualClass $virtualClass): bool
    {
        $this->validateVirtualClass($virtualClass);
        $stmt = $this->db->prepare(
            "INSERT INTO virtualclass (titre, description, lienAcces, plateforme, capacite, idCourse)
             VALUES (:titre, :description, :lienAcces, :plateforme, :capacite, :idCourse)"
        );
        return $stmt->execute([
            ':titre'       => $virtualClass->getTitre(),
            ':description' => $virtualClass->getDescription(),
            ':lienAcces'   => $virtualClass->getLienAcces(),
            ':plateforme'  => $virtualClass->getPlateforme(),
            ':capacite'    => $virtualClass->getCapacite(),
            ':idCourse'    => ((int)$virtualClass->getIdCourse() > 0) ? (int)$virtualClass->getIdCourse() : null,
        ]);
    }

    public function updateVirtualClass(VirtualClass $virtualClass, $id): bool
    {
        $this->validateVirtualClass($virtualClass);
        $stmt = $this->db->prepare(
            "UPDATE virtualclass
             SET titre=:titre, description=:description, lienAcces=:lienAcces,
                 plateforme=:plateforme, capacite=:capacite, idCourse=:idCourse
             WHERE idClass=:id"
        );
        return $stmt->execute([
            ':titre'       => $virtualClass->getTitre(),
            ':description' => $virtualClass->getDescription(),
            ':lienAcces'   => $virtualClass->getLienAcces(),
            ':plateforme'  => $virtualClass->getPlateforme(),
            ':capacite'    => $virtualClass->getCapacite(),
            ':idCourse'    => ((int)$virtualClass->getIdCourse() > 0) ? (int)$virtualClass->getIdCourse() : null,
            ':id'          => (int) $id,
        ]);
    }

    public function deleteVirtualClass($id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM virtualclass WHERE idClass = :id");
        return $stmt->execute([':id' => (int) $id]);
    }

    public function getVirtualClassById($id): ?VirtualClass
    {
        $stmt = $this->db->prepare("SELECT * FROM virtualclass WHERE idClass = :id");
        $stmt->execute([':id' => (int) $id]);
        $row = $stmt->fetch();
        if (!$row) return null;
        return new VirtualClass(
            (int) $row['idClass'],
            $row['titre'],
            $row['description'] ?? '',
            $row['lienAcces'],
            $row['plateforme'],
            (int) ($row['capacite'] ?? 30),
            (int) $row['idCourse']
        );
    }

    private function validateVirtualClass(VirtualClass $vc): void
    {
        if (trim($vc->getTitre()) === '')
            throw new InvalidArgumentException('Le titre est obligatoire.');
        if (trim($vc->getLienAcces()) === '')
            throw new InvalidArgumentException("Le lien d'accès est obligatoire.");
        if (trim($vc->getPlateforme()) === '')
            throw new InvalidArgumentException('La plateforme est obligatoire.');
        if ((int) $vc->getCapacite() <= 0)
            throw new InvalidArgumentException('La capacité doit être supérieure à 0.');
        if ((int) $vc->getIdCourse() > 0) {
            $s = $this->db->prepare('SELECT idCourse FROM course WHERE idCourse = :id');
            $s->execute([':id' => (int) $vc->getIdCourse()]);
            if (!$s->fetch())
                throw new InvalidArgumentException("Le cours sélectionné n'existe pas.");
        }
    }
}
