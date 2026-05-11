<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/Validator.php';

class CourseController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Config::getConnexion();
    }

    public function listAll(): array
    {
        $sql = 'SELECT idCourse, titre, description, niveau, duree, statut, langue, prix, image, objectifs, prerequis FROM course ORDER BY idCourse DESC';
        return $this->db->query($sql)->fetchAll();
    }

    public function listPublished(): array
    {
        $sql = 'SELECT idCourse, titre, description, niveau, duree, statut, langue, prix, image, objectifs, prerequis FROM course WHERE statut = :statut ORDER BY idCourse DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['statut' => 'publie']);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $sql = 'SELECT idCourse, titre, description, niveau, duree, statut, langue, prix, image, objectifs, prerequis FROM course WHERE idCourse = :idCourse';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['idCourse' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getCourseById(int $id): ?array
    {
        return $this->getById($id);
    }

    public function getCoursesPaginated(int $page = 1, int $limit = 12): array
    {
        $offset = ($page - 1) * $limit;
        $totalStmt = $this->db->query('SELECT COUNT(*) FROM course WHERE statut = \'publie\'');
        $total = (int)$totalStmt->fetchColumn();

        $stmt = $this->db->prepare('SELECT idCourse, titre, description, niveau, duree, statut, langue, prix, image, objectifs, prerequis FROM course WHERE statut = \'publie\' ORDER BY idCourse DESC LIMIT :lim OFFSET :off');
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'courses'      => $stmt->fetchAll(),
            'totalCourses' => $total,
            'totalPages'   => (int)ceil($total / $limit),
            'currentPage'  => $page,
        ];
    }

    public function getRecommendedCourses(string $niveauInitial, string $objectifPersonnel): array
    {
        $sql = 'SELECT idCourse, titre, description, niveau, duree, statut, langue, prix, image, objectifs, prerequis
                FROM course
                WHERE (niveau = :niveau OR description LIKE :obj)
                  AND statut = \'publie\'
                LIMIT 3';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['niveau' => $niveauInitial, 'obj' => '%' . $objectifPersonnel . '%']);
        return $stmt->fetchAll();
    }

    public function add(array $data): array
    {
        Validator::reset();
        $this->validate($data);
        if (Validator::hasErrors()) {
            return ['success' => false, 'errors' => Validator::getErrors(), 'message' => 'Veuillez corriger les erreurs du formulaire.'];
        }

        $sql = 'INSERT INTO course (titre, description, niveau, duree, statut, langue, prix, image, objectifs, prerequis)
                VALUES (:titre, :description, :niveau, :duree, :statut, :langue, :prix, :image, :objectifs, :prerequis)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'titre'       => trim((string)$data['titre']),
            'description' => trim((string)$data['description']),
            'niveau'      => (string)$data['niveau'],
            'duree'       => (int)$data['duree'],
            'statut'      => (string)$data['statut'],
            'langue'      => trim((string)$data['langue']),
            'prix'        => (float)$data['prix'],
            'image'       => trim((string)($data['image'] ?? '')) !== '' ? trim((string)$data['image']) : null,
            'objectifs'   => trim((string)($data['objectifs'] ?? '')) !== '' ? trim((string)$data['objectifs']) : null,
            'prerequis'   => trim((string)($data['prerequis'] ?? '')) !== '' ? trim((string)$data['prerequis']) : null,
        ]);

        return ['success' => true, 'errors' => [], 'message' => 'Cours ajouté avec succès.'];
    }

    public function update(int $id, array $data): array
    {
        if ($this->getById($id) === null) {
            return ['success' => false, 'errors' => ['id' => 'Cours introuvable.'], 'message' => 'Cours introuvable.'];
        }

        Validator::reset();
        $this->validate($data);
        if (Validator::hasErrors()) {
            return ['success' => false, 'errors' => Validator::getErrors(), 'message' => 'Veuillez corriger les erreurs du formulaire.'];
        }

        $sql = 'UPDATE course
                SET titre = :titre,
                    description = :description,
                    niveau = :niveau,
                    duree = :duree,
                    statut = :statut,
                    langue = :langue,
                    prix = :prix,
                    image = :image,
                    objectifs = :objectifs,
                    prerequis = :prerequis
                WHERE idCourse = :idCourse';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'titre'       => trim((string)$data['titre']),
            'description' => trim((string)$data['description']),
            'niveau'      => (string)$data['niveau'],
            'duree'       => (int)$data['duree'],
            'statut'      => (string)$data['statut'],
            'langue'      => trim((string)$data['langue']),
            'prix'        => (float)$data['prix'],
            'image'       => trim((string)($data['image'] ?? '')) !== '' ? trim((string)$data['image']) : null,
            'objectifs'   => trim((string)($data['objectifs'] ?? '')) !== '' ? trim((string)$data['objectifs']) : null,
            'prerequis'   => trim((string)($data['prerequis'] ?? '')) !== '' ? trim((string)$data['prerequis']) : null,
            'idCourse'    => $id,
        ]);

        return ['success' => true, 'errors' => [], 'message' => 'Cours modifié avec succès.'];
    }

    public function delete(int $id): array
    {
        $sql = 'DELETE FROM course WHERE idCourse = :idCourse';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['idCourse' => $id]);

        if ($stmt->rowCount() === 0) {
            return ['success' => false, 'errors' => ['id' => 'Cours introuvable.'], 'message' => 'Cours introuvable.'];
        }

        return ['success' => true, 'errors' => [], 'message' => 'Cours supprimé avec succès.'];
    }

    private function validate(array $data): void
    {
        Validator::required('titre', $data['titre'] ?? '', 'Titre');
        Validator::string('titre', $data['titre'] ?? '', 'Titre', 3, 100);

        Validator::required('description', $data['description'] ?? '', 'Description');
        Validator::string('description', $data['description'] ?? '', 'Description', 10, 5000);

        Validator::required('niveau', $data['niveau'] ?? '', 'Niveau');
        Validator::inArray('niveau', $data['niveau'] ?? '', ['debutant', 'intermediaire', 'avance'], 'Niveau');

        Validator::required('duree', $data['duree'] ?? '', 'Durée');
        Validator::integer('duree', $data['duree'] ?? '', 'Durée', 1, 2000);

        Validator::required('statut', $data['statut'] ?? '', 'Statut');
        Validator::inArray('statut', $data['statut'] ?? '', ['brouillon', 'publie', 'archive'], 'Statut');

        Validator::required('langue', $data['langue'] ?? '', 'Langue');
        Validator::string('langue', $data['langue'] ?? '', 'Langue', 2, 30);

        Validator::required('prix', $data['prix'] ?? 0, 'Prix');
        Validator::number('prix', $data['prix'] ?? 0, 'Prix', 0, 999999);

        Validator::url('image', $data['image'] ?? '', 'Image');

        if (trim((string)($data['objectifs'] ?? '')) !== '') {
            Validator::string('objectifs', $data['objectifs'], 'Objectifs', 3, 5000);
        }

        if (trim((string)($data['prerequis'] ?? '')) !== '') {
            Validator::string('prerequis', $data['prerequis'], 'Prérequis', 3, 5000);
        }
    }
}
?>
