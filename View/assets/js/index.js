// ═══════════════════════════════════════════════════════════════════
// VALIDATION DU FORMULAIRE D'AJOUT COURS (Simple)
// ═══════════════════════════════════════════════════════════════════

function validateAddCourseForm() {
    const form = document.querySelector('form[method="POST"][enctype="multipart/form-data"]');
    if (!form) return true;

    // Récupérer les champs
    const titre = form.querySelector('[name="titre"]');
    const description = form.querySelector('[name="description"]');
    const duree = form.querySelector('[name="duree"]');
    const prix = form.querySelector('[name="prix"]');
    const niveau = form.querySelector('[name="niveau"]');
    const image = form.querySelector('[name="image"]');

    let isValid = true;

    // Supprimer les anciens messages d'erreur
    form.querySelectorAll('.error-msg').forEach(el => el.remove());
    form.querySelectorAll('.form-error').forEach(el => el.classList.remove('form-error'));

    // Validation Titre
    if (titre && titre.value.trim().length < 3) {
        showError(titre, 'Titre: minimum 3 caractères requis');
        isValid = false;
    }

    // Validation Description
    if (description && description.value.trim().length < 10) {
        showError(description, 'Description: minimum 10 caractères requis');
        isValid = false;
    }

    // Validation Durée
    if (duree && (isNaN(duree.value) || duree.value < 1)) {
        showError(duree, 'Durée: nombre positif obligatoire (min 1)');
        isValid = false;
    }

    // Validation Prix
    if (prix && (isNaN(prix.value) || prix.value < 0)) {
        showError(prix, 'Prix: nombre positif ou zéro obligatoire');
        isValid = false;
    }

    // Validation Niveau
    if (niveau && niveau.value === '') {
        showError(niveau, 'Niveau: obligatoire');
        isValid = false;
    }

    // Validation Image (optionnelle mais si présente, vérifier)
    if (image && image.files.length > 0) {
        const file = image.files[0];
        const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
        const maxSize = 5 * 1024 * 1024; // 5MB (plus raisonnable que 20MB)

        if (!validTypes.includes(file.type)) {
            showError(image, 'Image: JPG, PNG, GIF ou WEBP uniquement');
            isValid = false;
        }
        if (file.size > maxSize) {
            showError(image, 'Image: taille max 5MB');
            isValid = false;
        }
    }

    return isValid;
}

// Fonction utilitaire pour afficher les erreurs
function showError(field, message) {
    field.classList.add('form-error');
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-msg';
    errorDiv.style.cssText = 'color: #ef4444; font-size: 0.8rem; margin-top: 0.3rem;';
    errorDiv.textContent = '❌ ' + message;
    field.parentNode.appendChild(errorDiv);
}

// ═══════════════════════════════════════════════════════════════════
// APERÇU D'IMAGE (Simple)
// ═══════════════════════════════════════════════════════════════════

function initImagePreview() {
    const imageInput = document.querySelector('[name="image"]');
    if (!imageInput) return;

    // Supprimer l'ancien event listener pour éviter les doublons
    const newImageInput = imageInput.cloneNode(true);
    imageInput.parentNode.replaceChild(newImageInput, imageInput);

    newImageInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            // Vérifier le type et la taille avant l'aperçu
            const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                alert('Format non supporté. Utilisez JPG, PNG, GIF ou WEBP.');
                this.value = '';
                return;
            }
            if (file.size > 5 * 1024 * 1024) {
                alert('Fichier trop volumineux. Maximum 5MB.');
                this.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                let preview = document.getElementById('imagePreview');
                if (!preview) {
                    const container = document.createElement('div');
                    container.id = 'previewContainer';
                    container.style.marginTop = '1rem';
                    container.style.position = 'relative';
                    container.style.display = 'inline-block';
                    
                    preview = document.createElement('img');
                    preview.id = 'imagePreview';
                    preview.style.maxWidth = '200px';
                    preview.style.maxHeight = '200px';
                    preview.style.borderRadius = '8px';
                    preview.style.border = '2px solid var(--accent)';
                    preview.style.padding = '4px';
                    
                    const removeBtn = document.createElement('button');
                    removeBtn.textContent = '✖ Supprimer';
                    removeBtn.style.cssText = 'display: block; margin-top: 0.5rem; padding: 0.3rem 0.8rem; background: rgba(239,68,68,0.2); border: 1px solid #ef4444; color: #ef4444; border-radius: 6px; cursor: pointer; font-size: 0.8rem;';
                    removeBtn.onclick = function() {
                        newImageInput.value = '';
                        preview.src = '';
                        container.remove();
                    };
                    
                    container.appendChild(preview);
                    container.appendChild(removeBtn);
                    newImageInput.parentNode.appendChild(container);
                }
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });
}

// ═══════════════════════════════════════════════════════════════════
// FILTRE DE RECHERCHE SIMPLE (course_list.php)
// ═══════════════════════════════════════════════════════════════════

