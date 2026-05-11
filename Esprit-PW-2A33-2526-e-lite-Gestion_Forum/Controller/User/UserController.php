<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../Model/User/User.php';
require_once __DIR__ . '/../../Model/User/Role.php';

class UserController {

    private function isLoggedIn(): bool {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    private function redirect(string $path): void {
        $bp = rtrim(str_replace("\\", "/", dirname($_SERVER['SCRIPT_NAME'])), "/");
        if ($bp === "." || $bp === "/") $bp = "";
        header("Location: $bp$path");
        exit;
    }

    /** Retourne le rôle normalisé depuis la session */
    private function sessionRole(): string {
        return strtolower(trim((string)($_SESSION['user_role'] ?? '')));
    }

    private function isAdminOrFormateur(): bool {
        if (!$this->isLoggedIn()) return false;
        return in_array($this->sessionRole(), ['admin', 'formateur', 'teacher', 'instructor'], true);
    }

    private function isStudent(): bool {
        return in_array($this->sessionRole(), ['etudiant', 'student', 'eleve'], true);
    }

    public function login(): void {
        if ($this->isLoggedIn()) {
            $this->redirect($this->isAdminOrFormateur() ? '/forum/manage' : '/forum');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_captcha = (int)($_POST['captcha'] ?? 0);
            $email        = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
            $password     = $_POST['password'] ?? '';

            if (!isset($_SESSION['captcha_result']) || $user_captcha !== $_SESSION['captcha_result']) {
                $error = "Hell no bot";
                $_SESSION['captcha_a']      = rand(1, 10);
                $_SESSION['captcha_b']      = rand(1, 10);
                $_SESSION['captcha_result'] = $_SESSION['captcha_a'] + $_SESSION['captcha_b'];
                include __DIR__ . '/../../View/layout/header.php';
                include __DIR__ . '/../../View/User/auth/login.php';
                include __DIR__ . '/../../View/layout/footer.php';
                return;
            }

            $user = User::authenticate($email, $password);
            if ($user) {
                $_SESSION['user_id']    = $user['idUser'];
                $_SESSION['user_role']  = $user['role'];   // ENUM: etudiant | formateur | admin
                $_SESSION['user_nom']   = $user['nom'];
                $_SESSION['user_prenom']= $user['prenom'];
                unset($_SESSION['captcha_a'], $_SESSION['captcha_b'], $_SESSION['captcha_result']);
                $this->redirect($this->isAdminOrFormateur() ? '/forum/manage' : '/forum');
            } else {
                $error = "Email ou mot de passe incorrect.";
            }
        } else {
            $_SESSION['captcha_a']      = rand(1, 10);
            $_SESSION['captcha_b']      = rand(1, 10);
            $_SESSION['captcha_result'] = $_SESSION['captcha_a'] + $_SESSION['captcha_b'];
        }

        include __DIR__ . '/../../View/layout/header.php';
        include __DIR__ . '/../../View/User/auth/login.php';
        include __DIR__ . '/../../View/layout/footer.php';
    }

    public function logout(): void {
        session_destroy();
        $this->redirect('/login');
    }

    public function register(): void {
        if ($this->isLoggedIn()) $this->redirect('/');
        $roles = Role::getAll(); // retourne etudiant / formateur

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom     = htmlspecialchars(trim($_POST['nom']    ?? ''), ENT_QUOTES, 'UTF-8');
            $prenom  = htmlspecialchars(trim($_POST['prenom'] ?? ''), ENT_QUOTES, 'UTF-8');
            $email   = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
            $role    = $_POST['role'] ?? 'etudiant';
            $password= $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';
            $errors  = [];

            if (empty($nom)    || strlen($nom)    < 2) $errors[] = "Nom min 2 caractères.";
            if (empty($prenom) || strlen($prenom) < 2) $errors[] = "Prénom min 2 caractères.";
            if (empty($email)  || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide.";
            if (strlen($password) < 6)  $errors[] = "Mot de passe min 6 caractères.";
            if ($password !== $confirm)  $errors[] = "Les mots de passe ne correspondent pas.";
            if (User::findByEmail($email)) $errors[] = "Email déjà utilisé.";

            // Seuls etudiant et formateur sont autorisés à l'inscription
            $allowedRoles = ['etudiant', 'formateur'];
            if (!in_array($role, $allowedRoles, true)) $errors[] = "Rôle invalide.";

            if (empty($errors)) {
                $userId = User::create([
                    'nom'        => $nom,
                    'prenom'     => $prenom,
                    'email'      => $email,
                    'motDePasse' => password_hash($password, PASSWORD_DEFAULT),
                    'role'       => $role,
                    'statut'     => 'actif'
                ]);
                if ($userId) {
                    $_SESSION['flash']['success'] = "Inscription réussie. Veuillez vous connecter.";
                    $this->redirect('/login');
                } else {
                    $error = "Erreur lors de l'inscription.";
                }
            } else {
                $error = implode('<br>', $errors);
            }
        }

        include __DIR__ . '/../../View/layout/header.php';
        include __DIR__ . '/../../View/User/auth/register.php';
        include __DIR__ . '/../../View/layout/footer.php';
    }

    public function profile(): void {
        if (!$this->isLoggedIn()) $this->redirect('/login');
        $user = User::findById($_SESSION['user_id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['upload_avatar']) && isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $finfo   = finfo_open(FILEINFO_MIME_TYPE);
                $mime    = finfo_file($finfo, $_FILES['avatar']['tmp_name']);
                finfo_close($finfo);
                if (!in_array($mime, $allowed)) {
                    $error = "Format non autorisé.";
                } elseif ($_FILES['avatar']['size'] > 5 * 1024 * 1024) {
                    $error = "Fichier trop volumineux (max 5 Mo).";
                } else {
                    $uploadDir = "uploads/User/profile_pictures/";
                    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                    $ext     = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                    $newName = "avatar_" . $_SESSION['user_id'] . "_" . time() . "." . $ext;
                    $target  = $uploadDir . $newName;
                    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target)) {
                        if (!empty($user['photo']) && file_exists($user['photo'])) unlink($user['photo']);
                        User::update($_SESSION['user_id'], ['photo' => $target]);
                        $_SESSION['flash']['success'] = "Photo de profil mise à jour.";
                        $this->redirect('/profile');
                    } else {
                        $error = "Erreur lors du téléchargement.";
                    }
                }
            } else {
                $nom       = htmlspecialchars(trim($_POST['nom']    ?? ''), ENT_QUOTES, 'UTF-8');
                $prenom    = htmlspecialchars(trim($_POST['prenom'] ?? ''), ENT_QUOTES, 'UTF-8');
                $telephone = trim($_POST['telephone'] ?? '');
                $bio       = trim($_POST['bio'] ?? '');
                $errors    = [];

                if (empty($nom)    || strlen($nom)    < 2) $errors[] = "Nom min 2 caractères.";
                if (empty($prenom) || strlen($prenom) < 2) $errors[] = "Prénom min 2 caractères.";
                if (!empty($telephone) && !preg_match('/^[0-9+\-\s]{8,20}$/', $telephone)) $errors[] = "Téléphone invalide.";
                if (!empty($bio) && strlen($bio) > 500) $errors[] = "Bio max 500 caractères.";

                if (empty($errors)) {
                    User::update($_SESSION['user_id'], [
                        'nom'       => $nom,
                        'prenom'    => $prenom,
                        'telephone' => $telephone,
                        'bio'       => $bio
                    ]);
                    $_SESSION['user_nom']    = $nom;
                    $_SESSION['user_prenom'] = $prenom;
                    $_SESSION['flash']['success'] = "Profil mis à jour.";
                    $this->redirect('/profile');
                } else {
                    $error = implode('<br>', $errors);
                }
            }
        }

