<?php
require_once __DIR__ . '/../../../Controller/SupportCourseController.php';
require_once __DIR__ . '/../../../Controller/CourseController.php';

$baseUrl = '/gestioncours';
$pageTitle = 'BackOffice - Modifier un support';
$controller = new SupportCourseController();
$courseController = new CourseController();
$courses = $courseController->listAll();
$errors = [];
$message = '';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$support = $controller->getById($id);

if ($support === null) {
    http_response_code(404);
    echo 'Support introuvable.';
    exit;
}

$data = $support;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = array_merge($data, $_POST);
    $result = $controller->update($id, $data);
    $errors = $result['errors'];
    $message = $result['message'];

    if ($result['success']) {
        header('Location: ' . $baseUrl . '/View/BackOffice/support_course/list.php');
        exit;
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>
<section>
    <h2>Modifier le support #<?= $id ?></h2>
    <p><a href="<?= $baseUrl ?>/View/BackOffice/support_course/list.php">Retour à la liste</a></p>
    <?php if ($message !== ''): ?><p><?= htmlspecialchars($message) ?></p><?php endif; ?>

    <form method="post">
        <label>Titre</label><br>
        <input type="text" name="titre" value="<?= htmlspecialchars((string)$data['titre']) ?>" required><br>

        <label>Type</label><br>
        <select name="type">
            <option value="pdf" <?= $data['type'] === 'pdf' ? 'selected' : '' ?>>PDF</option>
            <option value="video" <?= $data['type'] === 'video' ? 'selected' : '' ?>>Vidéo</option>
            <option value="document" <?= $data['type'] === 'document' ? 'selected' : '' ?>>Document</option>
            <option value="lien" <?= $data['type'] === 'lien' ? 'selected' : '' ?>>Lien</option>
            <option value="autre" <?= $data['type'] === 'autre' ? 'selected' : '' ?>>Autre</option>
        </select><br>

        <label>URL</label><br>
        <input type="url" name="url" value="<?= htmlspecialchars((string)$data['url']) ?>" required><br>

        <label>Description</label><br>
        <textarea name="description"><?= htmlspecialchars((string)($data['description'] ?? '')) ?></textarea><br>

        <label>Cours</label><br>
        <select name="idCourse" required>
            <?php foreach ($courses as $course): ?>
                <option value="<?= (int)$course['idCourse'] ?>" <?= (string)$data['idCourse'] === (string)$course['idCourse'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($course['titre']) ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <button type="submit">Mettre à jour</button>
    </form>
</section>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
