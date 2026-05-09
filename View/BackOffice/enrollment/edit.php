<?php
require_once __DIR__ . '/../../../Controller/EnrollmentController.php';
require_once __DIR__ . '/../../../Controller/CourseController.php';

$baseUrl = '/gestioncours';
$pageTitle = 'BackOffice - Modifier une inscription';
$controller = new EnrollmentController();
$courseController = new CourseController();
$courses = $courseController->listAll();
$errors = [];
$message = '';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$enrollment = $controller->getById($id);

if ($enrollment === null) {
    http_response_code(404);
    echo 'Inscription introuvable.';
    exit;
}

$data = $enrollment;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = array_merge($data, $_POST);
    $result = $controller->update($id, $data);
    $errors = $result['errors'];
    $message = $result['message'];

    if ($result['success']) {
        header('Location: ' . $baseUrl . '/View/BackOffice/enrollment/list.php');
        exit;
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>
<section>
    <h2>Modifier l'inscription #<?= $id ?></h2>
    <p><a href="<?= $baseUrl ?>/View/BackOffice/enrollment/list.php">Retour à la liste</a></p>
    <?php if ($message !== ''): ?><p><?= htmlspecialchars($message) ?></p><?php endif; ?>

    <form method="post">
        <label>ID Utilisateur</label><br>
        <input type="number" name="idUser" min="1" value="<?= htmlspecialchars((string)$data['idUser']) ?>" required><br>

        <label>Cours</label><br>
        <select name="idCourse" required>
            <?php foreach ($courses as $course): ?>
                <option value="<?= (int)$course['idCourse'] ?>" <?= (string)$data['idCourse'] === (string)$course['idCourse'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($course['titre']) ?>
                </option>
            <?php endforeach; ?>
        </select><br>

        <label>Niveau initial</label><br>
        <select name="niveauInitial">
            <option value="debutant" <?= $data['niveauInitial'] === 'debutant' ? 'selected' : '' ?>>Débutant</option>
            <option value="intermediaire" <?= $data['niveauInitial'] === 'intermediaire' ? 'selected' : '' ?>>Intermédiaire</option>
            <option value="avance" <?= $data['niveauInitial'] === 'avance' ? 'selected' : '' ?>>Avancé</option>
        </select><br>

        <label>Objectif personnel</label><br>
        <textarea name="objectifPersonnel" required><?= htmlspecialchars((string)$data['objectifPersonnel']) ?></textarea><br>

        <label>Engagement (%)</label><br>
        <input type="number" name="engagement" min="1" max="100" value="<?= htmlspecialchars((string)$data['engagement']) ?>" required><br>

        <label>Mode d'accès</label><br>
        <select name="modeAcces">
            <option value="gratuit" <?= $data['modeAcces'] === 'gratuit' ? 'selected' : '' ?>>Gratuit</option>
            <option value="payant" <?= $data['modeAcces'] === 'payant' ? 'selected' : '' ?>>Payant</option>
        </select><br>

        <label>Progression (%)</label><br>
        <input type="number" name="progression" min="0" max="100" value="<?= htmlspecialchars((string)$data['progression']) ?>" required><br>

        <label>Dernière activité</label><br>
        <input type="date" name="derniereActivite" value="<?= htmlspecialchars((string)($data['derniereActivite'] ?? '')) ?>"><br>

        <label>Temps total passé (min)</label><br>
        <input type="number" name="tempsTotalPasse" min="0" value="<?= htmlspecialchars((string)$data['tempsTotalPasse']) ?>" required><br>

        <label>Statut</label><br>
        <select name="statut">
            <option value="actif" <?= $data['statut'] === 'actif' ? 'selected' : '' ?>>Actif</option>
            <option value="termine" <?= $data['statut'] === 'termine' ? 'selected' : '' ?>>Terminé</option>
            <option value="abandonne" <?= $data['statut'] === 'abandonne' ? 'selected' : '' ?>>Abandonné</option>
        </select><br>

        <label>Note finale</label><br>
        <input type="number" step="0.01" min="0" max="100" name="noteFinale" value="<?= htmlspecialchars((string)($data['noteFinale'] ?? '')) ?>"><br>

        <label>
            <input type="checkbox" name="certificatObtenu" value="1" <?= !empty($data['certificatObtenu']) ? 'checked' : '' ?>>
            Certificat obtenu
        </label><br><br>

        <button type="submit">Mettre à jour</button>
    </form>
</section>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
