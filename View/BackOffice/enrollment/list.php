<?php
require_once __DIR__ . '/../../../Controller/EnrollmentController.php';

$baseUrl = '/gestioncours';
$pageTitle = 'BackOffice - Liste des inscriptions';
$controller = new EnrollmentController();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $result = $controller->delete((int)$_POST['delete_id']);
    $message = $result['message'];
}

$enrollments = $controller->listAll();

require_once __DIR__ . '/../../includes/header.php';
?>
<section>
    <h2>Liste des inscriptions</h2>
    <p><a href="<?= $baseUrl ?>/View/BackOffice/enrollment/add.php">Ajouter une inscription</a></p>
    <?php if ($message !== ''): ?><p><?= htmlspecialchars($message) ?></p><?php endif; ?>

    <table border="1" cellpadding="8" cellspacing="0" width="100%">
        <thead>
        <tr>
            <th>ID</th><th>User</th><th>Cours</th><th>Niveau</th><th>Progression</th><th>Statut</th><th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($enrollments as $enrollment): ?>
            <tr>
                <td><?= (int)$enrollment['idEnrollment'] ?></td>
                <td><?= (int)$enrollment['idUser'] ?></td>
                <td><?= (int)$enrollment['idCourse'] ?></td>
                <td><?= htmlspecialchars($enrollment['niveauInitial']) ?></td>
                <td><?= (int)$enrollment['progression'] ?>%</td>
                <td><?= htmlspecialchars($enrollment['statut']) ?></td>
                <td>
                    <a href="<?= $baseUrl ?>/View/BackOffice/enrollment/edit.php?id=<?= (int)$enrollment['idEnrollment'] ?>">Modifier</a>
                    |
                    <form method="post" style="display:inline;" onsubmit="return confirm('Supprimer cette inscription ?');">
                        <input type="hidden" name="delete_id" value="<?= (int)$enrollment['idEnrollment'] ?>">
                        <button type="submit">Supprimer</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
