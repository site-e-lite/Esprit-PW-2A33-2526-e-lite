<?php
require_once __DIR__ . '/../../../Controller/CourseController.php';

$baseUrl = '/gestioncours';
$pageTitle = 'BackOffice - Liste des cours';
$controller = new CourseController();
$message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $result = $controller->delete((int)$_POST['delete_id']);
    $message = $result['message'];
    $errors = $result['errors'];
}

$courses = $controller->listAll();

require_once __DIR__ . '/../../includes/header.php';
?>

<section>
    <h2>Liste des cours</h2>
    <p><a href="<?= $baseUrl ?>/View/BackOffice/course/add.php">Ajouter un cours</a></p>

    <?php if ($message !== ''): ?>
        <p><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <table border="1" cellpadding="8" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>Titre</th>
                <th>Niveau</th>
                <th>Durée</th>
                <th>Statut</th>
                <th>Prix</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($courses as $course): ?>
                <tr>
                    <td><?= (int)$course['idCourse'] ?></td>
                    <td><?= htmlspecialchars($course['titre']) ?></td>
                    <td><?= htmlspecialchars($course['niveau']) ?></td>
                    <td><?= (int)$course['duree'] ?> h</td>
                    <td><?= htmlspecialchars($course['statut']) ?></td>
                    <td><?= number_format((float)$course['prix'], 2, ',', ' ') ?> TND</td>
                    <td>
                        <a href="<?= $baseUrl ?>/View/BackOffice/course/edit.php?id=<?= (int)$course['idCourse'] ?>">Modifier</a>
                        |
                        <a href="<?= $baseUrl ?>/View/FrontOffice/course/show.php?id=<?= (int)$course['idCourse'] ?>">Voir</a>
                        |
                        <form method="post" style="display:inline;" onsubmit="return confirm('Supprimer ce cours ?');">
                            <input type="hidden" name="delete_id" value="<?= (int)$course['idCourse'] ?>">
                            <button type="submit">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
