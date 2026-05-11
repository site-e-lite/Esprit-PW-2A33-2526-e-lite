/* ================================================================
   index.js — Quiz module shared JS
   NOTE: QCU response builder (ensureMinRows, bindQuestionResponseBuilder)
   is handled directly in question_add.php and question_update.php via
   an inline IIFE. Do NOT redefine window.ensureQuestionResponseRows here.
================================================================ */

document.addEventListener('DOMContentLoaded', () => {
    // ── Reveal on scroll ──────────────────────────────────────────
    const revealElements = document.querySelectorAll('.reveal');
    const revealOptions = { threshold: 0.15, rootMargin: '0px 0px -50px 0px' };
    const revealOnScroll = new IntersectionObserver(function(entries, observer) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
                observer.unobserve(entry.target);
            }
        });
    }, revealOptions);
    revealElements.forEach(el => revealOnScroll.observe(el));

    // ── Quiz option click (front-office demo) ─────────────────────
    const quizOptions = document.querySelectorAll('.quiz-option');
    const feedback = document.getElementById('quizFeedback');
    quizOptions.forEach(option => {
        option.addEventListener('click', function() {
            quizOptions.forEach(opt => {
                opt.style.backgroundColor = 'rgba(0,0,0,0.4)';
                opt.style.borderColor = 'rgba(255,255,255,0.08)';
            });
            this.style.backgroundColor = 'rgba(212,175,55,0.2)';
            this.style.borderColor = '#d4af37';
            if (feedback) {
                feedback.style.display = 'block';
                feedback.animate([
                    { opacity: 0, transform: 'translateY(-10px)' },
                    { opacity: 1, transform: 'translateY(0)' }
                ], { duration: 400, fill: 'forwards' });
            }
        });
    });

    // ── Modal overlay close ───────────────────────────────────────
    const overlay = document.getElementById('modalOverlay');
    if (overlay) {
        overlay.addEventListener('click', function() {
            document.querySelectorAll('.modal.active').forEach(modal => closeModal(modal.id));
        });
    }

    // ── Smooth scroll ─────────────────────────────────────────────
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth' }); }
        });
    });

    // ── Form submit validation ────────────────────────────────────
    const quizAddForm      = document.getElementById('quizAddForm');
    const quizUpdateForm   = document.getElementById('quizUpdateForm');
    const quizGenerateForm = document.getElementById('quizGenerateForm');
    const questionAddForm  = document.getElementById('questionAddForm');
    const questionUpdateForm = document.getElementById('questionUpdateForm');
    const quizAttemptForm  = document.querySelector('.quiz-page form');

    if (quizAddForm) {
        quizAddForm.addEventListener('submit', function(e) {
            if (!validateQuizForm(quizAddForm)) e.preventDefault();
        });
    }

    if (quizGenerateForm) {
        quizGenerateForm.addEventListener('submit', function(e) {
            if (!validateQuizForm(quizGenerateForm)) e.preventDefault();
        });
    }

    if (quizUpdateForm) {
        quizUpdateForm.addEventListener('submit', function(e) {
            const customValid = validateQuizForm(quizUpdateForm);
            const selectedQuestions = quizUpdateForm.querySelectorAll('input[name="questionIds[]"]:checked');
            const statutField = quizUpdateForm.querySelector('[name="statut"]');
            const questionMsg = document.getElementById('questionIds-msg');
            const statutMsg   = document.getElementById('statut-msg');
            const noQuestionsButActive = selectedQuestions.length === 0 &&
                                         statutField && normalizeValue(statutField) === 'Actif';

            if (statutMsg)   statutMsg.textContent   = '';
            if (questionMsg) questionMsg.textContent = '';

            if (noQuestionsButActive) {
                const statutGroup   = document.getElementById('fg-statut');
                const questionGroup = document.getElementById('fg-questionIds');
                if (statutGroup)   statutGroup.classList.add('field-error');
                if (questionGroup) questionGroup.classList.add('field-error');
                if (statutMsg)   statutMsg.textContent   = 'Un quiz sans question ne peut pas être Actif.';
                if (questionMsg) questionMsg.textContent = 'Sélectionnez au moins une question ou passez le quiz en Inactif.';
                if (statutField) statutField.focus();
            }

            if (!customValid || noQuestionsButActive) e.preventDefault();
        });
    }

    // question forms: only attach submit validation — QCU builder is in the page IIFE
    if (questionAddForm) {
        questionAddForm.addEventListener('submit', function(e) {
            if (!validateQuestionForm(questionAddForm)) e.preventDefault();
        });
    }

    if (questionUpdateForm) {
        questionUpdateForm.addEventListener('submit', function(e) {
            if (!validateQuestionForm(questionUpdateForm)) e.preventDefault();
        });
    }

    if (quizAttemptForm && quizAttemptForm.closest('.quiz-page')) {
        quizAttemptForm.addEventListener('submit', function(e) {
            if (!validateQuizAttempt(quizAttemptForm)) e.preventDefault();
        });
    }

    // ── Range slider ──────────────────────────────────────────────
    document.querySelectorAll('input[type="range"]').forEach(function(range) {
        const output = range.nextElementSibling?.tagName === 'OUTPUT' ? range.nextElementSibling : null;
        const min = 1, max = 40;
        if (output) output.textContent = range.value + ' h/semaine';
        range.addEventListener('input', function() {
            let val = Math.min(max, Math.max(min, Number(range.value) || min));
            range.value = val;
            if (output) output.textContent = val + ' h/semaine';
        });
    });

    // ── Quiz/question niveau filter (quiz_add / quiz_update) ──────
    const niveauSelect = document.querySelector('select[name="niveau"]');
    if (niveauSelect && (document.getElementById('quizAddForm') || document.getElementById('quizUpdateForm'))) {
        function normalizeNiveau(str) {
            return (str || '').toLowerCase().trim()
                .normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        }

        function filterQuestionsByNiveau() {
            const sel = normalizeNiveau(niveauSelect.value);
            let visible = 0;
            document.querySelectorAll('.question-item').forEach(function(item) {
                const n = normalizeNiveau(item.getAttribute('data-niveau'));
                const show = sel === '' || n === sel || n === '';
                item.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            const picker = document.querySelector('.question-picker');
            if (!picker) return;
            let msg = picker.querySelector('.no-questions-msg');
            if (sel !== '' && visible === 0) {
                if (!msg) {
                    msg = document.createElement('div');
                    msg.className = 'no-questions-msg';
                    msg.style.cssText = 'padding:1rem;text-align:center;color:rgba(255,255,255,0.4);font-size:0.9rem;border-top:1px solid rgba(255,255,255,0.1);';
                    picker.appendChild(msg);
                }
                msg.textContent = 'Aucune question du niveau "' +
                    (niveauSelect.selectedOptions[0]?.textContent || niveauSelect.value) + '" disponible.';
            } else if (msg) {
                msg.remove();
            }
        }

        niveauSelect.addEventListener('change', filterQuestionsByNiveau);
        filterQuestionsByNiveau();
    }
});

