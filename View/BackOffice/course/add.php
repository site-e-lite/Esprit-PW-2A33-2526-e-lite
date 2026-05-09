<?php
require_once __DIR__ . '/../../../Controller/CourseController.php';

$baseUrl    = '/gestioncours';
$pageTitle  = 'BackOffice - Ajouter un cours';
$controller = new CourseController();
$errors     = [];
$message    = '';

$data = [
    'titre'       => '',
    'description' => '',
    'niveau'      => 'debutant',
    'duree'       => '1',
    'statut'      => 'brouillon',
    'langue'      => 'Français',
    'prix'        => '0',
    'image'       => '',
    'objectifs'   => '',
    'prerequis'   => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data   = array_merge($data, $_POST);
    $result = $controller->add($data);
    $message = $result['message'];
    $errors  = $result['errors'];

    if ($result['success']) {
        header('Location: ' . $baseUrl . '/View/BackOffice/course/list.php');
        exit;
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<style>
.erreur-js {
    display: none;
    color: #d32f2f;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}
.erreur-js.erreur {
    display: block;
}
input.erreur, textarea.erreur, select.erreur {
    border-color: #d32f2f;
    background-color: #ffebee;
}
input.valide, textarea.valide, select.valide {
    border-color: #388e3c;
    background-color: #f1f8e9;
}
</style>

<section>
    <h2>Ajouter un cours</h2>
    <p><a href="<?= $baseUrl ?>/View/BackOffice/course/list.php">Retour à la liste</a></p>
    <?php if ($message !== ''): ?><p><?= htmlspecialchars($message) ?></p><?php endif; ?>

    <form method="post" id="courseForm" novalidate>

        <label for="titre">Titre</label><br>
        <input type="text" id="titre" name="titre" value="<?= htmlspecialchars((string)$data['titre']) ?>"><br>
        <small id="titreErreur" class="erreur-js"></small><br>

        <label for="description">Description</label><br>
        <textarea id="description" name="description"><?= htmlspecialchars((string)$data['description']) ?></textarea><br>
        <small id="descriptionErreur" class="erreur-js"></small><br>

        <label for="niveau">Niveau</label><br>
        <select id="niveau" name="niveau">
            <option value="debutant"      <?= $data['niveau'] === 'debutant'      ? 'selected' : '' ?>>Débutant</option>
            <option value="intermediaire" <?= $data['niveau'] === 'intermediaire' ? 'selected' : '' ?>>Intermédiaire</option>
            <option value="avance"        <?= $data['niveau'] === 'avance'        ? 'selected' : '' ?>>Avancé</option>
        </select><br>
        <small id="niveauErreur" class="erreur-js"></small><br>

        <label for="duree">Durée (heures)</label><br>
        <input type="text" id="duree" name="duree" value="<?= htmlspecialchars((string)$data['duree']) ?>"><br>
        <small id="dureeErreur" class="erreur-js"></small><br>

        <label for="statut">Statut</label><br>
        <select id="statut" name="statut">
            <option value="brouillon" <?= $data['statut'] === 'brouillon' ? 'selected' : '' ?>>Brouillon</option>
            <option value="publie"    <?= $data['statut'] === 'publie'    ? 'selected' : '' ?>>Publié</option>
            <option value="archive"   <?= $data['statut'] === 'archive'   ? 'selected' : '' ?>>Archivé</option>
        </select><br>
        <small id="statutErreur" class="erreur-js"></small><br>

        <label for="langue">Langue</label><br>
        <input type="text" id="langue" name="langue" value="<?= htmlspecialchars((string)$data['langue']) ?>"><br>
        <small id="langueErreur" class="erreur-js"></small><br>

        <label for="prix">Prix (TND)</label><br>
        <input type="text" id="prix" name="prix" value="<?= htmlspecialchars((string)$data['prix']) ?>"><br>
        <small id="prixErreur" class="erreur-js"></small><br>

        <label for="image">Image (URL — optionnel)</label><br>
        <input type="text" id="image" name="image" value="<?= htmlspecialchars((string)$data['image']) ?>"><br>
        <small id="imageErreur" class="erreur-js"></small><br>

        <label for="objectifs">Objectifs</label><br>
        <textarea id="objectifs" name="objectifs"><?= htmlspecialchars((string)$data['objectifs']) ?></textarea><br>
        <small id="objectifsErreur" class="erreur-js"></small><br>

        <label for="prerequis">Prérequis</label><br>
        <textarea id="prerequis" name="prerequis"><?= htmlspecialchars((string)$data['prerequis']) ?></textarea><br>
        <small id="prerequisErreur" class="erreur-js"></small><br><br>

        <button type="submit">Enregistrer</button>
    </form>
</section>

<script>
function afficherErreur(fieldId, message) {
    const field = document.getElementById(fieldId);
    const errorContainer = document.getElementById(fieldId + 'Erreur');
    
    if (message) {
        field.classList.add('erreur');
        field.classList.remove('valide');
        errorContainer.textContent = message;
        errorContainer.classList.add('erreur');
    } else {
        field.classList.remove('erreur');
        field.classList.add('valide');
        errorContainer.textContent = '';
        errorContainer.classList.remove('erreur');
    }
}

function validerFormulaireComplet() {
    const titre = document.getElementById('titre').value.trim();
    const description = document.getElementById('description').value.trim();
    const duree = document.getElementById('duree').value.trim();
    const langue = document.getElementById('langue').value.trim();
    const prix = document.getElementById('prix').value.trim();
    const image = document.getElementById('image').value.trim();
    
    let isValid = true;
    
    if (!titre || titre.length === 0) {
        afficherErreur('titre', 'Le titre est requis.');
        isValid = false;
    } else if (titre.length < 3) {
        afficherErreur('titre', 'Le titre doit contenir au moins 3 caractères.');
        isValid = false;
    } else {
        afficherErreur('titre', '');
    }
    
    if (!description || description.length === 0) {
        afficherErreur('description', 'La description est requise.');
        isValid = false;
    } else if (description.length < 10) {
        afficherErreur('description', 'La description doit contenir au moins 10 caractères.');
        isValid = false;
    } else {
        afficherErreur('description', '');
    }
    
    if (!duree || duree.length === 0) {
        afficherErreur('duree', 'La durée est requise.');
        isValid = false;
    } else if (isNaN(duree) || duree <= 0 || duree > 500) {
        afficherErreur('duree', 'La durée doit être entre 1 et 500 heures.');
        isValid = false;
    } else {
        afficherErreur('duree', '');
    }
    
    if (!langue || langue.length === 0) {
        afficherErreur('langue', 'La langue est requise.');
        isValid = false;
    } else {
        afficherErreur('langue', '');
    }
    
    if (!prix || prix.length === 0) {
        afficherErreur('prix', 'Le prix est requis.');
        isValid = false;
    } else if (isNaN(prix) || prix < 0 || prix > 9999.99) {
        afficherErreur('prix', 'Le prix doit être entre 0 et 9999.99.');
        isValid = false;
    } else if (!/^\d+(\.\d{1,2})?$/.test(prix)) {
        afficherErreur('prix', 'Le prix ne peut avoir plus de 2 décimales.');
        isValid = false;
    } else {
        afficherErreur('prix', '');
    }
    
    if (image && image.length > 0) {
        try {
            new URL(image);
            afficherErreur('image', '');
        } catch (e) {
            afficherErreur('image', 'L\'URL de l\'image n\'est pas valide.');
            isValid = false;
        }
    } else {
        afficherErreur('image', '');
    }
    
    return isValid;
}

function validerFormulaireEtSoumettre(event) {
    event.preventDefault();
    
    if (validerFormulaireComplet()) {
        document.getElementById('courseForm').submit();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('courseForm');
    
    const titre = document.getElementById('titre');
    titre.addEventListener('input', function() {
        const value = this.value.trim();
        if (value.length === 0) {
            afficherErreur('titre', 'Le titre est requis.');
        } else if (value.length < 3) {
            afficherErreur('titre', 'Le titre doit contenir au moins 3 caractères.');
        } else {
            afficherErreur('titre', '');
        }
    });
    titre.addEventListener('blur', function() {
        if (this.value.trim().length === 0) {
            afficherErreur('titre', 'Le titre est requis.');
        }
    });
    
    const description = document.getElementById('description');
    description.addEventListener('input', function() {
        const value = this.value.trim();
        if (value.length === 0) {
            afficherErreur('description', 'La description est requise.');
        } else if (value.length < 10) {
            afficherErreur('description', 'La description doit contenir au moins 10 caractères.');
        } else {
            afficherErreur('description', '');
        }
    });
    description.addEventListener('blur', function() {
        if (this.value.trim().length === 0) {
            afficherErreur('description', 'La description est requise.');
        }
    });
    
    const duree = document.getElementById('duree');
    duree.addEventListener('input', function() {
        const value = this.value.trim();
        if (value.length === 0) {
            afficherErreur('duree', 'La durée est requise.');
        } else if (isNaN(value) || value <= 0 || value > 500) {
            afficherErreur('duree', 'La durée doit être entre 1 et 500 heures.');
        } else {
            afficherErreur('duree', '');
        }
    });
    duree.addEventListener('blur', function() {
        if (this.value.trim().length === 0) {
            afficherErreur('duree', 'La durée est requise.');
        }
    });
    
    const langue = document.getElementById('langue');
    langue.addEventListener('input', function() {
        const value = this.value.trim();
        if (value.length === 0) {
            afficherErreur('langue', 'La langue est requise.');
        } else {
            afficherErreur('langue', '');
        }
    });
    langue.addEventListener('blur', function() {
        if (this.value.trim().length === 0) {
            afficherErreur('langue', 'La langue est requise.');
        }
    });
    
    const prix = document.getElementById('prix');
    prix.addEventListener('input', function() {
        const value = this.value.trim();
        if (value.length === 0) {
            afficherErreur('prix', 'Le prix est requis.');
        } else if (isNaN(value) || value < 0 || value > 9999.99) {
            afficherErreur('prix', 'Le prix doit être entre 0 et 9999.99.');
        } else if (!/^\d+(\.\d{1,2})?$/.test(value)) {
            afficherErreur('prix', 'Le prix ne peut avoir plus de 2 décimales.');
        } else {
            afficherErreur('prix', '');
        }
    });
    prix.addEventListener('blur', function() {
        if (this.value.trim().length === 0) {
            afficherErreur('prix', 'Le prix est requis.');
        }
    });
    
    const image = document.getElementById('image');
    image.addEventListener('input', function() {
        const value = this.value.trim();
        if (value.length === 0) {
            afficherErreur('image', '');
        } else {
            try {
                new URL(value);
                afficherErreur('image', '');
            } catch (e) {
                afficherErreur('image', 'L\'URL de l\'image n\'est pas valide.');
            }
        }
    });
    image.addEventListener('blur', function() {
        const value = this.value.trim();
        if (value.length > 0) {
            try {
                new URL(value);
                afficherErreur('image', '');
            } catch (e) {
                afficherErreur('image', 'L\'URL de l\'image n\'est pas valide.');
            }
        }
    });
    
    form.addEventListener('submit', validerFormulaireEtSoumettre);
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
