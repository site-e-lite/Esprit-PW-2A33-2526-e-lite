<?php
/**
 * google_login.php — Redirects directly to Google OAuth consent screen.
 * Access: http://localhost/gestioncours/google_login.php
 */
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

$redirectUri = 'http://localhost/gestioncours/google_callback.php';

$client = new Google_Client();
$client->setClientId(GOOGLE_CLIENT_ID);
$client->setClientSecret(GOOGLE_CLIENT_SECRET);
$client->setRedirectUri($redirectUri);
$client->addScope('email');
$client->addScope('profile');
$client->setAccessType('online');
$client->setPrompt('select_account');

$_SESSION['google_redirect_uri'] = $redirectUri;
$_SESSION['google_after_login']  = $_GET['redirect'] ?? '/gestioncours/View/FrontOffice/dashboard.php';

header('Location: ' . $client->createAuthUrl());
exit;