/* ── Modal helpers ──────────────────────────────────────────────── */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    const overlay = document.getElementById('modalOverlay');
    if (modal && overlay) {
        overlay.classList.add('active');
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    const overlay = document.getElementById('modalOverlay');
    if (modal && overlay) {
        modal.classList.remove('active');
        overlay.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
}

function openGenericModal(title) {
    const titleEl = document.getElementById('genericModalTitle');
    if (titleEl) titleEl.innerHTML = title;
    openModal('modalGeneric');
}

/* ── Field helpers ──────────────────────────────────────────────── */
function normalizeValue(control) {
    return control ? control.value.trim() : '';
}

function markField(form, fieldId, valid, message) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    const group = field.closest('.field-group');
    if (!group) return;
    group.classList.remove('field-error', 'field-success');
    if (valid === false) group.classList.add('field-error');
    else if (valid === true) group.classList.add('field-success');
    const msg = group.querySelector('.field-msg');
    if (msg) msg.textContent = message || '';
}

function showValidationError(form, message) {
    const existing = form.querySelector('.js-validation-error');
    if (existing) existing.remove();
    const banner = document.createElement('div');
    banner.className = 'js-validation-error';
    banner.style.cssText = 'background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.4);border-radius:10px;padding:0.8rem 1.2rem;color:#ef4444;font-size:0.85rem;font-weight:600;margin-bottom:1rem;display:flex;align-items:center;gap:0.6rem;';
    banner.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + message;
    form.insertBefore(banner, form.firstChild);
    setTimeout(() => banner.remove(), 4000);
}

