<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_pr = realpath(__DIR__ . '/../../..');
$_dr = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
$basePath = ($_pr && $_dr && strpos($_pr, $_dr) === 0)
    ? rtrim(str_replace('\\', '/', substr($_pr, strlen($_dr))), '/')
    : '';
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . $basePath . '/login');
    exit;
}

require_once __DIR__ . '/../../../Controller/VirtualClass/VirtualClassController.php';

// ── Mailtrap API config (token works directly — no SMTP needed) ──────────
define('MAILTRAP_TOKEN', 'b07db8b011dec4fdd9b7ff1099b3cb01');
define('MAILTRAP_FROM',  'hello@demomailtrap.co');   // pre-verified demo sender
define('MAILTRAP_NAME',  'e-lite Platform');

/**
 * Send HTML email via Mailtrap HTTP API.
 * Uses your API token directly — no SMTP credentials required.
 */
function sendViaMailtrapAPI(array $recipients, string $subject, string $htmlBody, string $fromName): array
{
    $toList = array_map(fn($e) => ['email' => $e], $recipients);

    $payload = json_encode([
        'from'    => ['email' => MAILTRAP_FROM, 'name' => $fromName],
        'to'      => $toList,
        'subject' => $subject,
        'html'    => $htmlBody,
    ]);

    $ch = curl_init('https://send.api.mailtrap.io/api/send');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . MAILTRAP_TOKEN,
            'Content-Type: application/json',
        ],
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        throw new RuntimeException("cURL error: {$curlErr}");
    }

    $data = json_decode($response, true);

    if ($httpCode !== 200 && $httpCode !== 202) {
        $msg = $data['errors'][0] ?? $data['message'] ?? $response;
        throw new RuntimeException("API error {$httpCode}: {$msg}");
    }

    return $data;
}

$controller = new VirtualClassController();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: ' . $basePath . '/virtualclass');
    exit;
}

$vc = $controller->getVirtualClassById($id);
if (!$vc) {
    header('Location: ' . $basePath . '/virtualclass?error=' . rawurlencode('Classe introuvable'));
    exit;
}

