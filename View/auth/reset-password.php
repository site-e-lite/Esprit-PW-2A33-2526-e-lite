<div class="auth-container glass-card">
    <h2>Nouveau mot de passe</h2>
    <?php if (isset($error)) echo "<div class='alert error'>$error</div>"; ?>
    <form method="POST" action="/reset-password">
        <div class="form-group"><label>Nouveau mot de passe (min. 6)</label><input type="password" name="password" required></div>
        <div class="form-group"><label>Confirmer</label><input type="password" name="confirm_password" required></div>
        <button type="submit" class="btn-primary">Réinitialiser</button>
    </form>
</div>
