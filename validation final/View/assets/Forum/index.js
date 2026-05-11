document.addEventListener('DOMContentLoaded', () => {
    // 1. Scroll Reveal Animations (Intersection Observer)
    const revealElements = document.querySelectorAll('.reveal');
    // threshold:0 + rootMargin positif = déclenche dès qu'un pixel entre dans le viewport
    const revealOptions = { threshold: 0, rootMargin: "0px 0px 0px 0px" };
    const revealOnScroll = new IntersectionObserver(function(entries, observer) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
                observer.unobserve(entry.target);
            }
        });
    }, revealOptions);
    revealElements.forEach(el => revealOnScroll.observe(el));

    // Observer aussi chaque class-card individuellement pour éviter
    // que les cartes cachées derrière une grande section ne s'affichent pas
    document.querySelectorAll('.class-card').forEach(card => {
        card.style.opacity = '1';
        card.style.transform = 'none';
    });

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


//  Form Validation — Forum (Add / Update)
function validateForum(form) {
    const titre       = (form.titre       ? form.titre.value.trim()       : '');
    const description = (form.description ? form.description.value.trim() : '');
    const idCourse    = (form.idCourse    ? form.idCourse.value.trim()    : '0');

    if (titre.length < 5) {
        showValidationError(form, "Le titre du forum doit contenir au moins 5 caractères.");
        return false;
    }
    if (description.length < 10) {
        showValidationError(form, "La description doit contenir au moins 10 caractères.");
        return false;
    }

    const lettersOnly = /^[a-zA-ZÀ-ÿ\s.,;:!'"?()\-–—]+$/;
    if (!lettersOnly.test(description)) {
        showValidationError(form, "La description ne doit contenir que des lettres (pas de chiffres).");
        return false;
    }

    if (idCourse !== '' && idCourse !== '0' && isNaN(idCourse)) {
        showValidationError(form, "L'ID du cours doit être un identifiant numérique valide.");
        return false;
    }
    return true;
}


//  Form Validation — Post 
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

//  Form Validation — Post Update (standalone)

function validatePostUpdate(form) {
    const contenu = (form.contenu ? form.contenu.value.trim() : '');
    if (contenu.length < 5) {
        showValidationError(form, "Le message est trop court. Minimum 5 caractères requis.");
        return false;
    }
    return true;
}

window.blockDigits = function(el) {
    const pos = el.selectionStart;
    const cleaned = el.value.replace(/[0-9]/g, '');
    if (cleaned !== el.value) {
        el.value = cleaned;
        if(el.setSelectionRange) {
            el.setSelectionRange(Math.max(0, pos - 1), Math.max(0, pos - 1));
        }
    }
}


//  Shared inline error display
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