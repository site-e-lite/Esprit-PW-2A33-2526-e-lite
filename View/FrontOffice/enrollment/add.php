<?php
require_once __DIR__ . '/../../../Controller/EnrollmentController.php';
require_once __DIR__ . '/../../../Controller/CourseController.php';

$baseUrl = '/gestioncours';
$pageTitle = 'FrontOffice - Inscription';
$controller = new EnrollmentController();
$courseController = new CourseController();
$courses = $courseController->listPublished();
$errors = [];
$message = '';

$data = [
    'idUser' => '1',
    'idCourse' => isset($_GET['idCourse']) ? (string)((int)$_GET['idCourse']) : '',
    'niveauInitial' => 'debutant',
    'objectifPersonnel' => '',
    'engagement' => '50',
    'modeAcces' => 'gratuit',
    'progression' => '0',
    'derniereActivite' => '',
    'tempsTotalPasse' => '0',
    'statut' => 'actif',
    'noteFinale' => '',
    'certificatObtenu' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = array_merge($data, $_POST);
    $result = $controller->add($data);
    $errors = $result['errors'];
    $message = $result['message'];

    if ($result['success']) {
        header('Location: ' . $baseUrl . '/View/FrontOffice/course/index.php');
        exit;
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>
<section>
    <h2>S'inscrire à un cours</h2>
    <?php if ($message !== ''): ?><p><?= htmlspecialchars($message) ?></p><?php endif; ?>

    <form method="post" id="enrollmentForm">
        <label>ID Utilisateur</label><br>
        <input type="number" name="idUser" min="1" value="<?= htmlspecialchars((string)$data['idUser']) ?>"><br>
        <span id="error-idUser" style="color:#ef4444;font-size:0.85rem;display:none;"></span>
        <small><?= htmlspecialchars($errors['idUser'] ?? '') ?></small><br>

        <label>Cours</label><br>
        <select name="idCourse">
            <option value="">Choisir...</option>
            <?php foreach ($courses as $course): ?>
                <option value="<?= (int)$course['idCourse'] ?>" <?= (string)$data['idCourse'] === (string)$course['idCourse'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($course['titre']) ?>
                </option>
            <?php endforeach; ?>
        </select><br>
        <span id="error-idCourse" style="color:#ef4444;font-size:0.85rem;display:none;"></span>
        <small><?= htmlspecialchars($errors['idCourse'] ?? '') ?></small><br>

        <label>Niveau initial</label><br>
        <select name="niveauInitial">
            <option value="debutant">Débutant</option>
            <option value="intermediaire">Intermédiaire</option>
            <option value="avance">Avancé</option>
        </select><br>

        <label>Objectif personnel</label><br>
        <textarea name="objectifPersonnel"><?= htmlspecialchars((string)$data['objectifPersonnel']) ?></textarea><br>
        <span id="error-objectifPersonnel" style="color:#ef4444;font-size:0.85rem;display:none;"></span><br>

        <label>Engagement (%)</label><br>
        <input type="number" name="engagement" min="1" max="100" value="<?= htmlspecialchars((string)$data['engagement']) ?>"><br>
        <span id="error-engagement" style="color:#ef4444;font-size:0.85rem;display:none;"></span><br>

        <label>Mode d'accès</label><br>
        <select name="modeAcces">
            <option value="gratuit">Gratuit</option>
            <option value="payant">Payant</option>
        </select><br><br>

        <input type="hidden" name="progression" value="0">
        <input type="hidden" name="tempsTotalPasse" value="0">
        <input type="hidden" name="statut" value="actif">

        <button type="submit">S'inscrire</button>
    </form>
    <script src="<?= $baseUrl ?>/View/assets/index.js"></script>
</section>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
