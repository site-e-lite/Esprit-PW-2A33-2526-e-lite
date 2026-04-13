<?php
require_once __DIR__ . '/../../Controller/PostController.php';
$postController = new PostController();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $contenu     = trim($_POST['contenu'] ?? '');
    $idUser      = intval($_POST['IdUser'] ?? $_POST['idUser'] ?? 0);
    $idForum     = intval($_POST['IdForum'] ?? $_POST['idForum'] ?? 0);
    $pieceJointe = trim($_POST['pieceJointe'] ?? '');

    if (strlen($contenu) >= 5 && $idUser > 0 && $idForum > 0) {
        $post = new Post($contenu, $idUser, $idForum, $pieceJointe ?: null);
        $postController->addPost($post);
        header('Location: posts_list.php?added=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter Post | e-lite BackOffice</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { display: flex; background-color: var(--black); margin: 0; overflow-x: hidden; font-family: var(--font-main,'Inter',sans-serif); }
        .admin-sidebar { width: 280px; height: 100vh; background: rgba(10,10,10,0.95); border-right: 1px solid var(--glass-border); position: fixed; top: 0; left: 0; display: flex; flex-direction: column; padding: 2rem 1.5rem; z-index: 100; }
        .admin-sidebar .logo { font-size: 2rem; margin-bottom: 3rem; text-align: center; text-decoration: none; color: inherit; }
        .admin-nav { display: flex; flex-direction: column; gap: 1rem; list-style: none; padding: 0; margin: 0; }
        .admin-nav li a { display: flex; align-items: center; gap: 1rem; color: var(--light-gray); text-decoration: none; padding: 1rem 1.5rem; border-radius: 12px; font-weight: 500; transition: all 0.3s; }
        .admin-nav li a i { font-size: 1.2rem; width: 20px; text-align: center; }
        .admin-nav li a:hover, .admin-nav li a.active { background: rgba(234,179,8,0.1); color: var(--accent); transform: translateX(5px); box-shadow: inset 2px 0 0 var(--accent); }
        .admin-content { margin-left: 280px; flex: 1; padding: 2.5rem 4rem; min-height: 100vh; background: radial-gradient(circle at top right, rgba(234,179,8,0.05) 0%, transparent 40%); }
        .form-card { max-width: 640px; margin: 0 auto; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 20px; padding: 2.5rem; backdrop-filter: blur(12px); position: relative; overflow: hidden; }
        .form-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, #eab308, #f59e0b, #d97706); border-radius: 20px 20px 0 0; }
        .card-title { display: flex; align-items: center; gap: 0.8rem; font-size: 1.5rem; font-weight: 700; margin-bottom: 0.4rem; color: #fff; }
        .card-title i { color: #eab308; }
        .card-subtitle { color: rgba(255,255,255,0.4); font-size: 0.9rem; margin-bottom: 2.5rem; }
        .back-link { display: inline-flex; align-items: center; gap: 0.5rem; color: rgba(255,255,255,0.4); text-decoration: none; font-size: 0.85rem; margin-bottom: 2rem; transition: color 0.2s; }
        .back-link:hover { color: #eab308; }

        /* Two-col grid for ID fields */
        .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1.2rem; }

        /* Fields */
        .field-group { position: relative; margin-bottom: 1.8rem; }
        .field-group label { display: block; font-size: 0.76rem; font-weight: 700; color: rgba(255,255,255,0.5); letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 0.5rem; transition: color 0.3s; }
        .field-group:focus-within label { color: #eab308; }
        .field-group.field-error label { color: #ef4444; }
        .field-group.field-success label { color: #10b981; }
        .field-group input, .field-group textarea { width: 100%; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; color: #fff; font-family: inherit; font-size: 0.95rem; padding: 0.9rem 1.1rem; box-sizing: border-box; outline: none; transition: border-color 0.3s, background 0.3s, box-shadow 0.3s; resize: vertical; }
        .field-group input::placeholder, .field-group textarea::placeholder { color: rgba(255,255,255,0.2); }
        .field-group input:focus, .field-group textarea:focus { border-color: #eab308; background: rgba(234,179,8,0.03); box-shadow: 0 0 0 3px rgba(234,179,8,0.12); }
        .field-group.field-error input, .field-group.field-error textarea { border-color: #ef4444; background: rgba(239,68,68,0.04); box-shadow: 0 0 0 3px rgba(239,68,68,0.08); }
        .field-group.field-success input, .field-group.field-success textarea { border-color: #10b981; background: rgba(16,185,129,0.04); }
        input[type="number"]::-webkit-inner-spin-button { opacity: 0.4; }
        .field-msg { margin-top: 0.4rem; font-size: 0.75rem; min-height: 1rem; padding-left: 0.2rem; }
        .field-group.field-error .field-msg { color: #ef4444; }
        .field-group.field-success .field-msg { color: #10b981; }

        /* Char counter */
        .char-counter { position: absolute; bottom: 0.6rem; right: 1rem; font-size: 0.72rem; color: rgba(255,255,255,0.3); pointer-events: none; transition: color 0.3s; }
        .char-counter.near { color: #f59e0b; }
        .char-counter.over { color: #ef4444; }

        /* Progress bar */
        .progress-wrap { width: 100%; height: 4px; background: rgba(255,255,255,0.06); border-radius: 4px; margin-bottom: 2rem; overflow: hidden; }
        .progress-fill { height: 100%; border-radius: 4px; background: linear-gradient(90deg, #eab308, #10b981); transition: width 0.4s ease; }

        /* Submit */
        .submit-btn { width: 100%; padding: 1rem; border: none; border-radius: 12px; background: linear-gradient(135deg, #eab308, #d97706); color: #000; font-weight: 700; font-size: 1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.6rem; transition: transform 0.2s, box-shadow 0.3s; margin-top: 1rem; font-family: inherit; }
        .submit-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(234,179,8,0.35); }
        .submit-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
        .btn-spinner { display: none; width: 16px; height: 16px; border: 2px solid rgba(0,0,0,0.3); border-top-color: #000; border-radius: 50%; animation: spin 0.8s linear infinite; }
        .submit-btn.loading .btn-spinner { display: block; }
        .submit-btn.loading .btn-text { display: none; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Toast */
        .toast { position: fixed; bottom: 2rem; right: 2rem; background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.4); border-radius: 12px; padding: 1rem 1.5rem; display: flex; align-items: center; gap: 0.8rem; color: #10b981; font-weight: 600; font-size: 0.9rem; transform: translateY(100px); opacity: 0; transition: all 0.4s cubic-bezier(0.4,0,0.2,1); z-index: 9999; pointer-events: none; }
        .toast.show { transform: translateY(0); opacity: 1; }
        .toast.error { background: rgba(239,68,68,0.15); border-color: rgba(239,68,68,0.4); color: #ef4444; }
    </style>
</head>
<body>

<div class="toast" id="toast"><i class="fas fa-check-circle"></i><span id="toastMsg"></span></div>

<aside class="admin-sidebar">
    <a href="dashboard.php" class="logo">e-lite<span style="color:#eab308;">.</span>
        <div style="font-size:0.8rem; letter-spacing:3px; color:rgba(255,255,255,0.4); font-family:inherit; text-transform:uppercase; font-weight:400;">BackOffice</div>
    </a>
    <ul class="admin-nav">
            <li><a href="quizzes_list.php"><i class="fas fa-tasks"></i> Liste des Quiz</a></li>
            <li><a href="quiz_add.php"><i class="fas fa-plus-circle"></i> Ajouter un Quiz</a></li>
            <li><a href="questions_list.php"><i class="fas fa-question-circle"></i> Liste des Questions</a></li>
            <li><a href="question_add.php"><i class="fas fa-plus-square"></i> Ajouter une Question</a></li>
        </ul>
</aside>

<main class="admin-content">
    <a href="posts_list.php" class="back-link"><i class="fas fa-arrow-left"></i> Retour à la liste des posts</a>

    <div class="form-card">
        <div class="card-title"><i class="fas fa-paper-plane"></i> Publier un Message</div>
        <p class="card-subtitle">Ajoutez un nouveau message en tant qu'administrateur.</p>

        <!-- Completion progress bar -->
        <div class="progress-wrap">
            <div class="progress-fill" id="progressFill" style="width:0%"></div>
        </div>

        <form id="postAddForm" action="" method="POST" novalidate>

            <!-- Contenu -->
            <div class="field-group" id="fg-contenu" style="position:relative;">
                <label for="contenu">Contenu du message <span style="color:rgba(255,255,255,0.3); font-weight:400; text-transform:none;">*</span></label>
                <textarea id="contenu" name="contenu"
                          rows="6" maxlength="2000"
                          placeholder="Saisissez le contenu du message (min. 5 caractères)…"
                          oninput="validateContenu(); updateProgress()"
                          onblur="validateContenu(true)"></textarea>
                <span class="char-counter" id="charCount">0 / 2000</span>
                <div class="field-msg" id="contenu-msg"></div>
            </div>

            <!-- User ID & Forum ID row -->
            <div class="field-row">
                <div class="field-group" id="fg-iduser">
                    <label for="IdUser">ID Utilisateur <span style="color:rgba(255,255,255,0.3); font-weight:400; text-transform:none;">*</span></label>
                    <input type="number" id="IdUser" name="IdUser"
                           placeholder="Ex: 1" min="1"
                           oninput="validateIdUser(); updateProgress()"
                           onblur="validateIdUser(true)">
                    <div class="field-msg" id="user-msg"></div>
                </div>
                <div class="field-group" id="fg-idforum">
                    <label for="IdForum">ID Forum <span style="color:rgba(255,255,255,0.3); font-weight:400; text-transform:none;">*</span></label>
                    <input type="number" id="IdForum" name="IdForum"
                           placeholder="Ex: 5" min="1"
                           oninput="validateIdForum(); updateProgress()"
                           onblur="validateIdForum(true)">
                    <div class="field-msg" id="forum-msg"></div>
                </div>
            </div>

            <!-- Pièce Jointe -->
            <div class="field-group" id="fg-pj">
                <label for="pieceJointe">Pièce jointe <span style="color:rgba(255,255,255,0.3); font-weight:400; text-transform:none;">(optionnelle – URL ou nom de fichier)</span></label>
                <input type="text" id="pieceJointe" name="pieceJointe"
                       placeholder="ex: document.pdf ou https://…"
                       oninput="validatePJ()"
                       onblur="validatePJ(true)">
                <div class="field-msg" id="pj-msg"></div>
            </div>

            <button type="submit" class="submit-btn" id="submitBtn" onclick="return handleSubmit(event)">
                <div class="btn-spinner"></div>
                <span class="btn-text"><i class="fas fa-paper-plane"></i>&nbsp; Publier le Message</span>
            </button>
        </form>
    </div>
</main>

<script>
/* ══════════════════════════════════════════════
   Professional JS Validation — Post Add Form
   ══════════════════════════════════════════════ */

function setField(groupId, state, msg = '') {
    const g = document.getElementById(groupId);
    if (!g) return;
    g.classList.remove('field-error', 'field-success');
    if (state) g.classList.add('field-' + state);
    const m = g.querySelector('.field-msg');
    if (m) m.textContent = msg;
}

function updateProgress() {
    const fields = [
        validateContenu.isValid,
        validateIdUser.isValid,
        validateIdForum.isValid
    ].filter(Boolean).length;
    const pct = Math.round((fields / 3) * 100);
    document.getElementById('progressFill').style.width = pct + '%';
}

/* Mark validity state on each function */
validateContenu.isValid = false;
validateIdUser.isValid  = false;
validateIdForum.isValid = false;

function validateContenu(onBlur = false) {
    const ta = document.getElementById('contenu');
    const v  = ta.value;
    const t  = v.trim();
    const counter = document.getElementById('charCount');
    counter.textContent = v.length + ' / 2000';
    counter.className = 'char-counter' + (v.length > 1700 ? ' near' : '') + (v.length >= 2000 ? ' over' : '');

    if (!t) {
        if (onBlur) setField('fg-contenu', 'error', '⚠ Le contenu est obligatoire.');
        else setField('fg-contenu', '', '');
        validateContenu.isValid = false; return false;
    }
    if (t.length < 5) {
        setField('fg-contenu', 'error', `⚠ Minimum 5 caractères (actuellement ${t.length}).`);
        validateContenu.isValid = false; return false;
    }
    setField('fg-contenu', 'success', '✓ Contenu valide.');
    validateContenu.isValid = true; return true;
}

function validateIdUser(onBlur = false) {
    const v = parseInt(document.getElementById('IdUser').value) || 0;
    if (v <= 0) {
        if (onBlur) setField('fg-iduser', 'error', '⚠ ID utilisateur invalide (≥ 1).');
        else setField('fg-iduser', '', '');
        validateIdUser.isValid = false; return false;
    }
    setField('fg-iduser', 'success', `✓ Utilisateur #${v}.`);
    validateIdUser.isValid = true; return true;
}

function validateIdForum(onBlur = false) {
    const v = parseInt(document.getElementById('IdForum').value) || 0;
    if (v <= 0) {
        if (onBlur) setField('fg-idforum', 'error', '⚠ ID forum invalide (≥ 1).');
        else setField('fg-idforum', '', '');
        validateIdForum.isValid = false; return false;
    }
    setField('fg-idforum', 'success', `✓ Forum #${v}.`);
    validateIdForum.isValid = true; return true;
}

function validatePJ(onBlur = false) {
    const v = document.getElementById('pieceJointe').value.trim();
    if (!v) { setField('fg-pj', '', ''); return true; } // optional

    const urlR  = /^(https?:\/\/|ftp:\/\/).+/i;
    const fileR = /^[\w\-. ]+\.(pdf|doc|docx|png|jpg|jpeg|gif|zip|txt|xls|xlsx|csv|mp4|mp3|pptx?)$/i;

    if (urlR.test(v) || fileR.test(v)) {
        setField('fg-pj', 'success', '✓ Pièce jointe valide.');
        return true;
    }
    if (onBlur) {
        setField('fg-pj', 'error', '⚠ Entrez une URL (http://…) ou un nom de fichier avec extension.');
        return false;
    }
    return true;
}

function handleSubmit(e) {
    e.preventDefault();
    const c  = validateContenu(true);
    const u  = validateIdUser(true);
    const f  = validateIdForum(true);
    const pj = validatePJ(true);
    updateProgress();

    if (!c || !u || !f || !pj) {
        showToast('Veuillez corriger les erreurs avant de publier.', 'error');
        document.querySelector('.field-error')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return false;
    }
    const btn = document.getElementById('submitBtn');
    btn.classList.add('loading'); btn.disabled = true;
    document.getElementById('postAddForm').submit();
    return true;
}

function showToast(msg, type = 'success') {
    const t = document.getElementById('toast');
    t.className = 'toast' + (type === 'error' ? ' error' : '');
    t.querySelector('i').className = type === 'error' ? 'fas fa-times-circle' : 'fas fa-check-circle';
    document.getElementById('toastMsg').textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 3500);
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('contenu')?.focus();
});
</script>
</body>
</html>