function initSearchFilter() {
    // Chercher une barre de recherche existante ou en créer une
    let searchInput = document.querySelector('#searchInput, .search-bar input, [name="search"]');
    
    if (!searchInput && document.querySelector('table')) {
        // Créer une barre de recherche automatiquement si elle n'existe pas
        const searchContainer = document.createElement('div');
        searchContainer.style.marginBottom = '1rem';
        searchContainer.style.maxWidth = '300px';
        
        searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.placeholder = '🔍 Rechercher un cours...';
        searchInput.style.cssText = 'width: 100%; padding: 0.75rem 1rem; background: rgba(0,0,0,0.4); border: 1px solid var(--glass-border); border-radius: 50px; color: var(--text-main);';
        
        searchContainer.appendChild(searchInput);
        const tableContainer = document.querySelector('.glass-card, .table-container');
        if (tableContainer) {
            tableContainer.insertBefore(searchContainer, tableContainer.firstChild);
        }
    }
    
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('table tbody tr');
            let visibleCount = 0;
            
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const isVisible = text.includes(searchTerm);
                row.style.display = isVisible ? '' : 'none';
                if (isVisible) visibleCount++;
            });
            
            // Afficher un message si aucun résultat
            let noResultMsg = document.querySelector('#noResultMsg');
            if (visibleCount === 0 && tableRows.length > 0) {
                if (!noResultMsg) {
                    noResultMsg = document.createElement('tr');
                    noResultMsg.id = 'noResultMsg';
                    noResultMsg.innerHTML = '<td colspan="100%" style="text-align: center; padding: 2rem;">📚 Aucun cours trouvé</td>';
                    document.querySelector('table tbody').appendChild(noResultMsg);
                }
            } else if (noResultMsg) {
                noResultMsg.remove();
            }
        });
    }
}

// ═══════════════════════════════════════════════════════════════════
// CONFIRMATION AVANT SUPPRESSION (Simple)
// ═══════════════════════════════════════════════════════════════════

function initDeleteConfirmation() {
    // Pour les liens de suppression
    document.querySelectorAll('a[href*="delete"], a[href*="supprimer"]').forEach(link => {
        link.addEventListener('click', function(e) {
            if (!confirm('⚠️ Êtes-vous sûr de vouloir supprimer ce cours ?\n\nCette action est irréversible.')) {
                e.preventDefault();
            }
        });
    });
    
    // Pour les formulaires de suppression
    document.querySelectorAll('form[onsubmit*="confirm"], form button[type="submit"]').forEach(btn => {
        const form = btn.closest('form');
        if (form && form.querySelector('input[name="action"][value="delete"], input[name="delete"]')) {
            form.addEventListener('submit', function(e) {
                if (!confirm('⚠️ Êtes-vous sûr de vouloir supprimer ce cours ?\n\nCette action est irréversible.')) {
                    e.preventDefault();
                }
            });
        }
    });
    
    // Pour les boutons avec classe btn-danger ou data-delete
    document.querySelectorAll('.btn-danger, [data-delete="true"], [data-delete-course]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm('⚠️ Êtes-vous sûr de vouloir supprimer ce cours ?')) {
                e.preventDefault();
            }
        });
    });
}

// ═══════════════════════════════════════════════════════════════════
// INITIALISATION DE TOUTES LES FONCTIONNALITÉS
// ═══════════════════════════════════════════════════════════════════

// Attendre que le DOM soit complètement chargé
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Validation du formulaire d'ajout
    const addCourseForm = document.querySelector('form[method="POST"][enctype="multipart/form-data"]');
    if (addCourseForm) {
        addCourseForm.addEventListener('submit', function(e) {
            if (!validateAddCourseForm()) {
                e.preventDefault();
                // Scroll vers la première erreur
                const firstError = document.querySelector('.form-error');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    }
    
    // 2. Aperçu d'image
    initImagePreview();
    
    // 3. Filtre de recherche
    initSearchFilter();
    
    // 4. Confirmation de suppression
    initDeleteConfirmation();
    
    // 5. Auto-disparition des messages de succès/erreur
    const alerts = document.querySelectorAll('.alert-success, .alert-error, .alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
    
    console.log('✅ Contrôles de saisie activés avec succès !');
});

// ═══════════════════════════════════════════════════════════════════
// STYLES CSS DYNAMIQUES POUR LES ERREURS
// ═══════════════════════════════════════════════════════════════════

// Ajouter les styles automatiquement
const style = document.createElement('style');
style.textContent = `
    .form-error {
        border-color: #ef4444 !important;
        box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2) !important;
    }
    .error-msg {
        animation: slideDown 0.3s ease;
    }
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .alert-success, .alert-error, .alert {
        animation: slideIn 0.3s ease;
        cursor: pointer;
    }
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
`;
document.head.appendChild(style);