<?php
require_once __DIR__ . '/../../../Controller/SupportCourseController.php';
require_once __DIR__ . '/../../../Controller/CourseController.php';

$baseUrl = '/gestioncours';
$pageTitle = 'BackOffice - Liste des supports';
$controller = new SupportCourseController();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $result = $controller->delete((int)$_POST['delete_id']);
    $message = $result['message'];
}

$supports = $controller->listAll();

require_once __DIR__ . '/../../includes/header.php';
?>
<section>
    <h2>Liste des supports</h2>
    <p><a href="<?= $baseUrl ?>/View/BackOffice/support_course/add.php">Ajouter un support</a></p>
    <?php if ($message !== ''): ?><p><?= htmlspecialchars($message) ?></p><?php endif; ?>

    <table border="1" cellpadding="8" cellspacing="0" width="100%">
        <thead><tr><th>ID</th><th>Titre</th><th>Type</th><th>URL</th><th>ID Cours</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($supports as $support): ?>
            <tr>
                <td><?= (int)$support['idSupport'] ?></td>
                <td><?= htmlspecialchars($support['titre']) ?></td>
                <td><?= htmlspecialchars($support['type']) ?></td>
                <td><a href="<?= htmlspecialchars($support['url']) ?>" target="_blank">Lien</a></td>
                <td><?= (int)$support['idCourse'] ?></td>
                <td>
                    <a href="<?= $baseUrl ?>/View/BackOffice/support_course/edit.php?id=<?= (int)$support['idSupport'] ?>">Modifier</a>
                    |
                    <form method="post" style="display:inline;" onsubmit="return confirm('Supprimer ce support ?');">
                        <input type="hidden" name="delete_id" value="<?= (int)$support['idSupport'] ?>">
                        <button type="submit">Supprimer</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
