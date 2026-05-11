<div class="auth-container glass-card">
    <h2>Récupération de mot de passe</h2>
    <?php if (isset($error)) echo "<div class='alert error'>$error</div>"; ?>
    <form method="POST" action="<?= $basePath ?? '' ?>/forgot">
        <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
        <div class="form-group"><label>Votre ID utilisateur</label><input type="number" name="user_id" required></div>
        <button type="submit" class="btn-primary">Vérifier et réinitialiser</button>
    </form>
    <p><a href="<?= $basePath ?? '' ?>/login">Retour à la connexion</a></p>
</div>
