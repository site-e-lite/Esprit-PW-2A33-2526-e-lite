document.addEventListener('DOMContentLoaded', () => {
    // Fait apparaître les éléments au scroll.
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

    // Met en évidence la réponse choisie et affiche le retour.
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

    // Ferme les fenêtres modales quand on clique sur le fond.
    const overlay = document.getElementById('modalOverlay');
    if (overlay) {
        overlay.addEventListener('click', function() {
            document.querySelectorAll('.modal.active').forEach(modal => closeModal(modal.id));
        });
    }

    // Défilement doux vers les ancres de la page.
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

function normalizeValue(control) {
    // Petit helper pour éviter de répéter les trim partout.
    return control ? control.value.trim() : '';
}

function markField(form, fieldId, valid, message) {
    // On applique ici le retour visuel du champ, sans mélanger la logique métier.
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
    if (msg) {
        msg.textContent = message || '';
    }
}

function validateRequiredText(form, fieldId, label, minLength = 1) {
    // Validation simple pour les champs texte obligatoires.
    const control = form.querySelector('[name="' + fieldId + '"]');
    const value = normalizeValue(control);

    if (value === '') {
        markField(form, fieldId, false, label + ' : champ obligatoire.');
        return false;
    }

    if (value.length < minLength) {
        markField(form, fieldId, false, label + ' : minimum ' + minLength + ' caractères.');
        return false;
    }

    markField(form, fieldId, true, label + ' valide.');
    return true;
}

function validateIntegerField(form, fieldId, label, minValue = null, maxValue = null) {
    // Les champs numériques du back-office passent par cette vérification.
    const control = form.querySelector('[name="' + fieldId + '"]');
    const value = normalizeValue(control);
    const numberValue = Number(value);

    if (value === '') {
        markField(form, fieldId, false, label + ' : champ obligatoire.');
        return false;
    }

    if (!Number.isInteger(numberValue)) {
        markField(form, fieldId, false, label + ' : entier obligatoire.');
        return false;
    }

    if (minValue !== null && numberValue < minValue) {
        markField(form, fieldId, false, label + ' : valeur minimale ' + minValue + '.');
        return false;
    }

    if (maxValue !== null && numberValue > maxValue) {
        markField(form, fieldId, false, label + ' : valeur maximale ' + maxValue + '.');
        return false;
    }

    markField(form, fieldId, true, label + ' valide.');
    return true;
}

function validateFloatField(form, fieldId, label, minValue = null, maxValue = null) {
    const control = form.querySelector('[name="' + fieldId + '"]');
    const value = normalizeValue(control);
    const numberValue = Number(value);

    if (value === '' || Number.isNaN(numberValue)) {
        markField(form, fieldId, false, label + ' : nombre valide obligatoire.');
        return false;
    }

    if (minValue !== null && numberValue < minValue) {
        markField(form, fieldId, false, label + ' : valeur minimale ' + minValue + '.');
        return false;
    }

    if (maxValue !== null && numberValue > maxValue) {
        markField(form, fieldId, false, label + ' : valeur maximale ' + maxValue + '.');
        return false;
    }

    markField(form, fieldId, true, label + ' valide.');
    return true;
}

function validateSelectField(form, fieldId, label, allowedValues = null) {
    // On verrouille les listes déroulantes sur les valeurs autorisées.
    const control = form.querySelector('[name="' + fieldId + '"]');
    const value = normalizeValue(control);

    if (value === '') {
        markField(form, fieldId, false, label + ' : champ obligatoire.');
        return false;
    }

    if (Array.isArray(allowedValues) && allowedValues.length > 0 && !allowedValues.includes(value)) {
        markField(form, fieldId, false, label + ' : valeur invalide.');
        return false;
    }

    markField(form, fieldId, true, label + ' valide.');
    return true;
}

function getQuestionResponseInputs(form) {
    return Array.from(form.querySelectorAll('[name="reponses[]"]'));
}

function createQuestionResponseRow(form, value = '') {
    const row = document.createElement('div');
    row.className = 'response-row';

    const input = document.createElement('input');
    input.type = 'text';
    input.name = 'reponses[]';
    input.placeholder = 'Réponse ' + (getQuestionResponseInputs(form).length + 1);
    input.value = value;

    const removeButton = document.createElement('button');
    removeButton.type = 'button';
    removeButton.className = 'remove-response-btn';
    removeButton.innerHTML = '<i class="fas fa-times"></i>';

    row.appendChild(input);
    row.appendChild(removeButton);
    return row;
}

function updateQuestionResponseControls(form) {
    const responseInputs = getQuestionResponseInputs(form);
    const removeButtons = form.querySelectorAll('.remove-response-btn');
    const canRemove = responseInputs.length > 2;

    removeButtons.forEach(function(button) {
        button.disabled = !canRemove;
    });
}

function ensureQuestionResponseRows(form, minimumCount = 2) {
    const container = form.querySelector('#responsesContainer');
    if (!container) {
        return;
    }

    const currentInputs = getQuestionResponseInputs(form);
    if (currentInputs.length === 0) {
        for (let index = 0; index < minimumCount; index++) {
            container.appendChild(createQuestionResponseRow(form));
        }
    }

    updateQuestionResponseControls(form);
}

function bindQuestionResponseBuilder(form) {
    const addButton = form.querySelector('#addResponseBtn');
    const container = form.querySelector('#responsesContainer');
    if (!container) {
        return;
    }

    if (addButton) {
        addButton.addEventListener('click', function() {
            container.appendChild(createQuestionResponseRow(form));
            updateQuestionResponseControls(form);
        });
    }

    // Délégation d'événement pour les boutons de suppression (existants ET dynamiques)
    container.addEventListener('click', function(event) {
        if (event.target.closest('.remove-response-btn')) {
            const button = event.target.closest('.remove-response-btn');
            const row = button.closest('.response-row');
            const inputs = getQuestionResponseInputs(form);
            
            // Permet la suppression si plus de 2 réponses
            if (inputs.length > 2 && row) {
                row.remove();
                updateQuestionResponseControls(form);
            }
        }
    });

    container.addEventListener('input', function() {
        updateQuestionResponseControls(form);
    });

    updateQuestionResponseControls(form);
}

window.ensureQuestionResponseRows = ensureQuestionResponseRows;

function validateQuestionChoices(form) {
    // Une QCU doit rester cohérente: au moins deux réponses visibles et remplies.
    const type = normalizeValue(form.querySelector('[name="type"]'));
    if (type !== 'QCU') {
        return true;
    }

    const responseInputs = getQuestionResponseInputs(form);
    const responses = responseInputs
        .map(function(control) {
            return normalizeValue(control);
        })
        .filter(function(value) {
            return value !== '';
        });

    if (responseInputs.length < 2 || responses.length !== responseInputs.length) {
        markField(form, 'responsesContainer', false, 'QCU : ajoutez au moins 2 réponses et remplissez tous les champs.');
        return false;
    }

    markField(form, 'responsesContainer', true, 'Réponses QCU valides.');
    return true;
}

function validateForum(form) {
    // Vérifie les champs principaux du forum avant envoi.
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

function validatePost(form) {
    // Contrôle le contenu et les IDs si on ajoute un message.
    const contenu = (form.contenu ? form.contenu.value.trim() : '');

    const actionInput = form.querySelector('input[name="action"]');
    const actionValue = actionInput ? actionInput.value : '';

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

function validatePostUpdate(form) {
    const contenu = (form.contenu ? form.contenu.value.trim() : '');
    if (contenu.length < 5) {
        showValidationError(form, "Le message est trop court. Minimum 5 caractères requis.");
        return false;
    }
    return true;
}

function showValidationError(form, message) {
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
    // Validation complète du quiz avant l'envoi au serveur.
    let valid = true;

    valid = validateRequiredText(form, 'titre', 'Titre') && valid;
    valid = validateIntegerField(form, 'duree', 'Durée', 1) && valid;
    valid = validateIntegerField(form, 'seuilReussite', 'Seuil de réussite', 40, 100) && valid;
    valid = validateSelectField(form, 'niveau', 'Niveau', ['Débutant', 'Intermédiaire', 'Avancé']) && valid;
    valid = validateSelectField(form, 'statut', 'Statut', ['Actif', 'Inactif', 'Brouillon']) && valid;
    valid = validateIntegerField(form, 'idCourse', 'ID Course', 1) && valid;

    if (!valid) {
        showValidationError(form, 'Merci de corriger les champs en rouge.');
    }
    return valid;
}

function validateQuestionForm(form) {
    // Même logique pour les questions, avec la contrainte spécifique du QCU.
    let valid = true;

    valid = validateRequiredText(form, 'enonce', 'Énoncé') && valid;
    valid = validateSelectField(form, 'type', 'Type', ['QCU', 'Ouverte']) && valid;
    valid = validateRequiredText(form, 'bonneReponse', 'Bonne réponse') && valid;
    valid = validateIntegerField(form, 'note', 'Note', 1) && valid;
    valid = validateQuestionChoices(form) && valid;

    if (!valid) {
        showValidationError(form, 'Merci de corriger les champs en rouge.');
    }
    return valid;
}

function validateQuizAttempt(form) {
    // Pendant le passage du quiz, on bloque l'envoi si une question n'a pas de réponse.
    const questionBlocks = form.querySelectorAll('.question-card');
    let valid = true;

    questionBlocks.forEach(function(block) {
        const radios = block.querySelectorAll('input[type="radio"]');
        let hasAnswer = false;

        if (radios.length > 0) {
            hasAnswer = Array.from(radios).some(function(radio) {
                return radio.checked;
            });
        } else {
            const textAnswer = block.querySelector('input[type="text"]');
            hasAnswer = textAnswer ? textAnswer.value.trim() !== '' : true;
        }

        block.classList.remove('field-error');
        if (!hasAnswer) {
            block.classList.add('field-error');
            valid = false;
        }
    });

    if (!valid) {
        showValidationError(form, 'Veuillez répondre à toutes les questions avant d’envoyer.');
    }

    return valid;
}

document.addEventListener('DOMContentLoaded', () => {
    // On branche les formulaires présents dans la page seulement.
    const quizAddForm = document.getElementById('quizAddForm');
    const quizUpdateForm = document.getElementById('quizUpdateForm');
    const questionAddForm = document.getElementById('questionAddForm');
    const questionUpdateForm = document.getElementById('questionUpdateForm');
    const quizAttemptForm = document.querySelector('.quiz-page form');

    if (quizAddForm) {
        quizAddForm.addEventListener('submit', function(event) {
            const customValid = validateQuizForm(quizAddForm);
            if (!customValid) {
                event.preventDefault();
            }
        });
    }

    const quizGenerateForm = document.getElementById('quizGenerateForm');
    if (quizGenerateForm) {
        quizGenerateForm.addEventListener('submit', function(event) {
            const customValid = validateQuizForm(quizGenerateForm);
            if (!customValid) {
                event.preventDefault();
            }
        });
    }

    if (quizUpdateForm) {
        quizUpdateForm.addEventListener('submit', function(event) {
            const customValid = validateQuizForm(quizUpdateForm);
            const selectedQuestions = quizUpdateForm.querySelectorAll('input[name="questionIds[]"]:checked');
            const statutField = quizUpdateForm.querySelector('[name="statut"]');
            const questionMsg = document.getElementById('questionIds-msg');
            const statutMsg = document.getElementById('statut-msg');
            const noQuestionsButActive = selectedQuestions.length === 0 && statutField && normalizeValue(statutField) === 'Actif';

            if (statutMsg) {
                statutMsg.textContent = '';
            }
            if (questionMsg) {
                questionMsg.textContent = '';
            }

            if (noQuestionsButActive) {
                const statutGroup = document.getElementById('fg-statut');
                const questionGroup = document.getElementById('fg-questionIds');
                if (statutGroup) statutGroup.classList.add('field-error');
                if (questionGroup) questionGroup.classList.add('field-error');
                if (statutMsg) statutMsg.textContent = 'Un quiz sans question ne peut pas être Actif.';
                if (questionMsg) questionMsg.textContent = 'Sélectionnez au moins une question ou passez le quiz en Inactif.';
                if (statutField) statutField.focus();
            }

            if (!customValid || noQuestionsButActive) {
                event.preventDefault();
            }
        });
    }

    if (questionAddForm) {
        bindQuestionResponseBuilder(questionAddForm);
        if (normalizeValue(questionAddForm.querySelector('[name="type"]')) === 'QCU') {
            ensureQuestionResponseRows(questionAddForm);
        }
        questionAddForm.addEventListener('submit', function(event) {
            const customValid = validateQuestionForm(questionAddForm);
            if (!customValid) {
                event.preventDefault();
            }
        });
    }

    if (questionUpdateForm) {
        bindQuestionResponseBuilder(questionUpdateForm);
        if (normalizeValue(questionUpdateForm.querySelector('[name="type"]')) === 'QCU') {
            ensureQuestionResponseRows(questionUpdateForm);
        }
        // Validate on page load to show any data inconsistencies
        validateQuestionForm(questionUpdateForm);
        questionUpdateForm.addEventListener('submit', function(event) {
            const customValid = validateQuestionForm(questionUpdateForm);
            if (!customValid) {
                event.preventDefault();
            }
        });
    }

    if (quizAttemptForm && quizAttemptForm.closest('.quiz-page')) {
        quizAttemptForm.addEventListener('submit', function(event) {
            if (!validateQuizAttempt(quizAttemptForm)) {
                event.preventDefault();
            }
        });
    }

    // Slider fallback: ensure output displays and clamp value within expected range
    document.querySelectorAll('input[type="range"]').forEach(function(range) {
        const output = range.nextElementSibling && range.nextElementSibling.tagName === 'OUTPUT' ? range.nextElementSibling : null;
        const min = 1; const max = 40;
        if (output) output.textContent = range.value + ' h/semaine';
        range.addEventListener('input', function() {
            let val = Number(range.value);
            if (Number.isNaN(val)) val = min;
            if (val < min) val = min;
            if (val > max) val = max;
            range.value = val;
            if (output) output.textContent = val + ' h/semaine';
        });
    });

    // Filter quiz questions by niveau: show only questions matching the quiz level
    const niveauSelect = document.querySelector('select[name="niveau"]');
    if (niveauSelect && (document.getElementById('quizAddForm') || document.getElementById('quizUpdateForm'))) {
        function filterQuestionsByNiveau() {
            const selectedNiveau = niveauSelect.value.toLowerCase().trim();
            const questionItems = document.querySelectorAll('.question-item');
            let visibleCount = 0;

            questionItems.forEach(function(item) {
                const itemNiveau = (item.getAttribute('data-niveau') || '').toLowerCase().trim();

                // Afficher si le niveau correspond ou si pas de niveau spécifié (colonne vide) ou niveau vide
                const matches = selectedNiveau === '' || 
                               itemNiveau === selectedNiveau || 
                               itemNiveau === '';
                
                item.style.display = matches ? '' : 'none';
                if (matches) visibleCount++;
            });

            // Afficher message s'il n'y a pas de questions disponibles
            const picker = document.querySelector('.question-picker');
            if (picker && selectedNiveau !== '') {
                let emptyMsg = picker.querySelector('.no-questions-msg');
                if (visibleCount === 0) {
                    if (!emptyMsg) {
                        emptyMsg = document.createElement('div');
                        emptyMsg.className = 'no-questions-msg';
                        emptyMsg.style.cssText = 'padding:1rem; text-align:center; color:rgba(255,255,255,0.4); font-size:0.9rem; border-top:1px solid rgba(255,255,255,0.1);';
                        emptyMsg.textContent = 'Aucune question du niveau "' + niveauSelect.selectedOptions[0].textContent + '" disponible.';
                        picker.appendChild(emptyMsg);
                    }
                } else if (emptyMsg) {
                    emptyMsg.remove();
                }
            } else if (picker) {
                let emptyMsg = picker.querySelector('.no-questions-msg');
                if (emptyMsg) emptyMsg.remove();
            }
        }

        niveauSelect.addEventListener('change', filterQuestionsByNiveau);
        // Initialiser le filtrage au chargement (pas de filtrage si niveau non sélectionné)
        filterQuestionsByNiveau();
    }
});