        include __DIR__ . '/../../View/layout/header.php';
        include __DIR__ . '/../../View/User/profile/edit.php';
        include __DIR__ . '/../../View/layout/footer.php';
    }

    public function deleteAccount(): void {
        if (!$this->isLoggedIn()) $this->redirect('/login');
        User::hardDelete($_SESSION['user_id']);
        session_destroy();
        setcookie(session_name(), '', time() - 3600, '/');
        header("Location: /");
        exit;
    }

    public function adminDashboard(): void {
        if (!$this->isLoggedIn() || !$this->isAdminOrFormateur()) $this->redirect('/login');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['change_role']) && $this->sessionRole() === 'admin') {
                $userId  = (int)$_POST['user_id'];
                $newRole = $_POST['new_role'] ?? 'etudiant';
                if ($userId != $_SESSION['user_id']) {
                    User::changeRole($userId, $newRole);
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

        $users        = User::getAll();
        $roles        = Role::getAll();
        $currentRole  = $this->sessionRole();

        include __DIR__ . '/../../View/layout/header.php';
        include __DIR__ . '/../../View/User/admin/dashboard.php';
        include __DIR__ . '/../../View/layout/footer.php';
    }

    public function studentDashboard(): void {
        if (!$this->isLoggedIn() || !$this->isStudent()) $this->redirect('/login');
        $user = User::findById($_SESSION['user_id']);
        include __DIR__ . '/../../View/layout/header.php';
        include __DIR__ . '/../../View/User/student/dashboard.php';
        include __DIR__ . '/../../View/layout/footer.php';
    }

    public function googleLogin(): void {
        $client = new Google_Client();
        $client->setClientId(GOOGLE_CLIENT_ID);
        $client->setClientSecret(GOOGLE_CLIENT_SECRET);
        $client->setRedirectUri(GOOGLE_REDIRECT_URI);
        $client->addScope('email');
        $client->addScope('profile');
        header('Location: ' . $client->createAuthUrl());
        exit;
    }

    public function googleCallback(): void {
        $client = new Google_Client();
        $client->setClientId(GOOGLE_CLIENT_ID);
        $client->setClientSecret(GOOGLE_CLIENT_SECRET);
        $client->setRedirectUri(GOOGLE_REDIRECT_URI);
        $client->authenticate($_GET['code']);
        $oauth    = new Google_Service_Oauth2($client);
        $userInfo = $oauth->userinfo->get();
        $email    = $userInfo->email;
        $user     = User::findByEmail($email);
        if (!$user) {
            $userId = User::create([
                'nom'        => $userInfo->familyName ?? '',
                'prenom'     => $userInfo->givenName  ?? '',
                'email'      => $email,
                'motDePasse' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
                'role'       => 'etudiant',
                'statut'     => 'actif'
            ]);
            $user = User::findById($userId);
        }
        $_SESSION['user_id']     = $user['idUser'];
        $_SESSION['user_role']   = $user['role'];
        $_SESSION['user_nom']    = $user['nom'];
        $_SESSION['user_prenom'] = $user['prenom'];
        $this->redirect($this->isAdminOrFormateur() ? '/forum/manage' : '/forum');
    }

    public function forgotPassword(): void {
        if ($this->isLoggedIn()) $this->redirect('/');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email  = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
            $userId = (int)($_POST['user_id'] ?? 0);
            $user   = User::findByEmail($email);
            if ($user && $user['idUser'] === $userId) {
                $_SESSION['reset_user_id'] = $user['idUser'];
                $this->redirect('/reset-password');
            } else {
                $error = "Email ou ID utilisateur invalide.";
            }
        }
        include __DIR__ . '/../../View/layout/header.php';
        include __DIR__ . '/../../View/User/auth/forgot.php';
        include __DIR__ . '/../../View/layout/footer.php';
    }

    public function resetPassword(): void {
        if ($this->isLoggedIn()) $this->redirect('/');
        if (!isset($_SESSION['reset_user_id'])) $this->redirect('/forgot');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirm  = $_POST['confirm_password'] ?? '';
            if (strlen($password) < 6) {
                $error = "Mot de passe trop court (min 6).";
            } elseif ($password !== $confirm) {
                $error = "Les mots de passe ne correspondent pas.";
            } else {
                User::update($_SESSION['reset_user_id'], ['motDePasse' => password_hash($password, PASSWORD_DEFAULT)]);
                unset($_SESSION['reset_user_id']);
                $_SESSION['flash']['success'] = "Mot de passe réinitialisé avec succès.";
                $this->redirect('/login');
            }
        }
        include __DIR__ . '/../../View/layout/header.php';
        include __DIR__ . '/../../View/User/auth/reset-password.php';
        include __DIR__ . '/../../View/layout/footer.php';
    }

    public function resetSuccess(): void {
        if ($this->isLoggedIn()) $this->redirect('/');
        include __DIR__ . '/../../View/layout/header.php';
        include __DIR__ . '/../../View/User/auth/reset-success.php';
        include __DIR__ . '/../../View/layout/footer.php';
    }

    public function removePhoto(): void {
        if (!$this->isLoggedIn()) $this->redirect('/login');
        $user = User::findById($_SESSION['user_id']);
        if (!empty($user['photo']) && file_exists($user['photo'])) {
            unlink($user['photo']);
        }
        User::update($_SESSION['user_id'], ['photo' => null]);
        $_SESSION['flash']['success'] = "Photo de profil supprimée.";
        $this->redirect('/profile');
    }
}
