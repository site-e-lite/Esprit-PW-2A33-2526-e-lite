<?php
require_once __DIR__ . '/../Model/CourseModel.php';
require_once __DIR__ . '/../Model/VirtualClassModel.php';
require_once __DIR__ . '/../Model/SessionModel.php';

class VirtualClassController
{
    private CourseModel       $courseModel;
    private VirtualClassModel $vcModel;
    private SessionModel      $sessionModel;

    public function __construct()
    {
        $this->courseModel  = new CourseModel();
        $this->vcModel      = new VirtualClassModel();
        $this->sessionModel = new SessionModel();
    }

    // ─── COURSE ACTIONS ───────────────────────────────────────────────────────

    public function addCourse(array $data): void
    {
        if (empty($data['titre'])) {
            $this->redirect('Le titre du cours est requis.', null, 'courses');
        }
        $this->courseModel->create($data);
        $this->redirect(null, 'Cours ajouté avec succès.', 'courses');
    }

    public function editCourse(int $id, array $data): void
    {
        if (empty($data['titre'])) {
            $this->redirect('Le titre du cours est requis.', null, 'courses');
        }
        $this->courseModel->update($id, $data);
        $this->redirect(null, 'Cours modifié avec succès.', 'courses');
    }

    public function deleteCourse(int $id): void
    {
        $this->courseModel->delete($id);
        $this->redirect(null, 'Cours supprimé.', 'courses');
    }

    // ─── VIRTUALCLASS ACTIONS ─────────────────────────────────────────────────

    public function addClass(array $data): void
    {
        if (empty($data['titre']) || empty($data['lienAcces']) || empty($data['idCourse'])) {
            $this->redirect("Titre, lien d'accès et cours associé sont requis.", null, 'classes');
        }
        $this->vcModel->create($data);
        $this->redirect(null, 'Classe virtuelle ajoutée avec succès.', 'classes');
    }

    public function editClass(int $id, array $data): void
    {
        if (empty($data['titre']) || empty($data['lienAcces']) || empty($data['idCourse'])) {
            $this->redirect("Titre, lien d'accès et cours associé sont requis.", null, 'classes');
        }
        $this->vcModel->update($id, $data);
        $this->redirect(null, 'Classe virtuelle modifiée avec succès.', 'classes');
    }

    public function deleteClass(int $id): void
    {
        $this->vcModel->delete($id);
        $this->redirect(null, 'Classe virtuelle supprimée.', 'classes');
    }

    // ─── SESSION ACTIONS ──────────────────────────────────────────────────────

    public function addSession(array $data): void
    {
        if (empty($data['dateSession']) || empty($data['heureDebut']) || empty($data['heureFin']) || empty($data['idClass'])) {
            $this->redirect('Tous les champs de session sont requis.', null, 'sessions');
        }
        if ((int)($data['capacite'] ?? 0) < 0) {
            $this->redirect('La capacité ne peut pas être négative.', null, 'sessions');
        }
        try {
            $this->sessionModel->create($data);
            $this->redirect(null, 'Session ajoutée avec succès.', 'sessions');
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $this->redirect("Erreur : la classe virtuelle sélectionnée n'existe pas. Créez d'abord une Classe Virtuelle dans l'onglet Classes.", null, 'sessions');
            }
            $this->redirect('Erreur base de données : ' . $e->getMessage(), null, 'sessions');
        }
    }

    public function editSession(int $id, array $data): void
    {
        if (empty($data['dateSession']) || empty($data['heureDebut']) || empty($data['heureFin']) || empty($data['idClass'])) {
            $this->redirect('Tous les champs de session sont requis.', null, 'sessions');
        }
        try {
            $this->sessionModel->update($id, $data);
            $this->redirect(null, 'Session modifiée avec succès.', 'sessions');
        } catch (PDOException $e) {
            $this->redirect('Erreur : ' . $e->getMessage(), null, 'sessions');
        }
    }

    public function deleteSession(int $id): void
    {
        $this->sessionModel->delete($id);
        $this->redirect(null, 'Session supprimée.', 'sessions');
    }

    // ─── DATA GETTERS ─────────────────────────────────────────────────────────

    public function getAllCourses(): array  { return $this->courseModel->getAll(); }
    public function getAllClasses(): array  { return $this->vcModel->getAll(); }
    public function getAllSessions(): array { return $this->sessionModel->getAll(); }

    // ─── REDIRECT — always stays on virtualclass.php ─────────────────────────

    private function redirect(?string $error = null, ?string $success = null, string $tab = 'courses'): void
    {
        $params = ['tab' => $tab];
        if ($error)   $params['error']   = $error;
        if ($success) $params['success'] = $success;

        // Always redirect to virtualclass.php — never back to dashboard or any other page
        $host   = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
        $script = $_SERVER['SCRIPT_NAME']; // e.g. /Gestion_Virtuelclass/View/BackOffice/virtualclass.php
        $dir    = dirname($script);        // e.g. /Gestion_Virtuelclass/View/BackOffice
        header('Location: ' . $host . $dir . '/virtualclass.php?' . http_build_query($params));
        exit;
    }
}
?>
