<div class="auth-container glass-card">
    <h2><i class="fas fa-key"></i> Connexion</h2>
    <?php if (isset($error)): ?>
        <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" action="/login">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" placeholder="votre@email.com" required>
        </div>
        <div class="form-group">
            <label>Mot de passe</label>
            <input type="password" name="password" placeholder="••••••" required>
        </div>
        <button type="submit" class="btn-primary" style="width:100%"><i class="fas fa-arrow-right"></i> Se connecter</button>
    </form>
    <p style="margin-top:1.5rem; text-align:center">Pas de compte ? <a href="/register" class="btn-outline" style="display:inline-block; padding:0.5rem 1rem">Inscrivez-vous</a></p>
</div>
