<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Model/VirtualClass/SessionClass.php';

class SessionController
{
    private $db;

    public function __construct()
    {
        $this->db = Config::getConnexion();
    }

    public function afficherSessions($idClass = null): array
    {
        if ($idClass !== null) {
            $stmt = $this->db->prepare(
                "SELECT s.idSession, s.dateSession, s.heureDebut, s.heureFin,
                        s.statut, s.idClass,
                        vc.titre AS classTitre, vc.lienAcces, vc.plateforme, vc.capacite AS classCapacite
                 FROM session s
                 INNER JOIN virtualclass vc ON vc.idClass = s.idClass
                 WHERE s.idClass = :idClass
                 ORDER BY s.dateSession DESC, s.heureDebut ASC"
            );
            $stmt->execute([':idClass' => (int) $idClass]);
            return $stmt->fetchAll();
        }

        $stmt = $this->db->prepare(
            "SELECT s.idSession, s.dateSession, s.heureDebut, s.heureFin,
                    s.statut, s.idClass,
                    vc.titre AS classTitre, vc.lienAcces, vc.plateforme, vc.idCourse, vc.capacite AS classCapacite,
                    c.titre AS courseTitre
             FROM session s
             INNER JOIN virtualclass vc ON vc.idClass = s.idClass
             LEFT JOIN course c ON c.idCourse = vc.idCourse
             ORDER BY s.dateSession DESC, s.heureDebut ASC"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function addSession(SessionClass $session): bool
    {
        $this->validateSession($session);
        $stmt = $this->db->prepare(
            "INSERT INTO session (dateSession, heureDebut, heureFin, statut, idClass)
             VALUES (:dateSession, :heureDebut, :heureFin, :statut, :idClass)"
        );
        return $stmt->execute([
            ':dateSession' => $session->getDateSession(),
            ':heureDebut'  => $session->getHeureDebut(),
            ':heureFin'    => $session->getHeureFin(),
            ':statut'      => $session->getStatut(),
            ':idClass'     => $session->getIdClass(),
        ]);
    }

    public function updateSession(SessionClass $session, $id): bool
    {
        $this->validateSession($session);
        $stmt = $this->db->prepare(
            "UPDATE session
             SET dateSession=:dateSession, heureDebut=:heureDebut, heureFin=:heureFin,
                 statut=:statut, idClass=:idClass
             WHERE idSession=:id"
        );
        return $stmt->execute([
            ':dateSession' => $session->getDateSession(),
            ':heureDebut'  => $session->getHeureDebut(),
            ':heureFin'    => $session->getHeureFin(),
            ':statut'      => $session->getStatut(),
            ':idClass'     => $session->getIdClass(),
            ':id'          => (int) $id,
        ]);
    }

    public function deleteSession($id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM session WHERE idSession = :id');
        return $stmt->execute([':id' => (int) $id]);
    }

    public function getSessionById($id): ?SessionClass
    {
        $stmt = $this->db->prepare(
            "SELECT s.idSession, s.dateSession, s.heureDebut, s.heureFin, s.statut, s.idClass
             FROM session s
             INNER JOIN virtualclass vc ON vc.idClass = s.idClass
             WHERE s.idSession = :id"
        );
        $stmt->execute([':id' => (int) $id]);
        $row = $stmt->fetch();
        if (!$row) return null;
        return new SessionClass(
            (int) $row['idSession'],
            $row['dateSession'],
            $row['heureDebut'],
            $row['heureFin'],
            $row['statut'],
            (int) $row['idClass']
        );
    }

    private function validateSession(SessionClass $s): void
    {
        if (!$this->isValidDate($s->getDateSession()))
            throw new InvalidArgumentException('Date de session invalide.');
        if (!$this->isValidTime($s->getHeureDebut()) || !$this->isValidTime($s->getHeureFin()))
            throw new InvalidArgumentException('Format heure invalide.');
        if (strtotime($s->getHeureFin()) <= strtotime($s->getHeureDebut()))
            throw new InvalidArgumentException('heureFin doit être supérieure à heureDebut.');
        if (trim($s->getStatut()) === '')
            throw new InvalidArgumentException('Le statut est obligatoire.');
        if ((int) $s->getIdClass() <= 0)
            throw new InvalidArgumentException('idClass invalide.');
        $c = $this->db->prepare('SELECT idClass FROM virtualclass WHERE idClass = :id');
        $c->execute([':id' => (int) $s->getIdClass()]);
        if (!$c->fetch())
            throw new InvalidArgumentException("La classe virtuelle sélectionnée n'existe pas.");
    }

    private function isValidDate($date): bool
    {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    private function isValidTime($time): bool
    {
        return (bool) preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', $time);
    }
}
