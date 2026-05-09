<?php
require_once __DIR__ . '/../../../Controller/CourseController.php';

$baseUrl = '/gestioncours';
$pageTitle = 'BackOffice - Modifier un cours';
$controller = new CourseController();
$errors = [];
$message = '';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$course = $controller->getById($id);

if ($course === null) {
    http_response_code(404);
    echo 'Cours introuvable.';
    exit;
}

$data = $course;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = array_merge($data, $_POST);
    $result = $controller->update($id, $data);
    $message = $result['message'];
    $errors = $result['errors'];

    if ($result['success']) {
        header('Location: ' . $baseUrl . '/View/BackOffice/course/list.php');
        exit;
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<section>
    <h2>Modifier le cours #<?= $id ?></h2>
    <p><a href="<?= $baseUrl ?>/View/BackOffice/course/list.php">Retour à la liste</a></p>
    <?php if ($message !== ''): ?><p><?= htmlspecialchars($message) ?></p><?php endif; ?>

    <!-- id="courseForm" active la validation JS de index.js -->
    <form method="post" id="courseForm">
        <label>Titre</label><br>
        <input type="text" name="titre" value="<?= htmlspecialchars((string)$data['titre']) ?>" required><br>
        <small><?= htmlspecialchars($errors['titre'] ?? '') ?></small><br>

        <label>Description</label><br>
        <textarea name="description" required><?= htmlspecialchars((string)$data['description']) ?></textarea><br>
        <small><?= htmlspecialchars($errors['description'] ?? '') ?></small><br>

        <label>Niveau</label><br>
        <select name="niveau">
            <option value="debutant" <?= $data['niveau'] === 'debutant' ? 'selected' : '' ?>>Débutant</option>
            <option value="intermediaire" <?= $data['niveau'] === 'intermediaire' ? 'selected' : '' ?>>Intermédiaire</option>
            <option value="avance" <?= $data['niveau'] === 'avance' ? 'selected' : '' ?>>Avancé</option>
        </select><br>

        <label>Durée (heures)</label><br>
        <input type="number" min="1" name="duree" value="<?= htmlspecialchars((string)$data['duree']) ?>" required><br>
        <small><?= htmlspecialchars($errors['duree'] ?? '') ?></small><br>

        <label>Statut</label><br>
        <select name="statut">
            <option value="brouillon" <?= $data['statut'] === 'brouillon' ? 'selected' : '' ?>>Brouillon</option>
            <option value="publie" <?= $data['statut'] === 'publie' ? 'selected' : '' ?>>Publié</option>
            <option value="archive" <?= $data['statut'] === 'archive' ? 'selected' : '' ?>>Archivé</option>
        </select><br>

        <label>Langue</label><br>
        <input type="text" name="langue" value="<?= htmlspecialchars((string)$data['langue']) ?>" required><br>

        <label>Prix</label><br>
        <input type="number" step="0.01" min="0" name="prix" value="<?= htmlspecialchars((string)$data['prix']) ?>" required><br>

        <label>Image (URL)</label><br>
        <input type="url" name="image" value="<?= htmlspecialchars((string)($data['image'] ?? '')) ?>"><br>

        <label>Objectifs</label><br>
        <textarea name="objectifs"><?= htmlspecialchars((string)($data['objectifs'] ?? '')) ?></textarea><br>

        <label>Prérequis</label><br>
        <textarea name="prerequis"><?= htmlspecialchars((string)($data['prerequis'] ?? '')) ?></textarea><br><br>

        <button type="submit">Mettre à jour</button>
    </form>
</section>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
