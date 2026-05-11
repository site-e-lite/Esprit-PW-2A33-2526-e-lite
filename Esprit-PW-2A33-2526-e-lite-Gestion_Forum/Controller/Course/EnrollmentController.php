<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/Validator.php';

class EnrollmentController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Config::getConnexion();
    }

    public function listAll(): array
    {
        $sql = 'SELECT idEnrollment, idUser, idCourse, niveauInitial, objectifPersonnel, engagement, modeAcces, dateInscription, progression, derniereActivite, tempsTotalPasse, statut, noteFinale, certificatObtenu FROM enrollment ORDER BY idEnrollment DESC';
        return $this->db->query($sql)->fetchAll();
    }

    public function listByUser(int $idUser): array
    {
        $sql = 'SELECT idEnrollment, idUser, idCourse, niveauInitial, objectifPersonnel, engagement, modeAcces, dateInscription, progression, derniereActivite, tempsTotalPasse, statut, noteFinale, certificatObtenu
                FROM enrollment
                WHERE idUser = :idUser
                ORDER BY dateInscription DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['idUser' => $idUser]);
        return $stmt->fetchAll();
    }

    public function getMyEnrollments(int $idUser): array
    {
        $sql = 'SELECT e.idEnrollment, e.idUser, e.idCourse, e.niveauInitial, e.objectifPersonnel,
                       e.engagement, e.modeAcces, e.dateInscription, e.progression, e.derniereActivite,
                       e.tempsTotalPasse, e.statut, e.noteFinale, e.certificatObtenu,
                       c.titre, c.description, c.duree, c.niveau, c.prix, c.image
                FROM enrollment e
                JOIN course c ON e.idCourse = c.idCourse
                WHERE e.idUser = :idUser
                ORDER BY e.dateInscription DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['idUser' => $idUser]);
        return $stmt->fetchAll();
    }

    public function listByCourse(int $idCourse): array
    {
        $sql = 'SELECT idEnrollment, idUser, idCourse, niveauInitial, objectifPersonnel, engagement, modeAcces, dateInscription, progression, derniereActivite, tempsTotalPasse, statut, noteFinale, certificatObtenu
                FROM enrollment
                WHERE idCourse = :idCourse
                ORDER BY dateInscription DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['idCourse' => $idCourse]);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $sql = 'SELECT idEnrollment, idUser, idCourse, niveauInitial, objectifPersonnel, engagement, modeAcces, dateInscription, progression, derniereActivite, tempsTotalPasse, statut, noteFinale, certificatObtenu FROM enrollment WHERE idEnrollment = :idEnrollment';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['idEnrollment' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function isUserEnrolled(int $idUser, int $idCourse): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM enrollment WHERE idUser = :idUser AND idCourse = :idCourse');
        $stmt->execute(['idUser' => $idUser, 'idCourse' => $idCourse]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function enrollUser(): array
    {
        if (!isset($_SESSION['user_id'])) {
            return ['success' => false, 'message' => 'Vous devez être connecté pour vous inscrire'];
        }

        if (!isset($_POST['idCourse']) || !is_numeric($_POST['idCourse']) || $_POST['idCourse'] <= 0) {
            return ['success' => false, 'message' => 'ID cours invalide'];
        }

        $idUser   = (int)$_SESSION['user_id'];
        $idCourse = (int)$_POST['idCourse'];

        if ($this->isUserEnrolled($idUser, $idCourse)) {
            return ['success' => false, 'message' => 'Vous êtes déjà inscrit à ce cours'];
        }

        $data = [
            'idUser'            => $idUser,
            'idCourse'          => $idCourse,
            'niveauInitial'     => $_POST['niveauInitial']     ?? 'debutant',
            'objectifPersonnel' => $_POST['objectifPersonnel'] ?? '',
            'engagement'        => $_POST['engagement']        ?? 50,
            'modeAcces'         => $_POST['modeAcces']         ?? 'gratuit',
            'progression'       => 0,
            'derniereActivite'  => null,
            'tempsTotalPasse'   => 0,
            'statut'            => 'actif',
            'noteFinale'        => null,
            'certificatObtenu'  => 0,
        ];

        try {
            $sql = 'INSERT INTO enrollment
                    (idUser, idCourse, niveauInitial, objectifPersonnel, engagement, modeAcces, progression, derniereActivite, tempsTotalPasse, statut, noteFinale, certificatObtenu)
                    VALUES
                    (:idUser, :idCourse, :niveauInitial, :objectifPersonnel, :engagement, :modeAcces, :progression, :derniereActivite, :tempsTotalPasse, :statut, :noteFinale, :certificatObtenu)';
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'idUser'            => $data['idUser'],
                'idCourse'          => $data['idCourse'],
                'niveauInitial'     => (string)$data['niveauInitial'],
                'objectifPersonnel' => (string)$data['objectifPersonnel'],
                'engagement'        => (int)$data['engagement'],
                'modeAcces'         => (string)$data['modeAcces'],
                'progression'       => 0,
                'derniereActivite'  => null,
                'tempsTotalPasse'   => 0,
                'statut'            => 'actif',
                'noteFinale'        => null,
                'certificatObtenu'  => 0,
            ]);
            return ['success' => true, 'message' => 'Inscription réussie ! Bienvenue dans le cours.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erreur : ' . $e->getMessage()];
        }
    }

    public function add(array $data): array
    {
        Validator::reset();
        $this->validate($data);
        if (Validator::hasErrors()) {
            return ['success' => false, 'errors' => Validator::getErrors(), 'message' => 'Veuillez corriger les erreurs du formulaire.'];
        }

        $sql = 'INSERT INTO enrollment
                (idUser, idCourse, niveauInitial, objectifPersonnel, engagement, modeAcces, progression, derniereActivite, tempsTotalPasse, statut, noteFinale, certificatObtenu)
                VALUES
                (:idUser, :idCourse, :niveauInitial, :objectifPersonnel, :engagement, :modeAcces, :progression, :derniereActivite, :tempsTotalPasse, :statut, :noteFinale, :certificatObtenu)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'idUser'            => (int)$data['idUser'],
            'idCourse'          => (int)$data['idCourse'],
            'niveauInitial'     => (string)$data['niveauInitial'],
            'objectifPersonnel' => trim((string)$data['objectifPersonnel']),
            'engagement'        => (int)$data['engagement'],
            'modeAcces'         => (string)$data['modeAcces'],
            'progression'       => (int)$data['progression'],
            'derniereActivite'  => trim((string)($data['derniereActivite'] ?? '')) !== '' ? (string)$data['derniereActivite'] : null,
            'tempsTotalPasse'   => (int)$data['tempsTotalPasse'],
            'statut'            => (string)$data['statut'],
            'noteFinale'        => trim((string)($data['noteFinale'] ?? '')) !== '' ? (float)$data['noteFinale'] : null,
            'certificatObtenu'  => !empty($data['certificatObtenu']) ? 1 : 0,
        ]);

        return ['success' => true, 'errors' => [], 'message' => 'Inscription ajoutée avec succès.'];
    }

    public function update(int $id, array $data): array
    {
        if ($this->getById($id) === null) {
            return ['success' => false, 'errors' => ['id' => 'Inscription introuvable.'], 'message' => 'Inscription introuvable.'];
        }

        Validator::reset();
        $this->validate($data);
        if (Validator::hasErrors()) {
            return ['success' => false, 'errors' => Validator::getErrors(), 'message' => 'Veuillez corriger les erreurs du formulaire.'];
        }

        $sql = 'UPDATE enrollment
                SET idUser = :idUser,
                    idCourse = :idCourse,
                    niveauInitial = :niveauInitial,
                    objectifPersonnel = :objectifPersonnel,
                    engagement = :engagement,
                    modeAcces = :modeAcces,
                    progression = :progression,
                    derniereActivite = :derniereActivite,
                    tempsTotalPasse = :tempsTotalPasse,
                    statut = :statut,
                    noteFinale = :noteFinale,
                    certificatObtenu = :certificatObtenu
                WHERE idEnrollment = :idEnrollment';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'idUser'            => (int)$data['idUser'],
            'idCourse'          => (int)$data['idCourse'],
            'niveauInitial'     => (string)$data['niveauInitial'],
            'objectifPersonnel' => trim((string)$data['objectifPersonnel']),
            'engagement'        => (int)$data['engagement'],
            'modeAcces'         => (string)$data['modeAcces'],
            'progression'       => (int)$data['progression'],
            'derniereActivite'  => trim((string)($data['derniereActivite'] ?? '')) !== '' ? (string)$data['derniereActivite'] : null,
            'tempsTotalPasse'   => (int)$data['tempsTotalPasse'],
            'statut'            => (string)$data['statut'],
            'noteFinale'        => trim((string)($data['noteFinale'] ?? '')) !== '' ? (float)$data['noteFinale'] : null,
            'certificatObtenu'  => !empty($data['certificatObtenu']) ? 1 : 0,
            'idEnrollment'      => $id,
        ]);

        return ['success' => true, 'errors' => [], 'message' => 'Inscription modifiée avec succès.'];
    }

    public function delete(int $id): array
    {
        $stmt = $this->db->prepare('DELETE FROM enrollment WHERE idEnrollment = :idEnrollment');
        $stmt->execute(['idEnrollment' => $id]);

        if ($stmt->rowCount() === 0) {
            return ['success' => false, 'errors' => ['id' => 'Inscription introuvable.'], 'message' => 'Inscription introuvable.'];
        }

        return ['success' => true, 'errors' => [], 'message' => 'Inscription supprimée avec succès.'];
    }

    private function validate(array $data): void
    {
        Validator::required('idUser', $data['idUser'] ?? '', 'Utilisateur');
        Validator::integer('idUser', $data['idUser'] ?? '', 'Utilisateur', 1);

        Validator::required('idCourse', $data['idCourse'] ?? '', 'Cours');
        Validator::integer('idCourse', $data['idCourse'] ?? '', 'Cours', 1);

        Validator::required('niveauInitial', $data['niveauInitial'] ?? '', 'Niveau initial');
        Validator::inArray('niveauInitial', $data['niveauInitial'] ?? '', ['debutant', 'intermediaire', 'avance'], 'Niveau initial');

        Validator::required('objectifPersonnel', $data['objectifPersonnel'] ?? '', 'Objectif personnel');
        Validator::string('objectifPersonnel', $data['objectifPersonnel'] ?? '', 'Objectif personnel', 5, 5000);

        Validator::required('engagement', $data['engagement'] ?? '', 'Engagement');
        Validator::integer('engagement', $data['engagement'] ?? '', 'Engagement', 1, 100);

        Validator::required('modeAcces', $data['modeAcces'] ?? '', 'Mode d\'accès');
        Validator::inArray('modeAcces', $data['modeAcces'] ?? '', ['gratuit', 'payant'], 'Mode d\'accès');

        Validator::required('progression', $data['progression'] ?? '', 'Progression');
        Validator::integer('progression', $data['progression'] ?? '', 'Progression', 0, 100);

        Validator::required('tempsTotalPasse', $data['tempsTotalPasse'] ?? '', 'Temps total passé');
        Validator::integer('tempsTotalPasse', $data['tempsTotalPasse'] ?? '', 'Temps total passé', 0);

        Validator::required('statut', $data['statut'] ?? '', 'Statut');
        Validator::inArray('statut', $data['statut'] ?? '', ['actif', 'termine', 'abandonne'], 'Statut');

        if (trim((string)($data['noteFinale'] ?? '')) !== '') {
            Validator::number('noteFinale', $data['noteFinale'], 'Note finale', 0, 100);
        }

        if (isset($data['derniereActivite']) && trim((string)$data['derniereActivite']) !== '') {
            Validator::date('derniereActivite', $data['derniereActivite'], 'Dernière activité');
        }
    }
}
?>
