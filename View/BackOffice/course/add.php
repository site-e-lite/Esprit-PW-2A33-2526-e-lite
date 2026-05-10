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
    $data    = array_merge($data, $_POST);
    $result  = $controller->add($data);
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
.erreur-js          { display:none; color:#d32f2f; font-size:.82rem; margin-top:.25rem; }
.erreur-js.visible  { display:block; }
input.erreur, textarea.erreur, select.erreur { border-color:#d32f2f !important; }
input.valide, textarea.valide, select.valide { border-color:#388e3c !important; }
</style>

<section>
    <h2>Ajouter un cours</h2>
    <p><a href="<?= $baseUrl ?>/View/BackOffice/course/list.php">Retour à la liste</a></p>
    <?php if ($message !== ''): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="post" id="courseForm" novalidate>

        <label for="titre">Titre</label><br>
        <input type="text" id="titre" name="titre"
               value="<?= htmlspecialchars((string)$data['titre']) ?>"><br>
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
        <input type="text" id="duree" name="duree"
               value="<?= htmlspecialchars((string)$data['duree']) ?>"><br>
        <small id="dureeErreur" class="erreur-js"></small><br>

        <label for="statut">Statut</label><br>
        <select id="statut" name="statut">
            <option value="brouillon" <?= $data['statut'] === 'brouillon' ? 'selected' : '' ?>>Brouillon</option>
            <option value="publie"    <?= $data['statut'] === 'publie'    ? 'selected' : '' ?>>Publié</option>
            <option value="archive"   <?= $data['statut'] === 'archive'   ? 'selected' : '' ?>>Archivé</option>
        </select><br>

        <label for="langue">Langue</label><br>
        <input type="text" id="langue" name="langue"
               value="<?= htmlspecialchars((string)$data['langue']) ?>"><br>
        <small id="langueErreur" class="erreur-js"></small><br>

        <label for="prix">Prix (TND)</label><br>
        <input type="text" id="prix" name="prix"
               value="<?= htmlspecialchars((string)$data['prix']) ?>"><br>
        <small id="prixErreur" class="erreur-js"></small><br>

        <label for="image">Image (URL — optionnel)</label><br>
        <input type="text" id="image" name="image"
               value="<?= htmlspecialchars((string)$data['image']) ?>"><br>
        <small id="imageErreur" class="erreur-js"></small><br>

        <label for="objectifs">Objectifs</label><br>
        <textarea id="objectifs" name="objectifs"><?= htmlspecialchars((string)$data['objectifs']) ?></textarea><br>

        <label for="prerequis">Prérequis</label><br>
        <textarea id="prerequis" name="prerequis"><?= htmlspecialchars((string)$data['prerequis']) ?></textarea><br><br>

        <button type="submit">Enregistrer</button>
    </form>
</section>

<script>
// ── Helpers ──────────────────────────────────────────────────────
function afficherErreur(id, msg) {
    var field = document.getElementById(id);
    var span  = document.getElementById(id + 'Erreur');
    if (!field) return;
    if (msg) {
        field.classList.add('erreur');
        field.classList.remove('valide');
        if (span) { span.textContent = msg; span.classList.add('visible'); }
    } else {
        field.classList.remove('erreur');
        field.classList.add('valide');
        if (span) { span.textContent = ''; span.classList.remove('visible'); }
    }
}

// ── Règles de validation ─────────────────────────────────────────
function regleTitre(v) {
    v = v.trim();
    if (!v)         return 'Le titre est obligatoire.';
    if (v.length<3) return 'Le titre doit contenir au moins 3 caractères.';
    if (v.length>100) return 'Le titre ne doit pas dépasser 100 caractères.';
    return '';
}
function regleDescription(v) {
    v = v.trim();
    if (!v)          return 'La description est obligatoire.';
    if (v.length<20) return 'La description doit contenir au moins 20 caractères.';
    return '';
}
function regleDuree(v) {
    v = v.trim();
    if (!v)                    return 'La durée est obligatoire.';
    if (!/^\d+$/.test(v))      return 'La durée doit être un nombre entier.';
    var n = parseInt(v, 10);
    if (n < 1)   return 'La durée doit être au moins 1 heure.';
    if (n > 500) return 'La durée ne peut pas dépasser 500 heures.';
    return '';
}
function reglePrix(v) {
    v = v.trim();
    if (!v)           return 'Le prix est obligatoire.';
    if (isNaN(v))     return 'Le prix doit être un nombre.';
    var n = parseFloat(v);
    if (n < 0)        return 'Le prix ne peut pas être négatif.';
    if (n > 9999.99)  return 'Le prix ne peut pas dépasser 9999.99 TND.';
    return '';
}
function regleLangue(v) {
    v = v.trim();
    if (!v)                          return 'La langue est obligatoire.';
    if (!/^[a-zA-ZÀ-ÿ\s]+$/.test(v)) return 'La langue ne doit contenir que des lettres.';
    return '';
}
function regleNiveau(v) {
    if (['debutant','intermediaire','avance'].indexOf(v) === -1)
        return 'Veuillez choisir un niveau valide.';
    return '';
}
function regleImage(v) {
    v = v.trim();
    if (!v) return '';
    if (!/^https?:\/\/.+/.test(v)) return "L'URL doit commencer par http:// ou https://";
    return '';
}

// ── Map champ → règle ────────────────────────────────────────────
var regles = {
    titre:       regleTitre,
    description: regleDescription,
    duree:       regleDuree,
    prix:        reglePrix,
    langue:      regleLangue,
    niveau:      regleNiveau,
    image:       regleImage
};

// ── Validation complète ──────────────────────────────────────────
function validerTout() {
    var ok = true;
    Object.keys(regles).forEach(function(id) {
        var field = document.getElementById(id);
        if (!field) return;
        var msg = regles[id](field.value);
        afficherErreur(id, msg);
        if (msg) ok = false;
    });
    return ok;
}

// ── Validation en temps réel ─────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    Object.keys(regles).forEach(function(id) {
        var field = document.getElementById(id);
        if (!field) return;
        field.addEventListener('input', function() {
            afficherErreur(id, regles[id](field.value));
        });
        field.addEventListener('blur', function() {
            afficherErreur(id, regles[id](field.value));
        });
    });

    // ── Submit ───────────────────────────────────────────────────
    document.getElementById('courseForm').addEventListener('submit', function(e) {
        if (!validerTout()) {
            e.preventDefault();
            var first = document.querySelector('input.erreur, textarea.erreur, select.erreur');
            if (first) { first.scrollIntoView({behavior:'smooth', block:'center'}); first.focus(); }
        }
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
