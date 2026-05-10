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

// ══════════════════════════════════════════
//  Form Validation — Enrollment (S'inscrire)
// ══════════════════════════════════════════
(function () {
    const form = document.getElementById('enrollmentForm');
    if (!form) return;

    // ── Field rules ──────────────────────────────────────────────
    const rules = {
        idUser: {
            validate: v => /^\d+$/.test(v) && parseInt(v) >= 1,
            message: "L'ID utilisateur doit être un nombre entier ≥ 1."
        },
        idCourse: {
            validate: v => v !== '',
            message: "Veuillez choisir un cours."
        },
        objectifPersonnel: {
            validate: v => v.trim().length >= 5,
            message: "L'objectif personnel doit contenir au moins 5 caractères."
        },
        engagement: {
            validate: v => /^\d+$/.test(v) && parseInt(v) >= 1 && parseInt(v) <= 100,
            message: "L'engagement doit être un nombre entre 1 et 100."
        }
    };

    // ── Helpers ──────────────────────────────────────────────────
    function getField(name) {
        return form.querySelector(`[name="${name}"]`);
    }

    function setFieldState(field, valid) {
        if (!field) return;
        field.style.borderColor = valid ? '#22c55e' : '#ef4444';
        field.style.outline = 'none';
    }

    function setError(name, message) {
        const el = document.getElementById(`error-${name}`);
        if (!el) return;
        el.textContent = message;
        el.style.display = message ? 'block' : 'none';
    }

    function clearError(name) {
        setError(name, '');
        const field = getField(name);
        if (field) field.style.borderColor = '';
    }

    // ── Live validation on blur ───────────────────────────────────
    Object.keys(rules).forEach(name => {
        const field = getField(name);
        if (!field) return;
        field.addEventListener('blur', () => {
            const valid = rules[name].validate(field.value);
            setFieldState(field, valid);
            setError(name, valid ? '' : rules[name].message);
        });
        field.addEventListener('input', () => {
            if (field.style.borderColor) {
                const valid = rules[name].validate(field.value);
                setFieldState(field, valid);
                setError(name, valid ? '' : rules[name].message);
            }
        });
    });

    // ── Submit handler ────────────────────────────────────────────
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        let allValid = true;

        Object.keys(rules).forEach(name => {
            const field = getField(name);
            if (!field) return;
            const valid = rules[name].validate(field.value);
            setFieldState(field, valid);
            setError(name, valid ? '' : rules[name].message);
            if (!valid) allValid = false;
        });

        if (!allValid) return; // stop — errors shown inline

        // All valid → demo alert, no real submit
        alert('Inscription réussie !');
    });
})();

// ══════════════════════════════════════════
//  Form Validation — Course (Ajouter / Modifier)
//  NOTE: Validation is handled inline in add.php / edit.php
//  These helpers are kept for external use only.
// ══════════════════════════════════════════

function isValidTitre(titre) {
    const t = titre.trim();
    if (t.length === 0) return { valid: false, message: 'Le titre est obligatoire.' };
    if (t.length < 3)   return { valid: false, message: 'Le titre doit contenir au moins 3 caractères.' };
    if (t.length > 100) return { valid: false, message: 'Le titre ne doit pas dépasser 100 caractères.' };
    return { valid: true, message: '' };
}

function isValidDescription(desc) {
    const d = desc.trim();
    if (d.length === 0) return { valid: false, message: 'La description est obligatoire.' };
    if (d.length < 20)  return { valid: false, message: 'La description doit contenir au moins 20 caractères.' };
    return { valid: true, message: '' };
}

function isValidDuree(duree) {
    const val = Number(duree);
    if (String(duree).trim() === '') return { valid: false, message: 'La durée est obligatoire.' };
    if (!Number.isInteger(val))      return { valid: false, message: 'La durée doit être un nombre entier.' };
    if (val < 1)                     return { valid: false, message: 'La durée doit être au moins 1 heure.' };
    if (val > 500)                   return { valid: false, message: 'La durée ne peut pas dépasser 500 heures.' };
    return { valid: true, message: '' };
}

function isValidPrix(prix) {
    const val = parseFloat(prix);
    if (String(prix).trim() === '') return { valid: false, message: 'Le prix est obligatoire.' };
    if (isNaN(val))                 return { valid: false, message: 'Le prix doit être un nombre.' };
    if (val < 0)                    return { valid: false, message: 'Le prix ne peut pas être négatif.' };
    if (val > 9999.99)              return { valid: false, message: 'Le prix ne peut pas dépasser 9999.99 TND.' };
    return { valid: true, message: '' };
}

function isValidLangue(langue) {
    const l = langue.trim();
    if (l.length === 0)                   return { valid: false, message: 'La langue est obligatoire.' };
    if (!/^[a-zA-ZÀ-ÿ\s]+$/.test(l))    return { valid: false, message: 'La langue ne doit contenir que des lettres.' };
    return { valid: true, message: '' };
}

function isValidImageUrl(url) {
    const u = url.trim();
    if (u.length === 0)                return { valid: true,  message: '' };
    if (!/^https?:\/\/.+/.test(u))    return { valid: false, message: "L'URL doit commencer par http:// ou https://" };
    return { valid: true, message: '' };
}

function isValidNiveau(niveau) {
    const allowed = ['debutant', 'intermediaire', 'avance'];
    if (!allowed.includes(niveau)) return { valid: false, message: "Le niveau doit être débutant, intermédiaire ou avancé." };
    return { valid: true, message: '' };
}
