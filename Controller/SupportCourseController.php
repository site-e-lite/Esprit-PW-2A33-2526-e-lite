<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Utils/Validator.php';

class SupportCourseController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Config::getInstance()->getConnexion();
    }

    public function listAll(): array
    {
        $sql = 'SELECT idSupport, titre, type, url, description, dateAjout, idCourse FROM support_course ORDER BY idSupport DESC';
        return $this->db->query($sql)->fetchAll();
    }

    public function listByCourse(int $idCourse): array
    {
        $sql = 'SELECT idSupport, titre, type, url, description, dateAjout, idCourse
                FROM support_course
                WHERE idCourse = :idCourse
                ORDER BY dateAjout DESC';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['idCourse' => $idCourse]);
        return $stmt->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $sql = 'SELECT idSupport, titre, type, url, description, dateAjout, idCourse FROM support_course WHERE idSupport = :idSupport';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['idSupport' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function add(array $data): array
    {
        Validator::reset();
        $this->validate($data);
        if (Validator::hasErrors()) {
            return ['success' => false, 'errors' => Validator::getErrors(), 'message' => 'Veuillez corriger les erreurs du formulaire.'];
        }

        $sql = 'INSERT INTO support_course (titre, type, url, description, idCourse)
                VALUES (:titre, :type, :url, :description, :idCourse)';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'titre' => trim((string)$data['titre']),
            'type' => (string)$data['type'],
            'url' => trim((string)$data['url']),
            'description' => trim((string)($data['description'] ?? '')) !== '' ? trim((string)$data['description']) : null,
            'idCourse' => (int)$data['idCourse'],
        ]);

        return ['success' => true, 'errors' => [], 'message' => 'Support ajouté avec succès.'];
    }

    public function update(int $id, array $data): array
    {
        if ($this->getById($id) === null) {
            return ['success' => false, 'errors' => ['id' => 'Support introuvable.'], 'message' => 'Support introuvable.'];
        }

        Validator::reset();
        $this->validate($data);
        if (Validator::hasErrors()) {
            return ['success' => false, 'errors' => Validator::getErrors(), 'message' => 'Veuillez corriger les erreurs du formulaire.'];
        }

        $sql = 'UPDATE support_course
                SET titre = :titre,
                    type = :type,
                    url = :url,
                    description = :description,
                    idCourse = :idCourse
                WHERE idSupport = :idSupport';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'titre' => trim((string)$data['titre']),
            'type' => (string)$data['type'],
            'url' => trim((string)$data['url']),
            'description' => trim((string)($data['description'] ?? '')) !== '' ? trim((string)$data['description']) : null,
            'idCourse' => (int)$data['idCourse'],
            'idSupport' => $id,
        ]);

        return ['success' => true, 'errors' => [], 'message' => 'Support modifié avec succès.'];
    }

    public function delete(int $id): array
    {
        $stmt = $this->db->prepare('DELETE FROM support_course WHERE idSupport = :idSupport');
        $stmt->execute(['idSupport' => $id]);

        if ($stmt->rowCount() === 0) {
            return ['success' => false, 'errors' => ['id' => 'Support introuvable.'], 'message' => 'Support introuvable.'];
        }

        return ['success' => true, 'errors' => [], 'message' => 'Support supprimé avec succès.'];
    }

    private function validate(array $data): void
    {
        Validator::required('titre', $data['titre'] ?? '', 'Titre');
        Validator::string('titre', $data['titre'] ?? '', 'Titre', 3, 100);

        Validator::required('type', $data['type'] ?? '', 'Type');
        Validator::inArray('type', $data['type'] ?? '', ['pdf', 'video', 'document', 'lien', 'autre'], 'Type');

        Validator::required('url', $data['url'] ?? '', 'URL');
        Validator::url('url', $data['url'] ?? '', 'URL');

        Validator::required('idCourse', $data['idCourse'] ?? '', 'Cours');
        Validator::integer('idCourse', $data['idCourse'] ?? '', 'Cours', 1);

        if (trim((string)($data['description'] ?? '')) !== '') {
            Validator::string('description', $data['description'], 'Description', 3, 1000);
        }
    }
}
?>