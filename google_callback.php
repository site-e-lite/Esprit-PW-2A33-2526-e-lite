<?php
/**
 * google_callback.php
 * Standalone Google OAuth callback — independent of the main router.
 * Access: http://localhost/gestioncours/google_callback.php
 *
 * Google redirects here after the user grants permission.
 * This file creates/finds the user in DB and sets the session.
 */
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Model/User/User.php';

// ── Helpers ──────────────────────────────────────────────────────
function showError(string $title, string $detail): void
{
    http_response_code(400);
    echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8">
    <title>Erreur OAuth</title>
    <style>body{font-family:monospace;background:#0d1117;color:#c9d1d9;padding:2rem;max-width:700px;margin:0 auto;}
    .err{color:#f85149;} a{color:#58a6ff;}</style></head><body>
    <h2 class="err">❌ ' . htmlspecialchars($title) . '</h2>
    <p>' . htmlspecialchars($detail) . '</p>
    <p><a href="/gestioncours/login">← Retour à la connexion</a></p>
    </body></html>';
    exit;
}

// ── Validate callback ─────────────────────────────────────────────
if (isset($_GET['error'])) {
    showError(
        'Accès refusé par Google',
        'L\'utilisateur a annulé ou Google a retourné une erreur : ' . $_GET['error']
    );
}

if (empty($_GET['code'])) {
    showError('Code manquant', 'Aucun code d\'autorisation reçu de Google.');
}

// ── Build client with same redirect URI used in google_login.php ──
$redirectUri = $_SESSION['google_redirect_uri']
    ?? 'http://localhost/gestioncours/google_callback.php';

$client = new Google_Client();
$client->setClientId(GOOGLE_CLIENT_ID);
$client->setClientSecret(GOOGLE_CLIENT_SECRET);
$client->setRedirectUri($redirectUri);

// ── Exchange code for token ───────────────────────────────────────
try {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    if (isset($token['error'])) {
        showError(
            'Erreur d\'échange de token',
            $token['error_description'] ?? $token['error']
        );
    }

    $client->setAccessToken($token);

    // Get user info from Google
    $oauth2   = new Google_Service_Oauth2($client);
    $userInfo = $oauth2->userinfo->get();

    $email   = $userInfo->email;
    $nom     = $userInfo->familyName  ?? '';
    $prenom  = $userInfo->givenName   ?? '';
    $photo   = $userInfo->picture     ?? null;

    if (empty($email)) {
        showError('Email manquant', 'Google n\'a pas fourni d\'adresse email.');
    }

} catch (Exception $e) {
    showError('Erreur Google API', $e->getMessage());
}

// ── Find or create user in DB ─────────────────────────────────────
try {
    $user = User::findByEmail($email);

    if (!$user) {
        // Create new user with student role (idRole = 2)
        $pdo    = Config::getConnexion();
        $stmt   = $pdo->prepare("SELECT idRole FROM role WHERE nom = 'etudiant' LIMIT 1");
        $stmt->execute();
        $idRole = $stmt->fetchColumn() ?: 2;

        $userId = User::create([
            'nom'        => $nom,
            'prenom'     => $prenom,
            'email'      => $email,
            'motDePasse' => password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
            'idRole'     => $idRole,
            'statut'     => 'actif',
            'photo'      => $photo,
        ]);

        $user = User::findById((int)$userId);
    }

    if (!$user) {
        showError('Erreur base de données', 'Impossible de créer ou trouver l\'utilisateur.');
    }

    // Update photo if changed
    if ($photo && ($user['photo'] ?? '') !== $photo) {
        User::update((int)$user['idUser'], ['photo' => $photo]);
    }

} catch (Exception $e) {
    showError('Erreur base de données', $e->getMessage());
}

// ── Set session ───────────────────────────────────────────────────
$_SESSION['user_id']     = $user['idUser'];
$_SESSION['user_role']   = $user['idRole'];
$_SESSION['role_nom']    = $user['role_nom'] ?? '';
$_SESSION['user_nom']    = $user['nom'];
$_SESSION['user_prenom'] = $user['prenom'];

// Clean up OAuth session data
unset($_SESSION['google_redirect_uri']);

// ── Redirect ──────────────────────────────────────────────────────
$afterLogin = $_SESSION['google_after_login'] ?? '/gestioncours/View/FrontOffice/dashboard.php';
unset($_SESSION['google_after_login']);

header('Location: ' . $afterLogin);
exit;
