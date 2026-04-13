document.addEventListener('DOMContentLoaded', () => {
    // 1. Scroll Reveal Animations (Intersection Observer)
    const revealElements = document.querySelectorAll('.reveal');
    const revealOptions = { threshold: 0.15, rootMargin: "0px 0px -50px 0px" };
    const revealOnScroll = new IntersectionObserver(function(entries, observer) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
                observer.unobserve(entry.target);
            }
        });
    }, revealOptions);
    revealElements.forEach(el => revealOnScroll.observe(el));

    // 2. Quiz Logic (Simulation IA)
    const quizOptions = document.querySelectorAll('.quiz-option');
    const feedback = document.getElementById('quizFeedback');
    quizOptions.forEach(option => {
        option.addEventListener('click', function() {
            quizOptions.forEach(opt => {
                opt.style.backgroundColor = 'rgba(0,0,0,0.4)';
                opt.style.borderColor = 'rgba(255, 255, 255, 0.08)';
            });
            this.style.backgroundColor = 'rgba(212, 175, 55, 0.2)';
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

    // ─── Safe modal overlay click-outside to close ───
    // Only bind if the overlay exists on this page
    const overlay = document.getElementById('modalOverlay');
    if (overlay) {
        overlay.addEventListener('click', function() {
            document.querySelectorAll('.modal.active').forEach(modal => closeModal(modal.id));
        });
    }

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            const target = document.querySelector(targetId);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
});

// ══════════════════════════════════════════
//  Modal Management
// ══════════════════════════════════════════
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

function openGenericModal(title, textContext) {
    const titleEl = document.getElementById('genericModalTitle');
    if (titleEl) titleEl.innerHTML = title;
    openModal('modalGeneric');
}

// ══════════════════════════════════════════
//  Form Validation — Forum (Add / Update)
// ══════════════════════════════════════════
function validateForum(form) {
    const titre       = (form.titre       ? form.titre.value.trim()       : '');
    const description = (form.description ? form.description.value.trim() : '');
    const idCourse    = (form.idCourse    ? form.idCourse.value.trim()    : '0');

    if (titre.length < 3) {
        showValidationError(form, "Le titre du forum doit contenir au moins 3 caractères.");
        return false;
    }
    if (description.length < 10) {
        showValidationError(form, "La description doit contenir au moins 10 caractères.");
        return false;
    }
    if (idCourse !== '' && idCourse !== '0' && isNaN(idCourse)) {
        showValidationError(form, "L'ID du cours doit être un identifiant numérique valide.");
        return false;
    }
    return true;
}

// ══════════════════════════════════════════
//  Form Validation — Post (Add or Update)
// ══════════════════════════════════════════
function validatePost(form) {
    const contenu = (form.contenu ? form.contenu.value.trim() : '');

    // Read the hidden <input name="action"> field (NOT form.action which is the URL)
    const actionInput = form.querySelector('input[name="action"]');
    const actionValue = actionInput ? actionInput.value : '';

    // idForum is required only for add_post
    // update_post modal only has idPost — no idForum needed
    if (actionValue === 'add_post') {
        const idForumEl = form.querySelector('[name="idForum"]');
        const idUserEl  = form.querySelector('[name="idUser"]');
        const idForum   = idForumEl ? idForumEl.value.trim() : '';
        const idUser    = idUserEl  ? idUserEl.value.trim()  : '';

        if (!idForum || isNaN(idForum) || parseInt(idForum) <= 0) {
            showValidationError(form, "L'ID du forum est invalide ou manquant.");
            return false;
        }
        if (idUser && (isNaN(idUser) || parseInt(idUser) <= 0)) {
            showValidationError(form, "L'ID utilisateur doit être un nombre valide (≥ 1).");
            return false;
        }
    }

    if (contenu.length < 5) {
        showValidationError(form, "Le message est trop court. Minimum 5 caractères requis.");
        return false;
    }
    return true;
}

// ══════════════════════════════════════════
//  Form Validation — Post Update (standalone)
// ══════════════════════════════════════════
function validatePostUpdate(form) {
    const contenu = (form.contenu ? form.contenu.value.trim() : '');
    if (contenu.length < 5) {
        showValidationError(form, "Le message est trop court. Minimum 5 caractères requis.");
        return false;
    }
    return true;
}

// ══════════════════════════════════════════
//  Shared inline error display
// ══════════════════════════════════════════
function showValidationError(form, message) {
    // Remove any existing error banner
    const existing = form.querySelector('.js-validation-error');
    if (existing) existing.remove();

    const banner = document.createElement('div');
    banner.className = 'js-validation-error';
    banner.style.cssText = [
        'background: rgba(239,68,68,0.12)',
        'border: 1px solid rgba(239,68,68,0.4)',
        'border-radius: 10px',
        'padding: 0.8rem 1.2rem',
        'color: #ef4444',
        'font-size: 0.85rem',
        'font-weight: 600',
        'margin-bottom: 1rem',
        'display: flex',
        'align-items: center',
        'gap: 0.6rem'
    ].join(';');
    banner.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + message;

    // Insert at the top of the form
    form.insertBefore(banner, form.firstChild);

    // Auto-remove after 4s
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

// ══════════════════════════════════════════
//  Form Validation — Quiz
// ══════════════════════════════════════════
function setValidationField(fieldId, valid, message) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    const group = field.closest('.field-group');
    if (!group) return;
    group.classList.remove('field-error', 'field-success');
    if (valid === false) {
        group.classList.add('field-error');
    } else if (valid === true) {
        group.classList.add('field-success');
    }
    const msg = group.querySelector('.field-msg');
    if (msg) msg.textContent = message || '';
}

function validateQuizForm(form) {
    const titre = (form.titre ? form.titre.value.trim() : '');
    const duree = parseInt(form.duree ? form.duree.value : '0', 10);
    const seuil = parseInt(form.seuilReussite ? form.seuilReussite.value : '0', 10);
    const niveau = (form.niveau ? form.niveau.value.trim() : '');
    const statut = (form.statut ? form.statut.value.trim() : '');

    let valid = true;

    if (titre === '') {
        setValidationField('titre', false, 'Le titre est obligatoire.');
        valid = false;
    } else if (titre.length < 3) {
        setValidationField('titre', false, 'Le titre doit contenir au moins 3 caractères.');
        valid = false;
    } else {
        setValidationField('titre', true, 'Titre valide.');
    }

    if (!Number.isInteger(duree) || duree <= 0) {
        setValidationField('duree', false, 'La durée doit être supérieure à 0.');
        valid = false;
    } else {
        setValidationField('duree', true, 'Durée valide.');
    }

    if (!Number.isInteger(seuil) || seuil < 0 || seuil > 100) {
        setValidationField('seuilReussite', false, 'Le seuil doit être entre 0 et 100.');
        valid = false;
    } else {
        setValidationField('seuilReussite', true, 'Seuil valide.');
    }

    if (niveau === '') {
        setValidationField('niveau', false, 'Le niveau est obligatoire.');
        valid = false;
    } else {
        setValidationField('niveau', true, 'Niveau valide.');
    }

    if (statut === '') {
        setValidationField('statut', false, 'Le statut est obligatoire.');
        valid = false;
    } else {
        setValidationField('statut', true, 'Statut valide.');
    }

    if (!valid) {
        showValidationError(form, 'Merci de corriger les champs en rouge.');
    }
    return valid;
}

// ══════════════════════════════════════════
//  Form Validation — Question
// ══════════════════════════════════════════
function validateQuestionForm(form) {
    const enonce = (form.enonce ? form.enonce.value.trim() : '');
    const type = (form.type ? form.type.value.trim() : '');
    const choixA = (form.choixA ? form.choixA.value.trim() : '');
    const choixB = (form.choixB ? form.choixB.value.trim() : '');
    const choixC = (form.choixC ? form.choixC.value.trim() : '');
    const choixD = (form.choixD ? form.choixD.value.trim() : '');
    const bonneReponse = (form.bonneReponse ? form.bonneReponse.value.trim() : '');
    const note = parseFloat(form.note ? form.note.value : '0');
    const idQuiz = parseInt(form.idQuiz ? form.idQuiz.value : '0', 10);

    let valid = true;

    if (enonce === '') {
        setValidationField('enonce', false, 'L’énoncé est obligatoire.');
        valid = false;
    } else {
        setValidationField('enonce', true, 'Énoncé valide.');
    }

    if (type === '') {
        setValidationField('type', false, 'Le type de question est obligatoire.');
        valid = false;
    } else {
        setValidationField('type', true, 'Type valide.');
    }

    if (bonneReponse === '') {
        setValidationField('bonneReponse', false, 'La bonne réponse est obligatoire.');
        valid = false;
    } else {
        setValidationField('bonneReponse', true, 'Bonne réponse valide.');
    }

    if (!(note > 0)) {
        setValidationField('note', false, 'La note doit être supérieure à 0.');
        valid = false;
    } else {
        setValidationField('note', true, 'Note valide.');
    }

    if (!Number.isInteger(idQuiz) || idQuiz <= 0) {
        setValidationField('idQuiz', false, 'L’ID du quiz doit être un nombre valide.');
        valid = false;
    } else {
        setValidationField('idQuiz', true, 'ID Quiz valide.');
    }

    if (type === 'QCM') {
        const choixRemplis = [choixA, choixB, choixC, choixD].filter(c => c !== '').length;
        if (choixRemplis < 2) {
            setValidationField('choixA', false, 'Au moins deux choix doivent être remplis pour un QCM.');
            setValidationField('choixB', false, 'Au moins deux choix doivent être remplis pour un QCM.');
            valid = false;
        }
    }

    if (!valid) {
        showValidationError(form, 'Merci de corriger les champs en rouge.');
    }
    return valid;
}