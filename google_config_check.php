<?php
/**
 * google_config_check.php
 * Diagnostic tool for Google OAuth redirect_uri_mismatch errors.
 * Access: http://localhost/gestioncours/google_config_check.php
 */
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Google OAuth — Diagnostic</title>
    <style>
        body  { font-family: monospace; background: #0d1117; color: #c9d1d9; padding: 2rem; max-width: 900px; margin: 0 auto; }
        h1    { color: #58a6ff; }
        h2    { color: #79c0ff; margin-top: 2rem; }
        .ok   { color: #3fb950; }
        .warn { color: #d29922; }
        .err  { color: #f85149; }
        .box  { background: #161b22; border: 1px solid #30363d; border-radius: 8px; padding: 1.2rem; margin: 1rem 0; }
        .copy { background: #0d1117; border: 1px solid #388bfd; border-radius: 6px; padding: .8rem 1rem; font-size: 1rem; color: #79c0ff; word-break: break-all; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #30363d; padding: .6rem 1rem; text-align: left; }
        th { background: #161b22; color: #58a6ff; }
        ol li { margin: .5rem 0; line-height: 1.7; }
        a  { color: #58a6ff; }
    </style>
</head>
<body>
<h1>🔍 Google OAuth — Diagnostic redirect_uri_mismatch</h1>

<?php
$clientId    = GOOGLE_CLIENT_ID;
$clientSecret= GOOGLE_CLIENT_SECRET;
$redirectUri = GOOGLE_REDIRECT_URI;

// Detect actual base URL
$scheme   = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script   = $_SERVER['SCRIPT_NAME'] ?? '/gestioncours/google_config_check.php';
$basePath = rtrim(str_replace('\\', '/', dirname(dirname($script))), '/');
$actualBase = $scheme . '://' . $host . '/gestioncours';
$expectedCallback = $actualBase . '/google_callback.php';
$routerCallback   = $actualBase . '/login/google-callback';
?>

<h2>1. Configuration actuelle</h2>
<div class="box">
<table>
    <tr><th>Paramètre</th><th>Valeur</th><th>Statut</th></tr>
    <tr>
        <td>GOOGLE_CLIENT_ID</td>
        <td><?= htmlspecialchars(substr($clientId, 0, 30)) ?>...</td>
        <td class="<?= !empty($clientId) ? 'ok' : 'err' ?>"><?= !empty($clientId) ? '✓ Défini' : '✗ Vide' ?></td>
    </tr>
    <tr>
        <td>GOOGLE_CLIENT_SECRET</td>
        <td><?= !empty($clientSecret) ? str_repeat('*', 12) : '(vide)' ?></td>
        <td class="<?= !empty($clientSecret) ? 'ok' : 'err' ?>"><?= !empty($clientSecret) ? '✓ Défini' : '✗ Vide' ?></td>
    </tr>
    <tr>
        <td>GOOGLE_REDIRECT_URI (config.php)</td>
        <td><?= htmlspecialchars($redirectUri) ?></td>
        <td class="<?= !empty($redirectUri) ? 'ok' : 'err' ?>"><?= !empty($redirectUri) ? '✓ Défini' : '✗ Vide' ?></td>
    </tr>
</table>
</div>

<h2>2. URLs détectées automatiquement</h2>
<div class="box">
<table>
    <tr><th>Type</th><th>URL</th></tr>
    <tr><td>Base du projet</td><td><?= htmlspecialchars($actualBase) ?></td></tr>
    <tr><td>Callback fichier direct (recommandé)</td><td class="ok"><?= htmlspecialchars($expectedCallback) ?></td></tr>
    <tr><td>Callback via routeur</td><td><?= htmlspecialchars($routerCallback) ?></td></tr>
    <tr><td>URI dans config.php</td><td class="<?= $redirectUri === $expectedCallback || $redirectUri === $routerCallback ? 'ok' : 'warn' ?>"><?= htmlspecialchars($redirectUri) ?></td></tr>
</table>

<?php if ($redirectUri !== $expectedCallback && $redirectUri !== $routerCallback): ?>
    <p class="warn">⚠️ L'URI dans config.php ne correspond à aucune URL détectée. C'est probablement la cause du mismatch.</p>
<?php else: ?>
    <p class="ok">✓ L'URI dans config.php correspond à une URL valide.</p>
<?php endif; ?>
</div>

<h2>3. URIs à ajouter dans Google Cloud Console</h2>
<div class="box">
    <p>Ajoutez <strong>les deux URIs suivantes</strong> dans votre projet Google Cloud :</p>
    <p><strong>URI 1 (fichier direct — recommandé) :</strong></p>
    <div class="copy"><?= htmlspecialchars($expectedCallback) ?></div>
    <p><strong>URI 2 (via routeur) :</strong></p>
    <div class="copy"><?= htmlspecialchars($routerCallback) ?></div>
</div>

<h2>4. Étapes Google Cloud Console</h2>
<div class="box">
<ol>
    <li>Ouvrez <a href="https://console.cloud.google.com/apis/credentials" target="_blank">https://console.cloud.google.com/apis/credentials</a></li>
    <li>Cliquez sur votre <strong>OAuth 2.0 Client ID</strong> (type "Application Web")</li>
    <li>Dans <strong>"Origines JavaScript autorisées"</strong>, ajoutez :
        <div class="copy">http://localhost</div>
    </li>
    <li>Dans <strong>"URI de redirection autorisées"</strong>, ajoutez ces deux URIs :
        <div class="copy"><?= htmlspecialchars($expectedCallback) ?></div>
        <div class="copy"><?= htmlspecialchars($routerCallback) ?></div>
    </li>
    <li>Cliquez <strong>Enregistrer</strong></li>
    <li>Attendez <strong>5 minutes</strong> (propagation Google)</li>
    <li>Testez : <a href="<?= htmlspecialchars($actualBase) ?>/google_login.php">Connexion Google →</a></li>
</ol>
</div>

<h2>5. Vérification de la bibliothèque</h2>
<div class="box">
<?php
$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
    echo '<p class="ok">✓ vendor/autoload.php trouvé</p>';
    if (class_exists('Google_Client')) {
        echo '<p class="ok">✓ Google_Client disponible (google/apiclient installé)</p>';
    } else {
        echo '<p class="err">✗ Google_Client introuvable — exécutez : <code>composer require google/apiclient</code></p>';
    }
} else {
    echo '<p class="err">✗ vendor/autoload.php introuvable — exécutez : <code>composer install</code></p>';
}
?>
</div>

<p style="margin-top:2rem; color:#555;">
    Après configuration, testez :
    <a href="<?= htmlspecialchars($actualBase) ?>/google_login.php">google_login.php</a>
</p>
</body>
</html>