$mailSuccess = null;
$mailError   = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_mail') {
    $rawEmails  = trim($_POST['emails'] ?? '');
    $subject    = trim($_POST['subject'] ?? '');
    $customMsg  = trim($_POST['custom_message'] ?? '');
    $senderName = trim($_POST['sender_name'] ?? 'e-lite Platform');

    $emailList = array_filter(array_map('trim', preg_split('/[\s,;]+/', $rawEmails)));
    $validEmails = array_filter($emailList, fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL));

    if (empty($validEmails)) {
        $mailError = 'Aucune adresse e-mail valide fournie.';
    } elseif (empty($subject)) {
        $mailError = 'Le sujet est obligatoire.';
    } else {
        $lien        = htmlspecialchars($vc->getLienAcces());
        $titre       = htmlspecialchars($vc->getTitre());
        $plateforme  = htmlspecialchars($vc->getPlateforme());
        $description = htmlspecialchars($vc->getDescription() ?: 'Rejoignez cette classe virtuelle interactive.');
        $qrUrl       = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($vc->getLienAcces());

        $htmlBody = <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>{$titre}</title></head>
<body style="margin:0;padding:0;background:#09090b;font-family:'Segoe UI',Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="background:#09090b;padding:40px 20px;">
    <tr><td align="center">
      <table width="600" cellpadding="0" cellspacing="0" style="background:#18181b;border-radius:20px;border:1px solid rgba(255,255,255,0.08);overflow:hidden;max-width:600px;">
        <!-- Header -->
        <tr><td style="background:linear-gradient(135deg,#1a1a0a,#2a2000);padding:40px 40px 30px;text-align:center;border-bottom:1px solid rgba(234,179,8,0.2);">
          <div style="font-size:2rem;font-weight:800;color:#f4f4f5;letter-spacing:-1px;">e-lite<span style="color:#eab308;">.</span></div>
          <div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:3px;color:#a1a1aa;margin-top:4px;">Plateforme E-Learning</div>
        </td></tr>
        <!-- Badge -->
        <tr><td style="padding:30px 40px 0;text-align:center;">
          <span style="background:rgba(234,179,8,0.12);border:1px solid rgba(234,179,8,0.4);color:#eab308;padding:6px 18px;border-radius:50px;font-size:0.8rem;font-weight:600;text-transform:uppercase;letter-spacing:1px;">
            Invitation — Classe Virtuelle
          </span>
        </td></tr>
        <!-- Title -->
        <tr><td style="padding:20px 40px 10px;text-align:center;">
          <h1 style="margin:0;font-size:1.8rem;color:#f4f4f5;font-weight:700;line-height:1.2;">{$titre}</h1>
          <p style="margin:12px 0 0;color:#a1a1aa;font-size:0.95rem;line-height:1.6;">{$description}</p>
        </td></tr>
        <!-- Platform badge -->
        <tr><td style="padding:16px 40px;text-align:center;">
          <span style="background:rgba(59,130,246,0.12);border:1px solid rgba(59,130,246,0.3);color:#60a5fa;padding:5px 14px;border-radius:20px;font-size:0.82rem;font-weight:600;">
            📡 {$plateforme}
          </span>
        </td></tr>
        <!-- Custom message -->
HTML;
        if ($customMsg) {
            $htmlBody .= <<<HTML
        <tr><td style="padding:0 40px 10px;">
          <div style="background:rgba(255,255,255,0.03);border-left:3px solid #eab308;border-radius:0 10px 10px 0;padding:14px 18px;">
            <p style="margin:0;color:#d4d4d8;font-size:0.92rem;line-height:1.6;font-style:italic;">{$customMsg}</p>
          </div>
        </td></tr>
HTML;
        }
        $htmlBody .= <<<HTML
        <!-- QR Code -->
        <tr><td style="padding:24px 40px;text-align:center;">
          <p style="margin:0 0 14px;color:#a1a1aa;font-size:0.85rem;text-transform:uppercase;letter-spacing:1px;font-weight:600;">Scannez pour rejoindre</p>
          <div style="display:inline-block;background:#fff;padding:12px;border-radius:14px;box-shadow:0 8px 24px rgba(0,0,0,0.4);">
            <img src="{$qrUrl}" alt="QR Code" width="180" height="180" style="display:block;border-radius:6px;">
          </div>
        </td></tr>
        <!-- CTA Button -->
        <tr><td style="padding:10px 40px 30px;text-align:center;">
          <a href="{$lien}" style="display:inline-block;background:linear-gradient(135deg,#eab308,#fef08a);color:#18181b;text-decoration:none;padding:14px 36px;border-radius:50px;font-weight:700;font-size:1rem;box-shadow:0 4px 20px rgba(234,179,8,0.35);">
            🚀 Rejoindre la Classe
          </a>
          <p style="margin:14px 0 0;color:#71717a;font-size:0.78rem;">Ou copiez ce lien : <a href="{$lien}" style="color:#eab308;">{$lien}</a></p>
        </td></tr>
        <!-- Footer -->
        <tr><td style="background:rgba(0,0,0,0.3);padding:20px 40px;text-align:center;border-top:1px solid rgba(255,255,255,0.05);">
          <p style="margin:0;color:#52525b;font-size:0.78rem;">© 2026 e-lite Platform · Tous droits réservés</p>
          <p style="margin:6px 0 0;color:#52525b;font-size:0.75rem;">Cet e-mail a été envoyé via le BackOffice e-lite.</p>
        </td></tr>
      </table>
    </td></tr>
  </table>
</body></html>
HTML;

        try {
            sendViaMailtrapAPI(
                array_values($validEmails),
                $subject,
                $htmlBody,
                $senderName
            );
            $cnt = count($validEmails);
            $mailSuccess = "✅ E-mail envoyé avec succès à {$cnt} destinataire(s).";
        } catch (Throwable $e) {
            $mailError = "❌ Erreur envoi : " . htmlspecialchars($e->getMessage());
        }
    }
}

$qrSize   = 280;
$qrUrl    = 'https://api.qrserver.com/v1/create-qr-code/?size=' . $qrSize . 'x' . $qrSize . '&data=' . urlencode($vc->getLienAcces()) . '&color=eab308&bgcolor=09090b&margin=10';
$qrDlUrl  = 'https://api.qrserver.com/v1/create-qr-code/?size=600x600&data=' . urlencode($vc->getLienAcces()) . '&color=eab308&bgcolor=09090b&margin=10&format=png';
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>QR Code & Mailing — <?= htmlspecialchars($vc->getTitre()) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= htmlspecialchars($basePath) ?>/View/assets/User/index.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
header { display: none !important; }
body { display:flex; min-height:100vh; background:var(--dark-bg); overflow-x:hidden; }
.sidebar { width:280px; background:rgba(5,5,5,0.8); border-right:1px solid var(--glass-border); padding:2rem; flex-shrink:0; }
.sidebar .nav-links { display:flex; flex-direction:column; gap:0.8rem; margin-top:1rem; }
.sidebar .nav-links a { color:var(--light-gray); text-decoration:none; padding:0.8rem 1rem; border-radius:12px; display:flex; gap:0.8rem; align-items:center; transition:all 0.25s; }
.sidebar .nav-links a.active, .sidebar .nav-links a:hover { background:rgba(234,179,8,0.1); color:var(--accent); }
.main-content { flex:1; padding:2.5rem; overflow-y:auto; height:100vh; }
.page-grid { display:grid; grid-template-columns:1fr 1fr; gap:2rem; margin-top:1.5rem; }
@media(max-width:900px){ .page-grid { grid-template-columns:1fr; } }

