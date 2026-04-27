<?php
require_once __DIR__ . '/../../Controller/ForumController.php';
$forumController = new ForumController();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titre       = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $idCourse    = intval($_POST['IdCourse'] ?? 0);

    // Description: accept text and numbers mixed, but reject numbers only
    $descriptionValide = !preg_match('/^\d+$/', $description);

    if (strlen($titre) >= 3 && strlen($description) >= 10 && $descriptionValide) {
        $forum = new Forum($titre, $description, $idCourse);
        $forumController->addForum($forum);
        header('Location: forums_list.php?added=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter Forum | e-lite BackOffice</title>
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

        /* ─── Form Card ─── */
        .form-card {
            max-width: 640px; margin: 0 auto;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 20px; padding: 2.5rem;
            backdrop-filter: blur(12px);
            position: relative; overflow: hidden;
        }
        .form-card::before {
            content: ''; position: absolute;
            top: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, #eab308, #f59e0b, #d97706);
            border-radius: 20px 20px 0 0;
        }
        .card-title { display: flex; align-items: center; gap: 0.8rem; font-size: 1.5rem; font-weight: 700; margin-bottom: 0.4rem; color: #fff; }
        .card-title i { color: #eab308; }
        .card-subtitle { color: rgba(255,255,255,0.4); font-size: 0.9rem; margin-bottom: 2.5rem; }
        .back-link { display: inline-flex; align-items: center; gap: 0.5rem; color: rgba(255,255,255,0.4); text-decoration: none; font-size: 0.85rem; margin-bottom: 2rem; transition: color 0.2s; }
        .back-link:hover { color: #eab308; }

        /* ─── Floating Label Fields ─── */
        .field-group { position: relative; margin-bottom: 1.8rem; }
        .field-group label { position: absolute; top: -0.6rem; left: 1rem; background: #0a0a0a; padding: 0 0.4rem; font-size: 0.75rem; font-weight: 600; color: rgba(255,255,255,0.5); letter-spacing: 0.08em; text-transform: uppercase; transition: color 0.3s; pointer-events: none; z-index: 2; }
        .field-group:focus-within label { color: #eab308; }
        .field-group.field-error label { color: #ef4444; }
        .field-group.field-success label { color: #10b981; }
        .field-group input, .field-group textarea { width: 100%; background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; color: #fff; font-family: inherit; font-size: 0.95rem; padding: 1rem 1.1rem; box-sizing: border-box; outline: none; transition: border-color 0.3s, background 0.3s, box-shadow 0.3s; resize: vertical; }
        .field-group input:focus, .field-group textarea:focus { border-color: #eab308; background: rgba(234,179,8,0.03); box-shadow: 0 0 0 3px rgba(234,179,8,0.12); }
        .field-group.field-error input, .field-group.field-error textarea { border-color: #ef4444; background: rgba(239,68,68,0.04); box-shadow: 0 0 0 3px rgba(239,68,68,0.08); }
        .field-group.field-success input, .field-group.field-success textarea { border-color: #10b981; background: rgba(16,185,129,0.04); }
        input[type="number"]::-webkit-inner-spin-button { opacity: 0.4; }
        .field-msg { margin-top: 0.4rem; font-size: 0.78rem; min-height: 1rem; padding-left: 0.5rem; transition: color 0.3s; }
        .field-group.field-error .field-msg { color: #ef4444; }
        .field-group.field-success .field-msg { color: #10b981; }
        .char-counter { position: absolute; bottom: 0.6rem; right: 1rem; font-size: 0.72rem; color: rgba(255,255,255,0.3); pointer-events: none; transition: color 0.3s; }
        .char-counter.near { color: #f59e0b; }
        .char-counter.over { color: #ef4444; }

        /* Step indicators */
        .steps { display: flex; gap: 0; margin-bottom: 2.5rem; }
        .step { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 0.4rem; position: relative; }
        .step:not(:last-child)::after { content: ''; position: absolute; top: 14px; left: 50%; width: 100%; height: 2px; background: rgba(255,255,255,0.08); z-index: 0; }
        .step-dot { width: 28px; height: 28px; border-radius: 50%; background: rgba(255,255,255,0.06); border: 2px solid rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; color: rgba(255,255,255,0.4); transition: all 0.4s; z-index: 1; }
        .step-dot.active { background: rgba(234,179,8,0.15); border-color: #eab308; color: #eab308; }
        .step-dot.done { background: rgba(16,185,129,0.15); border-color: #10b981; color: #10b981; }
        .step-label { font-size: 0.7rem; color: rgba(255,255,255,0.3); font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; }
        .step.active .step-label { color: #eab308; }
        .step.done .step-label { color: #10b981; }

        /* Submit */
        .submit-btn { width: 100%; padding: 1rem; border: none; border-radius: 12px; background: linear-gradient(135deg, #eab308, #d97706); color: #000; font-weight: 700; font-size: 1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.6rem; transition: transform 0.2s, box-shadow 0.3s, filter 0.2s; margin-top: 2rem; font-family: inherit; }
        .submit-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(234,179,8,0.35); filter: brightness(1.1); }
        .submit-btn:active { transform: translateY(0); }
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
        <li><a href="dashboard.php"><i class="fas fa-home"></i> Vue d'ensemble</a></li>
        <li><a href="forums_list.php" class="active"><i class="fas fa-list"></i> Liste Forums</a></li>
        <li><a href="posts_list.php"><i class="fas fa-comments"></i> Liste Posts</a></li>
    </ul>
</aside>

<main class="admin-content">
    <a href="forums_list.php" class="back-link"><i class="fas fa-arrow-left"></i> Retour à la liste</a>

    <div class="form-card">
        <div class="card-title"><i class="fas fa-plus-circle"></i> Créer un Forum</div>
        <p class="card-subtitle">Remplissez les informations pour ouvrir un nouveau sujet de discussion.</p>

        <!-- Step Progress -->
        <div class="steps" id="stepsBar">
            <div class="step active" id="step1">
                <div class="step-dot active" id="dot1">1</div>
                <span class="step-label">Titre</span>
            </div>
            <div class="step" id="step2">
                <div class="step-dot" id="dot2">2</div>
                <span class="step-label">Description</span>
            </div>
            <div class="step" id="step3">
                <div class="step-dot" id="dot3">3</div>
                <span class="step-label">Cours</span>
            </div>
        </div>

        <form id="forumAddForm" action="" method="POST" novalidate>

            <!-- Titre -->
            <div class="field-group" id="fg-titre">
                <label for="titre">Titre de la discussion</label>
                <input type="text" id="titre" name="titre"
                       placeholder="Ex: Questions sur le Chapitre 3…"
                       maxlength="120"
                       oninput="validateTitre()"
                       onblur="validateTitre(true)">
                <div class="field-msg" id="titre-msg"></div>
            </div>

            <!-- Description -->
            <div class="field-group" id="fg-description" style="position:relative;">
                <label for="description">Description</label>
                <textarea id="description" name="description"
                          rows="5"
                          maxlength="1000"
                          placeholder="Décrivez le sujet de ce forum (min. 10 caractères)…"
                          onkeydown="if(/[0-9]/.test(event.key)){event.preventDefault();}"
                          oninput="validateDescription()"
                          onblur="validateDescription(true)"></textarea>
                <span class="char-counter" id="descCount">0 / 1000</span>
                <div class="field-msg" id="desc-msg"></div>
            </div>

            <!-- ID Cours -->
            <div class="field-group" id="fg-idcourse">
                <label for="IdCourse">ID du Cours lié <span style="color:rgba(255,255,255,0.3); font-weight:400; text-transform:none;">(optionnel)</span></label>
                <input type="number" id="IdCourse" name="IdCourse"
                       placeholder="Laisser à 0 si aucun"
                       value="0" min="0"
                       oninput="validateCourse()"
                       onblur="validateCourse(true)">
                <div class="field-msg" id="course-msg"></div>
            </div>

            <button type="submit" class="submit-btn" id="submitBtn" onclick="return handleSubmit(event)">
                <div class="btn-spinner"></div>
                <span class="btn-text"><i class="fas fa-plus"></i>&nbsp; Créer le Forum</span>
            </button>
        </form>
    </div>
</main>

<script>
/* ══════════════════════════════════════════════
   Professional JS Validation — Forum Add Form
   ══════════════════════════════════════════════ */

function setField(groupId, state, msg = '') {
    const g = document.getElementById(groupId);
    if (!g) return;
    g.classList.remove('field-error', 'field-success');
    if (state) g.classList.add('field-' + state);
    const m = g.querySelector('.field-msg');
    if (m) m.textContent = msg;
}

/* Block digits from being typed or pasted into the description */
function blockDigits(el) {
    const pos = el.selectionStart;
    const cleaned = el.value.replace(/[0-9]/g, '');
    if (cleaned !== el.value) {
        el.value = cleaned;
        // restore cursor position
        el.setSelectionRange(Math.max(0, pos - 1), Math.max(0, pos - 1));
    }
}

function updateStep(stepNum, state) {
    const dot = document.getElementById('dot' + stepNum);
    const step = document.getElementById('step' + stepNum);
    dot.className = 'step-dot ' + (state || '');
    step.className = 'step ' + (state || '');
    dot.textContent = state === 'done' ? '✓' : stepNum;
}

/* Titre */
function validateTitre(onBlur = false) {
    const v = document.getElementById('titre').value.trim();
    if (!v) {
        if (onBlur) setField('fg-titre', 'error', '⚠ Le titre est obligatoire.');
        else { setField('fg-titre', '', ''); updateStep(1, 'active'); }
        return false;
    }
    if (v.length < 3) {
        setField('fg-titre', 'error', `⚠ Minimum 3 caractères (actuellement ${v.length}).`);
        updateStep(1, 'active');
        return false;
    }
    if (v.length > 120) {
        setField('fg-titre', 'error', '⚠ Maximum 120 caractères dépassé.');
        return false;
    }
    setField('fg-titre', 'success', '✓ Titre valide.');
    updateStep(1, 'done');
    return true;
}

/* Description */
function validateDescription(onBlur = false) {
    const ta = document.getElementById('description');
    const v = ta.value;
    const trimmed = v.trim();

    // Char counter
    const counter = document.getElementById('descCount');
    counter.textContent = v.length + ' / 1000';
    counter.className = 'char-counter' + (v.length > 850 ? ' near' : '') + (v.length >= 1000 ? ' over' : '');

    if (!trimmed) {
        if (onBlur) setField('fg-description', 'error', '⚠ La description est obligatoire.');
        else { setField('fg-description', '', ''); updateStep(2, ''); }
        return false;
    }
    if (trimmed.length < 10) {
        setField('fg-description', 'error', `⚠ Minimum 10 caractères (actuellement ${trimmed.length}).`);
        updateStep(2, 'active');
        return false;
    }
    // Reject only numbers, allow text with or without numbers
    if (/^\d+$/.test(trimmed)) {
        setField('fg-description', 'error', '⚠ La description doit contenir du texte, pas seulement des chiffres.');
        updateStep(2, 'active');
        return false;
    }
    setField('fg-description', 'success', '✓ Description valide.');
    updateStep(2, 'done');
    return true;
}

/* Cours (optional) */
function validateCourse(onBlur = false) {
    const v = parseInt(document.getElementById('IdCourse').value) || 0;
    if (v < 0) {
        setField('fg-idcourse', 'error', '⚠ L\'ID doit être supérieur ou égal à 0.');
        return false;
    }
    setField('fg-idcourse', 'success', v === 0 ? '✓ Aucun cours lié.' : `✓ Cours #${v} sélectionné.`);
    updateStep(3, 'done');
    return true;
}

/* Submit */
function handleSubmit(e) {
    e.preventDefault();
    const t = validateTitre(true);
    const d = validateDescription(true);
    const c = validateCourse(true);
    if (!t || !d || !c) {
        showToast('Veuillez corriger les erreurs avant de continuer.', 'error');
        document.querySelector('.field-error')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return false;
    }
    const btn = document.getElementById('submitBtn');
    btn.classList.add('loading'); btn.disabled = true;
    document.getElementById('forumAddForm').submit();
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
    // Activate first step
    updateStep(1, 'active');
    validateCourse(); // initialize course field as valid (default 0)
    // Focus first field
    document.getElementById('titre')?.focus();
});
</script>
</body>
</html>