function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    if (!toast) return;
    toast.className = 'toast' + (type === 'error' ? ' error' : '');
    toast.querySelector('span').textContent = message;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3500);
}

/* ── Field validators ───────────────────────────────────────────── */
function validateRequiredText(form, fieldId, label, minLength = 1) {
    const value = normalizeValue(form.querySelector('[name="' + fieldId + '"]'));
    if (!value) { markField(form, fieldId, false, label + ' : champ obligatoire.'); return false; }
    if (value.length < minLength) { markField(form, fieldId, false, label + ' : minimum ' + minLength + ' caractères.'); return false; }
    markField(form, fieldId, true, label + ' valide.');
    return true;
}

function validateIntegerField(form, fieldId, label, minValue = null, maxValue = null) {
    const value = normalizeValue(form.querySelector('[name="' + fieldId + '"]'));
    const n = Number(value);
    if (!value) { markField(form, fieldId, false, label + ' : champ obligatoire.'); return false; }
    if (!Number.isInteger(n)) { markField(form, fieldId, false, label + ' : entier obligatoire.'); return false; }
    if (minValue !== null && n < minValue) { markField(form, fieldId, false, label + ' : valeur minimale ' + minValue + '.'); return false; }
    if (maxValue !== null && n > maxValue) { markField(form, fieldId, false, label + ' : valeur maximale ' + maxValue + '.'); return false; }
    markField(form, fieldId, true, label + ' valide.');
    return true;
}

function validateSelectField(form, fieldId, label, allowedValues = null) {
    const value = normalizeValue(form.querySelector('[name="' + fieldId + '"]'));
    if (!value) { markField(form, fieldId, false, label + ' : champ obligatoire.'); return false; }
    if (Array.isArray(allowedValues) && allowedValues.length && !allowedValues.includes(value)) {
        markField(form, fieldId, false, label + ' : valeur invalide.'); return false;
    }
    markField(form, fieldId, true, label + ' valide.');
    return true;
}

/* ── QCU choices validator (used by validateQuestionForm) ───────── */
function getQuestionResponseInputs(form) {
    return Array.from(form.querySelectorAll('[name="reponses[]"]'));
}

function validateQuestionChoices(form) {
    const type = normalizeValue(form.querySelector('[name="type"]'));
    if (type !== 'QCU') return true;
    const inputs = getQuestionResponseInputs(form);
    const filled = inputs.filter(i => normalizeValue(i) !== '');
    if (inputs.length < 2 || filled.length !== inputs.length) {
        markField(form, 'responsesContainer', false, 'QCU : ajoutez au moins 2 réponses et remplissez tous les champs.');
        return false;
    }
    markField(form, 'responsesContainer', true, 'Réponses QCU valides.');
    return true;
}