/* QR Panel */
.qr-panel { background:var(--glass-bg); border:1px solid var(--glass-border); border-radius:20px; padding:2rem; display:flex; flex-direction:column; align-items:center; gap:1.2rem; }
.qr-frame { background:#fff; padding:16px; border-radius:16px; box-shadow:0 0 40px rgba(234,179,8,0.15), 0 8px 32px rgba(0,0,0,0.5); position:relative; }
.qr-frame img { display:block; border-radius:8px; }
.qr-scan-ring { position:absolute; inset:-6px; border-radius:20px; border:2px solid rgba(234,179,8,0.5); animation:scanPulse 2s ease-in-out infinite; pointer-events:none; }
@keyframes scanPulse { 0%,100%{opacity:0.4;transform:scale(1)} 50%{opacity:1;transform:scale(1.02)} }
.qr-link-box { width:100%; background:rgba(0,0,0,0.3); border:1px solid var(--glass-border); border-radius:10px; padding:0.7rem 1rem; display:flex; align-items:center; gap:0.6rem; }
.qr-link-box span { flex:1; font-size:0.78rem; color:var(--accent); word-break:break-all; }
.copy-btn { background:rgba(234,179,8,0.1); border:1px solid rgba(234,179,8,0.3); color:var(--accent); padding:0.4rem 0.8rem; border-radius:8px; cursor:pointer; font-size:0.78rem; white-space:nowrap; transition:all 0.2s; }
.copy-btn:hover { background:rgba(234,179,8,0.25); }
.dl-btn { display:flex; align-items:center; gap:0.6rem; background:linear-gradient(135deg,rgba(234,179,8,0.12),rgba(234,179,8,0.06)); border:1px solid rgba(234,179,8,0.35); color:var(--accent); padding:0.65rem 1.4rem; border-radius:50px; text-decoration:none; font-size:0.88rem; font-weight:600; transition:all 0.25s; }
.dl-btn:hover { background:rgba(234,179,8,0.2); transform:translateY(-2px); }
.plat-badge { display:inline-flex; align-items:center; gap:0.5rem; background:rgba(59,130,246,0.1); border:1px solid rgba(59,130,246,0.3); color:#60a5fa; padding:0.35rem 1rem; border-radius:20px; font-size:0.82rem; font-weight:600; }

/* Mail Panel */
.mail-panel { background:var(--glass-bg); border:1px solid var(--glass-border); border-radius:20px; padding:2rem; }
.mail-panel h2 { font-size:1.2rem; margin:0 0 1.4rem; display:flex; align-items:center; gap:0.7rem; }
.field-label { font-size:0.75rem; text-transform:uppercase; letter-spacing:0.8px; color:var(--light-gray); font-weight:600; margin-bottom:0.4rem; display:block; }
.field-input { width:100%; background:rgba(0,0,0,0.4); border:1px solid var(--glass-border); border-radius:10px; padding:0.75rem 1rem; color:var(--text-main); font-family:inherit; font-size:0.9rem; transition:border-color 0.2s; box-sizing:border-box; }
.field-input:focus { outline:none; border-color:var(--accent); box-shadow:0 0 0 3px rgba(234,179,8,0.12); }
textarea.field-input { resize:vertical; min-height:90px; }
.field-group { margin-bottom:1rem; }
.recipients-hint { font-size:0.75rem; color:var(--light-gray); margin-top:0.3rem; }
.tag-container { display:flex; flex-wrap:wrap; gap:0.4rem; margin-top:0.5rem; min-height:28px; }
.email-tag { background:rgba(234,179,8,0.1); border:1px solid rgba(234,179,8,0.3); color:var(--accent); padding:0.2rem 0.7rem; border-radius:20px; font-size:0.78rem; display:flex; align-items:center; gap:0.4rem; }
.email-tag .rm { cursor:pointer; opacity:0.6; font-size:0.7rem; }
.email-tag .rm:hover { opacity:1; color:#ef4444; }
.send-btn { width:100%; background:linear-gradient(135deg,#eab308,#fef08a); color:#18181b; border:none; padding:0.9rem; border-radius:50px; font-weight:700; font-size:0.95rem; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:0.7rem; transition:all 0.3s; margin-top:1.2rem; }
.send-btn:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(234,179,8,0.35); }
.send-btn:disabled { opacity:0.5; cursor:not-allowed; transform:none; }
.alert-ok { background:rgba(16,185,129,0.1); border:1px solid rgba(16,185,129,0.3); color:#10b981; padding:0.85rem 1rem; border-radius:10px; margin-bottom:1rem; display:flex; align-items:center; gap:0.6rem; font-size:0.9rem; }
.alert-ko { background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3); color:#ef4444; padding:0.85rem 1rem; border-radius:10px; margin-bottom:1rem; display:flex; align-items:center; gap:0.6rem; font-size:0.9rem; }

/* Preview toggle */
.preview-toggle { background:rgba(255,255,255,0.04); border:1px solid var(--glass-border); border-radius:10px; padding:0.6rem 1rem; color:var(--light-gray); font-size:0.82rem; cursor:pointer; display:flex; align-items:center; gap:0.5rem; transition:all 0.2s; margin-top:0.5rem; width:100%; }
.preview-toggle:hover { border-color:var(--accent); color:var(--accent); }
.email-preview { margin-top:1rem; border:1px solid var(--glass-border); border-radius:12px; overflow:hidden; display:none; }
.email-preview iframe { width:100%; height:420px; border:none; background:#fff; }

/* Info card */
.vc-info-card { background:rgba(0,0,0,0.25); border:1px solid var(--glass-border); border-radius:14px; padding:1.2rem 1.4rem; margin-bottom:1.5rem; display:flex; align-items:center; gap:1.2rem; }
.vc-info-card .vc-icon { width:48px; height:48px; border-radius:12px; background:rgba(234,179,8,0.1); display:flex; align-items:center; justify-content:center; color:var(--accent); font-size:1.3rem; flex-shrink:0; }
.vc-info-card .vc-meta { flex:1; }
.vc-info-card .vc-meta h3 { margin:0 0 0.2rem; font-size:1rem; }
.vc-info-card .vc-meta p { margin:0; font-size:0.8rem; color:var(--light-gray); }

/* Scan animation overlay */
.scan-line { position:absolute; left:0; right:0; height:2px; background:linear-gradient(90deg,transparent,rgba(234,179,8,0.8),transparent); animation:scanLine 2.5s linear infinite; top:0; }
@keyframes scanLine { 0%{top:0} 100%{top:100%} }
</style>
</head>
<body>
<aside class="sidebar">
  <a href="<?= htmlspecialchars($basePath) ?>/" class="logo">e-lite<span>.</span></a>
  <nav class="nav-links">
    <a href="<?= htmlspecialchars($basePath) ?>/virtualclass" class="active"><i class="fas fa-play-circle"></i> Classes Virtuelles</a>
    <a href="<?= htmlspecialchars($basePath) ?>/virtualclass/sessions"><i class="fas fa-calendar-alt"></i> Séances</a>
    <a href="<?= htmlspecialchars($basePath) ?>/virtualclass/dashboard"><i class="fas fa-chart-line"></i> Dashboard</a>
    <a href="<?= htmlspecialchars($basePath) ?>/forum" style="margin-top:auto;"><i class="fas fa-globe"></i> Front Office</a>
  </nav>
</aside>
<main class="main-content">
  <!-- Page header -->
  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
    <div>
      <h1 style="margin:0; font-size:1.7rem;"><i class="fas fa-qrcode" style="color:var(--accent);"></i> QR Code & Mailing</h1>
      <p style="margin:0.3rem 0 0; color:var(--light-gray); font-size:0.88rem;">Générez et partagez l'accès à votre classe virtuelle</p>
    </div>
    <a href="<?= htmlspecialchars($basePath) ?>/virtualclass" class="btn-outline" style="padding:0.5rem 1.1rem; font-size:0.85rem;"><i class="fas fa-arrow-left"></i> Retour</a>
  </div>

  <!-- Virtual class info strip -->
  <div class="vc-info-card">
    <div class="vc-icon"><i class="fas fa-video"></i></div>
    <div class="vc-meta">
      <h3><?= htmlspecialchars($vc->getTitre()) ?></h3>
      <p>
        <span class="plat-badge" style="font-size:0.75rem; padding:0.2rem 0.7rem;">
          <?php
          $platIcons = ['Zoom'=>'fab fa-video','Meet'=>'fab fa-google','Teams'=>'fab fa-microsoft','Autre'=>'fas fa-laptop'];
          $icon = $platIcons[$vc->getPlateforme()] ?? 'fas fa-laptop';
          ?>
          <i class="<?= $icon ?>"></i> <?= htmlspecialchars($vc->getPlateforme()) ?>
        </span>
        &nbsp;·&nbsp; ID #<?= (int)$vc->getIdClass() ?>
        <?php if ($vc->getDescription()): ?>
          &nbsp;·&nbsp; <?= htmlspecialchars(mb_substr($vc->getDescription(), 0, 60)) ?>…
        <?php endif; ?>
      </p>
    </div>
    <a href="<?= htmlspecialchars($vc->getLienAcces()) ?>" target="_blank" class="btn-outline" style="padding:0.45rem 1rem; font-size:0.8rem; white-space:nowrap;">
      <i class="fas fa-external-link-alt"></i> Ouvrir
    </a>
  </div>

  <div class="page-grid">
    <!-- ═══ QR CODE PANEL ═══ -->
    <div class="qr-panel">
      <h2 style="margin:0; font-size:1.15rem; display:flex; align-items:center; gap:0.6rem; align-self:flex-start;">
        <i class="fas fa-qrcode" style="color:var(--accent);"></i> QR Code d'accès
      </h2>

      <div class="qr-frame">
        <div class="scan-line"></div>
        <div class="qr-scan-ring"></div>
        <img src="<?= $qrUrl ?>" alt="QR Code <?= htmlspecialchars($vc->getTitre()) ?>" width="<?= $qrSize ?>" height="<?= $qrSize ?>" id="qr-img">
      </div>

      <!-- Platform + size selector -->
      <div style="display:flex; gap:0.6rem; align-items:center; flex-wrap:wrap; justify-content:center;">
        <span class="plat-badge"><i class="<?= $icon ?>"></i> <?= htmlspecialchars($vc->getPlateforme()) ?></span>
        <select id="qr-size-select" onchange="updateQR()" style="background:rgba(0,0,0,0.4); border:1px solid var(--glass-border); color:var(--text-main); padding:0.35rem 0.7rem; border-radius:8px; font-size:0.8rem; cursor:pointer;">
          <option value="200">200×200</option>
          <option value="280" selected>280×280</option>
          <option value="400">400×400</option>
          <option value="600">600×600</option>
        </select>
      </div>

      <!-- Link display -->
      <div class="qr-link-box">
        <i class="fas fa-link" style="color:var(--accent); font-size:0.8rem; flex-shrink:0;"></i>
        <span id="qr-link-text"><?= htmlspecialchars($vc->getLienAcces()) ?></span>
        <button class="copy-btn" onclick="copyLink()"><i class="fas fa-copy"></i> Copier</button>
      </div>

      <!-- Download buttons -->
      <div style="display:flex; gap:0.8rem; flex-wrap:wrap; justify-content:center;">
        <a id="qr-dl-btn" href="<?= $qrDlUrl ?>" download="qr-<?= (int)$vc->getIdClass() ?>-<?= urlencode($vc->getTitre()) ?>.png" class="dl-btn">
          <i class="fas fa-download"></i> Télécharger PNG
        </a>
        <button class="dl-btn" onclick="printQR()" style="cursor:pointer; border:none;">
          <i class="fas fa-print"></i> Imprimer
        </button>
      </div>

      <!-- QR stats hint -->
      <div style="text-align:center; padding:0.8rem 1rem; background:rgba(234,179,8,0.04); border:1px solid rgba(234,179,8,0.12); border-radius:10px; width:100%;">
        <p style="margin:0; font-size:0.78rem; color:var(--light-gray); line-height:1.6;">
          <i class="fas fa-info-circle" style="color:var(--accent);"></i>
          Ce QR code encode directement le lien d'accès à la classe virtuelle.<br>
          Partagez-le dans vos supports de cours, affiches ou e-mails.
        </p>
      </div>
    </div>

    <!-- ═══ MAILING PANEL ═══ -->
    <div class="mail-panel">
      <h2><i class="fas fa-paper-plane" style="color:var(--accent);"></i> Envoyer par E-mail</h2>

      <?php
      // API token is hardcoded — always ready
      $smtpConfigured = true;
      ?>

      <?php if ($mailSuccess): ?>
        <div class="alert-ok"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($mailSuccess) ?></div>
      <?php endif; ?>
      <?php if ($mailError): ?>
        <div class="alert-ko"><i class="fas fa-exclamation-circle"></i> <?= $mailError ?></div>
      <?php endif; ?>

      <form method="POST" action="<?= htmlspecialchars($basePath) ?>/virtualclass/qr/<?= (int)$vc->getIdClass() ?>" id="mailForm" novalidate>
        <input type="hidden" name="action" value="send_mail">

        <div class="field-group">
          <label class="field-label"><i class="fas fa-user-edit"></i> Nom de l'expéditeur</label>
          <input type="text" name="sender_name" class="field-input" value="e-lite Platform" placeholder="Nom affiché dans l'e-mail">
        </div>

        <div class="field-group">
          <label class="field-label"><i class="fas fa-users"></i> Destinataires *</label>
          <input type="text" id="email-input" class="field-input" placeholder="boujmilmohamed3@gmail.com" autocomplete="off">
          <input type="hidden" name="emails" id="emails-hidden">
          <div class="recipients-hint"><i class="fas fa-info-circle"></i> Avec le compte Mailtrap gratuit, envoyez uniquement à <strong>boujmilmohamed3@gmail.com</strong>.</div>
          <div class="tag-container" id="tag-container"></div>
        </div>

        <div class="field-group">
          <label class="field-label"><i class="fas fa-heading"></i> Sujet *</label>
          <input type="text" name="subject" class="field-input" id="mail-subject"
            value="Invitation — <?= htmlspecialchars($vc->getTitre()) ?>"
            placeholder="Objet de l'e-mail">
        </div>

        <div class="field-group">
          <label class="field-label"><i class="fas fa-comment-alt"></i> Message personnalisé <span style="color:var(--light-gray); text-transform:none; font-size:0.72rem;">(optionnel)</span></label>
          <textarea name="custom_message" class="field-input" id="mail-custom" rows="3"
            placeholder="Ajoutez un message d'introduction personnalisé…"></textarea>
        </div>

        <button type="button" class="preview-toggle" onclick="togglePreview()">
          <i class="fas fa-eye"></i> Aperçu de l'e-mail
          <i class="fas fa-chevron-down" id="preview-chevron" style="margin-left:auto;"></i>
        </button>
        <div class="email-preview" id="email-preview">
          <iframe id="preview-frame" title="Aperçu e-mail"></iframe>
        </div>

        <button type="submit" class="send-btn" id="send-btn" <?= !$smtpConfigured ? 'disabled title="Configurez smtp_config.php d\'abord"' : '' ?>>
          <i class="fas fa-paper-plane"></i>
          <?= $smtpConfigured ? "Envoyer l'invitation" : 'SMTP non configuré' ?>
        </button>
      </form>
    </div>
  </div>
</main><script>
const RAW_LINK = <?= json_encode($vc->getLienAcces()) ?>;
const VC_TITRE = <?= json_encode($vc->getTitre()) ?>;
const VC_PLAT  = <?= json_encode($vc->getPlateforme()) ?>;
const VC_DESC  = <?= json_encode($vc->getDescription() ?: '') ?>;
const VC_ID    = <?= (int)$vc->getIdClass() ?>;
const SMTP_OK  = <?= $smtpConfigured ? 'true' : 'false' ?>;

/* ── QR size update ── */
function updateQR() {
  const sz = document.getElementById('qr-size-select').value;
  const base = 'https://api.qrserver.com/v1/create-qr-code/?color=eab308&bgcolor=09090b&margin=10&data=' + encodeURIComponent(RAW_LINK);
  const img = document.getElementById('qr-img');
  img.src = base + '&size=' + sz + 'x' + sz;
  img.width = Math.min(parseInt(sz), 280);
  img.height = Math.min(parseInt(sz), 280);
  const dlBtn = document.getElementById('qr-dl-btn');
  dlBtn.href = base + '&size=600x600&format=png';
}

/* ── Copy link ── */
function copyLink() {
  navigator.clipboard.writeText(RAW_LINK).then(() => {
    showToast('Lien copié dans le presse-papiers !', 'success');
  }).catch(() => {
    const ta = document.createElement('textarea');
    ta.value = RAW_LINK; document.body.appendChild(ta);
    ta.select(); document.execCommand('copy'); document.body.removeChild(ta);
    showToast('Lien copié !', 'success');
  });
}

/* ── Print QR ── */
function printQR() {
  const img = document.getElementById('qr-img');
  const w = window.open('', '_blank', 'width=500,height=600');
  w.document.write(`<!DOCTYPE html><html><head><title>QR Code — ${VC_TITRE}</title>
  <style>body{margin:0;display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:100vh;background:#fff;font-family:Arial,sans-serif;}
  h2{font-size:1.1rem;margin-bottom:1rem;color:#18181b;}p{font-size:0.75rem;color:#666;margin-top:0.8rem;word-break:break-all;max-width:320px;text-align:center;}
  </style></head><body>
  <h2>${VC_TITRE}</h2>
  <img src="${img.src}" style="border:12px solid #fff;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,0.15);">
  <p>${RAW_LINK}</p>
  <script>window.onload=()=>{window.print();}<\/script></body></html>`);
  w.document.close();
}

/* ── Email tag input ── */
let emailSet = new Set();

function addEmail(raw) {
  const parts = raw.split(/[\s,;]+/).map(e => e.trim()).filter(Boolean);
  parts.forEach(e => {
    if (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(e) && !emailSet.has(e)) {
      emailSet.add(e);
      renderTags();
    }
  });
  document.getElementById('email-input').value = '';
  syncHidden();
}

function removeEmail(e) {
  emailSet.delete(e);
  renderTags();
  syncHidden();
}

function renderTags() {
  const c = document.getElementById('tag-container');
  c.innerHTML = '';
  emailSet.forEach(e => {
    const tag = document.createElement('span');
    tag.className = 'email-tag';
    tag.innerHTML = `<i class="fas fa-envelope" style="font-size:0.7rem;"></i>${e}<span class="rm" onclick="removeEmail('${e.replace(/'/g,"\\'")}')">✕</span>`;
    c.appendChild(tag);
  });
  const btn = document.getElementById('send-btn');
  if (btn) btn.disabled = emailSet.size === 0;
}

function syncHidden() {
  document.getElementById('emails-hidden').value = [...emailSet].join(',');
}

document.addEventListener('DOMContentLoaded', () => {
  const inp = document.getElementById('email-input');
  if (!inp) return;

  // Pre-fill account owner email for demo plan
  addEmail('boujmilmohamed3@gmail.com');

  inp.addEventListener('keydown', e => {
    if (['Enter','Tab',',',' ',';'].includes(e.key)) {
      e.preventDefault();
      if (inp.value.trim()) addEmail(inp.value);
    }
  });
  inp.addEventListener('paste', e => {
    setTimeout(() => { addEmail(inp.value); }, 50);
  });
  inp.addEventListener('blur', () => {
    if (inp.value.trim()) addEmail(inp.value);
  });

  // Disable send btn initially
  const btn = document.getElementById('send-btn');
  if (btn) btn.disabled = true;

  // Form submit guard
  const form = document.getElementById('mailForm');
  if (form) {
    form.addEventListener('submit', e => {
      if (!SMTP_OK) {
        e.preventDefault();
        showToast('Configurez smtp_config.php avant d\'envoyer.', 'error');
        return;
      }
      if (inp.value.trim()) addEmail(inp.value);
      syncHidden();
      if (emailSet.size === 0) {
        e.preventDefault();
        showToast('Ajoutez au moins un destinataire valide.', 'error');
        return;
      }
      const subj = document.getElementById('mail-subject');
      if (subj && !subj.value.trim()) {
        e.preventDefault();
        showToast('Le sujet est obligatoire.', 'error');
        subj.focus();
        return;
      }
      const btn = document.getElementById('send-btn');
      if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi en cours…'; }
    });
  }
});

/* ── Email preview ── */
let previewOpen = false;
function togglePreview() {
  const box = document.getElementById('email-preview');
  const chev = document.getElementById('preview-chevron');
  previewOpen = !previewOpen;
  if (previewOpen) {
    box.style.display = 'block';
    chev.style.transform = 'rotate(180deg)';
    buildPreview();
  } else {
    box.style.display = 'none';
    chev.style.transform = '';
  }
}

function buildPreview() {
  const custom = document.getElementById('mail-custom') ? document.getElementById('mail-custom').value : '';
  const qrSz = document.getElementById('qr-size-select') ? document.getElementById('qr-size-select').value : '200';
  const qrSrc = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' + encodeURIComponent(RAW_LINK) + '&color=eab308&bgcolor=09090b&margin=10';
  const customBlock = custom ? `<tr><td style="padding:0 40px 10px;"><div style="background:rgba(255,255,255,0.03);border-left:3px solid #eab308;border-radius:0 10px 10px 0;padding:14px 18px;"><p style="margin:0;color:#d4d4d8;font-size:0.92rem;line-height:1.6;font-style:italic;">${custom}</p></div></td></tr>` : '';
  const html = `<!DOCTYPE html><html><head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#09090b;font-family:'Segoe UI',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#09090b;padding:30px 20px;">
<tr><td align="center"><table width="560" cellpadding="0" cellspacing="0" style="background:#18181b;border-radius:20px;border:1px solid rgba(255,255,255,0.08);overflow:hidden;max-width:560px;">
<tr><td style="background:linear-gradient(135deg,#1a1a0a,#2a2000);padding:30px 40px;text-align:center;border-bottom:1px solid rgba(234,179,8,0.2);">
<div style="font-size:1.8rem;font-weight:800;color:#f4f4f5;">e-lite<span style="color:#eab308;">.</span></div>
<div style="font-size:0.7rem;text-transform:uppercase;letter-spacing:3px;color:#a1a1aa;margin-top:4px;">Plateforme E-Learning</div></td></tr>
<tr><td style="padding:24px 40px 0;text-align:center;"><span style="background:rgba(234,179,8,0.12);border:1px solid rgba(234,179,8,0.4);color:#eab308;padding:5px 16px;border-radius:50px;font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:1px;">Invitation — Classe Virtuelle</span></td></tr>
<tr><td style="padding:18px 40px 8px;text-align:center;"><h1 style="margin:0;font-size:1.5rem;color:#f4f4f5;font-weight:700;">${VC_TITRE}</h1>
<p style="margin:10px 0 0;color:#a1a1aa;font-size:0.9rem;line-height:1.6;">${VC_DESC || 'Rejoignez cette classe virtuelle interactive.'}</p></td></tr>
<tr><td style="padding:12px 40px;text-align:center;"><span style="background:rgba(59,130,246,0.12);border:1px solid rgba(59,130,246,0.3);color:#60a5fa;padding:4px 12px;border-radius:20px;font-size:0.78rem;font-weight:600;">📡 ${VC_PLAT}</span></td></tr>
${customBlock}
<tr><td style="padding:20px 40px;text-align:center;">
<p style="margin:0 0 12px;color:#a1a1aa;font-size:0.78rem;text-transform:uppercase;letter-spacing:1px;font-weight:600;">Scannez pour rejoindre</p>
<div style="display:inline-block;background:#fff;padding:10px;border-radius:12px;"><img src="${qrSrc}" width="160" height="160" style="display:block;border-radius:6px;"></div></td></tr>
<tr><td style="padding:8px 40px 24px;text-align:center;">
<a href="${RAW_LINK}" style="display:inline-block;background:linear-gradient(135deg,#eab308,#fef08a);color:#18181b;text-decoration:none;padding:12px 30px;border-radius:50px;font-weight:700;font-size:0.9rem;">🚀 Rejoindre la Classe</a>
<p style="margin:12px 0 0;color:#71717a;font-size:0.72rem;">Lien : <a href="${RAW_LINK}" style="color:#eab308;">${RAW_LINK}</a></p></td></tr>
<tr><td style="background:rgba(0,0,0,0.3);padding:16px 40px;text-align:center;border-top:1px solid rgba(255,255,255,0.05);">
<p style="margin:0;color:#52525b;font-size:0.72rem;">© 2026 e-lite Platform · Tous droits réservés</p></td></tr>
</table></td></tr></table></body></html>`;
  const frame = document.getElementById('preview-frame');
  frame.srcdoc = html;
}

/* ── Toast ── */
function showToast(msg, type) {
  const old = document.getElementById('qr-toast');
  if (old) old.remove();
  const t = document.createElement('div');
  t.id = 'qr-toast';
  const colors = type === 'success'
    ? 'background:rgba(16,185,129,0.14);border:1px solid rgba(16,185,129,0.38);color:#6ee7b7;'
    : 'background:rgba(239,68,68,0.14);border:1px solid rgba(239,68,68,0.4);color:#fca5a5;';
  t.style.cssText = `position:fixed;top:1.4rem;right:1.6rem;z-index:9999;padding:0.85rem 1.4rem;border-radius:12px;font-size:0.9rem;font-weight:500;display:flex;align-items:center;gap:0.7rem;opacity:0;transform:translateY(-16px);transition:all 0.35s cubic-bezier(.34,1.56,.64,1);backdrop-filter:blur(12px);${colors}`;
  t.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i> ${msg}`;
  document.body.appendChild(t);
  requestAnimationFrame(() => { t.style.opacity = '1'; t.style.transform = 'translateY(0)'; });
  setTimeout(() => { t.style.opacity = '0'; t.style.transform = 'translateY(-16px)'; setTimeout(() => t.remove(), 400); }, 3500);
}
</script>
</body>
</html>