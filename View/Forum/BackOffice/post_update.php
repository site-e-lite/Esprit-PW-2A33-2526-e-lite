<?php
require_once __DIR__ . '/../../../Controller/Forum/PostController.php';
$postController = new PostController();

$postData = null;
if (isset($_GET['id'])) {
    $postData = $postController->getPostById($_GET['id']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Use case-insensitive access for both uppercase and lowercase keys
    $idPost     = $_POST['IdPost']     ?? $_POST['idPost']     ?? null;
    $idUser     = $_POST['IdUser']     ?? $_POST['idUser']     ?? null;
    $idForum    = $_POST['IdForum']    ?? $_POST['idForum']    ?? null;
    $contenu    = $_POST['contenu']    ?? '';
    $pieceJointe = $_POST['pieceJointe'] ?? '';

    if ($idPost && strlen(trim($contenu)) >= 5 && !preg_match('/^\d+$/', trim($contenu))) {
        $post = new Post($contenu, $idUser, $idForum, $pieceJointe);
        $postController->updatePost($post, $idPost);
        header('Location: posts_list.php?updated=1');
        exit;
    }
}

// Normalize post data keys to lowercase for consistent use in the form
$pd = [];
if ($postData) {
    foreach ($postData as $k => $v) {
        $pd[strtolower($k)] = $v;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Post | e-lite BackOffice</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/Forum/index.css?v=<?= time() ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ─── Layout ─── */
        body { display: flex; background-color: var(--black); margin: 0; overflow-x: hidden; font-family: var(--font-main, 'Inter', sans-serif); }
        .admin-sidebar { width: 280px; height: 100vh; background: rgba(10,10,10,0.95); border-right: 1px solid var(--glass-border); position: fixed; top: 0; left: 0; display: flex; flex-direction: column; padding: 2rem 1.5rem; z-index: 100; }
        .admin-sidebar .logo { font-size: 2rem; margin-bottom: 3rem; text-align: center; text-decoration: none; color: inherit; }
        .admin-nav { display: flex; flex-direction: column; gap: 1rem; list-style: none; padding: 0; margin: 0; }
        .admin-nav li a { display: flex; align-items: center; gap: 1rem; color: var(--light-gray); text-decoration: none; padding: 1rem 1.5rem; border-radius: 12px; font-weight: 500; transition: all 0.3s; }
        .admin-nav li a i { font-size: 1.2rem; width: 20px; text-align: center; }
        .admin-nav li a:hover, .admin-nav li a.active { background: rgba(234,179,8,0.1); color: var(--accent); transform: translateX(5px); box-shadow: inset 2px 0 0 var(--accent); }
        .admin-content { margin-left: 280px; flex: 1; padding: 2.5rem 4rem; min-height: 100vh; background: radial-gradient(circle at top right, rgba(234,179,8,0.05) 0%, transparent 40%); }

        /* ─── Card ─── */
        .update-card {
            max-width: 640px;
            margin: 0 auto;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 20px;
            padding: 2.5rem;
            backdrop-filter: blur(12px);
            position: relative;
            overflow: hidden;
        }
        .update-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, #eab308, #f59e0b, #d97706);
            border-radius: 20px 20px 0 0;
        }
        .card-title {
            display: flex; align-items: center; gap: 0.8rem;
            font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem; color: #fff;
        }
        .card-title i { color: #eab308; }
        .card-subtitle { color: rgba(255,255,255,0.4); font-size: 0.9rem; margin-bottom: 2.5rem; }

        /* ─── Post Meta Badge ─── */
        .post-meta {
            display: flex; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap;
        }
        .meta-badge {
            display: inline-flex; align-items: center; gap: 0.5rem;
            background: rgba(234,179,8,0.08);
            border: 1px solid rgba(234,179,8,0.2);
            border-radius: 30px;
            padding: 0.4rem 1rem;
            font-size: 0.8rem;
            color: #eab308;
            font-weight: 600;
        }
        .meta-badge i { font-size: 0.75rem; }

        /* ─── Floating Label Fields ─── */
        .field-group {
            position: relative;
            margin-bottom: 1.8rem;
        }
        .field-group label {
            position: absolute;
            top: -0.6rem; left: 1rem;
            background: #0a0a0a;
            padding: 0 0.4rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: rgba(255,255,255,0.5);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            transition: color 0.3s;
            pointer-events: none;
            z-index: 2;
        }
        .field-group:focus-within label { color: #eab308; }
        .field-group.field-error label { color: #ef4444; }
        .field-group.field-success label { color: #10b981; }

        .field-group input,
        .field-group textarea,
        .field-group select {
            width: 100%;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 12px;
            color: #fff;
            font-family: inherit;
            font-size: 0.95rem;
            padding: 1rem 1.1rem;
            box-sizing: border-box;
            outline: none;
            transition: border-color 0.3s, background 0.3s, box-shadow 0.3s;
            resize: vertical;
        }
        .field-group input:focus,
        .field-group textarea:focus {
            border-color: #eab308;
            background: rgba(234,179,8,0.03);
            box-shadow: 0 0 0 3px rgba(234,179,8,0.12);
        }
        .field-group.field-error input,
        .field-group.field-error textarea {
            border-color: #ef4444;
            background: rgba(239,68,68,0.04);
            box-shadow: 0 0 0 3px rgba(239,68,68,0.08);
        }
        .field-group.field-success input,
        .field-group.field-success textarea {
            border-color: #10b981;
            background: rgba(16,185,129,0.04);
        }
        .field-group input:disabled {
            opacity: 0.45;
            cursor: not-allowed;
            border-style: dashed;
        }

        /* Field status icon */
        .field-icon {
            position: absolute;
            right: 1rem; top: 50%;
            transform: translateY(-50%);
            font-size: 0.9rem;
            pointer-events: none;
            transition: color 0.3s, opacity 0.3s;
            opacity: 0;
        }
        .field-group.has-icon .field-icon { opacity: 1; }
        .field-group.field-error .field-icon { color: #ef4444; }
        .field-group.field-success .field-icon { color: #10b981; }

        /* Error / helper text */
        .field-msg {
            margin-top: 0.4rem;
            font-size: 0.78rem;
            min-height: 1rem;
            padding-left: 0.5rem;
            transition: color 0.3s;
        }
        .field-group.field-error .field-msg { color: #ef4444; }
        .field-group.field-success .field-msg { color: #10b981; }
        .field-msg:empty { display: none; }

        /* Char counter */
        .char-counter {
            position: absolute;
            bottom: 0.6rem; right: 1rem;
            font-size: 0.72rem;
            color: rgba(255,255,255,0.3);
            transition: color 0.3s;
            pointer-events: none;
        }
        .char-counter.near { color: #f59e0b; }
        .char-counter.over { color: #ef4444; }

        /* ─── Submit Button ─── */
        .submit-btn {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #eab308, #d97706);
            color: #000;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 0.6rem;
            transition: transform 0.2s, box-shadow 0.3s, filter 0.2s;
            margin-top: 2rem;
        }
        .submit-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(234,179,8,0.35); filter: brightness(1.1); }
        .submit-btn:active { transform: translateY(0); }
        .submit-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
        .submit-btn .btn-spinner { display: none; width: 16px; height: 16px; border: 2px solid rgba(0,0,0,0.3); border-top-color: #000; border-radius: 50%; animation: spin 0.8s linear infinite; }
        .submit-btn.loading .btn-spinner { display: block; }
        .submit-btn.loading .btn-text { display: none; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Back link */
        .back-link {
            display: inline-flex; align-items: center; gap: 0.5rem;
            color: rgba(255,255,255,0.4); text-decoration: none;
            font-size: 0.85rem; margin-bottom: 2rem;
            transition: color 0.2s;
        }
        .back-link:hover { color: #eab308; }

        /* ─── Toast Notification ─── */
        .toast {
            position: fixed; bottom: 2rem; right: 2rem;
            background: rgba(16,185,129,0.15);
            border: 1px solid rgba(16,185,129,0.4);
            border-radius: 12px;
            padding: 1rem 1.5rem;
            display: flex; align-items: center; gap: 0.8rem;
            color: #10b981; font-weight: 600; font-size: 0.9rem;
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 9999;
            pointer-events: none;
        }
        .toast.show { transform: translateY(0); opacity: 1; }
        .toast.error { background: rgba(239,68,68,0.15); border-color: rgba(239,68,68,0.4); color: #ef4444; }

        /* ─── Progress Rings ─── */
        .progress-ring { position: relative; display: inline-block; }
    </style>
</head>
<body>

    <!-- Toast -->
    <div class="toast" id="toast">
        <i class="fas fa-check-circle"></i>
        <span id="toastMsg"></span>
    </div>

    <aside class="admin-sidebar">
        <a href="dashboard.php" class="logo">e-lite<span style="color:#eab308;">.</span>
            <div style="font-size:0.8rem; letter-spacing:3px; color:rgba(255,255,255,0.4); font-family:inherit; text-transform:uppercase; font-weight:400;">BackOffice</div>
        </a>
        <ul class="admin-nav">
            <li><a href="dashboard.php"><i class="fas fa-home"></i> Vue d'ensemble</a></li>
            <li><a href="forums_list.php"><i class="fas fa-list"></i> Liste Forums</a></li>
            <li><a href="posts_list.php" class="active"><i class="fas fa-comments"></i> Liste Posts</a></li>
        </ul>
    </aside>

    <main class="admin-content">
        <a href="posts_list.php" class="back-link"><i class="fas fa-arrow-left"></i> Retour à la liste des posts</a>

        <div class="update-card">
            <div class="card-title"><i class="fas fa-edit"></i> Modifier le Message</div>
            <p class="card-subtitle">Mettez à jour le contenu ou la pièce jointe du message.</p>

            <?php if (!empty($pd)): ?>
            <!-- Post meta badges -->
            <div class="post-meta">
                <span class="meta-badge"><i class="fas fa-hashtag"></i> Post #<?= htmlspecialchars($pd['idpost']) ?></span>
                <span class="meta-badge"><i class="fas fa-user"></i> User #<?= htmlspecialchars($pd['iduser']) ?></span>
                <span class="meta-badge"><i class="fas fa-comments"></i> Forum #<?= htmlspecialchars($pd['idforum']) ?></span>
            </div>

            <form id="postUpdateForm" action="" method="POST" novalidate>
                <input type="hidden" name="IdPost"  value="<?= htmlspecialchars($pd['idpost'])  ?>">
                <input type="hidden" name="IdUser"  value="<?= htmlspecialchars($pd['iduser'])  ?>">
                <input type="hidden" name="IdForum" value="<?= htmlspecialchars($pd['idforum']) ?>">

                <!-- Read-only author -->
                <div class="field-group">
                    <label>Auteur</label>
                    <input type="text" disabled value="Utilisateur #<?= htmlspecialchars($pd['iduser']) ?>">
                </div>

                <!-- Content textarea with char counter -->
                <div class="field-group" id="fg-contenu" style="position:relative;">
                    <label for="contenu">Contenu du message</label>
                    <textarea id="contenu" name="contenu"
                              rows="6"
                              maxlength="2000"
                              placeholder="Saisissez le contenu du message (min. 5 caractères)…"
                              oninput="validateContenu()"
                              onblur="validateContenu(true)"><?= htmlspecialchars($pd['contenu']) ?></textarea>
                    <span class="char-counter" id="charCount">0 / 2000</span>
                    <div class="field-msg" id="contenu-msg"></div>
                </div>

                <!-- Piece jointe -->
                <div class="field-group" id="fg-pieceJointe" style="position:relative;">
                    <label for="pieceJointe">Pièce Jointe <span style="color:rgba(255,255,255,0.3); font-weight:400; text-transform:none;">(optionnelle – URL ou nom de fichier)</span></label>
                    <input type="text" id="pieceJointe" name="pieceJointe"
                           placeholder="ex: rapport.pdf ou https://..."
                           value="<?= htmlspecialchars($pd['piecejointe'] ?? '') ?>"
                           oninput="validatePJ()"
                           onblur="validatePJ(true)">
                    <i class="fas fa-paperclip field-icon" id="pj-icon"></i>
                    <div class="field-msg" id="pj-msg"></div>
                </div>

                <button type="submit" class="submit-btn" id="submitBtn" onclick="return handleSubmit(event)">
                    <div class="btn-spinner"></div>
                    <span class="btn-text"><i class="fas fa-save"></i>&nbsp; Enregistrer les modifications</span>
                </button>
            </form>
            <?php else: ?>
                <div style="text-align:center; padding: 3rem 1rem;">
                    <i class="fas fa-search" style="font-size:3rem; color:rgba(255,255,255,0.1); margin-bottom:1rem; display:block;"></i>
                    <p style="color:rgba(255,255,255,0.4);">Aucun post sélectionné ou introuvable.</p>
                    <a href="posts_list.php" class="submit-btn" style="display:inline-flex; width:auto; padding:0.8rem 2rem; text-decoration:none; margin-top:1rem;">
                        <i class="fas fa-arrow-left"></i> Retourner à la liste
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>

<script>
/* ═══════════════════════════════════════════════
   Professional JS Validation — Post Update Form
   ═══════════════════════════════════════════════ */

const MAX_CONTENU = 2000;
const MIN_CONTENU = 5;

/* ── Helpers ── */
function setFieldState(groupId, state, msg = '') {
    const g = document.getElementById(groupId);
    if (!g) return;
    g.classList.remove('field-error', 'field-success', 'has-icon');
    if (state) { g.classList.add('field-' + state); }
    const msgEl = g.querySelector('.field-msg');
    if (msgEl) msgEl.textContent = msg;
}

function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    const toastMsg = document.getElementById('toastMsg');
    toast.className = 'toast' + (type === 'error' ? ' error' : '');
    const icon = toast.querySelector('i');
    icon.className = type === 'error' ? 'fas fa-times-circle' : 'fas fa-check-circle';
    toastMsg.textContent = message;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3500);
}

/* ── Contenu Validation ── */
function validateContenu(onBlur = false) {
    const textarea = document.getElementById('contenu');
    const val = textarea.value;
    const len = val.length;

    // Update char counter
    const counter = document.getElementById('charCount');
    counter.textContent = len + ' / ' + MAX_CONTENU;
    counter.className = 'char-counter';
    if (len > MAX_CONTENU * 0.85) counter.classList.add('near');
    if (len >= MAX_CONTENU)       counter.classList.add('over');

    const trimmed = val.trim();

    if (trimmed.length === 0) {
        if (onBlur) setFieldState('fg-contenu', 'error', '⚠ Ce champ est obligatoire.');
        else setFieldState('fg-contenu', '', '');
        return false;
    }
    if (trimmed.length < MIN_CONTENU) {
        setFieldState('fg-contenu', 'error', `⚠ Minimum ${MIN_CONTENU} caractères requis (actuellement ${trimmed.length}).`);
        return false;
    }
    if (len > MAX_CONTENU) {
        setFieldState('fg-contenu', 'error', `⚠ Maximum ${MAX_CONTENU} caractères dépassé.`);
        return false;
    }
    if (/^\d+$/.test(trimmed)) {
        setFieldState('fg-contenu', 'error', '⚠ Le contenu doit contenir du texte, pas seulement des chiffres.');
        return false;
    }

    setFieldState('fg-contenu', 'success', '✓ Contenu valide.');
    return true;
}

/* ── Pièce Jointe Validation (optional) ── */
function validatePJ(onBlur = false) {
    const input   = document.getElementById('pieceJointe');
    const iconEl  = document.getElementById('pj-icon');
    const val = input.value.trim();

    const fg = document.getElementById('fg-pieceJointe');
    fg.classList.remove('field-error', 'field-success', 'has-icon');

    if (val === '') {
        setFieldState('fg-pieceJointe', '', '');
        return true; // optional field
    }

    // Basic URL or filename check
    const urlRegex = /^(https?:\/\/|ftp:\/\/).+/i;
    const fileRegex = /^[\w\-. ]+\.(pdf|doc|docx|png|jpg|jpeg|gif|zip|txt|xls|xlsx|csv|mp4|mp3|pptx?)$/i;

    if (urlRegex.test(val) || fileRegex.test(val)) {
        fg.classList.add('field-success', 'has-icon');
        iconEl.classList.replace('fa-paperclip', 'fa-check');
        setFieldState('fg-pieceJointe', 'success', '✓ Format de pièce jointe valide.');
        return true;
    }

    if (onBlur) {
        fg.classList.add('field-error', 'has-icon');
        iconEl.classList.replace('fa-check', 'fa-paperclip');
        setFieldState('fg-pieceJointe', 'error', '⚠ Entrez une URL valide (http://…) ou un nom de fichier avec extension.');
        return false;
    }

    return true; // don't error on input, only on blur
}

/* ── Final Submit Handler ── */
function handleSubmit(e) {
    e.preventDefault();
    const contenuOk = validateContenu(true);
    const pjOk      = validatePJ(true);

    if (!contenuOk || !pjOk) {
        showToast('Veuillez corriger les erreurs avant de soumettre.', 'error');
        // Scroll to first error
        const firstErr = document.querySelector('.field-error');
        if (firstErr) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return false;
    }

    // Show loading state
    const btn = document.getElementById('submitBtn');
    btn.classList.add('loading');
    btn.disabled = true;

    // Submit the form
    document.getElementById('postUpdateForm').submit();
    return true;
}

/* ── Init on page load ── */
document.addEventListener('DOMContentLoaded', () => {
    // Run silent validation to show initial state
    const contenu = document.getElementById('contenu');
    if (contenu && contenu.value.trim().length >= MIN_CONTENU) {
        validateContenu();
    }

    // Update char counter immediately
    if (contenu) {
        const counter = document.getElementById('charCount');
        counter.textContent = contenu.value.length + ' / ' + MAX_CONTENU;
    }

    // Check for success redirect param
    const params = new URLSearchParams(window.location.search);
    if (params.get('updated') === '1') {
        showToast('Message mis à jour avec succès !');
    }
});
</script>

</body>
</html>
