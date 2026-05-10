<?php
/**
 * EnrollmentController.php
 * Gestion des inscriptions — architecture MVC, base e_lite unifiée.
 * Conflict markers removed. Single authoritative version.
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Utils/Validator.php';

class EnrollmentController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Config::getInstance()->getConnexion();
    }

    // ── READ ─────────────────────────────────────────────────────

    public function listAll(): array
    {
        return $this->db->query(
            'SELECT idEnrollment, idUser, idCourse, niveauInitial, objectifPersonnel,
                    engagement, modeAcces, dateInscription, progression, derniereActivite,
                    tempsTotalPasse, statut, noteFinale, certificatObtenu
             FROM enrollment ORDER BY idEnrollment DESC'
        )->fetchAll();
    }

    public function listByUser(int $idUser): array
    {
        $stmt = $this->db->prepare(
            'SELECT e.*, c.titre AS courseTitre, c.image AS courseImage, c.niveau AS courseNiveau
             FROM enrollment e
             JOIN course c ON c.idCourse = e.idCourse
             WHERE e.idUser = :idUser
             ORDER BY e.dateInscription DESC'
        );
        $stmt->execute(['idUser' => $idUser]);
        return $stmt->fetchAll();
    }

    public function listByCourse(int $idCourse): array
    {
        $stmt = $this->db->prepare(
            'SELECT e.*, u.nom, u.prenom, u.email
             FROM enrollment e
             JOIN user u ON u.idUser = e.idUser
             WHERE e.idCourse = :idCourse
             ORDER BY e.dateInscription DESC'
        );
        $stmt->execute(['idCourse' => $idCourse]);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT idEnrollment, idUser, idCourse, niveauInitial, objectifPersonnel,
                    engagement, modeAcces, dateInscription, progression, derniereActivite,
                    tempsTotalPasse, statut, noteFinale, certificatObtenu
             FROM enrollment WHERE idEnrollment = :idEnrollment'
        );
        $stmt->execute(['idEnrollment' => $id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Check if a user is already enrolled in a course.
     */
    public function isEnrolled(int $idUser, int $idCourse): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM enrollment WHERE idUser = :idUser AND idCourse = :idCourse'
        );
        $stmt->execute(['idUser' => $idUser, 'idCourse' => $idCourse]);
        return (bool) $stmt->fetch();
    }

    // ── WRITE ────────────────────────────────────────────────────

    public function add(array $data): array
    {
        // Prevent duplicate enrollment
        if ($this->isEnrolled((int)($data['idUser'] ?? 0), (int)($data['idCourse'] ?? 0))) {
            return ['success' => false, 'errors' => [], 'message' => 'Vous êtes déjà inscrit à ce cours.'];
        }

        Validator::reset();
        $this->validate($data);
        if (Validator::hasErrors()) {
            return ['success' => false, 'errors' => Validator::getErrors(), 'message' => 'Veuillez corriger les erreurs du formulaire.'];
        }

        $stmt = $this->db->prepare(
            'INSERT INTO enrollment
             (idUser, idCourse, niveauInitial, objectifPersonnel, engagement, modeAcces,
              progression, derniereActivite, tempsTotalPasse, statut, noteFinale, certificatObtenu)
             VALUES
             (:idUser, :idCourse, :niveauInitial, :objectifPersonnel, :engagement, :modeAcces,
              :progression, :derniereActivite, :tempsTotalPasse, :statut, :noteFinale, :certificatObtenu)'
        );
        $stmt->execute($this->buildParams($data));

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

        $stmt = $this->db->prepare(
            'UPDATE enrollment
             SET idUser = :idUser, idCourse = :idCourse, niveauInitial = :niveauInitial,
                 objectifPersonnel = :objectifPersonnel, engagement = :engagement,
                 modeAcces = :modeAcces, progression = :progression,
                 derniereActivite = :derniereActivite, tempsTotalPasse = :tempsTotalPasse,
                 statut = :statut, noteFinale = :noteFinale, certificatObtenu = :certificatObtenu
             WHERE idEnrollment = :idEnrollment'
        );
        $params = $this->buildParams($data);
        $params['idEnrollment'] = $id;
        $stmt->execute($params);

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

    /**
     * Update last activity timestamp for a user-course pair.
     */
    public function touchActivity(int $idUser, int $idCourse): void
    {
        $stmt = $this->db->prepare(
            'UPDATE enrollment SET derniereActivite = NOW()
             WHERE idUser = :idUser AND idCourse = :idCourse'
        );
        $stmt->execute(['idUser' => $idUser, 'idCourse' => $idCourse]);
    }

    // ── PRIVATE HELPERS ──────────────────────────────────────────

    private function buildParams(array $data): array
    {
        return [
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
        ];
    }

    private function validate(array $data): void
    {
        Validator::required('idUser',   $data['idUser']   ?? '', 'Utilisateur');
        Validator::integer('idUser',    $data['idUser']   ?? '', 'Utilisateur', 1);

        Validator::required('idCourse', $data['idCourse'] ?? '', 'Cours');
        Validator::integer('idCourse',  $data['idCourse'] ?? '', 'Cours', 1);

        Validator::required('niveauInitial', $data['niveauInitial'] ?? '', 'Niveau initial');
        Validator::inArray('niveauInitial',  $data['niveauInitial'] ?? '', ['debutant', 'intermediaire', 'avance'], 'Niveau initial');

        Validator::required('objectifPersonnel', $data['objectifPersonnel'] ?? '', 'Objectif personnel');
        Validator::string('objectifPersonnel',   $data['objectifPersonnel'] ?? '', 'Objectif personnel', 5, 5000);

        Validator::required('engagement', $data['engagement'] ?? '', 'Engagement');
        Validator::integer('engagement',  $data['engagement'] ?? '', 'Engagement', 1, 100);

        Validator::required('modeAcces', $data['modeAcces'] ?? '', "Mode d'accès");
        Validator::inArray('modeAcces',  $data['modeAcces'] ?? '', ['gratuit', 'payant'], "Mode d'accès");

        Validator::required('progression', $data['progression'] ?? '', 'Progression');
        Validator::integer('progression',  $data['progression'] ?? '', 'Progression', 0, 100);

        Validator::required('tempsTotalPasse', $data['tempsTotalPasse'] ?? '', 'Temps total passé');
        Validator::integer('tempsTotalPasse',  $data['tempsTotalPasse'] ?? '', 'Temps total passé', 0);

        Validator::required('statut', $data['statut'] ?? '', 'Statut');
        Validator::inArray('statut',  $data['statut'] ?? '', ['actif', 'termine', 'abandonne'], 'Statut');

        if (trim((string)($data['noteFinale'] ?? '')) !== '') {
            Validator::number('noteFinale', $data['noteFinale'], 'Note finale', 0, 100);
        }
        if (isset($data['derniereActivite']) && trim((string)$data['derniereActivite']) !== '') {
            Validator::date('derniereActivite', $data['derniereActivite'], 'Dernière activité');
        }
    }
}
