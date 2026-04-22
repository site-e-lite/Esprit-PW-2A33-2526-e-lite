<?php
require_once __DIR__ . '/../Model/User.php';
require_once __DIR__ . '/../Model/Role.php';

class UserController {
    private function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    private function redirect($path) {
        header("Location: $path");
        exit;
    }

    private function isStudent() {
        $role = strtolower($_SESSION['role_nom'] ?? '');
        return in_array($role, ['student', 'etudiant']);
    }

    private function isAdminOrFormateur() {
        $role = strtolower($_SESSION['role_nom'] ?? '');
        return in_array($role, ['admin', 'administrateur', 'formateur']);
    }

    public function login() {
        if ($this->isLoggedIn()) {
            if ($this->isStudent()) $this->redirect('/student/dashboard');
            else $this->redirect('/admin/dashboard');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $error = "Email et mot de passe requis.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = "Email invalide.";
            } else {
                $user = User::authenticate($email, $password);
                if ($user) {
                    $_SESSION['user_id'] = $user['idUser'];
                    $_SESSION['role_nom'] = $user['role_nom'];
                    $_SESSION['user_nom'] = $user['nom'];
                    $_SESSION['user_prenom'] = $user['prenom'];
                    if ($this->isStudent()) $this->redirect('/student/dashboard');
                    else $this->redirect('/admin/dashboard');
                } else {
                    $error = "Email ou mot de passe incorrect.";
                }
            }
        }
        include __DIR__ . '/../View/layout/header.php';
        include __DIR__ . '/../View/auth/login.php';
        include __DIR__ . '/../View/layout/footer.php';
    }

    public function logout() {
        session_destroy();
        $this->redirect('/login');
    }

    public function register() {
        if ($this->isLoggedIn()) $this->redirect('/');
        $roles = Role::getAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = htmlspecialchars(trim($_POST['nom'] ?? ''), ENT_QUOTES, 'UTF-8');
            $prenom = htmlspecialchars(trim($_POST['prenom'] ?? ''), ENT_QUOTES, 'UTF-8');
            $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
            $idRole = (int)($_POST['role'] ?? 0);
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            $errors = [];

            // Validation rules
            if (empty($nom)) $errors[] = "Le nom est requis.";
            elseif (strlen($nom) < 2) $errors[] = "Le nom doit contenir au moins 2 caractères.";
            if (empty($prenom)) $errors[] = "Le prénom est requis.";
            elseif (strlen($prenom) < 2) $errors[] = "Le prénom doit contenir au moins 2 caractères.";
            if (empty($email)) $errors[] = "L'email est requis.";
            elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide.";
            if (strlen($password) < 6) $errors[] = "Le mot de passe doit contenir au moins 6 caractères.";
            if ($password !== $confirm) $errors[] = "Les mots de passe ne correspondent pas.";
            if (User::findByEmail($email)) $errors[] = "Cet email est déjà utilisé.";

            // Prevent registration with admin role (only via admin panel)
            $roleExists = false;
            foreach ($roles as $r) {
                if ($r['idRole'] == $idRole && strtolower($r['nom']) !== 'admin') {
                    $roleExists = true;
                    break;
                }
            }
            if (!$roleExists) $errors[] = "Rôle invalide ou non autorisé.";

            if (empty($errors)) {
                $data = [
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'email' => $email,
                    'motDePasse' => password_hash($password, PASSWORD_DEFAULT),
                    'idRole' => $idRole,
                    'statut' => 'actif'
                ];
                $userId = User::create($data);
                if ($userId) {
                    $_SESSION['flash']['success'] = "Inscription réussie. Veuillez vous connecter.";
                    $this->redirect('/login');
                } else {
                    $error = "Erreur lors de l'inscription. Veuillez réessayer.";
                }
            } else {
                $error = implode('<br>', $errors);
            }
        }
        include __DIR__ . '/../View/layout/header.php';
        include __DIR__ . '/../View/auth/register.php';
        include __DIR__ . '/../View/layout/footer.php';
    }

    public function profile() {
        if (!$this->isLoggedIn()) $this->redirect('/login');
        $user = User::findById($_SESSION['user_id']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = htmlspecialchars(trim($_POST['nom'] ?? ''), ENT_QUOTES, 'UTF-8');
            $prenom = htmlspecialchars(trim($_POST['prenom'] ?? ''), ENT_QUOTES, 'UTF-8');
            $telephone = trim($_POST['telephone'] ?? '');
            $bio = trim($_POST['bio'] ?? '');

            $errors = [];
            if (empty($nom)) $errors[] = "Le nom est requis.";
            elseif (strlen($nom) < 2) $errors[] = "Le nom doit contenir au moins 2 caractères.";
            if (empty($prenom)) $errors[] = "Le prénom est requis.";
            elseif (strlen($prenom) < 2) $errors[] = "Le prénom doit contenir au moins 2 caractères.";
            if (!empty($telephone) && !preg_match('/^[0-9+\-\s]{8,20}$/', $telephone)) {
                $errors[] = "Numéro de téléphone invalide (8-20 chiffres, +, -, espaces).";
            }
            if (!empty($bio) && strlen($bio) > 500) {
                $errors[] = "La bio ne peut pas dépasser 500 caractères.";
            }

            if (empty($errors)) {
                User::update($_SESSION['user_id'], ['nom' => $nom, 'prenom' => $prenom, 'telephone' => $telephone, 'bio' => $bio]);
                $_SESSION['user_nom'] = $nom;
                $_SESSION['user_prenom'] = $prenom;
                $_SESSION['flash']['success'] = "Profil mis à jour.";
                $this->redirect('/profile');
            } else {
                $error = implode('<br>', $errors);
            }
        }
        include __DIR__ . '/../View/layout/header.php';
        include __DIR__ . '/../View/profile/edit.php';
        include __DIR__ . '/../View/layout/footer.php';
    }

    public function deleteAccount() {
        if (!$this->isLoggedIn()) $this->redirect('/login');
        $userId = $_SESSION['user_id'];
        User::hardDelete($userId);
        session_destroy();
        $_SESSION = [];
        setcookie(session_name(), '', time() - 3600, '/');
        header("Location: /");
        exit;
    }

    public function adminDashboard() {
        if (!$this->isLoggedIn() || !$this->isAdminOrFormateur()) $this->redirect('/login');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['change_role']) && strtolower($_SESSION['role_nom'] ?? '') === 'admin') {
                $userId = (int)$_POST['user_id'];
                $newRoleId = (int)$_POST['new_role'];
                if ($userId != $_SESSION['user_id']) {
                    User::changeRole($userId, $newRoleId);
                    $_SESSION['flash']['success'] = "Rôle modifié.";
                }
                $this->redirect('/admin/dashboard');
            } elseif (isset($_POST['ban_user'])) {
                $userId = (int)$_POST['user_id'];
                if ($userId != $_SESSION['user_id']) {
                    User::softDelete($userId);
                    $_SESSION['flash']['success'] = "Utilisateur banni.";
                }
                $this->redirect('/admin/dashboard');
            } elseif (isset($_POST['delete_user'])) {
                $userId = (int)$_POST['user_id'];
                if ($userId != $_SESSION['user_id']) {
                    User::hardDelete($userId);
                    $_SESSION['flash']['success'] = "Utilisateur supprimé définitivement.";
                }
                $this->redirect('/admin/dashboard');
            }
        }
        $users = User::getAll();
        $roles = Role::getAll();
        include __DIR__ . '/../View/layout/header.php';
        include __DIR__ . '/../View/admin/dashboard.php';
        include __DIR__ . '/../View/layout/footer.php';
    }

    public function studentDashboard() {
        if (!$this->isLoggedIn() || !$this->isStudent()) $this->redirect('/login');
        $user = User::findById($_SESSION['user_id']);
        include __DIR__ . '/../View/layout/header.php';
        include __DIR__ . '/../View/student/dashboard.php';
        include __DIR__ . '/../View/layout/footer.php';
    }
}
