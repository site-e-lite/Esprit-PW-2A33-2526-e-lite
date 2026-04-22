<div class="student-dashboard">
    <h1><i class="fas fa-graduation-cap"></i> Bienvenue, <?= htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']) ?> !</h1>
    <p>Votre espace d'apprentissage éco-digital.</p>
    <hr>
    <?php
    $user = \User::findById($_SESSION['user_id']);
    include __DIR__ . '/../profile/edit.php';
    ?>
</div>