/* ── Form-level validators ──────────────────────────────────────── */
function validateQuizForm(form) {
    let v = true;
    v = validateRequiredText(form, 'titre', 'Titre') && v;
    v = validateIntegerField(form, 'duree', 'Durée', 1) && v;
    v = validateIntegerField(form, 'seuilReussite', 'Seuil de réussite', 40, 100) && v;
    v = validateSelectField(form, 'niveau', 'Niveau', ['Débutant', 'Intermédiaire', 'Avancé']) && v;
    v = validateSelectField(form, 'statut', 'Statut', ['Actif', 'Inactif', 'Brouillon']) && v;
    // idCourse est un <select> — on valide juste qu'une valeur est choisie
    v = validateSelectField(form, 'idCourse', 'Cours associé') && v;

    if (!v) {
        showValidationError(form, 'Merci de corriger les champs en rouge.');
        // Scroll vers le premier champ en erreur
        const firstError = form.querySelector('.field-error');
        if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    return v;
}

function validateQuestionForm(form) {
    let v = true;
    v = validateRequiredText(form, 'enonce', 'Énoncé') && v;
    v = validateSelectField(form, 'type', 'Type', ['QCU', 'Ouverte']) && v;
    v = validateRequiredText(form, 'bonneReponse', 'Bonne réponse') && v;
    v = validateIntegerField(form, 'note', 'Note', 1) && v;
    v = validateQuestionChoices(form) && v;
    if (!v) showValidationError(form, 'Merci de corriger les champs en rouge.');
    return v;
}

function validateQuizAttempt(form) {
    let v = true;
    form.querySelectorAll('.question-card').forEach(function(block) {
        const radios = block.querySelectorAll('input[type="radio"]');
        const hasAnswer = radios.length > 0
            ? Array.from(radios).some(r => r.checked)
            : (block.querySelector('input[type="text"]')?.value.trim() !== '');
        block.classList.toggle('field-error', !hasAnswer);
        if (!hasAnswer) v = false;
    });
    if (!v) showValidationError(form, "Veuillez répondre à toutes les questions avant d'envoyer.");
    return v;
}

/* ── Forum / Post validators (used by forum pages) ──────────────── */
function validateForum(form) {
    const titre       = form.titre?.value.trim() || '';
    const description = form.description?.value.trim() || '';
    const idCourse    = form.idCourse?.value.trim() || '0';
    if (titre.length < 3)       { showValidationError(form, 'Le titre du forum doit contenir au moins 3 caractères.'); return false; }
    if (description.length < 10){ showValidationError(form, 'La description doit contenir au moins 10 caractères.'); return false; }
    if (idCourse !== '' && idCourse !== '0' && isNaN(idCourse)) {
        showValidationError(form, "L'ID du cours doit être un identifiant numérique valide."); return false;
    }
    return true;
}

function validatePost(form) {
    const contenu = form.contenu?.value.trim() || '';
    const action  = form.querySelector('input[name="action"]')?.value || '';
    if (action === 'add_post') {
        const idForum = form.querySelector('[name="idForum"]')?.value.trim() || '';
        const idUser  = form.querySelector('[name="idUser"]')?.value.trim()  || '';
        if (!idForum || isNaN(idForum) || parseInt(idForum) <= 0) {
            showValidationError(form, "L'ID du forum est invalide ou manquant."); return false;
        }
        if (idUser && (isNaN(idUser) || parseInt(idUser) <= 0)) {
            showValidationError(form, "L'ID utilisateur doit être un nombre valide (≥ 1)."); return false;
        }
    }
    if (contenu.length < 5) { showValidationError(form, 'Le message est trop court. Minimum 5 caractères requis.'); return false; }
    return true;
}

function validatePostUpdate(form) {
    const contenu = form.contenu?.value.trim() || '';
    if (contenu.length < 5) { showValidationError(form, 'Le message est trop court. Minimum 5 caractères requis.'); return false; }
    return true;
}

function validateAddPost(form)    { return validatePost(form); }
function validateUpdatePost(form) { return validatePostUpdate(form); }

function setValidationField(fieldId, valid, message) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    const group = field.closest('.field-group');
    if (!group) return;
    group.classList.remove('field-error', 'field-success');
    if (valid === false) group.classList.add('field-error');
    else if (valid === true) group.classList.add('field-success');
    const msg = group.querySelector('.field-msg');
    if (msg) msg.textContent = message || '';
}
